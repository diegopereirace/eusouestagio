# Tasks: Cards Laranja — Vagas em Para Estudantes

**Input**: Artefatos de design em `specs/025-vagas-cards-estudantes/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/vagas-cards-estudantes-render.md`, `contracts/deploy-vagas-cards-estudantes.md`, `quickstart.md`  
**Branch**: `feature-para-estudantes` (ou branch ativa alinhada à feature)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/025-vagas-cards-estudantes` (via `.specify/feature.json`); template alinhado a `specs/024-perfil-destaque-estudante/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar Twig/CSS Home (`003`), Twig legado `page_1`, wrapper `.css-vagas-page`, View `vagas` display `page_1` e último hook `11039` → próximo `11040` antes de alterar tema/config/install.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/vagas-cards-estudantes-render.md`, `contracts/deploy-vagas-cards-estudantes.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/025-vagas-cards-estudantes/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar markup de referência do card laranja Home (`.item-vaga--destaque`, ícone, regime, meta empresa • local, salário, Ver Mais, Inscreva-se, Buscar mais vagas) em `themes/custom/default/templates/views/views-view-field--vagas--block-1--nothing.html.twig`
- [X] T003 [P] Inventariar Twig legado `page_1` (card branco / `field_text_simple_2` / “Ver Detalhes”) e wrappers de listagem (`.css-vagas-page`, `.view-content.row.g-4`) em `themes/custom/default/templates/views/views-view-field--vagas--page-1--nothing.html.twig`, `themes/custom/default/templates/views/views-view--vagas--page-1.html.twig` e `themes/custom/default/templates/views/views-view-unformatted--vagas--page-1.html.twig`
- [X] T004 [P] Inventariar seletores CSS atuais do card destaque (escopo só `.css-vagas-home .item-vaga--destaque…`, token `#FD761A`, media queries) em `themes/custom/default/assets/css/style.css` (~linhas 1030–1260)
- [X] T005 Confirmar estado atual do display `page_1` em `config/sync/views.view.vagas.yml` (path `para-estudantes`, `css_class: css-vagas-page container`, header “Vagas Disponíveis”, `row_class` herdado `col-md-4 col-12`, filtros/pager/empty intactos) e que `block_1` permanece fora de redesign
- [X] T006 Confirmar último hook `custom_configs_update_11039` → próximo livre `11040` e standard de helpers idempotentes em `modules/custom/custom_configs/custom_configs.install`
- [X] T007 [P] Confirmar que `.cursor/rules/drupal-deploy-configs.mdc` já cobre FR-010 (`hook_update_N` + `cex` + fluxo `cim`→`updb`→`cim`→`cr`); anotar lacuna só se houver gap factual vs. `specs/025-vagas-cards-estudantes/spec.md`

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar restrições compartilhadas — zero field storage/View Mode novo; não tocar Twig/header/footer de `block_1`; não alterar filtros/sort/pager/path/empty/`use_ajax` de `page_1`; não redesenhar `block_2`. Pré-requisito de todas as user stories.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T008 Confirmar reuso Fields + Custom Text `nothing` em `page_1` (sem View Mode / storage / instance novos) conforme `specs/025-vagas-cards-estudantes/data-model.md` e `config/sync/views.view.vagas.yml`
- [X] T009 [P] Congelar escopo negativo: **não** editar `themes/custom/default/templates/views/views-view-field--vagas--block-1--nothing.html.twig` nem `themes/custom/default/templates/views/views-view-field--vagas--block-2--nothing.html.twig` nesta feature
- [X] T010 [P] Documentar checklist de preservação funcional de `page_1` (filters, exposed_form, pager 12, empty, path `para-estudantes`, `use_ajax`) a validar nas US4/US5 em `specs/025-vagas-cards-estudantes/contracts/vagas-cards-estudantes-render.md` (seção Isolamento) — sem alterar YAML ainda
- [X] T011 Confirmar que o wrapper da view já carrega `.css-vagas-page` + `.row.g-4` e que o gap de grid é só `row_class` do style (alvo `col-12 col-md-6 col-lg-4`) em `themes/custom/default/templates/views/views-view--vagas--page-1.html.twig` + `config/sync/views.view.vagas.yml`

