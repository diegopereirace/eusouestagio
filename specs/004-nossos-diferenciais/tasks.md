# Tasks: Bloco Nossos Diferenciais

**Input**: Design documents from `/specs/004-nossos-diferenciais/`  
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/block-render.md, quickstart.md  
**Tests**: Não solicitados (plan: validação manual + quickstart)  
**Organization**: Tasks agrupadas por user story para implementação e teste independente  
**Note**: `.specify/scripts` e template Spec Kit ausentes neste repo — paths resolvidos via `.specify/feature.json` + artefatos da feature

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Pode rodar em paralelo (arquivos diferentes, sem dependência de task incompleta)
- **[Story]**: User story (US1, US2, US3)
- Incluir caminho de arquivo exato na descrição

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Confirmar baseline do projeto e inventário de storages reutilizáveis antes de criar YAML

- [X] T001 Confirmar branch de trabalho e `config_sync_directory = 'config/sync'` em `sites/default/settings.php` / `sites/default/settings.php.prod`
- [X] T002 [P] Inventariar storages reutilizáveis existentes em `config/sync/field.storage.block_content.field_text_simple.yml`, `config/sync/field.storage.block_content.field_text_simple_long.yml`, `config/sync/field.storage.block_content.field_image.yml`, `config/sync/field.storage.paragraph.field_text_simple.yml`, `config/sync/field.storage.paragraph.field_text_simple_long.yml` e `config/sync/field.storage.paragraph.field_image.yml` (não recriar)
- [X] T003 [P] Espelhar padrões de bundle/paragraph em `config/sync/block_content.type.cto.yml`, `config/sync/paragraphs.paragraphs_type.icone_titulo_descricao.yml` e `config/sync/field.storage.block_content.field_icon_title_text_p.yml` como referência para `field_diferenciais_lista`
- [X] T004 Confirmar próximo `hook_update_N` disponível após `custom_configs_update_11003` em `modules/custom/custom_configs/custom_configs.install` (usar `custom_configs_update_11004`)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Estrutura Configuration Management (tipos, fields, displays) — bloqueia todas as user stories  
**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase

- [X] T005 Criar tipo de bloco em `config/sync/block_content.type.nossos_diferenciais.yml` (label “Nossos Diferenciais”, `revision` alinhado a `cto`)
- [X] T006 [P] Criar tipo de paragraph em `config/sync/paragraphs.paragraphs_type.diferencial_item_p.yml` (label “Item de Diferencial”)
- [X] T007 Criar **único** storage novo em `config/sync/field.storage.block_content.field_diferenciais_lista.yml` (entity_reference_revisions → paragraph, cardinality ilimitada, espelhar `field_icon_title_text_p` sem travar em 3)
- [X] T008 [P] Criar field instances do bloco em `config/sync/field.field.block_content.nossos_diferenciais.field_text_simple.yml`, `config/sync/field.field.block_content.nossos_diferenciais.field_text_simple_long.yml`, `config/sync/field.field.block_content.nossos_diferenciais.field_image.yml` e `config/sync/field.field.block_content.nossos_diferenciais.field_diferenciais_lista.yml` (reuso de storages; lista target bundle `diferencial_item_p`)
- [X] T009 [P] Criar field instances do paragraph em `config/sync/field.field.paragraph.diferencial_item_p.field_image.yml`, `config/sync/field.field.paragraph.diferencial_item_p.field_text_simple.yml` e `config/sync/field.field.paragraph.diferencial_item_p.field_text_simple_long.yml`
- [X] T010 Criar form display do bloco em `config/sync/core.entity_form_display.block_content.nossos_diferenciais.default.yml` (widget paragraphs para a lista; labels de produto)
- [X] T011 [P] Criar view display do bloco em `config/sync/core.entity_view_display.block_content.nossos_diferenciais.default.yml` (lista via `entity_reference_revisions_entity_view`)
- [X] T012 [P] Criar form + view displays do paragraph em `config/sync/core.entity_form_display.paragraph.diferencial_item_p.default.yml` e `config/sync/core.entity_view_display.paragraph.diferencial_item_p.default.yml`
- [X] T013 Importar config local (`drush cim -y` ou criar via UI + `drush cex -y`) e validar tipos em Estrutura → Tipos de bloco / Tipos de parágrafo sem fields manuais no destino

**Checkpoint**: Estrutura sobe só com `cim` — editor e front ainda sem apresentação customizada

---

## Phase 3: User Story 1 — Visitante vê os diferenciais (Priority: P1) 🎯 MVP

**Goal**: Página pública com seção “Nossos Diferenciais” fiel ao contrato visual (header + duas colunas + lista com cores/geometria/Poppins)  
**Independent Test**: Bloco publicado e preenchido → abrir página e validar conteúdo + layout (desktop ≥992px duas colunas; mobile empilhado; cores 1º verde / 2º azul / 3º laranja) sem depender de outros blocos

