# Tasks: Centralização de Banners e Busca da Home

**Input**: Artefatos de design em `specs/001-banners-busca-home/`
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/`, `quickstart.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: preparar baseline de configuração versionável e estrutura do módulo custom para a feature.

- [X] T001 Configurar `config_sync_directory` em `sites/default/settings.php.prod` para `config/sync`
- [X] T002 Criar diretório versionado `config/sync/.gitkeep` para baseline de configuração
- [X] T003 Criar estrutura base do módulo em `modules/custom/custom_banners/custom_banners.info.yml`
- [X] T004 [P] Criar arquivo de hooks de atualização `modules/custom/custom_banners/custom_banners.install`
- [X] T005 [P] Criar arquivo inicial do módulo `modules/custom/custom_banners/custom_banners.module`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: estabelecer componentes compartilhados que bloqueiam todas as histórias (schema/config e contrato principal de filtros).

- [X] T006 Exportar baseline inicial de configuração para `config/sync/` (incluindo Views, content types, taxonomias e block placements)
- [X] T007 Registrar/ajustar tipo `banners` e campos no sync em `config/sync/node.type.banners.yml`
- [X] T008 [P] Versionar campo de local de exibição em `config/sync/field.field.node.banners.field_local_exibicao.yml`
- [X] T009 [P] Versionar campos de imagem desktop/mobile em `config/sync/field.field.node.banners.field_imagem_desktop.yml` e `config/sync/field.field.node.banners.field_imagem_mobile.yml`
- [X] T010 Criar style mobile de internas em `config/sync/image.style.banner_internas_mobile.yml`
- [X] T011 Ajustar display da View de vagas para filtro exposto de regime em `config/sync/views.view.vagas.yml`

**Checkpoint**: após esta fase, US1 e US2 podem avançar em paralelo parcial.

---

## Phase 3: User Story 1 - Banners unificados e busca hero funcional (Priority: P1)

**Goal**: visitante vê banners corretos por contexto/dispositivo e consegue buscar vagas por curso + regime a partir do hero na home.
**Independent Test Criteria**: home usa apenas banners com local `home`; internas usam apenas `internas`; hero aparece só na home; GET `/para-estudantes` combina `cursos` e `regime`.

- [X] T012 [US1] Criar plugin de bloco hero em `modules/custom/custom_banners/src/Plugin/Block/HeroSearchBlock.php`
- [X] T013 [P] [US1] Criar template do bloco hero com textos fixos em `modules/custom/custom_banners/templates/block--hero-search.html.twig`
- [X] T014 [P] [US1] Registrar endpoint de autocomplete em `modules/custom/custom_banners/custom_banners.routing.yml`
- [X] T015 [US1] Implementar controller de autocomplete em `modules/custom/custom_banners/src/Controller/CursosAutocompleteController.php`
- [X] T016 [US1] Declarar bibliotecas front do hero/autocomplete em `themes/custom/default/default.libraries.yml`
- [X] T017 [P] [US1] Implementar behavior JS para autocomplete no hero em `themes/custom/default/assets/js/hero-search.js`
- [X] T018 [P] [US1] Adicionar estilos do hero e pills em `themes/custom/default/assets/css/components/hero-search.css`
- [X] T019 [US1] Reconfigurar View de banners com display home e filtros por local em `config/sync/views.view.banners.yml`
- [X] T020 [P] [US1] Criar template do wrapper do carrossel em `themes/custom/default/templates/views/views-view--banners--block-home.html.twig`
- [X] T021 [P] [US1] Criar template de itens do carrossel com `<picture>` em `themes/custom/default/templates/views/views-view-unformatted--banners--block-home.html.twig`
- [X] T022 [US1] Atualizar templates de internas para remover overlay e usar `<picture>` em `themes/custom/default/templates/views/views-view-field--banners--block-1--nothing.html.twig`, `themes/custom/default/templates/views/views-view-field--banners--block-2--nothing.html.twig` e `themes/custom/default/templates/views/views-view-field--banners--block-3--nothing.html.twig`
- [X] T023 [US1] Ajustar preprocess da theme para slides/fetchpriority/fallback em `themes/custom/default/default.theme`
- [X] T024 [US1] Posicionar bloco hero na home e bloco de banners home no sync em `config/sync/block.block.*.yml`
- [X] T025 [US1] Implementar `hook_update_N` de migração (`banner_internas` + block bundle `banner` -> `banners`) em `modules/custom/custom_banners/custom_banners.install`
- [X] T026 [US1] Validar critérios SC-1/SC-3/SC-4/SC-6 com roteiro em `specs/001-banners-busca-home/quickstart.md`

