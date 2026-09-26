# Tasks: Bloco de Depoimentos — Para Empresas

**Input**: Artefatos de design em `specs/020-depoimentos-empresas/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/depoimentos-carousel-render.md`, `contracts/composition-para-empresas.md`, `quickstart.md`  
**Branch**: `feature-para-empresas` (ou branch ativa alinhada à feature)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/020-depoimentos-empresas` (via `.specify/feature.json`); template alinhado a `specs/019-para-empresas-page/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar storages reutilizáveis, composição PE atual (weights 0–4), último hook, ausência do tipo `depoimento`, padrões Twig/library de carrosséis e image style `thumbnail`.

- [x] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/depoimentos-carousel-render.md`, `contracts/composition-para-empresas.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/020-depoimentos-empresas/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [x] T002 [P] Confirmar existência e reuso de storages `field_text_simple`, `field_text_simple_long`, `field_imagem` (sem criar storage novo) em `config/sync/field.storage.node.field_text_simple.yml`, `config/sync/field.storage.node.field_text_simple_long.yml`, `config/sync/field.storage.node.field_imagem.yml`
- [x] T003 [P] Confirmar ausência do bundle `depoimento` e inventariar um form/view display de referência (ex.: `contato` ou `page`) em `config/sync/node.type.*.yml` e `config/sync/core.entity_*_display.node.*.yml`
- [x] T004 [P] Inventariar composição PE `content_full` weights 0–4 e CTA `default_ctav1paraempresas` weight **4** em `config/sync/block.block.default_beneficiosparaempresas.yml`, `config/sync/block.block.default_ctav1paraempresas.yml` e demais placements PE
- [x] T005 [P] Confirmar image style `thumbnail` (100×100) e ausência de Swiper/Splide no tema em `config/sync/image.style.thumbnail.yml`, `themes/custom/default/default.libraries.yml`
- [x] T006 [P] Inventariar padrão Twig/library de Views bloco (banners PE / home) para espelhar attach + omit-empty em `themes/custom/default/templates/views/`, `themes/custom/default/default.libraries.yml`
- [x] T007 Confirmar último hook `custom_configs_update_11031` → próximo livre `11032` e helpers reutilizáveis de seed/placement/assets em `modules/custom/custom_configs/custom_configs.install`
- [x] T008 [P] Confirmar que `.cursor/rules/drupal-deploy-configs.mdc` cobre FR-017–020 (`hook_update_N` + `cex` + `cim`→`updb`→`cim`→`cr`); anotar lacuna só se houver gap vs. `specs/020-depoimentos-empresas/spec.md`

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar content type `depoimento`, field instances (reuso), form/view displays, View `depoimentos_carousel` display `block_depoimentos_empresas`, placement weight **4** só `/para-empresas`, e CTA PE weight **5**. **Zero** field storages novos. **Não** alterar placements de home/Quem Somos.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [x] T009 Criar content type `depoimento` (label “Depoimento”; description B2B; preview `teaser`) em `config/sync/node.type.depoimento.yml`
- [x] T010 [P] Anexar instance `field_text_simple` ao bundle com label “Cargo / Empresa” em `config/sync/field.field.node.depoimento.field_text_simple.yml`
- [x] T011 [P] Anexar instance `field_text_simple_long` ao bundle com label “Texto do depoimento” em `config/sync/field.field.node.depoimento.field_text_simple_long.yml`
- [x] T012 [P] Anexar instance `field_imagem` ao bundle com label “Foto do autor” (directory `depoimentos/[date:custom:Y]-[date:custom:m]`) em `config/sync/field.field.node.depoimento.field_imagem.yml`
- [x] T013 Criar form display `default` (title + `field_imagem` + `field_text_simple` + `field_text_simple_long`) em `config/sync/core.entity_form_display.node.depoimento.default.yml`
- [x] T014 [P] Criar view display `default` (campos completos) em `config/sync/core.entity_view_display.node.depoimento.default.yml`
- [x] T015 [P] Criar view display `teaser` (title + cargo + texto + imagem `thumbnail`) em `config/sync/core.entity_view_display.node.depoimento.teaser.yml`
- [x] T016 Criar View `depoimentos_carousel` display Block `block_depoimentos_empresas` (filtros `status=1`, `type=depoimento`; sort `created` DESC; pager `none`; css_class `depoimentos-empresas-view`; sem empty area) em `config/sync/views.view.depoimentos_carousel.yml`
- [x] T017 Criar placement do bloco View em `content_full`, tema `default`, pages só `/para-empresas`, weight **4**, `label_display: '0'`, plugin `views_block:depoimentos_carousel-block_depoimentos_empresas` em `config/sync/block.block.default_views_block__depoimentos_carousel_block_depoimentos_empresas.yml`
- [x] T018 Atualizar weight do CTA PE de **4 → 5** em `config/sync/block.block.default_ctav1paraempresas.yml`
- [x] T019 Importar/validar estrutura local (`drush cim -y` ou UI + `drush cex -y`) — tipo + fields + displays + View + placements; **sem** alterar home/Quem Somos; seeds ainda ausentes (hook US4)