### Implementation for User Story 1

- [X] T014 [US1] Implementar template do bloco em `themes/custom/default/templates/block/block--nossos-diferenciais.html.twig` (ou suggestion estável `block--block-content--nossos-diferenciais.html.twig` / ID `default_nossosdiferenciais`) conforme `specs/004-nossos-diferenciais/contracts/block-render.md`
- [X] T015 [P] [US1] Implementar template do paragraph em `themes/custom/default/templates/paragraph/paragraph--diferencial-item-p.html.twig` (ícone à esquerda + título/descrição; `d-flex align-items-start gap-3`)
- [X] T016 [US1] Se suggestion por ID for instável, garantir suggestion em `themes/custom/default/default.theme` (`hook_theme_suggestions_block_alter` ou equivalente Barrio)
- [X] T017 [US1] Adicionar CSS do bloco em `themes/custom/default/assets/css/style.css`: escopo `.block-nossos-diferenciais`, geometria decorativa (círculo azul, anel verde, anéis cinza via `::before`/`::after` / wrappers `.nd-media`), cores de título via `:nth-child` ciclável, tipografia Poppins
- [X] T018 [US1] Atualizar `@import` Google Fonts em `themes/custom/default/assets/css/style.css` para incluir weight `300` (subtítulo Figma)
- [X] T019 [US1] Garantir imagem de destaque com `loading="lazy"` e `img-fluid` (ou equivalente) em `themes/custom/default/templates/block/block--nossos-diferenciais.html.twig`
- [X] T020 [US1] Implementar seed idempotente `custom_configs_update_11004` em `modules/custom/custom_configs/custom_configs.install`: criar `block_content` UUID fixo + 3 paragraphs (Encontramos / Gerenciamos / Desenvolvemos) com textos FR-013; imagem vazia; no-op se UUID já existir
- [X] T021 [P] [US1] (Opcional preferido R2) Criar placement `config/sync/block.block.default_nossosdiferenciais.yml` referenciando o **mesmo UUID** do seed, na região da home
- [X] T022 [US1] Rodar `drush updb -y` + `drush cr` e validar cenários B de `specs/004-nossos-diferenciais/quickstart.md` (SC-002, SC-003, SC-006)

**Checkpoint**: Visitante vê a seção completa no layout de referência (MVP entregável)

---

## Phase 4: User Story 2 — Editor gerencia o bloco (Priority: P1)

**Goal**: Editor cria/edita textos, imagem única e itens (ícone/título/descrição) no painel; mudanças refletem no front sem deploy de código  
**Independent Test**: Criar bloco do zero no admin, publicar e conferir página pública em &lt; 10 min (SC-001)

### Implementation for User Story 2

- [X] T023 [US2] Ajustar permissões create/edit/delete do bundle `nossos_diferenciais` nas roles que já gerenciam block content (ex.: `moderador`) e exportar diffs em `config/sync/user.role.*.yml`
- [X] T024 [US2] Revisar form display do bloco/paragraph (widgets, labels, obrigatoriedade de título do item) em `config/sync/core.entity_form_display.block_content.nossos_diferenciais.default.yml` e `config/sync/core.entity_form_display.paragraph.diferencial_item_p.default.yml` para fluxo editorial completo
- [X] T025 [US2] Validar no admin: criar/editar bloco, reordenar/remover itens paragraphs, trocar imagem; confirmar reflexão no front após publish/cache (cenário C de `specs/004-nossos-diferenciais/quickstart.md` — SC-001, SC-005)
- [X] T026 [US2] Confirmar que nenhum texto institucional fica hardcoded como única fonte de verdade (placeholders Twig só para ausência de dado) em `themes/custom/default/templates/block/block--nossos-diferenciais.html.twig`

**Checkpoint**: Editor opera 100% pelo painel; roles sobem via `cim` no destino

---

## Phase 5: User Story 3 — Fallbacks preservam o layout (Priority: P2)

**Goal**: Campos vazios não quebram a página; placeholder/omissão segura  
**Independent Test**: Publicar bloco omitindo campos um a um e verificar página sem erro de template

### Implementation for User Story 3

- [X] T027 [US3] No Twig do bloco `themes/custom/default/templates/block/block--nossos-diferenciais.html.twig`: omitir H2/P quando título/subtítulo vazios; placeholder visual em `.nd-media` sem imagem; renderizar header + coluna mídia com lista vazia
- [X] T028 [P] [US3] No Twig do paragraph `themes/custom/default/templates/paragraph/paragraph--diferencial-item-p.html.twig`: omitir ou placeholder de ícone; manter título/descrição legíveis; alt vazio/seguro
- [X] T029 [US3] Confirmar ciclo de cores com N≠3 itens (4º volta ao verde) via CSS `:nth-child` em `themes/custom/default/assets/css/style.css`
- [X] T030 [US3] Validar cenário D de `specs/004-nossos-diferenciais/quickstart.md` (SC-004) e edge cases da spec (overflow imagem, texto longo sem truncate)

