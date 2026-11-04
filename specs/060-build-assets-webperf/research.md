# Research: Build de assets do tema e agregação CSS/JS (WebPerf)

**Data**: 2026-11-04 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **Vite multi-entry compatível com libraries Drupal**, **produção sem Node**, **agregação nativa via hook + cex**, e **LCP hero sem expandir escopo de imagens**.

---

## R1 — Vite multi-entry vs bundle único

**Decision**: Usar **Vite** com `build.rollupOptions.input` (ou equivalente) listando **cada** CSS/JS próprio do tema como entry; saída em `dist/css/**` e `dist/js/**` com nomes estáveis (sem hash no filename, para não invalidar `libraries.yml` a cada build — ou documentar plugin de rename se hash for desejado depois).

**Rationale**: O tema tem ~30+ libraries anexadas sob demanda (home ≠ vagas ≠ painel). Um bundle monolítico forçaria CSS/JS de rotas alheias em toda página e conflitaria com o modelo Drupal de libraries.

**Alternatives considered**:
- Bundle único `theme.css` + `theme.js` — rejeitado (peso e acoplamento).
- Laravel Mix / Gulp — só se Vite falhar com multi-entry CSS; spec prefere Vite; Mix rejeitado por padrão.
- esbuild CLI puro — viável, mas Vite traz watch/DX/PostCSS com menos cola.

---

## R2 — Layout de pastas `src/` → `dist/`

**Decision**:

| Papel | Caminho |
|-------|---------|
| Fontes CSS | `themes/custom/default/src/css/**` (espelho de `assets/css`) |
| Fontes JS | `themes/custom/default/src/js/**` |
| Artefatos | `themes/custom/default/dist/css/**`, `dist/js/**` |
| Imagens | `assets/images/**` **permanecem** (fora do pipeline) |
| Libraries | `default.libraries.yml` → apenas `dist/...` para assets próprios |

Migração: copiar arquivos atuais `assets/css` e `assets/js` para `src/`; após build verde e libraries atualizadas, remover (ou deixar de referenciar) os paths legados em `assets/css|js`.

**Rationale**: FR-005 / FR-006; Assumption da spec.

**Alternatives considered**:
- Build in-place sobre `assets/` — rejeitado (mistura fonte e artefato).
- `build/` em vez de `dist/` — cosmético; `dist/` é o default da Assumption.

---

## R3 — Minificação CSS/JS e SCSS

**Decision**:

- CSS: PostCSS + cssnano (via Vite) na build de produção; watch sem ofuscação agressiva se atrapalhar debug (source maps opcionais em `dev`).
- JS: minificação padrão do Vite/ESBuild em `build`; preservar behaviors Drupal (`(function (Drupal, once, drupalSettings) { ... })`).
- SCSS: habilitar `sass` no toolchain; na migração inicial, fontes podem continuar `.css` — **não** é obrigatório converter tudo para partials nesta feature.

**Rationale**: FR-001–FR-004; Assumption SCSS.

**Alternatives considered**:
- Só `csso`/`uglify` manuais — rejeitado (sem watch unificado).
- Reescrever architecture SCSS completa — fora do escopo.

---

## R4 — Versionar `dist/` e ignorar `node_modules`

**Decision**:

- Commitar `dist/` (artefatos de produção).
- Adicionar `node_modules/` ao `.gitignore` do tema e/ou raiz (`**/node_modules/`).
- Documentar no quickstart: após editar `src/`, rodar `npm run build` **antes** do commit que altera o front.

**Rationale**: Spec Assumption; servidor de produção atual não tem Node como requisito; alinhado a `drupal-deploy-configs` (deploy = git pull + drush).

**Alternatives considered**:
- Gerar `dist/` só na CI e artifact deploy — rejeitado por agora (esteira atual é git-centric).
- Commitar `node_modules` — rejeitado.

---

## R5 — Agregação `system.performance` + hook `11090`

**Decision**:

