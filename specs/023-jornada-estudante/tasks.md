# Tasks: Jornada do Estudante

**Input**: Artefatos de design em `specs/023-jornada-estudante/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/jornada-estudante-render.md`, `contracts/deploy-jornada-estudante.md`, `quickstart.md`  
**Branch**: `feature-para-estudantes` (ou branch ativa alinhada à feature)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/023-jornada-estudante` (via `.specify/feature.json`); template alinhado a `specs/022-beneficios-estudantes/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar storages canônicos, bloco benefícios `022` (não reutilizar Twig/CSS), hero `021`, placement `content_full` weight `0` em `/para-estudantes` e último hook `11036` antes de criar config/tema/`11037`.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/jornada-estudante-render.md`, `contracts/deploy-jornada-estudante.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/023-jornada-estudante/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar storages reutilizáveis (não recriar; **não** alterar cardinality `field_itens_lista` = `-1`) em `config/sync/field.storage.block_content.field_text_simple.yml`, `config/sync/field.storage.block_content.field_itens_lista.yml`, `config/sync/field.storage.paragraph.field_text_simple.yml` e `config/sync/field.storage.paragraph.field_text_simple_long.yml`
- [X] T003 [P] Inventariar padrão visual mais próximo (022 — cabeçalho + grid `col-12/md-6/lg-3`; **não** reutilizar markup/CSS/classes `.be-*`) em `themes/custom/default/templates/block/block--block-beneficios-estudantes.html.twig`, `themes/custom/default/templates/paragraph/paragraph--card-icon-text-p.html.twig`, `themes/custom/default/assets/css/beneficios-estudantes.css` e `themes/custom/default/default.libraries.yml`
- [X] T004 [P] Inventariar convivência hero `021` + benefícios `022` + View `vagas` em `/para-estudantes` em `config/sync/block.block.default_views_block__banners_block_para_estudantes.yml`, `config/sync/block.block.default_beneficiosestudantes.yml` (weight `0`, região `content_full`) e ausência de `jornada_estudante` / `default_jornadaestudante`
- [X] T005 Confirmar último hook `custom_configs_update_11036` → próximo livre `11037` e helpers reutilizáveis de ensure/seed/placement (padrão `_custom_configs_*_beneficios_estudantes`) em `modules/custom/custom_configs/custom_configs.install`; confirmar ausência de form alter max-cardinality em `modules/custom/custom_configs/custom_configs.module`
- [X] T006 [P] Confirmar que `.cursor/rules/drupal-deploy-configs.mdc` já cobre FR-019–FR-021 (`hook_update_N` + `cex` + fluxo `cim`→`updb`→`cim`→`cr`); anotar lacuna só se houver gap factual vs. `specs/023-jornada-estudante/spec.md`

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar paragraph type `passo_jornada_p`, block type `jornada_estudante`, field instances (reuso de storages), form/view displays e placement `default_jornadaestudante` weight `1` — pré-requisito de todas as user stories. **Zero** field storage novo. **Não** alterar cardinality de `field_itens_lista`. **Não** tocar hero `021`/benefícios `022`/View `vagas`/PE/QS/home.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T007 Confirmar reuso (sem storage paralelo `field_text_simple_small` / lista nova; sem alterar cardinality de `field_itens_lista`) dos YAMLs listados em T002 sob `config/sync/field.storage.*`
- [X] T008 [P] Criar tipo de paragraph “Passo da Jornada” em `config/sync/paragraphs.paragraphs_type.passo_jornada_p.yml`
- [X] T009 [P] Criar tipo de bloco “Jornada do Estudante” em `config/sync/block_content.type.jornada_estudante.yml` (`revision` alinhado aos demais block types)
- [X] T010 [P] Criar field instances do paragraph em `config/sync/field.field.paragraph.passo_jornada_p.field_text_simple.yml` e `config/sync/field.field.paragraph.passo_jornada_p.field_text_simple_long.yml`
- [X] T011 Criar field instances do bloco em `config/sync/field.field.block_content.jornada_estudante.field_text_simple.yml` e `config/sync/field.field.block_content.jornada_estudante.field_itens_lista.yml` (handler → somente `passo_jornada_p`; storage permanece `-1` — max 4 só no form/validação US3)
- [X] T012 [P] Criar form + view displays do paragraph em `config/sync/core.entity_form_display.paragraph.passo_jornada_p.default.yml` e `config/sync/core.entity_view_display.paragraph.passo_jornada_p.default.yml`
- [X] T013 Criar form display do bloco (widget paragraphs; default type `passo_jornada_p`) em `config/sync/core.entity_form_display.block_content.jornada_estudante.default.yml`
- [X] T014 [P] Criar view display do bloco (lista via `entity_reference_revisions_entity_view`) em `config/sync/core.entity_view_display.block_content.jornada_estudante.default.yml`
- [X] T015 Criar placement `default_jornadaestudante` (tema `default`, região `content_full`, weight **`1`**, `label_display: '0'`, `request_path` = `/para-estudantes`, plugin UUID `b9c0d1e2-f3a4-4567-b890-1cdef0123456`) em `config/sync/block.block.default_jornadaestudante.yml`
- [X] T016 Importar/validar estrutura local (`drush cim -y` ou UI + `drush cex -y`) — tipos em Estrutura → Tipos de bloco / Tipos de parágrafo; **sem** alterar hero `021`/benefícios `022`/View `vagas`/PE/QS/home; seed de conteúdo ainda ausente (hook US5)