**Checkpoint**: Cadastro parcial e migração não quebram o front

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Deploy limpo multi-ambiente e governança

- [X] T031 Exportar config limpa (`drush cex -y`) e revisar diff apenas em `config/sync/` + `themes/custom/default/` + `modules/custom/custom_configs/` (sem lixo de ambiente)
- [X] T032 [P] Executar checklist “deploy fácil” (seção E) de `specs/004-nossos-diferenciais/quickstart.md` na ordem `cim` → `updb` → `cr`
- [X] T033 [P] Atualizar seção Spec Kit em `.cursor/rules/specify-rules.mdc` se paths/artefatos da feature 004 mudarem após tasks
- [X] T034 Perguntar ao usuário: “Essa alteração estrutural exige atualização no PRD.md?” (guardião §3.6 blocos — `estagio-prd-guardian`)
- [X] T035 Documentar UUID fixo do seed + receita de deploy no corpo do update hook / comentário em `modules/custom/custom_configs/custom_configs.install` alinhado ao quickstart

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)** → sem dependências; começa imediato
- **Phase 2 (Foundational)** → após Setup; **bloqueia** US1–US3
- **Phase 3 (US1)** → após Foundational; **MVP**
- **Phase 4 (US2)** → após Foundational; pode iniciar em paralelo a US1 após T013 (forms já em T010/T012); validação editorial completa após Twig (T014+) para ver front
- **Phase 5 (US3)** → após Twig base US1 (T014–T015); estende fallbacks
- **Phase 6 (Polish)** → após stories desejadas (mínimo US1)

### User Story Dependencies

- **US1 (P1)**: Independente após Phase 2 + seed/placement; valor visitante
- **US2 (P1)**: Independente após Phase 2 + permissões; não depende visualmente de US3
- **US3 (P2)**: Estende templates US1; não bloqueia MVP

### Within Each User Story

- US1: templates → CSS/Poppins → seed/placement → validação visitante
- US2: roles → ajuste forms → validação editorial
- US3: fallbacks Twig → ciclo N itens → validação edge cases

### Parallel Opportunities

```text
Phase 1:     T002 || T003
Phase 2:     T006 || (após T005+T007) T008 || T009; T011 || T012
Phase 3:     T014 → T016; T015 || T017/T018; T020 → T021
Phase 4:     T023 || T024 (após forms Phase 2)
Phase 5:     T027 || T028
Phase 6:     T032 || T033
```

---

## Parallel Example: User Story 1

```bash
# Após Phase 2 completa:
# Dev A: Twig bloco + suggestion
# Dev B: Twig paragraph
# Dev C: CSS geometria + Poppins 300
# Depois sequencial: seed update_11004 → placement YAML → updb/cr → quickstart B
```

---

## Implementation Strategy

### MVP First (User Story 1 only)

1. Complete Phase 1 + Phase 2 (config sync)
2. Complete Phase 3 (Twig/CSS + seed + validação visitante)
3. **STOP and VALIDATE** quickstart B
4. Deploy estrutura com `cim`/`updb`/`cr` sem dump

### Incremental Delivery

1. Setup + Foundational → estrutura importável
2. + US1 → seção pública (MVP)
3. + US2 → operação editorial + roles no sync
4. + US3 → resiliência de conteúdo parcial
5. Polish → cex limpo + pergunta PRD

### Suggested MVP Scope

**User Story 1** (T014–T022) após foundational T005–T013: bloco visível no layout Figma com conteúdo de referência via seed.

---

## Notes

- [P] = arquivos distintos / sem dependência de task incompleta
- [USn] = somente em fases de user story
- Sem tasks de PHPUnit (não pedidas)
- Proibido alterar `core/` ou `vendor/`
- Proibido criar `field_text_simple_small` ou reusar bundle `icone_titulo_descricao` no lugar de `diferencial_item_p`
- Deploy destino: `git pull` → `drush cim -y` → `drush updb -y` → `drush cr`
- Commit messages em pt-BR (Conventional Commits) quando o usuário pedir commit

---

## Task Summary

| Phase | Tasks | Count |
|-------|-------|-------|
| Phase 1 Setup | T001–T004 | 4 |
| Phase 2 Foundational | T005–T013 | 9 |
| Phase 3 US1 | T014–T022 | 9 |
| Phase 4 US2 | T023–T026 | 4 |
| Phase 5 US3 | T027–T030 | 4 |
| Phase 6 Polish | T031–T035 | 5 |
| **Total** | T001–T035 | **35** |
