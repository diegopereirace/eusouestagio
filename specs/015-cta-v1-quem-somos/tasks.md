# Tasks: Bloco CTA v1 — Quem Somos

**Input**: Artefatos de design em `specs/015-cta-v1-quem-somos/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/cta-v1-render.md`, `quickstart.md`  
**Branch**: `dev`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/015-cta-v1-quem-somos` (via `.specify/feature.json`); template alinhado a `specs/013-diferenciais-quem-somos/tasks.md` / `specs/014-numeros-quem-somos/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar storages reutilizáveis, bloco CTO (não reutilizar visual), placement Impact in Numbers (weight 11) e último hook antes de criar config/tema/`11019`.

- [X] T001 Confirmar artefatos SDD e feature ativa (`specs/015-cta-v1-quem-somos/`, `.specify/feature.json`, `.cursor/rules/specify-rules.mdc`)
- [X] T002 [P] Inventariar storages reutilizáveis (não recriar) em `config/sync/field.storage.block_content.field_text_simple.yml`, `config/sync/field.storage.block_content.field_text_simple_long.yml` e `config/sync/field.storage.block_content.field_link.yml`
- [X] T003 [P] Confirmar ausência de `field_link_2` e padrão `*_2` do projeto (ex.: `field_text_simple_2`) sob `config/sync/field.storage.block_content.*` — planejar criação só de `field.storage.block_content.field_link_2.yml`
- [X] T004 [P] Inventariar bloco CTO home (não alterar visual/CSS; só referência de reuso de `field_link`) em `config/sync/block_content.type.cto.yml`, `themes/custom/default/templates/block/block--block-cto.html.twig` e classes `.ui-btn--primary` em `themes/custom/default/assets/css/`
- [X] T005 [P] Inventariar placement Impact in Numbers (weight 11) e vizinho Diferenciais (weight 10) em `config/sync/block.block.default_impactnumbersquemsomos.yml` e `config/sync/block.block.default_diferenciaisquemsomos.yml`
- [X] T006 Confirmar último hook `custom_configs_update_11018` → próximo livre `11019` em `modules/custom/custom_configs/custom_configs.install`; inventariar library pattern em `themes/custom/default/default.libraries.yml`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar block type `cta_v1`, storage novo `field_link_2`, field instances (3 reuso + 1 novo), form/view displays e placement weight 12 — pré-requisito de todas as user stories. **Não** criar `field_text_simple_small` / `field_link_secundario`; **não** tocar features 009–014 nem bloco `cto`.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T007 Confirmar reuso (sem storages paralelos de texto; só `field_link_2` novo) dos YAMLs listados em T002 sob `config/sync/field.storage.block_content.*`
- [X] T008 [P] Criar tipo de bloco “CTA v1” em `config/sync/block_content.type.cta_v1.yml` (`revision` alinhado aos demais block types)
- [X] T009 Criar field storage `field_link_2` (tipo `link`, cardinality **1**, entity type `block_content`) em `config/sync/field.storage.block_content.field_link_2.yml`
- [X] T010 [P] Criar field instances do bloco em `config/sync/field.field.block_content.cta_v1.field_text_simple.yml`, `config/sync/field.field.block_content.cta_v1.field_text_simple_long.yml`, `config/sync/field.field.block_content.cta_v1.field_link.yml` e `config/sync/field.field.block_content.cta_v1.field_link_2.yml`
- [X] T011 Criar form display do bloco (widgets string/string_long/link; quatro campos editáveis) em `config/sync/core.entity_form_display.block_content.cta_v1.default.yml`
- [X] T012 [P] Criar view display do bloco (quatro campos visíveis para Twig) em `config/sync/core.entity_view_display.block_content.cta_v1.default.yml`
- [X] T013 Criar placement `default_ctav1quemsomos` (tema `default`, região `content_full`, weight **`12`**, `label_display: '0'`, `request_path` = `/quem-somos`, UUID placement `f7a8b9c0-d1e2-4f3a-a0b5-192a3b4c5d6e`, plugin `block_content:e6f7a8b9-c0d1-4e2f-9a3b-4c5d6e7f8091`) em `config/sync/block.block.default_ctav1quemsomos.yml`
- [X] T014 Incluir permissões create/edit/delete do bundle `cta_v1` nas roles que já gerenciam block content e preparar diffs em `config/sync/user.role.*.yml` (research R14; export final via `cex` em US5)
- [X] T015 Importar/validar estrutura local (`drush cim -y` ou UI + `drush cex -y`) — tipo **CTA v1** em Estrutura → Tipos de bloco; storage `field_link_2` presente; **sem** alterar banner/Sobre nós/Missão-Visão/Diferenciais/Impact Numbers/CTO

**Checkpoint**: estrutura sobe só com `cim`; front ainda sem Twig/CSS custom; seed ainda ausente.

---

## Phase 3: User Story 1 — Visitante vê o CTA em Quem Somos (Priority: P1) 🎯 MVP

