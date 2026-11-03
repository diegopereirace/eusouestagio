# Tasks: Seed — arquivos gerenciáveis imutáveis

**Input**: `specs/057-seed-arquivos-imutaveis/`  
**Hook novo**: nenhum

## Phase 1: Spec / regras

- [X] T001 Criar `spec.md`, `plan.md`, `tasks.md`, `quickstart.md` em `specs/057-seed-arquivos-imutaveis/`
- [X] T002 Apontar feature ativa em `.specify/feature.json` e `.cursor/rules/specify-rules.mdc`
- [X] T003 Documentar política em `estagio-fluxo-dev.mdc` e `drupal-deploy-configs.mdc`

## Phase 2: Código

- [X] T004 Endurecer `_custom_configs_seed_file_from_module_asset` com `bool $replace = FALSE`
- [X] T005 Remover force placeholder/ratio em `_custom_configs_seed_banner_para_estudantes`
- [X] T006 Converter `_custom_configs_sync_beneficios_estudantes_orbit_assets` para copy-if-missing
- [X] T007 Converter `_custom_configs_sync_beneficios_estudantes_collage` para fill-if-empty
- [X] T008 Converter `_custom_configs_sync_oportunidades_estudantes_icons` para fill-if-empty
- [X] T009 Converter `_custom_configs_sync_beneficios_estudantes_provisional_icons` para fill-if-empty
- [X] T010 Converter `_custom_configs_sync_diferenciais_quem_somos_provisional_icons` para fill-if-empty

## Phase 3: Validação

- [X] T011 Confirmar (grep) ausência de `needs_sync` por `md5_file` / `same_bytes` nos syncs listados
- [X] T012 Marcar tasks e quickstart alinhados ao comportamento final
