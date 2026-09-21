# Tasks: Números / Estatísticas Quem Somos (Impact in Numbers)

**Input**: Artefatos de design em `specs/014-numeros-quem-somos/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/impact-numbers-render.md`, `quickstart.md`  
**Branch**: `dev`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/014-numeros-quem-somos` (via `.specify/feature.json`); template alinhado a `specs/013-diferenciais-quem-somos/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar storages reutilizáveis, Twig/Node ativos, displays `quem_somos` e último hook antes de criar config/tema/`11017`.

- [X] T001 Confirmar artefatos SDD e feature ativa (`specs/014-numeros-quem-somos/`, `.specify/feature.json`, `.cursor/rules/specify-rules.mdc`)
- [X] T002 [P] Inventariar storages reutilizáveis (não recriar) em `config/sync/field.storage.paragraph.field_text_simple.yml` e `config/sync/field.storage.paragraph.field_text_simple_long.yml`
- [X] T003 [P] Confirmar incompatibilidade de `field_itens_p` (cardinality 3, bundle `para_empresas`) em `config/sync/field.storage.node.field_itens_p.yml` e `config/sync/field.field.node.para_empresas.field_itens_p.yml` — **não** reutilizar
- [X] T004 [P] Inventariar Twig/Node e libraries atuais em `themes/custom/default/templates/content/node--quem-somos.html.twig`, `themes/custom/default/default.libraries.yml` e CSS de referência full-bleed `themes/custom/default/assets/css/quem-somos-missao-visao.css`
- [X] T005 Inventariar form/view displays e Field Groups existentes do Node em `config/sync/core.entity_form_display.node.quem_somos.default.yml` e `config/sync/core.entity_view_display.node.quem_somos.default.yml` (`group_primeiro_bloco`, `group_missao_visao`)
- [X] T006 Confirmar último hook `custom_configs_update_11016` → próximo livre `11017` em `modules/custom/custom_configs/custom_configs.install`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar paragraph type `numero_destaque_p`, storage ERR `field_numeros_lista` (cardinality 4), field instances, form/view displays do paragraph e anexos ao Node `quem_somos` — pré-requisito de todas as user stories. Zero storage paralelo de texto; **não** tocar features 009–013 nem `field_itens_p`.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T007 Confirmar reuso (sem storages `field_text_simple_small` / `field_text_simple_small_2`) dos YAMLs listados em T002 sob `config/sync/field.storage.paragraph.*`
- [X] T008 [P] Criar tipo de paragraph “Número Destaque” em `config/sync/paragraphs.paragraphs_type.numero_destaque_p.yml`
- [X] T009 Criar field storage ERR `field_numeros_lista` (entity type `node`, target `paragraph`, cardinality **4**) em `config/sync/field.storage.node.field_numeros_lista.yml`
- [X] T010 [P] Criar field instances do paragraph em `config/sync/field.field.paragraph.numero_destaque_p.field_text_simple.yml` e `config/sync/field.field.paragraph.numero_destaque_p.field_text_simple_long.yml`
- [X] T011 Criar field instance do Node (handler → somente `numero_destaque_p`) em `config/sync/field.field.node.quem_somos.field_numeros_lista.yml`
- [X] T012 [P] Criar form + view displays do paragraph em `config/sync/core.entity_form_display.paragraph.numero_destaque_p.default.yml` e `config/sync/core.entity_view_display.paragraph.numero_destaque_p.default.yml`
- [X] T013 Atualizar form display do Node (widget paragraphs; default type `numero_destaque_p`; Field Group opcional `group_numeros` alinhado a `group_missao_visao`) em `config/sync/core.entity_form_display.node.quem_somos.default.yml`
- [X] T014 [P] Atualizar view display do Node (lista via `entity_reference_revisions_entity_view`; campos 009–012 intactos) em `config/sync/core.entity_view_display.node.quem_somos.default.yml`
- [X] T015 Importar/validar estrutura local (`drush cim -y` ou UI + `drush cex -y`) — tipo **Número Destaque** em Estrutura → Tipos de parágrafo; `field_numeros_lista` no Node Quem Somos com máx. 4; **sem** alterar banner/Sobre nós/Missão-Visão/Diferenciais

**Checkpoint**: estrutura sobe só com `cim`; front ainda sem seção Impact in Numbers custom; seed ainda ausente.

---

## Phase 3: User Story 1 — Visitante vê Impact in Numbers em Quem Somos (Priority: P1) 🎯 MVP