**Checkpoint**: estrutura sobe só com `cim`; front ainda sem Twig/CSS custom; seed ainda ausente; max 4 ainda não enforced no form.

---

## Phase 3: User Story 1 — Visitante vê a jornada em Para Estudantes (Priority: P1) 🎯 MVP

**Goal**: viewport ≥992px — título centralizado “Sua jornada até o sucesso” + grid 4 cards (`col-lg-3`); badge 48px (`loop.index`); badges 1–3 navy `#023C62`; último (`loop.last`) laranja `#FD7B1A`; card min-height ~202px, raio 16px, paddings ~64/40; classe raiz `block-jornada-estudante`; DOM alinhado ao contrato.  
**Independent Test Criteria**: abrir `/para-estudantes` ≥992px com bloco publicado e comparar estrutura (título + 4 passos numerados, último laranja) com Figma/`contracts/jornada-estudante-render.md` (SC-001).

- [X] T017 [US1] Registrar library `jornada_estudante` → `assets/css/jornada-estudante.css` em `themes/custom/default/default.libraries.yml`
- [X] T018 [P] [US1] Criar CSS encapsulado (container max `1280px`; paddings ~`64px`/`40px`; badge `48×48`; card `border-radius: 16px` + `min-height` ~`202px` + sombra sutil; `.je-badge` navy `#023C62`; `.je-badge--last` laranja `#FD7B1A`; Poppins; **somente** seletores sob `.block-jornada-estudante`; **não** mutar `--brand-orange` global) em `themes/custom/default/assets/css/jornada-estudante.css`
- [X] T019 [US1] Implementar Twig do bloco (wrapper `.je-container`; `h2.je-header__title.text-center`; grid `.row.justify-content-center.g-4.mt-4.je-grid`; **loop** sobre `field_itens_lista` com `loop.index`/`loop.last` renderizando badge + card; attach library; `id="jornada-estudante"`; preservar `attributes`/`content_attributes`/`title_*`; **não** reusar classes `.block-beneficios-estudantes` / `.be-*`) em `themes/custom/default/templates/block/block--block-jornada-estudante.html.twig`
- [X] T020 [P] [US1] Implementar Twig do paragraph (coluna `.col-12.col-md-6.col-lg-3` + chrome de card se o bloco não encapsular tudo; omitir título/descrição vazios; **sem** badge própria se o bloco já a renderiza — alinhar a research R4/R6) em `themes/custom/default/templates/paragraph/paragraph--passo-jornada-p.html.twig`
- [X] T021 [US1] Implementar fallbacks no Twig do bloco (omitir `h2` vazio; zero passos → só cabeçalho se houver; &lt;4 passos → badges 1…N e último da lista laranja; sem fatal) em `themes/custom/default/templates/block/block--block-jornada-estudante.html.twig`
- [X] T022 [US1] Validar SC-001 e cenários US1 (desktop lg+: título/4 cards/badges 1–3 navy/badge 4 laranja/tokens 48/16/202) conforme `specs/023-jornada-estudante/quickstart.md` seção B e `specs/023-jornada-estudante/contracts/jornada-estudante-render.md` (conteúdo de teste manual ou seed US5)

