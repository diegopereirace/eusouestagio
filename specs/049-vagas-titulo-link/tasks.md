# Tasks: 049-vagas-titulo-link

## Phase 1 — Spec & wiring

- [x] T001 Criar artefatos SDD em `specs/049-vagas-titulo-link/`
- [x] T002 Atualizar `.specify/feature.json` e contexto do agente

## Phase 2 — Implementação (US1/US2)

- [x] T003 Envolver título com `<a href="{{ vaga_url }}">` em `themes/custom/default/templates/views/views-view-field--vagas--page-1--nothing.html.twig`
- [x] T004 Estilizar `.vaga-lista__title-link` em `themes/custom/default/assets/css/components/vagas-lista-vertical.css`
- [x] T005 `drush cr` e validar `/vagas` (título → detalhe; CTA inalterado)

## Phase 3 — Deploy docs (US3)

- [x] T006 Confirmar quickstart: deploy = pull + `drush cr` (sem cex/updb)
