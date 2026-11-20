# Tasks: Home — Busca Inteligente

**Input**: artefatos em `specs/041-home-busca-inteligente/`  
**Branch**: `feat/home-busca-inteligente`  
**Config**: sem `hook_update_N` / sem `cex` obrigatório

## Phase 1: Setup / SDD

- [X] T001 Artefatos SDD (`spec`, `plan`, `research`, `data-model`, contracts, `quickstart`, checklist) + `.specify/feature.json` + `specify-rules.mdc`

## Phase 2: Foundational

- [X] T002 Criar `VagaLocationCourseIndex` (triples cacheados cidade/UF/curso/count + norms) em `modules/custom/custom_banners/src/Controller/VagaLocationCourseIndex.php`

## Phase 3: US2 — Endpoints

- [X] T003 [US2] Estender `CursosAutocompleteController` (params `cidade`/`estado`, índice, q vazio com constraint)
- [X] T004 [US2] Estender `CidadesAutocompleteController` (param `curso`, N filtrado, q vazio com constraint)

## Phase 4: US3 + US4 — JS / Twig / CSS

- [X] T005 [US3][US4] Cross-filter + debounce 300ms + cache key + abort + disable/hint em `hero-search.js`
- [X] T006 [US3] Hints no Twig `block--hero-search.html.twig`
- [X] T007 [US3] CSS disabled/hint em `hero-search.css` + bump library se necessário

## Phase 5: US1 / US5 / Polish

- [X] T008 Validar SC-001–SC-005 via quickstart; `drush cr`
- [X] T009 PRD cirúrgico (§3.6 / rotas autocomplete com constraints)