**Checkpoint**: visitante desktop vê a seção completa no layout de referência (MVP).

---

## Phase 4: User Story 2 — Visitante tablet/mobile vê grade adaptada (Priority: P1)

**Goal**: viewport md — 2 cards/linha (`col-md-6`); mobile estreito — 1/linha (`col-12`); legível; sem scroll horizontal causado pelo bloco.  
**Independent Test Criteria**: abrir `/para-estudantes` em ≤575.98px e 768–991px (SC-002).

- [X] T023 [US2] Confirmar/ajustar CSS responsivo (sem overflow-x; tipografia legível; badge 48px; card `min-height` preservado) sob `.block-jornada-estudante` em `themes/custom/default/assets/css/jornada-estudante.css`
- [X] T024 [US2] Confirmar classes Bootstrap do card (`.col-12.col-md-6.col-lg-3`) no Twig do bloco e/ou paragraph em `themes/custom/default/templates/block/block--block-jornada-estudante.html.twig` e `themes/custom/default/templates/paragraph/paragraph--passo-jornada-p.html.twig`
- [X] T025 [US2] Validar SC-002 / cenários US2 (mobile 1/linha; md 2/linha; sem scroll horizontal) conforme `specs/023-jornada-estudante/quickstart.md` seção B itens 2–3

**Checkpoint**: grade adaptada em tablet/mobile sem regressão desktop.

---

## Phase 5: User Story 4 — Bloco aparece só em Para Estudantes, após Benefícios (Priority: P1)

**Goal**: bloco só em `/para-estudantes` (`content_full` weight `1`); home / PE / QS sem `jornada_estudante`; ordem vertical hero (`021`) → benefícios (`022`, weight `0`) → jornada (weight `1`); View `vagas` intacta.  
**Independent Test Criteria**: comparar `/para-estudantes`, `<front>`, `/para-empresas` e `/quem-somos`; confirmar ordem hero → benefícios → jornada (SC-004).

- [X] T026 [US4] Confirmar YAML do placement (path `/para-estudantes`, região `content_full`, weight **`1`**, UUID alinhado ao seed) em `config/sync/block.block.default_jornadaestudante.yml`
- [X] T027 [P] [US4] Confirmar que configs/Twig/CSS do hero `021`, benefícios `022` e home permanecem inalterados em `config/sync/block.block.default_views_block__banners_block_para_estudantes.yml`, `config/sync/block.block.default_beneficiosestudantes.yml`, `themes/custom/default/templates/block/block--block-beneficios-estudantes.html.twig` e escopo CSS (sem seletores em `.hero-estudantes-wrapper` / `.block-beneficios-estudantes` / `.be-*`)
- [X] T028 [US4] Validar SC-004 / cenários US4 (presença só em `/para-estudantes`; ordem hero → benefícios → jornada; ausência em home/PE/QS) conforme `specs/023-jornada-estudante/quickstart.md` seção D

**Checkpoint**: isolamento de rota e ordem de leitura garantidos.

---

