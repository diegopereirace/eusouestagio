# Tasks: Bloco Nossa Metodologia

**Input**: Design documents from `/specs/005-nossa-metodologia/`  
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/block-render.md, quickstart.md  
**Tests**: Não solicitados (plan: validação funcional/responsiva + quickstart)  
**Organization**: Tasks agrupadas por user story para implementação e teste independente  
**Note**: `.specify/scripts` e template Spec Kit ausentes neste repo — paths resolvidos via `.specify/feature.json` + artefatos da feature

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Pode rodar em paralelo (arquivos diferentes, sem dependência de task incompleta)
- **[Story]**: User story (US1, US2, US3)
- Incluir caminho de arquivo exato na descrição

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Confirmar baseline, reuso de storages de texto e próximo update hook antes de criar YAML

- [X] T001 Confirmar branch de trabalho e `config_sync_directory = 'config/sync'` em `sites/default/settings.php` / `sites/default/settings.php.prod`
- [X] T002 [P] Inventariar storages de texto reutilizáveis em `config/sync/field.storage.block_content.field_text_simple.yml` e `config/sync/field.storage.block_content.field_text_simple_long.yml` (não recriar; não criar `field_text_simple_small`)
- [X] T003 [P] Confirmar ausência de `config/sync/field.storage.block_content.field_image_desktop.yml` e `config/sync/field.storage.block_content.field_image_mobile.yml` (criar na Phase 2; não reutilizar `field_image` nem storages de `node`)
- [X] T004 [P] Confirmar placement de referência em `config/sync/block.block.default_nossosdiferenciais.yml` (região `content_full`, weight `-4`, `<front>`) para posicionar o novo bloco com weight `-3`
- [X] T005 Confirmar próximo `hook_update_N` disponível após `custom_configs_update_11004` em `modules/custom/custom_configs/custom_configs.install` (usar `custom_configs_update_11005`)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Estrutura Configuration Management (tipo, storages novos de imagem, instances, displays) — bloqueia todas as user stories  
**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase

- [X] T006 Criar tipo de bloco em `config/sync/block_content.type.nossa_metodologia.yml` (label “Nossa Metodologia”, `revision` alinhado a `nossos_diferenciais` / `cto`)
- [X] T007 [P] Criar storage em `config/sync/field.storage.block_content.field_image_desktop.yml` (Image, cardinality 1, espelhar padrão de `field_image` em `block_content`)
- [X] T008 [P] Criar storage em `config/sync/field.storage.block_content.field_image_mobile.yml` (Image, cardinality 1, simétrico ao desktop)
- [X] T009 Criar field instances do bloco em `config/sync/field.field.block_content.nossa_metodologia.field_text_simple.yml`, `config/sync/field.field.block_content.nossa_metodologia.field_text_simple_long.yml`, `config/sync/field.field.block_content.nossa_metodologia.field_image_desktop.yml` e `config/sync/field.field.block_content.nossa_metodologia.field_image_mobile.yml` (textos reutilizam storages; imagens usam os novos; todos opcionais; URI `public://block/nossa-metodologia/[date:custom:Y]-[date:custom:m]`)
- [X] T010 Criar form display em `config/sync/core.entity_form_display.block_content.nossa_metodologia.default.yml` (ordem: título, subtítulo, imagem desktop, imagem mobile; labels de produto)
- [X] T011 [P] Criar view display em `config/sync/core.entity_view_display.block_content.nossa_metodologia.default.yml` (labels ocultos; renderização final no Twig)
- [X] T012 Importar config local (`drush cim -y` ou criar via UI + `drush cex -y`) e validar tipo “Nossa Metodologia” com quatro campos em Estrutura → Tipos de bloco, sem fields manuais no destino

**Checkpoint**: Estrutura sobe só com `cim` — editor e front ainda sem apresentação customizada / seed

---

## Phase 3: User Story 1 — Visitante vê a metodologia institucional (Priority: P1) 🎯 MVP

**Goal**: Home pública com seção “Nossa Metodologia” logo abaixo de “Nossos Diferenciais”, textos alinhados por viewport e fluxograma via `<picture>`  
**Independent Test**: Bloco publicado e preenchido na home → abrir página pública e validar conteúdo, ordem relativa aos diferenciais e troca de arte desktop/mobile (cenários 4–5 de `specs/005-nossa-metodologia/quickstart.md`)

### Implementation for User Story 1