**Goal**: viewport ≥768px — faixa escura full-bleed com 4 estatísticas em uma linha; destaque laranja Poppins 700; subtexto branco opacity 0.8 uppercase; classe raiz `section-impact-numbers`.  
**Independent Test Criteria**: abrir `/quem-somos` ≥768px com Node populado e comparar fundo, tipografia, ordem e copy com `contracts/impact-numbers-render.md` (SC-001).

- [X] T016 [US1] Registrar library `impact_numbers` → CSS dedicado em `themes/custom/default/default.libraries.yml`
- [X] T017 [P] [US1] Criar CSS encapsulado (fundo `#0F172A`; paddings `64px`/`40px`; max-width `1280px`; full-bleed breakout padrão 012; destaque Poppins 700 `#FD7B1A` caixa ~56px; subtexto `#FFFFFF` opacity 0.8 uppercase caixa ~20px; gap `8px`; **somente** seletores sob `.section-impact-numbers`) em `themes/custom/default/assets/css/impact-numbers.css`
- [X] T018 [US1] Estender Twig do Node (após Missão/Visão; `<section class="section-impact-numbers">` se ≥1 item utilizável; attach `default/impact_numbers`; `content|without('field_numeros_lista')` no restante; **não** alterar markup Sobre nós / Missão-Visão) em `themes/custom/default/templates/content/node--quem-somos.html.twig`
- [X] T019 [P] [US1] Implementar Twig do paragraph (coluna `.col-6.col-md-3`; item `.section-impact-numbers__item` flex column centrado; classes `__stat` / `__label`; omitir destaque/subtexto vazios; omitir item se ambos vazios) em `themes/custom/default/templates/paragraph/paragraph--numero-destaque-p.html.twig`
- [X] T020 [US1] Implementar fallbacks no Twig do Node (lista vazia → omitir seção; 1–3 itens → renderizar só existentes; DOM alinhado a `contracts/impact-numbers-render.md`) em `themes/custom/default/templates/content/node--quem-somos.html.twig`
- [X] T021 [US1] Validar SC-001 e cenários US1 (desktop md+) conforme `specs/014-numeros-quem-somos/quickstart.md` seção B e `specs/014-numeros-quem-somos/contracts/impact-numbers-render.md` (conteúdo de teste manual ou seed US4)

**Checkpoint**: visitante desktop vê a faixa Impact in Numbers no layout de referência (MVP).

---

## Phase 4: User Story 2 — Visitante mobile vê 2 itens por linha (Priority: P1)

**Goal**: viewport ≤575.98px — 2 itens/linha, legível, sem scroll horizontal; gap destaque↔subtexto ≈ 8px.  
**Independent Test Criteria**: `/quem-somos` ≤575.98px (SC-002).

- [X] T022 [US2] Confirmar/ajustar CSS mobile (sem overflow-x; tipografia/paddings legíveis; gap `8px`) sob `.section-impact-numbers` em `themes/custom/default/assets/css/impact-numbers.css`
- [X] T023 [US2] Confirmar classes Bootstrap do item (`.col-6.col-md-3`) no Twig do paragraph em `themes/custom/default/templates/paragraph/paragraph--numero-destaque-p.html.twig`
- [X] T024 [US2] Validar SC-002 / cenários US2 (mobile + gap) conforme `specs/014-numeros-quem-somos/quickstart.md` seção C

---

## Phase 5: User Story 3 — Editor gerencia os quatro números no Node (Priority: P1)

**Goal**: editor autentificado edita até 4 paragraphs “Número Destaque” (destaque + subtexto); 5º item bloqueado; mudanças refletem em `/quem-somos` sem deploy de código.  
**Independent Test Criteria**: editar no painel, salvar, recarregar `/quem-somos` (&lt; 3 min — SC-003).

- [X] T025 [US3] Revisar form displays (widgets, labels, lista paragraphs, cardinality 4, Field Group opcional `group_numeros`) em `config/sync/core.entity_form_display.node.quem_somos.default.yml` e `config/sync/core.entity_form_display.paragraph.numero_destaque_p.default.yml`
- [X] T026 [US3] Confirmar que nenhum texto institucional fica hardcoded como única fonte de verdade (placeholders Twig só para ausência) em `themes/custom/default/templates/content/node--quem-somos.html.twig` e `themes/custom/default/templates/paragraph/paragraph--numero-destaque-p.html.twig`
- [X] T027 [US3] Validar SC-003 / cenários US3 (edição &lt;3 min; bloqueio do 5º item; item parcial) conforme `specs/014-numeros-quem-somos/quickstart.md` seção D

