# Tasks: Ocultar identificação pública do Drupal

**Input**: `specs/061-ocultar-identidade-drupal/`  
**Hook**: nenhum  
**Config estrutural**: nenhuma

## Phase 1: Setup

- [X] T001 Criar artefatos SDD (`spec.md`, `plan.md`, `tasks.md`, `quickstart.md`, `research.md`) e apontar feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`

## Phase 2: Foundational

- [X] T002 Congelar escopo negativo: não editar `core/`/`vendor/`; não remover `X-Drupal-Cache*`; não alterar Metatag SEO; sem `hook_update_N`

## Phase 3: US1 — Remover meta Generator

- [X] T003 [US1] Implementar `custom_configs_page_attachments_alter()` em `modules/custom/custom_configs/custom_configs.module`

## Phase 4: US2 — Remover headers HTTP

- [X] T004 [US2] Criar `RemoveCmsIdentityHeadersSubscriber` + `custom_configs.services.yml`
- [X] T005 [US2] Atualizar `scaffold/htaccess-security-append.txt` e `.htaccess`

## Phase 5: Verificação e PRD

- [X] T006 `drush cr` + verificar HTML/headers (quickstart)
- [X] T007 Atualizar PRD M7 (checklist Generator / X-Generator)