## Phase 6: User Story 3 — Editor gerencia conteúdo sem código (Priority: P1)

**Goal**: editor com permissão edita título da seção, títulos/descrições dos passos e ordem (até 4); badges renumeram pela posição; tentativa de 5º passo impedida (form alter + validação); mudanças refletem em `/para-estudantes` sem deploy de código.  
**Independent Test Criteria**: editar no painel, salvar, recarregar `/para-estudantes`; tentar 5º passo e confirmar bloqueio (SC-003 / FR-004).

- [X] T029 [US3] Incluir permissões create/edit/delete do bundle `jornada_estudante` nas roles que já gerenciam block content e exportar diffs em `config/sync/user.role.*.yml` afetados
- [X] T030 [US3] Revisar form displays (widgets, labels, lista paragraphs default `passo_jornada_p`, obrigatoriedade editorial do título do passo se aplicável) em `config/sync/core.entity_form_display.block_content.jornada_estudante.default.yml` e `config/sync/core.entity_form_display.paragraph.passo_jornada_p.default.yml`
- [X] T031 [US3] Implementar limite max 4 passos no bundle `jornada_estudante` (`hook_form_alter` ocultando/desabilitando “Add more” com ≥4 itens + validação de submit rejeitando >4; **sem** mutar storage `field_itens_lista`) em `modules/custom/custom_configs/custom_configs.module` (e helpers no mesmo módulo se necessário)
- [X] T032 [US3] Confirmar que nenhum copy institucional fica hardcoded como única fonte de verdade (placeholders Twig só para ausência; número/cor só via `loop.index`/`loop.last`) em `themes/custom/default/templates/block/block--block-jornada-estudante.html.twig` e `themes/custom/default/templates/paragraph/paragraph--passo-jornada-p.html.twig`
- [X] T033 [US3] Validar SC-003 / cenários US3 (edição &lt;5 min; reorder renumera badges; último da lista fica laranja; 5º passo bloqueado) conforme `specs/023-jornada-estudante/quickstart.md` seção C

**Checkpoint**: conteúdo editorial gerenciável sem código; cardinalidade 4 enforced sem alterar storage.

---

## Phase 7: User Story 5 — Deploy reproduz estrutura e seed em outro ambiente (Priority: P1)

**Goal**: `custom_configs_update_11037` idempotente — ensure types/fields/displays; seed UUID `b9c0d1e2-f3a4-4567-b890-1cdef0123456` + título + 4 passos (copy Figma); ensure placement weight `1`; **nunca** duplicar nem sobrescrever editorial divergente; **sem** assets de imagem; fluxo `cim` → `updb` → `cim` → `cr`; `cex` versiona estrutural.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; 2ª `updb` sem duplicatas; editorial preservado (SC-005, SC-006, SC-007).

- [X] T034 [US5] Implementar `custom_configs_update_11037` idempotente (ensure `passo_jornada_p` + fields + displays; ensure `jornada_estudante` + fields/handler lista → `passo_jornada_p` + displays; seed `BlockContent` UUID `b9c0d1e2-f3a4-4567-b890-1cdef0123456` + título “Sua jornada até o sucesso” + 4 `passo_jornada_p` com copy das Assumptions **somente se vazios/ausentes**; ensure placement `default_jornadaestudante` região `content_full` / pages `/para-estudantes` / weight **`1`**; mensagem Drush created/skipped; **nunca** sobrescrever editorial divergente; **nunca** alterar storage `field_itens_lista` nem hero `021`/benefícios `022`/View `vagas`/PE/QS/home) em `modules/custom/custom_configs/custom_configs.install`
- [X] T035 [P] [US5] Extrair/reusar helpers privados (ensure type/fields/displays / seed block+paragraphs / ensure placement) no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T036 [US5] Exportar/confirmar configs estruturais com `drush cex -y` para os YAMLs listados em `specs/023-jornada-estudante/data-model.md` sob `config/sync/` (tipos, instances, displays, placement, `user.role.*`)
- [X] T037 [US5] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (SC-005/SC-006) conforme `specs/023-jornada-estudante/quickstart.md` seções A e E e `specs/023-jornada-estudante/contracts/deploy-jornada-estudante.md`
- [X] T038 [US5] Validar fallbacks pós-seed (título vazio omitido; zero passos → só cabeçalho; 2 passos → badges 1–2 e 2º laranja; SC-007) e isolamento pós-deploy conforme `specs/023-jornada-estudante/quickstart.md` seções D e E