**Checkpoint**: restrições claras; pronto para Twig/CSS/config de `page_1` sem risco de redesenhar Home.

---

## Phase 3: User Story 1 — Visitante vê cards laranja em Para Estudantes (Priority: P1) 🎯 MVP

**Goal**: reescrever Twig `page_1` com markup `.item-vaga--destaque` (ícone FA via `field_icone_fa` + fallback `briefcase`; regime; título; meta `field_empresa_u`→`field_nome_fantasia` • local; salário; Ver Mais; Inscreva-se; **sem** “Buscar mais vagas”); expandir CSS para `.css-vagas-page .item-vaga--destaque` sem regressão Home.  
**Independent Test Criteria**: abrir `/para-estudantes` com ≥3 vagas e comparar card com Home (cores, hierarquia, CTAs) — SC-001 / quickstart B.

- [X] T012 [US1] Reescrever Twig do campo Custom Text `page_1` espelhando o markup Home (`.item-vaga.item-vaga--destaque` + `item-inner` + `vaga-destaque__*`; dados via `row._entity`; empresa = `field_empresa_u.entity.field_nome_fantasia` **não** `field_text_simple_2`; ícone 1º termo `field_cursos_t`→`field_icone_fa` fallback `briefcase`; truncamentos título 42 / meta 48 / salário 28 / regime 14; omit empty alinhado à Home; **omitir** botão “Buscar mais vagas”; CTAs → `path('entity.node.canonical', …)`) em `themes/custom/default/templates/views/views-view-field--vagas--page-1--nothing.html.twig`
- [X] T013 [P] [US1] Expandir seletores CSS do card destaque de `.css-vagas-home .item-vaga--destaque…` para o grupo `.css-vagas-home .item-vaga--destaque, .css-vagas-page .item-vaga--destaque` (e descendentes / media queries equivalentes); manter token `#FD761A`; **sem** library nova; **sem** alterar visual Home além do seletor compartilhado) em `themes/custom/default/assets/css/style.css`
- [X] T014 [US1] Remover vestígios do card legado no Twig `page_1` (classes `.row-custom`, “Ver Detalhes”, pills de curso do layout antigo) em `themes/custom/default/templates/views/views-view-field--vagas--page-1--nothing.html.twig`
- [X] T015 [US1] Validar edges Twig (sem regime → `item-regime--empty`; sem empresa/local; sem salário; curso sem ícone; textos longos) no mesmo arquivo Twig e contra `specs/025-vagas-cards-estudantes/contracts/vagas-cards-estudantes-render.md`
- [X] T016 [US1] Validar SC-001 / cenários US1 (paridade visual Home × `/para-estudantes`; sem “Buscar mais vagas”; sem “Ver Detalhes”) conforme `specs/025-vagas-cards-estudantes/quickstart.md` seção B (após `drush cr`)

**Checkpoint**: visitante vê cards laranja na listagem — MVP de produto.

---

## Phase 4: User Story 2 — Grid responsivo 3 / 2 / 1 (Priority: P1)

**Goal**: override de `style.options.row_class` em `page_1` para `col-12 col-md-6 col-lg-4`; manter `.row.g-4` do Twig; Home permanece `col-md-4 col-12`.  
**Independent Test Criteria**: viewports ≥992 / 768–991 / ≤575 — SC-002 / quickstart C.

- [X] T017 [US2] Ajustar `row_class` do display `page_1` da View `vagas` para `col-12 col-md-6 col-lg-4` (style override no display; **não** alterar `block_1`) via admin/API e refletir em `config/sync/views.view.vagas.yml` (export formal na US5)
- [X] T018 [P] [US2] Confirmar que `views-view--vagas--page-1.html.twig` / unformatted mantêm `.view-content.row.g-4` sem classes conflitantes em `themes/custom/default/templates/views/views-view--vagas--page-1.html.twig` e `themes/custom/default/templates/views/views-view-unformatted--vagas--page-1.html.twig`
- [X] T019 [US2] Validar SC-002 / cenários US2 (3 / 2 / 1 colunas; sem scroll horizontal causado pelos cards) conforme `specs/025-vagas-cards-estudantes/quickstart.md` seção C

