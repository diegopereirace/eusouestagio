# Implementation Plan: Build de assets do tema e agregação CSS/JS (WebPerf)

**Branch**: `main` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo; setup executado manualmente a partir de `.specify/feature.json` → `specs/060-build-assets-webperf`)  
**Date**: 2026-11-04  
**Spec**: [spec.md](spec.md) | **Checklist**: [checklists/requirements.md](checklists/requirements.md)  
**Predecessors**: `046-breakpoints-tema`, `001-banners-busca-home`  
**Hook**: `custom_configs_update_11090` (garantir `system.performance` css/js preprocess = TRUE)  
**Input**: Especificação em `specs/060-build-assets-webperf/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar **três frentes** na mesma feature, sem passos manuais em produção:

1. **Pipeline Vite** no tema `themes/custom/default`: fontes em `src/` → artefatos minificados em `dist/`; scripts `dev`/`watch` e `build`; `default.libraries.yml` apontando só para `dist/` nas libraries **próprias** do tema (terceiros em `/libraries/` intactos).
2. **Agregação Drupal**: `hook_update_N` idempotente + `drush cex` para `system.performance` com `css.preprocess` / `js.preprocess` = TRUE.
3. **LCP hero**: auditar (e corrigir se faltar) `loading="eager"` + `fetchpriority="high"` no primeiro slide do banner da home; documentar conformidade.

Produção **não** exige Node: `dist/` versionado; `node_modules/` ignorado.

## Technical Context

| Item | Valor |
|------|--------|
| Language/Version | PHP 8.3+ / 8.4, Drupal 11, Twig 3; Node.js LTS (dev/CI build) |
| Primary Dependencies | Vite + plugins SCSS/PostCSS/cssnano; tema `default`; módulo `custom_configs`. **Sem** Composer novo |
| Storage | Config `system.performance` (preprocess). Sem CT/field novos. Artefatos estáticos `dist/` no tema |
| Testing | Manual via [quickstart.md](quickstart.md); smoke home + 1 página com library migrada; sem PHPUnit dedicado |
| Target Platform | Todas as páginas que anexam libraries do tema; prioridade home anônima; desktop e mobile |
| Performance | Minificação por arquivo (Vite) + agregação nativa Drupal; LCP hero already-eager |
| Constraints | Sem `core/`/`vendor/`; sem Node obrigatório em produção; não minificar `/libraries/`; política deploy `cim`→`updb`→`cim`→`cr`; política 057 N/A (sem File seed) |
| Scale/Scope | ~31 CSS + ~12 JS do tema → pipeline multi-entry; 1 hook `11090`; 1 YAML sync; `.gitignore` theme/root; quickstart |

**Estado atual verificado (2026-11-04):**

- Último hook: **`custom_configs_update_11089`** → próximo livre **`11090`**.
- Tema **sem** `package.json` / Vite / Webpack / Gulp; CSS plano (zero `.scss`).
- `default.libraries.yml`: dezenas de libraries próprias sob `assets/css/**` e `assets/js/**`; Bootstrap/FA/Mask via `/libraries/`.
- `config/sync/system.performance.yml`: `css.preprocess: false`, `js.preprocess: false` (`compress` já true; `cache.page.max_age: 0`).
- Home LCP: `views-view--banners--block-home.html.twig` já usa `fetchpriority="high" loading="eager"` no `loop.first`; slides seguintes `lazy`.
- `custom_banners_hero_search`: formulário de busca **sem** imagem — não é candidato LCP de imagem.
- Root `.gitignore`: **não** ignora `node_modules` / `dist` ainda.
- `mattpocock/skills`: busca preliminar na spec sem skill madura WebPerf/Vite; revalidar na implementação (FR-014).

## Constitution Check

Não há `.specify/memory/constitution.md`. Gates: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + `estagio-fluxo-dev.mdc` + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist; zero `[NEEDS CLARIFICATION]` |
| Sem `core/` / `vendor/` | PASS | só tema `default` + `custom_configs.install` + `config/sync` |
| Reuso / sem storages novos | PASS | nenhuma entity/field nova |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | `11090` + cex `system.performance.yml` |
| Seeds Entity API / 057 | N/A | sem seed de File/conteúdo editorial |
| Clean URLs | PASS | inalterado |
| Performance | PASS | objetivo da feature (minify + aggregate + LCP) |
| Contrib first / custom mínimo | PASS | agregação nativa core; Vite só no tema |
| PRD guardião | PASS (cirúrgico na implementação) | config estrutural `system.performance` — confirmar menção breve em § performance/deploy se o time exigir |
| Reinício cache | A executar na implementação | `cim`/`updb`/`cr` após preprocess |

**Post-design**: gates mantidos. Trade-offs em [research.md](research.md) e Complexity Tracking. Sem violação injustificada.

## Architecture

```text
themes/custom/default/
├── package.json / vite.config.* / .gitignore
├── src/
│   ├── css/**          # fontes (migradas de assets/css; .css ou .scss)
│   └── js/**           # fontes (migradas de assets/js)
├── dist/
│   ├── css/**          # artefatos minificados (versionados)
│   └── js/**           # artefatos minificados (versionados)
├── assets/images/**    # intacto (fora do pipeline)
├── default.libraries.yml  → paths dist/css|js (próprias)
└── templates/...       # LCP hero audit (já conforme)

config/sync/system.performance.yml
  css.preprocess: true
  js.preprocess: true

custom_configs_update_11090
  → ConfigFactory load system.performance
  → set preprocess TRUE se necessário (idempotente)
```

## Design Decisions

1. **Vite multi-entry** (não SPA bundle único): um entry por arquivo CSS/JS do tema, espelhando a árvore `src/` → `dist/`, para preservar libraries Drupal granulares e anexação condicional por página.
2. **Pastas**: `src/css`, `src/js` → `dist/css`, `dist/js`. Remover paths canônicos antigos de `assets/css` e `assets/js` das libraries após migração (pode manter stub vazio ou deletar fontes antigas após copy para `src/`).
3. **SCSS opcional**: pipeline aceita `.scss`; migração inicial pode copiar `.css` como está (mínimo = minify + watch). Sem reescrita obrigatória em partials.
4. **Versionar `dist/`**; ignorar `node_modules/` (root e/ou tema). Produção sem Node.
5. **Terceiros** (`bootstrap`, `fontawesome`, `jquery_mask`): libraries inalteradas; fora do Vite.
6. **Hook `11090`**: só garante preprocess TRUE; não mexe em `cache.page.max_age` nem outras chaves sem pedido.
7. **Origem**: `npm run build` → atualizar libraries → `drush updb` → `drush cex` → `drush cr`.
8. **Destino**: `git pull` → `cim` → `updb` → `cim` → `cr` (preprocess via sync e/ou hook).
9. **LCP**: auditar home; se já conforme → documentar no quickstart; se lacuna → corrigir Twig. Não expandir para todas as imagens.
10. **Exceções `preprocess: false`**: só se smoke quebrar um JS frágil; documentar na library (mínimo). Default = permitir agregação.
11. **FR-014**: na implementação, `npx skills find` (webperf/vite); registrar resultado no quickstart; não bloquear entrega.
12. **Fora**: WebP/AVIF, critical CSS, service worker, Node no servidor, redesign visual.

## Project Structure

### Documentation (this feature)

```text
specs/060-build-assets-webperf/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── theme-build-pipeline.md
│   ├── system-performance.md
│   ├── lcp-hero-home.md
│   └── deploy-build-assets-webperf.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
  package.json
  package-lock.json
  vite.config.mjs (ou .ts)
  .gitignore                    # node_modules/
  src/css/**                    # migrado de assets/css
  src/js/**                     # migrado de assets/js
  dist/css/**                   # gerado (commitado)
  dist/js/**                    # gerado (commitado)
  default.libraries.yml         # paths → dist/
  # assets/css, assets/js removidos ou deixados de fora das libraries
  templates/views/views-view--banners--block-home.html.twig  # audit LCP

modules/custom/custom_configs/
  custom_configs.install        # update_11090

config/sync/
  system.performance.yml        # preprocess true (via cex)

.gitignore (raiz)               # **/node_modules/ (e regra dist só se optarmos por NÃO versionar — default: versionar dist do tema)

PRD.md                          # menção cirúrgica opcional (performance / build tema)
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. `/speckit-tasks` → tasks ordenadas
3. Implementação: Vite + migração fontes → libraries → build → 11090 → cex → LCP audit → quickstart FR-014 → cim/updb/cr
4. Validação [quickstart.md](quickstart.md)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Multi-entry Vite (muitos outputs) vs bundle único | Drupal libraries são granulares; bundle único quebraria anexação por página e aumentaria CSS morto | Um único `app.css`/`app.js` site-wide |
| Versionar `dist/` no Git | Produção sem Node; alinhado à Assumption da spec | Build obrigatório no servidor / CI-only artifacts |
| Não alterar `cache.page.max_age: 0` nesta feature | Escopo = preprocess + minify + LCP; page cache é decisão operacional separada | Ligar page cache junto (risco de surpresa em staging) |
| Google Fonts `@import` em `style.css` permanece | Fora do escopo (otimização de fontes/imagens = features futuras) | Self-host + preload nesta feature |
| `mattpocock/skills` ausente/ inadequado | FR-014: verificar e documentar; não bloquear | Bloquear feature até instalar skill |
