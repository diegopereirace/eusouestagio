# Tasks: Diferenciais Quem Somos

**Input**: Artefatos de design em `specs/013-diferenciais-quem-somos/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/diferenciais-quem-somos-render.md`, `quickstart.md`  
**Branch**: `dev`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/013-diferenciais-quem-somos` (via `.specify/feature.json`); template alinhado a `specs/012-quem-somos-missao-visao/tasks.md` / `specs/004-nossos-diferenciais/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar storages, bloco home 004, placement `content_full` e último hook antes de criar config/tema/`11016`.

- [X] T001 Confirmar artefatos SDD e feature ativa (`specs/013-diferenciais-quem-somos/`, `.specify/feature.json`, `.cursor/rules/specify-rules.mdc`)
- [X] T002 [P] Inventariar storages reutilizáveis (não recriar) em `config/sync/field.storage.block_content.field_text_simple.yml`, `config/sync/field.storage.block_content.field_text_simple_long.yml`, `config/sync/field.storage.block_content.field_itens_lista.yml` (cardinality atual `2`), `config/sync/field.storage.paragraph.field_image.yml` e `config/sync/field.storage.paragraph.field_text_simple.yml`
- [X] T003 [P] Inventariar padrão home 004 (não alterar) em `config/sync/block_content.type.nossos_diferenciais.yml`, `config/sync/paragraphs.paragraphs_type.diferencial_item_p.yml`, `themes/custom/default/templates/block/block--block-nossos-diferenciais.html.twig`, `themes/custom/default/templates/paragraph/paragraph--diferencial-item-p.html.twig` e classes `.block-nossos-diferenciais`
- [X] T004 [P] Inventariar referência de bloco+paragraph em Quem Somos (`missao_visao` / placement) e libraries do tema em `config/sync/block.block.default_missaovisao.yml`, `themes/custom/default/default.libraries.yml` e um Twig `themes/custom/default/templates/block/block--block-*.html.twig` recente
- [X] T005 Confirmar último hook `custom_configs_update_11015` → próximo livre `11016` em `modules/custom/custom_configs/custom_configs.install`; preparar pasta de assets seed `modules/custom/custom_configs/assets/diferenciais-quem-somos/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar paragraph type, block type, elevação de cardinality da lista, field instances, form/view displays e placement — pré-requisito de todas as user stories. Zero storage novo de texto/imagem; **não** tocar `nossos_diferenciais`.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T006 Confirmar reuso (sem storages paralelos `field_text_simple_small` / lista nova) dos YAMLs listados em T002 sob `config/sync/field.storage.*`
- [X] T007 [P] Criar tipo de paragraph “Item Diferencial Simples” em `config/sync/paragraphs.paragraphs_type.diferencial_simples_p.yml`
- [X] T008 [P] Criar tipo de bloco “Diferenciais Quem Somos” em `config/sync/block_content.type.diferenciais_quem_somos.yml` (`revision` alinhado aos demais block types)
- [X] T009 Elevar cardinality de `field_itens_lista` de `2` para `-1` em `config/sync/field.storage.block_content.field_itens_lista.yml` (research R3; bundle `missao_visao` permanece válido)
- [X] T010 [P] Criar field instances do paragraph em `config/sync/field.field.paragraph.diferencial_simples_p.field_image.yml` e `config/sync/field.field.paragraph.diferencial_simples_p.field_text_simple.yml`
- [X] T011 Criar field instances do bloco em `config/sync/field.field.block_content.diferenciais_quem_somos.field_text_simple.yml`, `config/sync/field.field.block_content.diferenciais_quem_somos.field_text_simple_long.yml` e `config/sync/field.field.block_content.diferenciais_quem_somos.field_itens_lista.yml` (handler → somente `diferencial_simples_p`, cardinality ilimitada via storage)
- [X] T012 [P] Criar form + view displays do paragraph em `config/sync/core.entity_form_display.paragraph.diferencial_simples_p.default.yml` e `config/sync/core.entity_view_display.paragraph.diferencial_simples_p.default.yml`
- [X] T013 Criar form display do bloco (widget paragraphs; default type `diferencial_simples_p`) em `config/sync/core.entity_form_display.block_content.diferenciais_quem_somos.default.yml`
- [X] T014 [P] Criar view display do bloco (lista via `entity_reference_revisions_entity_view`) em `config/sync/core.entity_view_display.block_content.diferenciais_quem_somos.default.yml`
- [X] T015 Criar placement `default_diferenciaisquemsomos` (tema `default`, região `content_full`, weight `10`, `label_display: '0'`, `request_path` = `/quem-somos`, plugin UUID `d5e6f7a8-b9c0-4d1e-8f2a-3b4c5d6e7f80`) em `config/sync/block.block.default_diferenciaisquemsomos.yml`
- [X] T016 Importar/validar estrutura local (`drush cim -y` ou UI + `drush cex -y`) — tipos em Estrutura → Tipos de bloco / Tipos de parágrafo; cardinality ilimitada em `field_itens_lista`; **sem** alterar home 004

**Checkpoint**: estrutura sobe só com `cim`; front ainda sem Twig/CSS custom; seed ainda ausente.

---

## Phase 3: User Story 1 — Visitante vê os diferenciais em Quem Somos (Priority: P1) 🎯 MVP

**Goal**: viewport ≥992px — cabeçalho centralizado (título + descrição contida) + grid até 4 colunas; ícone ≤64px acima do rótulo Poppins SemiBold; classe raiz `block-diferenciais-quem-somos`.  
**Independent Test Criteria**: abrir `/quem-somos` ≥992px com bloco publicado e comparar estrutura com `contracts/diferenciais-quem-somos-render.md` (SC-001).

- [X] T017 [US1] Registrar library `diferenciais_quem_somos` → CSS dedicado em `themes/custom/default/default.libraries.yml`
- [X] T018 [P] [US1] Criar CSS base encapsulado (ícones `max-width: 64px`; Poppins SemiBold nos rótulos; tipografia do `h2`; **somente** seletores sob `.block-diferenciais-quem-somos`) em `themes/custom/default/assets/css/diferenciais-quem-somos.css`
- [X] T019 [US1] Implementar Twig do bloco (`.container.py-5`; `h2` centralizado; descrição `.col-lg-8.mx-auto`; grid `.row.mt-5.justify-content-center`; attach library; preservar `attributes`/`content_attributes`/`title_*`; **não** reusar classes `.block-nossos-diferenciais` / `.nd-*`) em `themes/custom/default/templates/block/block--block-diferenciais-quem-somos.html.twig`
- [X] T020 [P] [US1] Implementar Twig do paragraph (coluna `.col-6.col-md-4.col-lg-3.mb-4`; item `.text-center.d-flex.flex-column.align-items-center`; ícone `.img-fluid` + rótulo `.mt-3`; omitir ícone/rótulo vazios) em `themes/custom/default/templates/paragraph/paragraph--diferencial-simples-p.html.twig`
- [X] T021 [US1] Implementar fallbacks no Twig do bloco (omitir `h2`/descrição vazios; zero itens → só cabeçalho se houver; sem truncar lista >8) em `themes/custom/default/templates/block/block--block-diferenciais-quem-somos.html.twig`
- [X] T022 [US1] Validar SC-001 e cenários US1 (desktop lg+) conforme `specs/013-diferenciais-quem-somos/quickstart.md` seção B e `specs/013-diferenciais-quem-somos/contracts/diferenciais-quem-somos-render.md` (conteúdo de teste manual ou seed US5)

**Checkpoint**: visitante desktop vê a seção completa no layout de referência (MVP).

---

## Phase 4: User Story 2 — Visitante mobile vê 2 itens por linha (Priority: P1)

**Goal**: viewport ≤575.98px — 2 itens/linha, legível, sem scroll horizontal; md → até 3 itens (`col-md-4`).  
**Independent Test Criteria**: `/quem-somos` ≤575.98px e viewport md (SC-002).

- [X] T023 [US2] Confirmar/ajustar CSS mobile (sem overflow-x; tipografia legível; ícones ≤64px) sob `.block-diferenciais-quem-somos` em `themes/custom/default/assets/css/diferenciais-quem-somos.css`
- [X] T024 [US2] Confirmar classes Bootstrap do item (`.col-6.col-md-4.col-lg-3`) no Twig do paragraph em `themes/custom/default/templates/paragraph/paragraph--diferencial-simples-p.html.twig`
- [X] T025 [US2] Validar SC-002 / cenários US2 (mobile + md) conforme `specs/013-diferenciais-quem-somos/quickstart.md` seção C

---

## Phase 5: User Story 4 — Bloco aparece só em Quem Somos (Priority: P1)

**Goal**: bloco só em `/quem-somos` (`content_full`); home e outras internas sem `diferenciais_quem_somos`; `nossos_diferenciais` intacto.  
**Independent Test Criteria**: comparar `/quem-somos`, `<front>` e outra interna (SC-004).

- [X] T026 [US4] Confirmar YAML do placement (path `/quem-somos`, região `content_full`, UUID alinhado ao seed) em `config/sync/block.block.default_diferenciaisquemsomos.yml`
- [X] T027 [P] [US4] Confirmar que configs/Twig/CSS da home 004 permanecem inalterados em `config/sync/block_content.type.nossos_diferenciais.yml`, `themes/custom/default/templates/block/block--block-nossos-diferenciais.html.twig` e escopo CSS (sem seletores em `.block-nossos-diferenciais`)
- [X] T028 [US4] Validar SC-004 / cenários US4 (Quem Somos vs home vs interna) conforme `specs/013-diferenciais-quem-somos/quickstart.md` seção B itens 4–5

---

## Phase 6: User Story 3 — Editor gerencia conteúdo sem código (Priority: P1)

**Goal**: editor com permissão edita título, descrição, ícones, rótulos e ordem; lista ilimitada; mudanças refletem em `/quem-somos` sem deploy de código.  
**Independent Test Criteria**: editar no painel, salvar, recarregar `/quem-somos` (&lt; 5 min — SC-003).

- [X] T029 [US3] Incluir permissões create/edit/delete do bundle `diferenciais_quem_somos` nas roles que já gerenciam block content e exportar diffs em `config/sync/user.role.moderador.yml` e demais `config/sync/user.role.*.yml` afetados (research R12)
- [X] T030 [US3] Revisar form displays (widgets, labels, lista paragraphs, obrigatoriedade editorial do rótulo) em `config/sync/core.entity_form_display.block_content.diferenciais_quem_somos.default.yml` e `config/sync/core.entity_form_display.paragraph.diferencial_simples_p.default.yml`
- [X] T031 [US3] Confirmar que nenhum texto institucional fica hardcoded como única fonte de verdade (placeholders Twig só para ausência) em `themes/custom/default/templates/block/block--block-diferenciais-quem-somos.html.twig` e `themes/custom/default/templates/paragraph/paragraph--diferencial-simples-p.html.twig`
- [X] T032 [US3] Validar SC-003 / cenários US3 (edição &lt;5 min; item só com rótulo) conforme `specs/013-diferenciais-quem-somos/quickstart.md` seção D

---

## Phase 7: User Story 5 — Deploy reproduz estrutura e seed em outro ambiente (Priority: P1)

**Goal**: `custom_configs_update_11016` idempotente (ensure types/fields/displays + cardinality; seed UUID fixo + 8 itens + ícones; placement); fluxo `cim` → `updb` → `cr`; `cex` versiona estrutural.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; segunda `updb` sem duplicatas; editorial preservado (SC-005–SC-007).

- [X] T033 [P] [US5] Adicionar ícones seed (SVG/PNG genéricos) em `modules/custom/custom_configs/assets/diferenciais-quem-somos/`
- [X] T034 [US5] Implementar `custom_configs_update_11016` idempotente (ensure paragraph/block types + instances + displays; elevar cardinality `field_itens_lista` se ainda `2`; seed `BlockContent` UUID `d5e6f7a8-b9c0-4d1e-8f2a-3b4c5d6e7f80` + 8 `diferencial_simples_p` + cópia de ícones para `public://diferenciais-quem-somos/` **somente se vazios/ausentes**; ensure placement `default_diferenciaisquemsomos` + visibility `/quem-somos`; **nunca** sobrescrever editorial; **nunca** alterar `nossos_diferenciais`) em `modules/custom/custom_configs/custom_configs.install`
- [X] T035 [P] [US5] Extrair/reusar helpers privados de ensure/seed no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T036 [US5] Exportar/confirmar configs estruturais com `drush cex -y` para os YAMLs listados em `specs/013-diferenciais-quem-somos/data-model.md` sob `config/sync/` (tipos, instances, storage cardinality, displays, placement, `user.role.*`)
- [X] T037 [US5] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cr` e reexecutar `updb` (SC-005/SC-006) conforme `specs/013-diferenciais-quem-somos/quickstart.md` seções A e E
- [X] T038 [US5] Validar fallbacks pós-seed (título/descrição vazios omitidos; zero itens → só cabeçalho; SC-007) conforme `specs/013-diferenciais-quem-somos/quickstart.md` seção E

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, isolamento visual (009/010/012 + home 004) e aceite final SC-001–SC-008. Regra Cursor deploy já entregue (FR-021) — não recriar.

- [X] T039 [P] Atualizar §3.6 (e opcionalmente §3.1.0) documentando `diferenciais_quem_somos`, `diferencial_simples_p`, placement, UUID, `11016` e convivência com home `nossos_diferenciais` em `PRD.md`
- [X] T040 Confirmar presença da regra deploy (sem recriar) em `.cursor/rules/drupal-deploy-configs.mdc`
- [X] T041 Executar validação final completa (SC-001–SC-008; isolamento home/banner/Sobre nós/Missão-Visão; checklist deploy fácil) com `specs/013-diferenciais-quem-somos/quickstart.md` e `specs/013-diferenciais-quem-somos/checklists/requirements.md`
- [X] T042 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US4 (Phase 5) → US3 (Phase 6) → US5 (Phase 7) → Polish (Phase 8)
- US2 depende do markup/CSS base de US1 (T019–T021)
- US4 valida sobretudo o Foundational (T015); pode rodar em paralelo com US1 após Phase 2 (aceite completo após seed US5)
- US3 depende de form displays (T013/T012) + Twig com fallbacks (T021); ideal após seed mínimo (US5) ou conteúdo manual de teste
- US5 (hook/cex) precisa dos YAMLs da Phase 2; validar pós-Twig mínimo (T019–T020)
- Polish após US1–US5

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) 🎯 MVP
                    ├→ US2 (P1)
                    ├→ US4 (P1) ──┐
                    ├→ US3 (P1) ──┤
                    └→ US5 (P1) ──┴→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002, T003, T004 em paralelo após T001; T005 após T001
- **Foundational**: T007 ∥ T008 após T006; T010 após T007; T011 após T008+T009; T012 ∥ após T010; T013/T014 após T011; T015 após UUID/plugin definido; T016 por último
- **US1**: T018 (CSS) em paralelo com início de T019 após T017; T020 ∥ T019 (arquivos distintos); T021 após markup estável
- **US2 ∥ US4**: T023–T024 com T026–T027 após T019/T020
- **US3**: após T013 + T021; melhor com seed de US5
- **US5**: T033 ∥ preparo de T034; T035 junto a T034; T036–T038 após hook
- **Polish**: T039 ∥ T040; depois T041–T042

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (desktop cabeçalho + grid 4 colunas) — valor principal da feature
2. **Mobile + isolamento de rota**: US2 + US4
3. **Editorial + deploy**: US3 (roles/form) + US5 (`11016` + assets + cim/updb/cr + cex)
4. **Fechamento**: Polish (PRD §3.6 + aceite SC-001–SC-008; regra deploy já existente)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T042`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias por prioridade P1: US1 → US2 → US4 → US3 → US5 (todas P1 na spec; ordem reflete dependências de implementação)