**Checkpoint**: grid Figma 3/2/1 só em `/para-estudantes`.

---

## Phase 5: User Story 4 — Navegação a partir do card (Priority: P1)

**Goal**: “Ver Mais” e “Inscreva-se” → canonical da vaga; filtros expostos e paginação de `page_1` permanecem funcionais com cards laranja; empty state intacto.  
**Independent Test Criteria**: clicar CTAs + filtrar + paginar — SC-003 / FR-009 / quickstart E.

- [X] T020 [US4] Confirmar hrefs dos CTAs (“Ver Mais” / “Inscreva-se”) apontam para `entity.node.canonical` da vaga no Twig `page_1` em `themes/custom/default/templates/views/views-view-field--vagas--page-1--nothing.html.twig`
- [X] T021 [P] [US4] Confirmar que `filters` / `exposed_form` / `pager` / `empty` / `path` / `use_ajax` de `page_1` **não** foram alterados em `config/sync/views.view.vagas.yml` (diff só header/row_class/`css_class` se aplicável)
- [X] T022 [US4] Validar SC-003 / FR-009 / cenários US4 (CTA → vaga; filtro → cards laranja; pager → mesmo layout; empty sem cards quebrados) conforme `specs/025-vagas-cards-estudantes/quickstart.md` seção E

**Checkpoint**: conversão e descoberta intactas com o novo visual.

---

## Phase 6: User Story 3 — Cabeçalho da seção sem link circular (Priority: P2)

**Goal**: header de `page_1` = título **“Vagas de Destaque”** (Poppins/negrito, alinhado à esquerda); **sem** “Ver todas as vagas”; Home `block_1` mantém o link → `/para-estudantes`.  
**Independent Test Criteria**: comparar headers Home vs `/para-estudantes` — SC-005 / quickstart D.

- [X] T023 [US3] Atualizar area `header` do display `page_1` para HTML com título “Vagas de Destaque” (`h3.text-title` ou equivalente alinhado à Home) **sem** link “Ver todas as vagas” via admin/API → `config/sync/views.view.vagas.yml`
- [X] T024 [P] [US3] Ajustar tipografia do header da listagem sob `.css-vagas-page` (título Poppins negrito; sem afetar `.css-vagas-home .view-header`) em `themes/custom/default/assets/css/style.css`
- [X] T025 [P] [US3] Confirmar regressão zero no header/footer de `block_1` (“Vagas de Destaque” + “Ver todas as vagas” → `/para-estudantes`) em `config/sync/views.view.vagas.yml` (display `block_1`) e Twig Home
- [X] T026 [US3] Validar SC-005 / cenários US3 conforme `specs/025-vagas-cards-estudantes/quickstart.md` seção D

**Checkpoint**: título Figma na listagem; sem link circular; Home intacta.

---

## Phase 7: User Story 5 — Deploy sem passo manual no admin (Priority: P1)

**Goal**: `custom_configs_update_11040` idempotente garante header “Vagas de Destaque” (sem “Ver todas”), `row_class` `col-12 col-md-6 col-lg-4`, `css_class` com `css-vagas-page`; origem `drush cex`; destino `cim` → `updb` → `cim` → `cr`.  
**Independent Test Criteria**: receita de deploy + 2ª `updb` — SC-004 / SC-006 / quickstart A+F / contrato deploy.

- [X] T027 [US5] Implementar helper privado idempotente que carrega View `vagas` display `page_1`, aplica header alvo, `row_class` alvo e `css_class` defensivo, salva **só se houver diff**, **não** altera `block_1`/`block_2`/filtros/pager/path) em `modules/custom/custom_configs/custom_configs.install`
- [X] T028 [US5] Implementar `custom_configs_update_11040` chamando o helper e retornando mensagem Drush created/skipped em `modules/custom/custom_configs/custom_configs.install`
- [X] T029 [US5] Exportar configs estruturais com `drush cex -y` — diff focado em `page_1` (header, style/`row_class`, `css_class` se aplicável) em `config/sync/views.view.vagas.yml`
- [X] T030 [US5] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (idempotência) conforme `specs/025-vagas-cards-estudantes/quickstart.md` seções A e F e `specs/025-vagas-cards-estudantes/contracts/deploy-vagas-cards-estudantes.md`
- [X] T031 [US5] Validar gates pós-deploy (HTTP 200 `/para-estudantes`; cards laranja; título; sem “Ver todas”; Home intacta; SC-004/SC-006) conforme contrato de deploy

