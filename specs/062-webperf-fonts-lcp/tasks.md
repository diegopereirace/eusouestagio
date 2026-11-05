# Tasks: WebPerf fontes self-host + LCP/CLS home

**Input**: `specs/062-webperf-fonts-lcp/`  
**Hook**: `custom_configs_update_11091`  
**Config estrutural**: image styles banner_carousel*; metatag front

## Phase 1: Setup

- [X] T001 Criar artefatos SDD e apontar feature ativa (`.specify/feature.json` + `specify-rules.mdc`)

## Phase 2: Foundational

- [X] T002 Congelar escopo negativo: sem AdvAgg/page cache; sem alterar PE/estudantes; sem core/vendor

## Phase 3: US1 — Fontes

- [X] T003 [US1] Baixar/versionar WOFF2 Poppins+Inter em `themes/custom/default/fonts/`
- [X] T004 [US1] Criar CSS `@font-face` + libraries; remover `@import` Google; Inter só Contato; rebuild Vite

## Phase 4: US3 — Font Awesome

- [X] T005 [US3] Remover `all.min.js` de `default.libraries.yml` (fontawesome)

## Phase 5: US2 — Hero LCP/CLS

- [X] T006 [US2] aspect-ratio em `banner-carousel.css` + rebuild
- [X] T007 [US2] width/height no preprocess + Twig; preload na front

## Phase 6: WebP + Metatag

- [X] T008 Garantir WebP nos image styles + `custom_configs_update_11091` + cex
- [X] T009 [US4] Description na metatag front + cex

## Phase 7: Verificação

- [X] T010 `drush cr` + smoke home/contato/ícones; orientar reteste PSI