```php
// Pseudocódigo — custom_configs_update_11090
$config = \Drupal::configFactory()->getEditable('system.performance');
$changed = FALSE;
if (!$config->get('css.preprocess')) { $config->set('css.preprocess', TRUE); $changed = TRUE; }
if (!$config->get('js.preprocess')) { $config->set('js.preprocess', TRUE); $changed = TRUE; }
if ($changed) { $config->save(); return t('Performance: CSS/JS preprocess habilitados.'); }
return t('Performance: preprocess já ativos; nada a fazer.');
```

Na origem: após update, `drush cex -y` para versionar `config/sync/system.performance.yml` com preprocess true.

**Não alterar** nesta feature: `cache.page.max_age` (hoje `0`), `css.compress` / `js.compress` (já true).

**Rationale**: FR-008 / FR-009 / FR-013; SC-003 / SC-008.

**Alternatives considered**:
- Só editar YAML no sync sem hook — rejeitado (destino com sync parcial ainda precisa do hook).
- Só hook sem cex — rejeitado (config estrutural deve viver no sync).
- Ligar page cache max_age alto — fora do escopo (efeito colateral editorial/admin).

---

## R6 — Libraries e `preprocess: false` pontual

**Decision**: Por padrão, **não** setar `preprocess: false` nos arquivos do tema. Após smoke (home + página com JS migrado: ex. `/vagas` hero search ou painel), se algum script quebrar sob agregação, marcar **apenas** esse arquivo com `preprocess: false` na library e documentar no quickstart/contract.

**Rationale**: Edge case da spec; minimizar exceções.

**Alternatives considered**: Desligar preprocess em todo JS custom “por precaução” — rejeitado (anula SC-004).

---

## R7 — LCP hero da home

**Decision**:

1. Candidato LCP de imagem = primeiro slide do View banners `block_home` (`views-view--banners--block-home.html.twig`).
2. Estado atual (2026-11-04): **já conforme** — `loop.first` → `fetchpriority="high" loading="eager"`; demais → `lazy`.
3. `custom_banners_hero_search` **não** renderiza imagem LCP; não aplicar atributos de imagem ali.
4. Na implementação: re-auditar markup; se regressão, corrigir; senão documentar conformidade (FR-010 / SC-005).

**Rationale**: Spec US3 + regra `estagio-banner-home.mdc`.

**Alternatives considered**:
- Aplicar eager/high em todas as imagens do site — rejeitado (Fora de escopo).
- Preload `<link rel=preload>` da hero — útil, mas fora do MVP desta feature.

---

## R8 — DX scripts npm e engines

**Decision**: Em `package.json` do tema:

| Script | Comportamento |
|--------|----------------|
| `dev` / `watch` | Vite watch; recompila `src/` → `dist/` |
| `build` | Build produção minificado; exit 0 em ≤60s (SC-002) |

Documentar Node LTS (ex. ≥20) no quickstart. `npm install` na pasta do tema.

**Rationale**: FR-004 / FR-011 / SC-007.

**Alternatives considered**: Makefile na raiz do monorepo — opcional depois; MVP = scripts no tema.

---

## R9 — FR-014 skills `mattpocock/skills`

**Decision**: Na implementação, executar busca (`npx skills find` / skills.sh com termos `webperf`, `vite`, `frontend`). Se skill com ≥1k installs e valor tangível ao DX do pipeline → avaliar instalação; senão registrar “nenhuma skill adequada” no quickstart. **Não bloquear** a feature.

**Rationale**: FR-014; Assumption da spec.

**Alternatives considered**: Bloquear merge até achar skill — rejeitado.

---

## R10 — Impacto colateral conhecido (não bloquear)

**Decision**: Registrar como dívida consciente, **fora** do aceite obrigatório desta feature:

- `@import` Google Fonts no topo de `style.css` (custo de FCP/LCP).
- Imagens hero sem WebP/AVIF / image styles novos.
- `cache.page.max_age: 0` no sync.

Podem virar features 061+.

**Rationale**: Spec Fora / Assumptions PageSpeed.