**Checkpoint**: estrutura sobe só com `cim`; front ainda sem Twig/CSS/JS do carrossel nem seeds; ordem Benefícios(3) → Depoimentos(4) → CTA(5) pronta após seed US4.

---

## Phase 3: User Story 1 — Visitante vê depoimentos em carrossel center mode (Priority: P1) 🎯 MVP

**Goal**: em `/para-empresas`, seção entre Benefícios e CTA com carrossel center mode (ativo centralizado; laterais parcialmente visíveis + opacidade reduzida); dots; autoplay off; sem loop infinito; mobile utilizável sem scroll horizontal da página; zero rows → omit section.  
**Independent Test Criteria**: abrir `/para-empresas` ≥992px; localizar seção acima do CTA; interagir dots/swipe; viewport estreita (SC-001 parcial, SC-002, SC-003, SC-004) — conteúdo de teste manual ou seed US4.

- [x] T020 [US1] Registrar library `depoimentos_carousel` → `assets/css/depoimentos-carousel.css` + `assets/js/depoimentos-carousel.js` em `themes/custom/default/default.libraries.yml`
- [x] T021 [P] [US1] Criar CSS encapsulado (viewport/track/dots; center peeks; opacidade `--active` vs `--side`; sem overflow-x da página) sob `.depoimentos-empresas` em `themes/custom/default/assets/css/depoimentos-carousel.css`
- [x] T022 [P] [US1] Implementar JS center mode (index ativo; dots sync; swipe/touch; N=1 sem dots/laterais fantasma; autoplay off; sem clones) em `themes/custom/default/assets/js/depoimentos-carousel.js`
- [x] T023 [US1] Criar template do display que anexa a library e omite markup quando zero rows em `themes/custom/default/templates/views/views-view--depoimentos-carousel--block-depoimentos-empresas.html.twig`
- [x] T024 [US1] Implementar markup do carrossel (`section.depoimentos-empresas` → viewport → track → cards + dots `button[aria-label]`) alinhado a `specs/020-depoimentos-empresas/contracts/depoimentos-carousel-render.md` em `themes/custom/default/templates/views/views-view-unformatted--depoimentos-carousel--block-depoimentos-empresas.html.twig`
- [x] T025 [P] [US1] Adicionar preprocess opcional (classes/atributos do carrossel) se necessário em `themes/custom/default/default.theme`
- [x] T026 [US1] Validar SC-002/SC-003/SC-004 e cenários US1 (ordem Benefícios→Depoimentos→CTA; center mode; dots; mobile sem scroll-x) conforme `specs/020-depoimentos-empresas/quickstart.md` seções B e D e `contracts/depoimentos-carousel-render.md` / `contracts/composition-para-empresas.md`

**Checkpoint**: visitante vê carrossel center mode (MVP); card visual Figma completo depende de US2; seeds automáticos de US4.

---