---

## Phase 6: User Story 4 — Deploy reproduz estrutura e seed em outro ambiente (Priority: P1)

**Goal**: `custom_configs_update_11017` idempotente (ensure paragraph type + storage/instance + displays; seed 4 itens se lista vazia); fluxo `cim` → `updb` → `cr`; `cex` versiona estrutural; **nunca** sobrescrever editorial nem alterar 009–013.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; segunda `updb` sem duplicatas; editorial preservado (SC-005–SC-007).

- [X] T028 [US4] Implementar `custom_configs_update_11017` idempotente (ensure `numero_destaque_p` + `field_numeros_lista` card 4 + instances + form/view displays; localizar Node `quem_somos`; seed 4 paragraphs — `20k+`/`ESTUDANTES ATIVOS`, `1.2k+`/`EMPRESAS PARCEIRAS`, `8k+`/`ESTÁGIOS INICIADOS`, `95%`/`SATISFAÇÃO GLOBAL` — **somente se** lista vazia/ausente; **nunca** sobrescrever editorial; **nunca** tocar fields/blocos 009–013) em `modules/custom/custom_configs/custom_configs.install`
- [X] T029 [P] [US4] Extrair/reusar helpers privados de ensure/seed no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T030 [US4] Exportar/confirmar configs estruturais com `drush cex -y` para os YAMLs listados em `specs/014-numeros-quem-somos/data-model.md` sob `config/sync/` (paragraph type, storage, instances, displays node/paragraph)
- [X] T031 [US4] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cr` e reexecutar `updb` (SC-005/SC-006) conforme `specs/014-numeros-quem-somos/quickstart.md` seções A e E
- [X] T032 [US4] Validar fallbacks pós-seed (lista vazia → seção omitida; 1–3 itens; campos vazios; SC-007) conforme `specs/014-numeros-quem-somos/quickstart.md` seção E

---

## Phase 7: Polish & Cross-Cutting Concerns

**Objetivo**: PRD §3.1.0 cirúrgico, isolamento visual (009–013 + home) e aceite final SC-001–SC-008.

- [X] T033 [P] Atualizar §3.1.0 documentando `numero_destaque_p`, `field_numeros_lista` (card 4), tokens/library `impact_numbers`, hook `11017` e convivência com bloco 013 em `content_full` em `PRD.md`
- [X] T034 [P] Ampliar linha de deploy do layout v2 Quem Somos para incluir `11017` (junto a `11013`/`11015`/`11016`) em `PRD.md`
- [X] T035 Executar validação final completa (SC-001–SC-008; isolamento Sobre nós / Missão-Visão / Diferenciais / home; checklist deploy fácil) com `specs/014-numeros-quem-somos/quickstart.md` e `specs/014-numeros-quem-somos/checklists/requirements.md`
- [X] T036 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → Polish (Phase 7)
- US2 depende do markup/CSS base de US1 (T018–T020)
- US3 depende de form displays (T013/T012) + Twig com fallbacks (T020); ideal após seed mínimo (US4) ou conteúdo manual de teste
- US4 (hook/cex) precisa dos YAMLs da Phase 2; validar pós-Twig mínimo (T018–T019)
- Polish após US1–US4

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) 🎯 MVP
                    ├→ US2 (P1)
                    ├→ US3 (P1) ──┐
                    └→ US4 (P1) ──┴→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002, T003, T004 em paralelo após T001; T005 ∥ T006 após T001
- **Foundational**: T008 ∥ após T007; T009 após T007; T010 após T008; T011 após T008+T009; T012 ∥ após T010; T013/T014 após T011; T015 por último
- **US1**: T017 (CSS) em paralelo com início de T018 após T016; T019 ∥ T018 (arquivos distintos); T020 após markup estável
- **US2 ∥ início US3**: T022–T023 com revisão de forms T025 após T018/T019
- **US3**: após T013 + T020; melhor com seed de US4
- **US4**: T029 junto a T028; T030–T032 após hook
- **Polish**: T033 ∥ T034; depois T035–T036

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (desktop faixa escura + 4 números) — valor principal da feature
2. **Mobile**: US2 (grid 2 colunas, sem overflow-x)
3. **Editorial + deploy**: US3 (form/cardinality) + US4 (`11017` + cim/updb/cr + cex)
4. **Fechamento**: Polish (PRD §3.1.0 + aceite SC-001–SC-008)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T036`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias por prioridade P1: US1 → US2 → US3 → US4 (todas P1 na spec; ordem reflete dependências de implementação)