**Checkpoint**: deploy 100% automatizado; `/para-estudantes` reproduzível sem admin manual.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, specify-rules, reforço opcional da regra de deploy, isolamento visual (hero 021 / benefícios 022 / home) e aceite final SC-001–SC-008.

- [X] T039 [P] Atualizar §3.6 (rota `/para-estudantes`) documentando `jornada_estudante`, `passo_jornada_p`, placement `content_full` weight `1` `/para-estudantes`, UUID `b9c0…`, hook `11037` e convivência com hero `021` + benefícios `022` + View `vagas` em `PRD.md`
- [X] T040 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para refletir feature `023-jornada-estudante` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T041 [P] Reforçar `.cursor/rules/drupal-deploy-configs.mdc` **somente se** T006 identificar lacuna vs. FR-019–FR-021; caso contrário, no-op documentado
- [X] T042 Executar validação final completa (SC-001–SC-008; isolamento hero/benefícios/home “Como funciona”; checklist deploy) com `specs/023-jornada-estudante/quickstart.md` e `specs/023-jornada-estudante/checklists/requirements.md`
- [X] T043 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US4 (Phase 5) → US3 (Phase 6) → US5 (Phase 7) → Polish (Phase 8)
- US2 depende do markup/CSS base de US1 (T019–T021)
- US4 valida sobretudo o Foundational (T015); pode rodar em paralelo com US1 após Phase 2 (aceite completo após seed US5)
- US3 depende de form displays (T012/T013) + Twig com fallbacks (T021) + form alter max 4 (T031); ideal após seed mínimo (US5) ou conteúdo manual de teste
- US5 (hook/cex) precisa dos YAMLs da Phase 2; validar pós-Twig mínimo (T019–T020)
- Polish após US1–US5

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) 🎯 MVP
                    ├→ US2 (P1)
                    ├→ US4 (P1) ──┐
                    ├→ US3 (P1) ──┤  (inclui form alter max 4)
                    └→ US5 (P1) ──┴→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002–T004 ∥ T006 após T001; T005 após T001
- **Foundational**: T008 ∥ T009 após T007; T010 após T008; T011 após T009; T012 ∥ após T010; T013/T014 após T011; T015 após UUID/plugin definido; T016 por último
- **US1**: T018 (CSS) em paralelo com início de T019 após T017; T020 ∥ T019 (arquivos distintos); T021 após markup estável; T022 por último
- **US2 ∥ US4**: T023–T024 com T026–T027 após T019/T020
- **US3**: após T013 + T021; T031 (form alter) paralelo a T029/T030; melhor com seed de US5
- **US5**: T035 junto a T034; T036–T038 após hook
- **Polish**: T039 ∥ T040 ∥ T041; depois T042–T043

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (desktop título + grid 4 passos numerados, último laranja em `/para-estudantes`) — valor principal da feature
2. **Mobile + isolamento de rota**: US2 + US4
3. **Editorial + deploy**: US3 (roles/form + max 4) + US5 (`11037` + cim/updb/cim/cr + cex; sem assets de imagem)
4. **Fechamento**: Polish (PRD §3.6 + specify-rules + aceite SC-001–SC-008)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T043`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias por prioridade P1: US1 → US2 → US4 → US3 → US5 (todas P1 na spec; ordem reflete dependências de implementação)