## Phase 4: User Story 2 — Visitante lê o conteúdo do card (Priority: P1)

**Goal**: card ativo com avatar circular ~48px, nome (title), cargo/empresa, texto legível (~`#45464D`); tokens Figma (branco, radius 16px, padding 32px, largura ~404px, min-height ~188px); omit empty foto/texto/cargo.  
**Independent Test Criteria**: inspecionar card ativo vs Figma (SC-001).

- [x] T027 [US2] Implementar markup do card (`.depoimento-card` / header / avatar / meta / texto; omit empty) em Twig da View unformatted e/ou `themes/custom/default/templates/node/node--depoimento--teaser.html.twig` conforme row style da View
- [x] T028 [P] [US2] Estilizar card (fundo `#FFFFFF`, `border-radius: 16px`, `padding: 32px`, largura ~404px, min-height ~188px, avatar circular 48px via `thumbnail`+CSS, tipografia Poppins/tokens) sob `.depoimento-card` em `themes/custom/default/assets/css/depoimentos-carousel.css`
- [x] T029 [US2] Confirmar image style `thumbnail` no display teaser / fields da View (sem image style novo) em `config/sync/core.entity_view_display.node.depoimento.teaser.yml` e/ou `config/sync/views.view.depoimentos_carousel.yml`
- [x] T030 [US2] Validar SC-001 e cenários US2 (avatar+nome+cargo+texto; tokens Figma; sem broken image sem foto) conforme `specs/020-depoimentos-empresas/quickstart.md` seção C e `contracts/depoimentos-carousel-render.md`

**Checkpoint**: prova social legível e alinhada ao Figma no card ativo.

---

## Phase 5: User Story 4 — Deploy reproduz a seção sem painel manual (Priority: P1)

**Goal**: `custom_configs_update_11032` idempotente — ensure defensivo tipo/fields/displays; seed ≥4 nodes (UUIDs data-model) + assets `avatar-1.png`…`avatar-4.png` → `public://`; ensure placement weight 4 + CTA weight 5; nunca duplicar nem sobrescrever editorial; fluxo `cim` → `updb` → `cim` → `cr`; `cex` versiona estrutural.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; 2ª `updb` sem duplicatas (SC-006, SC-007, SC-009).

- [x] T031 [P] [US4] Versionar placeholders de avatar (geométricos/ilustrativos; sem fotos reais sem direito) em `modules/custom/custom_configs/assets/depoimentos/avatar-1.png` … `avatar-4.png`
- [x] T032 [US4] Implementar `custom_configs_update_11032` idempotente (ensure bundle/instances/displays se ausentes; seed 4 nodes UUIDs `a0b1c2d3-e4f5-4601-a001-depoimentos0001` … `…0004` com copy pt-BR; copiar assets → `public://depoimentos/…` só se campo vazio; ensure placement View w4 pages `/para-empresas`; ensure `default_ctav1paraempresas.weight = 5`; ausência de PNG não falha o update; **nunca** sobrescrever editorial divergente; **nunca** duplicar) em `modules/custom/custom_configs/custom_configs.install`
- [x] T033 [P] [US4] Extrair/reusar helpers privados (ensure bundle/fields, seed nodes+assets, ensure placement/weights) no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [x] T034 [US4] Exportar/confirmar configs estruturais com `drush cex -y` para os YAMLs listados em `specs/020-depoimentos-empresas/plan.md` / `data-model.md` sob `config/sync/`
- [x] T035 [US4] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (SC-006/SC-007) conforme `specs/020-depoimentos-empresas/quickstart.md` seção A e F
- [x] T036 [US4] Validar pós-seed (≥4 publicados; carrossel acima do CTA; zero duplicatas; zero publicados → página sem fatal SC-009; rotas `/` `/quem-somos` `/contato` sem bloco) conforme `specs/020-depoimentos-empresas/quickstart.md` seções F e G

**Checkpoint**: deploy 100% automatizado; `/para-empresas` reproduzível sem admin manual.

---

## Phase 6: User Story 3 — Editor gerencia depoimentos sem código (Priority: P2)

