# Data Model: Build de assets do tema e agregação CSS/JS (WebPerf)

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-04

Sem content types / field storages novos. Entidades = pipeline do tema, libraries, config de performance, contrato LCP do hero.

## Entidades

### 1. Pipeline do tema (`default`)

| Propriedade | Valor |
|-------------|--------|
| Tema | `themes/custom/default` |
| Toolchain | Vite (+ PostCSS/cssnano; Sass opcional) |
| Manifest npm | `package.json` (scripts `dev`/`watch`, `build`) |
| Fontes | `src/css/**`, `src/js/**` |
| Artefatos | `dist/css/**`, `dist/js/**` (versionados) |
| Ignorado | `node_modules/` |
| Fora | `assets/images/**`, `/libraries/**` |

### 2. Libraries do tema

| Item | Detalhe |
|------|---------|
| Arquivo | `default.libraries.yml` |
| Escopo migrado | Todas as entradas CSS/JS **próprias** que hoje apontam para `assets/css` ou `assets/js` |
| Destino | Paths relativos a `dist/css/...` e `dist/js/...` |
| Intocadas | `bootstrap`, `fontawesome`, `jquery_mask` (e qualquer path `/libraries/`) |
| Exceção | `preprocess: false` só se smoke exigir (documentar) |

### 3. Configuração `system.performance`

| Chave | Valor alvo | Notas |
|-------|------------|-------|
| `css.preprocess` | `true` | Agregação CSS |
| `js.preprocess` | `true` | Agregação JS |
| `css.compress` | `true` (já) | Não alterar nesta feature |
| `js.compress` | `true` (já) | Não alterar nesta feature |
| `cache.page.max_age` | `0` (atual) | **Não** alterar nesta feature |
| Sync | `config/sync/system.performance.yml` | Via `drush cex` |
| Hook | `custom_configs_update_11090` | Idempotente |

### 4. Update hook de performance

| Item | Valor |
|------|--------|
| Função | `custom_configs_update_11090` |
| Módulo | `custom_configs` |
| Efeito | Garante preprocess CSS/JS TRUE; no-op se já true |
| Side effects | Nenhum seed de conteúdo; não mexe em max_age |

### 5. Banner hero (LCP)

| Item | Valor |
|------|--------|
| View/display | `banners` / `block_home` |
| Template | `views-view--banners--block-home.html.twig` |
| Atributos 1º slide | `loading="eager"` + `fetchpriority="high"` |
| Slides seguintes | `loading="lazy"` permitido |
| Não-candidato | Bloco `custom_banners_hero_search` (sem imagem) |

## Relacionamentos

```text
src/css|js ──Vite build──► dist/css|js
                              │
default.libraries.yml ────────┘
                              │
Drupal attach_library ────────┼──► HTML <link>/<script>
                              │
system.performance.preprocess ─┴──► arquivos agregados (prod)

custom_configs_update_11090 ──► system.performance (TRUE)

View banners block_home ──Twig──► <img> LCP (eager+high)
```

## Validação

| Regra | Origem |
|-------|--------|
| 100% libraries próprias → `dist/` | FR-006, SC-001 |
| Terceiros `/libraries/` intactos | FR-007 |
| Preprocess TRUE pós-deploy | FR-008, SC-003 |
| Hook reexecutável sem erro | FR-008, SC-008 |
| LCP hero eager+high | FR-010, SC-005 |
| Sem regressão visual óbvia | FR-012, SC-006 |
| Sem CT/storage novos | Spec Fora |
| Sem Node em produção | Assumption artefatos |

## State transitions

```text
[assets/css|js legado] --migração--> [src/ + dist/ + libraries dist]
[preprocess false] --11090/cim--> [preprocess true]
[preprocess true] --11090 re-run--> [no-op]
[src alterado] --npm run build--> [dist atualizado]
[LCP audit] --conforme--> [documentar] | --lacuna--> [Twig fix]
```