**Goal**: viewport ≥768px — card azul claro contido (~1200px), cantos 32px, padding 64px, título + subtítulo + dois botões lado a lado; classe raiz `.block-cta-v1`; tokens Figma (`#D3E4FE`, primário `#FD7B1A`).  
**Independent Test Criteria**: abrir `/quem-somos` ≥768px com bloco publicado e comparar card/tipografia/copy/botões com `contracts/cta-v1-render.md` (SC-001, SC-005).

- [X] T016 [US1] Registrar library `cta_v1` → `assets/css/cta-v1.css` em `themes/custom/default/default.libraries.yml`
- [X] T017 [P] [US1] Criar CSS encapsulado (fundo `#D3E4FE`; radius `32px`; padding `64px`; max-width `1200px` centralizado; gap copy↔actions `24px`; título Poppins semibold/bold `#0F172A`; subtítulo Poppins `#45464D`; primário bg `#FD7B1A` texto branco; secundário bg `#FFFFFF` texto escuro sem borda forte; **somente** seletores sob `.block-cta-v1`; **não** usar `.ui-btn--primary`) em `themes/custom/default/assets/css/cta-v1.css`
- [X] T018 [US1] Implementar Twig do bloco (raiz `.block-cta-v1`; card `.cta-v1`; copy `__title`/`__subtitle`; actions com `.d-grid.gap-2.d-md-flex.justify-content-md-center.gap-md-3`; botões `.cta-v1__btn--primary` / `--secondary`; attach `default/cta_v1`; preservar `attributes`/`content_attributes`/`title_*`; DOM alinhado a `contracts/cta-v1-render.md`) em `themes/custom/default/templates/block/block--block-cta-v1.html.twig`
- [X] T019 [US1] Implementar fallbacks no Twig (omitir título/subtítulo vazios; omitir botão sem URI; omitir `.cta-v1__actions` se ambos ausentes; **não** renderizar card se todos os campos vazios — SC-008) em `themes/custom/default/templates/block/block--block-cta-v1.html.twig`
- [X] T020 [US1] Validar SC-001 e cenários US1 (desktop md+; card abaixo dos números; convivência 013/014 intacta) conforme `specs/015-cta-v1-quem-somos/quickstart.md` seção B e `specs/015-cta-v1-quem-somos/contracts/cta-v1-render.md` (conteúdo de teste manual ou seed US5)

**Checkpoint**: visitante desktop vê o CTA no layout de referência (MVP).

---

## Phase 4: User Story 2 — Visitante mobile vê botões empilhados (Priority: P1)

**Goal**: viewport ≤575.98px — botões empilhados, legíveis, sem scroll horizontal; gap textos↔botões ≈ 24px.  
**Independent Test Criteria**: `/quem-somos` ≤575.98px (SC-002).

- [X] T021 [US2] Confirmar/ajustar CSS mobile (sem overflow-x; tipografia/paddings legíveis; gap `24px`) sob `.block-cta-v1` em `themes/custom/default/assets/css/cta-v1.css`
- [X] T022 [US2] Confirmar classes Bootstrap das actions (`.d-grid.gap-2.d-md-flex.justify-content-md-center.gap-md-3`) no Twig em `themes/custom/default/templates/block/block--block-cta-v1.html.twig`
- [X] T023 [US2] Validar SC-002 / cenários US2 (mobile + gap) conforme `specs/015-cta-v1-quem-somos/quickstart.md` seção C

---

## Phase 5: User Story 3 — Visitante usa os botões de ação (Priority: P1)

**Goal**: cliques nos botões seedados levam a `/vagas` e `/cadastro/candidato` (URLs limpas); edição editorial de URI/texto reflete no front.  
**Independent Test Criteria**: clicar nos dois botões em `/quem-somos` e verificar destino (SC-003).

- [X] T024 [US3] Confirmar markup dos links (href a partir de `field_link` / `field_link_2`; title como rótulo; sem hardcode de URL no Twig como única fonte) em `themes/custom/default/templates/block/block--block-cta-v1.html.twig`
- [X] T025 [US3] Validar SC-003 / cenários US3 (Buscar vagas → `/vagas`; Cadastrar → `/cadastro/candidato`; alteração editorial) conforme `specs/015-cta-v1-quem-somos/quickstart.md` seção D (após seed US5 ou conteúdo manual)

**Checkpoint**: navegação dos CTAs funciona com Clean URLs.

---

## Phase 6: User Story 4 — Editor gerencia conteúdo sem código (Priority: P1)

**Goal**: editor autenticado edita título, subtítulo e os dois links; mudanças refletem em `/quem-somos` sem deploy de código (&lt; 3 min).  
**Independent Test Criteria**: editar no painel, salvar, recarregar `/quem-somos` (SC-004).

- [X] T026 [US4] Revisar form display (widgets, labels claros título/subtítulo/primário/secundário; quatro campos) em `config/sync/core.entity_form_display.block_content.cta_v1.default.yml`
- [X] T027 [US4] Confirmar permissões do bundle `cta_v1` nas roles exportadas sob `config/sync/user.role.*.yml` (alinhado a T014)
- [X] T028 [US4] Confirmar que nenhum texto institucional fica hardcoded como única fonte de verdade (placeholders Twig só para ausência) em `themes/custom/default/templates/block/block--block-cta-v1.html.twig`
- [X] T029 [US4] Validar SC-004 / cenários US4 (edição &lt;3 min) conforme `specs/015-cta-v1-quem-somos/quickstart.md` seção E