- [X] T013 [US1] Implementar template em `themes/custom/default/templates/block/block--block-nossa-metodologia.html.twig` conforme `specs/005-nossa-metodologia/contracts/block-render.md` (`.block-nossa-metodologia` > `.container` > header `nm-header text-center text-md-end` + `.nm-diagram` com `<picture>`; preservar `title_prefix`/`title_suffix`/`attributes`/`content_attributes`; sem `|raw`)
- [X] T014 [P] [US1] Se a suggestion por machine name for instável, garantir suggestion em `themes/custom/default/default.theme` (`hook_theme_suggestions_block_alter` ou equivalente Barrio) apontando para o template da T013
- [X] T015 [US1] Adicionar CSS escopado em `themes/custom/default/assets/css/style.css` sob `.block-nossa-metodologia` (Poppins já carregada 300–700; tipografia da seção; diagrama fluido sem overflow; sem JS novo)
- [X] T016 [US1] No Twig da T013, resolver URIs das artes e renderizar `<picture>` com `source media="(max-width: 767.98px)"`, `img.nm-diagram__image.img-fluid`, `loading="lazy"` e `alt` efetivo (desktop → mobile → `""`)
- [X] T017 [US1] Implementar seed idempotente `custom_configs_update_11005` + helper em `modules/custom/custom_configs/custom_configs.install`: criar `block_content` `nossa_metodologia` com **UUID v4 fixo novo** compartilhado com o placement; `info`/título “Nossa Metodologia”; subtítulo FR-015; imagens vazias; no-op se UUID já existir (não sobrescrever conteúdo editorial)
- [X] T018 [P] [US1] Criar placement `config/sync/block.block.default_nossametodologia.yml` referenciando o **mesmo UUID** do seed: tema `default`, região `content_full`, weight `-3`, `label_display: '0'`, visibilidade `request_path: <front>`
- [X] T019 [US1] Rodar `drush cim -y` → `drush updb -y` → `drush cr` e validar cenários visitante desktop/mobile de `specs/005-nossa-metodologia/quickstart.md` (SC-002, SC-003, SC-004, SC-005); em ambiente limpo, repetir `cim` após seed se o placement depender do UUID

**Checkpoint**: Visitante vê a seção na home no layout de referência (MVP entregável)

---

## Phase 4: User Story 2 — Editor gerencia o bloco sem suporte técnico (Priority: P1)

**Goal**: Editor cria/edita título, subtítulo e duas artes no painel; mudanças refletem no front sem deploy de código  
**Independent Test**: Criar bloco do zero no admin, publicar na home e conferir página pública em &lt; 8 min (SC-001)

### Implementation for User Story 2

- [X] T020 [US2] Conceder create/edit/delete do bundle `nossa_metodologia` ao papel `moderador` e exportar em `config/sync/user.role.moderador.yml` (espelhar padrão de `nossos_diferenciais`)
- [X] T021 [US2] Revisar form display em `config/sync/core.entity_form_display.block_content.nossa_metodologia.default.yml` (widgets de imagem, labels, obrigatoriedade opcional dos quatro campos) para fluxo editorial completo
- [X] T022 [US2] Validar no admin: autenticar como `moderador`, criar/editar bloco, preencher textos e duas artes, salvar; confirmar reflexão na home após cache (cenário 6 de `specs/005-nossa-metodologia/quickstart.md` — SC-001, SC-006)
- [X] T023 [US2] Confirmar que nenhum texto institucional fica hardcoded como única fonte de verdade (placeholders Twig só para ausência de dado) em `themes/custom/default/templates/block/block--block-nossa-metodologia.html.twig`

**Checkpoint**: Editor opera 100% pelo painel; roles sobem via `cim` no destino

---

## Phase 5: User Story 3 — Fallbacks preservam o layout (Priority: P2)

**Goal**: Campos vazios não quebram a página; omissões e fallbacks de imagem seguros  
**Independent Test**: Publicar bloco omitindo campos um a um e verificar home sem erro (cenário 7 do quickstart)

### Implementation for User Story 3

- [X] T024 [US3] No Twig `themes/custom/default/templates/block/block--block-nossa-metodologia.html.twig`: omitir H2 quando título vazio; omitir parágrafo quando subtítulo vazio; omitir `.nm-diagram`/`<picture>` quando ambas as imagens ausentes
- [X] T025 [US3] Completar matriz de fallback de arte no mesmo Twig: só desktop → desktop em todos os viewports; só mobile → mobile em todos; ambas → mobile no `source` e desktop no `img src` (conforme `specs/005-nossa-metodologia/contracts/block-render.md`)
- [X] T026 [US3] Validar cenário 7 (matriz de fallbacks) e edge cases da spec em `specs/005-nossa-metodologia/quickstart.md` (overflow da arte, subtítulo longo sem truncate, alt vazio, título+subtítulo vazios com diagrama)

**Checkpoint**: Cadastro parcial e migração não quebram o front

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Deploy limpo multi-ambiente e governança do PRD