**Checkpoint**: destino reproduz visual sem admin manual; hook reentrante seguro.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: PRD §3.6 cirúrgico, specify-rules, isolamento Home/`block_2`/seções 021–024, aceite final SC-001–SC-006.

- [X] T032 [P] Atualizar §3.6 documentando que `page_1` da View `vagas` usa cards laranja (paridade Home), header “Vagas de Destaque” sem “Ver todas”, grid `col-12 col-md-6 col-lg-4`, hook `11040` em `PRD.md`
- [X] T033 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para refletir feature `025-vagas-cards-estudantes` com caminhos `spec.md` / `plan.md` / `tasks.md` (não mais “pendente”)
- [X] T034 [P] Reforçar `.cursor/rules/drupal-deploy-configs.mdc` **somente se** T007 identificar lacuna vs. FR-010; caso contrário, no-op documentado
- [X] T035 [P] Amostrar regressão: Home `block_1`, `block_2` (se usado), hero/benefícios/jornada/perfil em `/para-estudantes` — sem regressão causada por esta feature (`themes/custom/default/`, placements 021–024)
- [X] T036 Executar validação final completa (SC-001–SC-006; checklist deploy; edges) com `specs/025-vagas-cards-estudantes/quickstart.md` e `specs/025-vagas-cards-estudantes/checklists/requirements.md`
- [X] T037 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US4 (Phase 5) → US3 (Phase 6) → US5 (Phase 7) → Polish (Phase 8)
- US2 depende do card renderizando (US1) para validar contagem por linha; config `row_class` pode ser preparada em paralelo ao CSS de US1
- US4 valida CTAs/filtros do Twig US1 + preservação de config; após US1 (e idealmente após US2 para layout estável)
- US3 (P2) é config/CSS de header; implementar **antes** de US5 para o `cex`/hook capturarem o estado alvo (SC-005)
- US5 (hook + cex + receita) depende de header (US3) + `row_class` (US2) + Twig/CSS (US1) já no código
- Polish após US1–US5

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) 🎯 MVP
                    ├→ US2 (P1) ──┐
                    ├→ US4 (P1) ──┤
                    └→ US3 (P2) ──┴→ US5 (P1) → Polish
```

## Parallel Execution Opportunities

- **Setup**: T002 ∥ T003 ∥ T004 após T001; T005/T006 após T001; T007 ∥ inventários
- **Foundational**: T009 ∥ T010 após T008; T011 após T008
- **US1**: T013 (CSS) ∥ início de T012 (Twig) — arquivos distintos; T014–T015 após markup estável; T016 por último
- **US2**: T018 ∥ T017; T019 após ambos
- **US4**: T020 ∥ T021 após US1; T022 por último
- **US3**: T024 (CSS) ∥ T023 (View header); T025 ∥; T026 por último
- **US5**: T027→T028 sequenciais; T029 após config UI/API alinhada; T030–T031 após hook+cex
- **Polish**: T032 ∥ T033 ∥ T034 ∥ T035; depois T036–T037

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (Twig card laranja + CSS `.css-vagas-page`) — valor principal da feature
2. **Layout + navegação**: US2 (grid) + US4 (CTAs/filtros/pager)
3. **Header + deploy**: US3 (título sem link) + US5 (`11040` + `cex` + `cim`→`updb`→`cim`→`cr`)
4. **Fechamento**: Polish (PRD §3.6 + specify-rules + aceite SC-001–SC-006)

## Format Validation

- Todas as tarefas usam `- [ ]`/`- [X]`, ID sequencial (`T001`…`T037`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias: US1 → US2 → US4 (P1) → US3 (P2, antes do deploy) → US5 (P1 deploy)