---

## Phase 4: User Story 2 - Pills e deploy sem dump (Priority: P2)

**Goal**: quick filters funcionam com 1 clique e deploy usa apenas fluxo de configuração + update hooks sem dump de banco.
**Independent Test Criteria**: cada pill gera URL filtrada válida; `drush cim` + `drush updb` migra legado; sem dependência de dump para estrutural.

- [X] T027 [US2] Implementar resolução de pills por taxonomia no bloco hero em `modules/custom/custom_banners/src/Plugin/Block/HeroSearchBlock.php`
- [X] T028 [P] [US2] Adicionar renderização condicional de pills inválidas no template `modules/custom/custom_banners/templates/block--hero-search.html.twig`
- [X] T029 [US2] Tornar endpoint de autocomplete resiliente a vocabulário ausente em `modules/custom/custom_banners/src/Controller/CursosAutocompleteController.php`
- [X] T030 [US2] Garantir idempotência e marcação de execução da migração em `modules/custom/custom_banners/custom_banners.install`
- [X] T031 [US2] Remover/neutralizar placements legados de banner e busca no sync em `config/sync/block.block.*.yml`
- [X] T032 [US2] Executar validação de deploy local sem dump e registrar evidências em `specs/001-banners-busca-home/quickstart.md`

---

## Phase 5: Polish & Cross-Cutting Concerns

**Objetivo**: finalizar governança, documentação e limpeza segura pós-estabilização.

- [X] T033 [P] Atualizar `PRD.md` com tipo `banners`, filtros de `vagas` e módulo `custom_banners`
- [X] T034 [P] Confirmar cobertura da regra de governança em `.cursor/rules/estagio-prd-guardian.mdc`
- [X] T035 Planejar remoção de YAMLs legados para Release 2 em `specs/001-banners-busca-home/plan.md`
- [X] T036 Executar validação final completa da feature com checklist em `specs/001-banners-busca-home/checklists/requirements.md`

---

## Dependencies & Execution Order

- Setup (Phase 1) -> Foundational (Phase 2) -> US1 (Phase 3) -> US2 (Phase 4) -> Polish (Phase 5)
- US1 depende de: T006-T011
- US2 depende de: T025 e contratos já implementados em US1
- Polish depende de: conclusão de US1 + US2

## Dependency Graph

- US1 (P1) -> US2 (P2)

## Parallel Execution Opportunities

- **Setup**: T004 e T005 podem rodar em paralelo após T003
- **Foundational**: T008 e T009 podem rodar em paralelo após T007
- **US1**: T013, T014, T017, T018, T020 e T021 podem avançar em paralelo após T012/T016/T019 conforme dependências locais
- **US2**: T028 pode rodar em paralelo com T029 após T027
- **Polish**: T033 e T034 podem rodar em paralelo

## Implementation Strategy

- **MVP primeiro**: concluir Phase 1 + Phase 2 + Phase 3 (US1) para entregar valor principal (banners unificados + hero de busca funcional).
- **Incremento 2**: executar US2 para pills robustas e validação completa de deploy sem dump.
- **Fechamento**: aplicar fase de polish e preparar remoção estrutural legada em release subsequente (R2).