- [X] T027 Exportar config limpa (`drush cex -y`) e revisar diff apenas em `config/sync/` + `themes/custom/default/` + `modules/custom/custom_configs/` + `PRD.md` (sem lixo de ambiente; sem `core/`/`vendor/`)
- [X] T028 [P] Executar checklist de deploy de `specs/005-nossa-metodologia/quickstart.md` (seções 1–3) na ordem `cim` → `updb` → `cr`; confirmar `config:status` limpo e seed no-op no segundo `updb`
- [X] T029 Atualizar inventário cirúrgico da seção 3.6 em `PRD.md` com o bloco `nossa_metodologia` (campos, placement `content_full` weight `-3` / `<front>`, seed `11005`) — aprovado em plan/research R10
- [X] T030 [P] Confirmar que `.cursor/rules/specify-rules.mdc` aponta para `specs/005-nossa-metodologia` (spec/plan/tasks) sem drift
- [X] T031 Documentar UUID fixo do seed + receita de deploy (`cim` → `updb` → `cr`; possível segundo `cim` em ambiente limpo) no comentário de `custom_configs_update_11005` em `modules/custom/custom_configs/custom_configs.install`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)** → sem dependências; começa imediato
- **Phase 2 (Foundational)** → após Setup; **bloqueia** US1–US3
- **Phase 3 (US1)** → após Foundational; **MVP**
- **Phase 4 (US2)** → após Foundational; permissões/forms podem avançar em paralelo à apresentação US1; validação editorial completa após Twig (T013+) para ver front
- **Phase 5 (US3)** → após Twig base US1 (T013/T016); estende fallbacks
- **Phase 6 (Polish)** → após stories desejadas (mínimo US1)

### User Story Dependencies

- **US1 (P1)**: Independente após Phase 2 + seed/placement; valor visitante
- **US2 (P1)**: Independente após Phase 2 + permissões; não depende visualmente de US3
- **US3 (P2)**: Estende templates US1; não bloqueia MVP

### Within Each User Story

- US1: Twig/`<picture>` → CSS → seed/placement → validação visitante
- US2: roles → ajuste forms → validação editorial
- US3: omissões Twig → matriz fallback → validação edge cases

### Parallel Opportunities

```text
Phase 1:     T002 || T003 || T004
Phase 2:     T007 || T008; após T006+T007+T008 → T009; T010 || T011
Phase 3:     T013 → T016; T014 || T015; T017 → T018 → T019
Phase 4:     T020 || T021 (após forms Phase 2)
Phase 5:     T024 → T025 → T026
Phase 6:     T028 || T030; T027 → T029
```

---

## Parallel Example: User Story 1

```bash
# Após Phase 2 completa:
# Dev A: Twig bloco + suggestion (T013–T014, T016)
# Dev B: CSS escopado Poppins (T015)
# Depois sequencial: seed update_11005 → placement YAML → cim/updb/cr → quickstart 4–5
```

---

## Implementation Strategy

### MVP First (User Story 1 only)

1. Complete Phase 1 + Phase 2 (config sync)
2. Complete Phase 3 (Twig/CSS + seed + placement + validação visitante)
3. **STOP and VALIDATE** quickstart cenários 4–5
4. Deploy estrutura com `cim`/`updb`/`cr` sem dump

### Incremental Delivery

1. Setup + Foundational → estrutura importável
2. + US1 → seção pública na home (MVP)
3. + US2 → operação editorial + roles no sync
4. + US3 → resiliência de conteúdo parcial
5. Polish → cex limpo + PRD §3.6 + UUID documentado

### Suggested MVP Scope

**User Story 1** (T013–T019) após foundational T006–T012: bloco visível na home com textos seed e layout `<picture>` pronto para upload editorial das artes.

---

## Notes

- [P] = arquivos distintos / sem dependência de task incompleta
- [USn] = somente em fases de user story
- Sem tasks de PHPUnit (não pedidas)
- Proibido alterar `core/` ou `vendor/`
- Proibido criar `field_text_simple_small`
- Setas entre passos são CSS (não campos CMS)
- Deploy destino: `git pull` → `drush cim -y` → `drush updb -y` → `drush cr`
- Commit messages em pt-BR (Conventional Commits) quando o usuário pedir commit

---

## Phase 7: Refactor — itens editáveis (2026-09-11)

**Purpose**: Substituir artes monolíticas desktop/mobile por etapas (imagens) + passos (paragraph)

- [X] T032 Atualizar artefatos SDD (`spec.md`, `data-model.md`, `plan.md`, `research.md`, `contracts/block-render.md`) e `PRD.md` §3.6
- [X] T033 Criar paragraph `metodologia_passo_p` + fields `field_image` / `field_text_simple` + displays em `config/sync/`
- [X] T034 Criar `field_metodologia_passos` (storage + instance) e instance `field_image` no bloco; remover desktop/mobile; atualizar form/view displays
- [X] T035 Reescrever Twig do bloco + `paragraph--metodologia-passo-p.html.twig` e CSS responsivo (`.nm-etapas` / `.nm-passos`)
- [X] T036 Update hook `custom_configs_update_11006` idempotente (seed títulos dos passos se vazio; não sobrescrever editorial)
- [X] T037 Versionar artes em `modules/custom/custom_configs/assets/nossa-metodologia/` + `custom_configs_update_11007` (anexa etapas/ícones só se campos vazios)

---

## Task Summary

| Phase | Tasks | Count |
|-------|-------|-------|
| Phase 1 Setup | T001–T005 | 5 |
| Phase 2 Foundational | T006–T012 | 7 |
| Phase 3 US1 | T013–T019 | 7 |
| Phase 4 US2 | T020–T023 | 4 |
| Phase 5 US3 | T024–T026 | 3 |
| Phase 6 Polish | T027–T031 | 5 |
| Phase 7 Refactor | T032–T036 | 5 |
| **Total** | T001–T036 | **36** |