---

## Phase 7: User Story 5 — Deploy reproduz estrutura, seed e placement (Priority: P1)

**Goal**: `custom_configs_update_11019` idempotente (ensure block type + `field_link_2` + instances + displays; seed UUID fixo + copy Figma; placement weight 12); fluxo `cim` → `updb` → `cr`; `cex` versiona estrutural; **nunca** sobrescrever editorial nem alterar 009–014/CTO.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; segunda `updb` sem duplicatas; editorial preservado (SC-005–SC-007, SC-006).

- [X] T030 [US5] Implementar `custom_configs_update_11019` idempotente (ensure `cta_v1` + storage `field_link_2` + instances dos quatro campos + form/view displays; seed `BlockContent` UUID `e6f7a8b9-c0d1-4e2f-9a3b-4c5d6e7f8091` com título `Seu próximo estágio começa aqui.`, subtítulo do design, primário `Buscar vagas` → `internal:/vagas`, secundário `Cadastrar gratuitamente` → `internal:/cadastro/candidato` **somente se** ausente/campos vazios; ensure placement `default_ctav1quemsomos` weight 12 + visibility `/quem-somos`; **nunca** sobrescrever editorial divergente; **nunca** tocar 009–014 nem `cto`) em `modules/custom/custom_configs/custom_configs.install`
- [X] T031 [P] [US5] Extrair/reusar helpers privados de ensure/seed/placement no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T032 [US5] Exportar/confirmar configs estruturais com `drush cex -y` para os YAMLs listados em `specs/015-cta-v1-quem-somos/data-model.md` sob `config/sync/` (block type, `field_link_2`, instances, displays, placement, `user.role.*`)
- [X] T033 [US5] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cr` e reexecutar `updb` (SC-005/SC-006/SC-007) conforme `specs/015-cta-v1-quem-somos/quickstart.md` seções A e F
- [X] T034 [US5] Validar fallbacks pós-seed (campos vazios omitidos; card omitido se tudo vazio; bloco ausente fora de `/quem-somos`; SC-008) conforme `specs/015-cta-v1-quem-somos/quickstart.md` seção F

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: PRD §3.6 cirúrgico, isolamento visual (009–014 + CTO/home) e aceite final SC-001–SC-009.

- [X] T035 [P] Atualizar §3.6 (e menção breve em §3.1.0 se necessário) documentando `cta_v1`, quatro campos, placement weight 12, UUID, hook `11019`, library `cta_v1` e convivência com Impact Numbers (11) / Diferenciais (10) em `PRD.md`
- [X] T036 [P] Ampliar linha de deploy do layout v2 Quem Somos para incluir `11019` (junto a `11012`–`11018`) em `PRD.md`
- [X] T037 Executar validação final completa (SC-001–SC-009; isolamento Impact Numbers / Diferenciais / home CTO; checklist deploy fácil) com `specs/015-cta-v1-quem-somos/quickstart.md` e `specs/015-cta-v1-quem-somos/checklists/requirements.md`
- [X] T038 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → Polish (Phase 8)
- US2 depende do markup/CSS base de US1 (T018–T019)
- US3 depende dos links no Twig (T018); aceite completo após seed US5 ou conteúdo manual
- US4 depende de form display (T011) + Twig com fallbacks (T019); ideal após seed US5
- US5 (hook/cex) precisa dos YAMLs da Phase 2; validar pós-Twig mínimo (T018)
- Polish após US1–US5

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) 🎯 MVP
                    ├→ US2 (P1)
                    ├→ US3 (P1) ──┐
                    ├→ US4 (P1) ──┤
                    └→ US5 (P1) ──┴→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002, T003, T004, T005 em paralelo após T001; T006 após T001
- **Foundational**: T008 ∥ após T007; T009 após T007; T010 após T008+T009; T011/T012 após T010; T013 após UUID/plugin definido; T014 ∥ T013; T015 por último
- **US1**: T017 (CSS) em paralelo com início de T018 após T016; T019 após markup estável; T020 por último
- **US2 ∥ início US3**: T021–T022 com T024 após T018
- **US3**: após T018; melhor com seed de US5
- **US4**: após T011 + T019; melhor com seed de US5
- **US5**: T031 junto a T030; T032–T034 após hook
- **Polish**: T035 ∥ T036; depois T037–T038

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (desktop card CTA Figma) — valor principal da feature
2. **Mobile + navegação**: US2 + US3
3. **Editorial + deploy**: US4 (form/perms) + US5 (`11019` + cim/updb/cr + cex)
4. **Fechamento**: Polish (PRD §3.6 + aceite SC-001–SC-009)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T038`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias por prioridade P1: US1 → US2 → US3 → US4 → US5 (todas P1 na spec; ordem reflete dependências de implementação)