**Goal**: editor cria/edita/publica/despublica nodes `depoimento`; carrossel reflete só publicados, `created` DESC; form display utilizável (&lt;5 min após cache).  
**Independent Test Criteria**: criar/editar/despublicar no painel; recarregar página pública (SC-005).

- [x] T037 [US3] Confirmar form display editorial (title=nome, labels Cargo/Empresa, Texto, Foto) utilizável em `config/sync/core.entity_form_display.node.depoimento.default.yml`
- [x] T038 [P] [US3] Confirmar filtros View (`status=1`, `type=depoimento`) e sort `created` DESC em `config/sync/views.view.depoimentos_carousel.yml`
- [x] T039 [US3] Validar SC-005 e cenários US3 (criar publicado aparece; despublicar some; ordem DESC) conforme `specs/020-depoimentos-empresas/quickstart.md` seção E

**Checkpoint**: conteúdo de prova social editável sem código/deploy.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, specify-rules, isolamento visual, aceite final SC-001–SC-009.

- [x] T040 [P] Atualizar `PRD.md` (§3.1 content type `depoimento`; §3.1.0b / §3.6 composição Benefícios→Depoimentos→CTA, View `depoimentos_carousel`, hook `11032`) de forma cirúrgica
- [x] T041 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para refletir feature `020-depoimentos-empresas` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [x] T042 [P] Reforçar `.cursor/rules/drupal-deploy-configs.mdc` **somente se** T008 identificar lacuna vs. FR-017–020; caso contrário, no-op documentado
- [x] T043 Executar validação final completa (SC-001–SC-009; seções B–G) com `specs/020-depoimentos-empresas/quickstart.md` e `specs/020-depoimentos-empresas/checklists/requirements.md`
- [x] T044 Limpar cache Drupal após alterações Twig/CSS/JS/config (`docker compose exec -T drupal vendor/bin/drush cr`)
- [x] T045 [P] Amostragem de regressão visual: home e `/quem-somos` inalterados; CSS/JS só sob `.depoimentos-empresas` (SC-008 / FR-015)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US4 (Phase 5) → US3 (Phase 6) → Polish (Phase 7)
- US1 depende dos YAMLs View/placement (T016–T018) + library/Twig
- US2 depende do markup track/cards de US1 (T024) para estilizar o card
- US4 (hook/cex/assets) precisa dos YAMLs da Phase 2; validar pós-Twig mínimo (T023–T024 / T027–T028)
- US3 ideal após seed US4 (conteúdo de teste já presente)
- Polish após US1–US4 (+ US3)

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) 🎯 MVP
                    ├→ US2 (P1)
                    ├→ US4 (P1) ──→ US3 (P2)
                    └──────────────→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002–T006 ∥ T008 após T001; T007 após T001
- **Foundational**: T010 ∥ T011 ∥ T012 após T009; T014 ∥ T015 após T013; T017 ∥ T018 após T016; T019 por último
- **US1**: T021 ∥ T022 após T020; T025 ∥ T023–T024; T026 por último
- **US2**: T028 ∥ T029 após T027; T030 por último
- **US4**: T031 ∥ início T032; T033 com T032; T034–T036 após hook
- **US3**: T037 ∥ T038 após Phase 2 + seed; T039 por último
- **US2 ∥ início US4**: após T024 (Twig mínimo) — seed pode rodar em paralelo ao polish visual do card se Phase 2 completa
- **Polish**: T040 ∥ T041 ∥ T042 ∥ T045; depois T043–T044

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (carrossel center mode em `/para-empresas`) — valor visual principal
2. **Card Figma**: US2 (avatar, tipografia, tokens)
3. **Deploy**: US4 (`11032` + assets + cim/updb/cim/cr + cex)
4. **Editorial**: US3 (CRUD no painel)
5. **Fechamento**: Polish (PRD + specify-rules + aceite SC-001–SC-009)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T045`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias por prioridade: US1 → US2 → US4 (P1) → US3 (P2); US1 = MVP
