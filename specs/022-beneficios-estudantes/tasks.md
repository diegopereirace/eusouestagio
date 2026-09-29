# Tasks: Benefícios Para Estudantes

**Input**: Artefatos de design em `specs/022-beneficios-estudantes/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/beneficios-estudantes-render.md`, `contracts/deploy-beneficios-estudantes.md`, `quickstart.md`  
**Branch**: `feature-para-estudantes` (ou branch ativa alinhada à feature)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/022-beneficios-estudantes` (via `.specify/feature.json`); template alinhado a `specs/013-diferenciais-quem-somos/tasks.md` + `specs/021-banner-para-estudantes/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar storages canônicos, bloco PE `diferenciais_quem_somos` (não reutilizar), hero `021`, placement `content_full` em `/para-estudantes` e último hook `11034` antes de criar config/tema/`11035`.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/beneficios-estudantes-render.md`, `contracts/deploy-beneficios-estudantes.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/022-beneficios-estudantes/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar storages reutilizáveis (não recriar; cardinality `field_itens_lista` já `-1`) em `config/sync/field.storage.block_content.field_text_simple.yml`, `config/sync/field.storage.block_content.field_text_simple_long.yml`, `config/sync/field.storage.block_content.field_itens_lista.yml`, `config/sync/field.storage.paragraph.field_image.yml`, `config/sync/field.storage.paragraph.field_text_simple.yml` e `config/sync/field.storage.paragraph.field_text_simple_long.yml`
- [X] T003 [P] Inventariar padrão visual mais próximo (013 — cabeçalho + grid; **não** reutilizar markup/CSS/classes) em `themes/custom/default/templates/block/block--block-diferenciais-quem-somos.html.twig`, `themes/custom/default/templates/paragraph/paragraph--diferencial-simples-p.html.twig`, `themes/custom/default/assets/css/diferenciais-quem-somos.css` e `themes/custom/default/default.libraries.yml`
- [X] T004 [P] Inventariar contraste PE (instância “Benefícios para Empresas” — **não** reutilizar) e parágrafo legado em `config/sync/block_content.type.diferenciais_quem_somos.yml`, `config/sync/block.block.default_diferenciaisquemsomos.yml` (ou placement PE), `config/sync/paragraphs.paragraphs_type.icone_titulo_descricao.yml` e `config/sync/paragraphs.paragraphs_type.diferencial_simples_p.yml`
- [X] T005 [P] Inventariar convivência hero `021` + View `vagas` + região `content_full` em `/para-estudantes` em `config/sync/block.block.default_views_block__banners_block_para_estudantes.yml`, `themes/custom/default/templates/layout/page.html.twig` (ou equivalente com regiões `banner`/`content_full`) e ausência de bloco exclusivo `content_full` nessa rota
- [X] T006 Confirmar último hook `custom_configs_update_11034` → próximo livre `11035` e helpers reutilizáveis de ensure/seed/placement em `modules/custom/custom_configs/custom_configs.install`; preparar pasta `modules/custom/custom_configs/assets/beneficios-estudantes/`
- [X] T007 [P] Confirmar que `.cursor/rules/drupal-deploy-configs.mdc` já cobre FR-018–FR-020 (`hook_update_N` + `cex` + fluxo `cim`→`updb`→`cim`→`cr`); anotar lacuna só se houver gap factual vs. `specs/022-beneficios-estudantes/spec.md`

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar paragraph type `card_icon_text_p`, block type `beneficios_estudantes`, field instances (reuso de storages), form/view displays e placement `default_beneficiosestudantes` — pré-requisito de todas as user stories. **Zero** field storage novo. **Não** alterar cardinality de `field_itens_lista`. **Não** tocar PE/QS/home/hero `021`/View `vagas`.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T008 Confirmar reuso (sem storages paralelos `field_text_simple_small` / `field_cards_lista`; sem alterar cardinality de `field_itens_lista`) dos YAMLs listados em T002 sob `config/sync/field.storage.*`
- [X] T009 [P] Criar tipo de paragraph “Card Ícone e Texto” em `config/sync/paragraphs.paragraphs_type.card_icon_text_p.yml`
- [X] T010 [P] Criar tipo de bloco “Benefícios Estudantes” em `config/sync/block_content.type.beneficios_estudantes.yml` (`revision` alinhado aos demais block types)
- [X] T011 [P] Criar field instances do paragraph em `config/sync/field.field.paragraph.card_icon_text_p.field_image.yml`, `config/sync/field.field.paragraph.card_icon_text_p.field_text_simple.yml` e `config/sync/field.field.paragraph.card_icon_text_p.field_text_simple_long.yml`
- [X] T012 Criar field instances do bloco em `config/sync/field.field.block_content.beneficios_estudantes.field_text_simple.yml`, `config/sync/field.field.block_content.beneficios_estudantes.field_text_simple_long.yml` e `config/sync/field.field.block_content.beneficios_estudantes.field_itens_lista.yml` (handler → somente `card_icon_text_p`; cardinality ilimitada via storage já `-1`)
- [X] T013 [P] Criar form + view displays do paragraph em `config/sync/core.entity_form_display.paragraph.card_icon_text_p.default.yml` e `config/sync/core.entity_view_display.paragraph.card_icon_text_p.default.yml`
- [X] T014 Criar form display do bloco (widget paragraphs; default type `card_icon_text_p`) em `config/sync/core.entity_form_display.block_content.beneficios_estudantes.default.yml`
- [X] T015 [P] Criar view display do bloco (lista via `entity_reference_revisions_entity_view`) em `config/sync/core.entity_view_display.block_content.beneficios_estudantes.default.yml`
- [X] T016 Criar placement `default_beneficiosestudantes` (tema `default`, região `content_full`, weight `0`, `label_display: '0'`, `request_path` = `/para-estudantes`, plugin UUID `a8b9c0d1-e2f3-4456-a789-0bcdef123456`) em `config/sync/block.block.default_beneficiosestudantes.yml`
- [X] T017 Importar/validar estrutura local (`drush cim -y` ou UI + `drush cex -y`) — tipos em Estrutura → Tipos de bloco / Tipos de parágrafo; **sem** alterar PE/QS/home/hero `021`/View `vagas`; seed de conteúdo ainda ausente (hook US5)

**Checkpoint**: estrutura sobe só com `cim`; front ainda sem Twig/CSS custom; seed ainda ausente.

---

## Phase 3: User Story 1 — Visitante vê os benefícios em Para Estudantes (Priority: P1) 🎯 MVP

**Goal**: viewport ≥992px — cabeçalho centralizado (título max ~404px + subtítulo max ~624px) + grid 4 cards (`col-lg-3`); cards ~258×310 (`min-height: 310px`), ícone ≤64px, Poppins `#0F172A`, chrome sutil; classe raiz `block-beneficios-estudantes`; DOM alinhado ao contrato.  
**Independent Test Criteria**: abrir `/para-estudantes` ≥992px com bloco publicado e comparar estrutura (cabeçalho + 4 cards) com Figma/`contracts/beneficios-estudantes-render.md` (SC-001).

- [X] T018 [US1] Registrar library `beneficios_estudantes` → `assets/css/beneficios-estudantes.css` em `themes/custom/default/default.libraries.yml`
- [X] T019 [P] [US1] Criar CSS encapsulado (título `max-width: 404px`; subtítulo `max-width: 624px`; card `min-height: 310px` + borda/sombra leve; ícone ≤64px; Poppins `#0F172A`; **somente** seletores sob `.block-beneficios-estudantes`) em `themes/custom/default/assets/css/beneficios-estudantes.css`
- [X] T020 [US1] Implementar Twig do bloco (`.container.py-5` / `py-lg-5`; `h2.be-header__title.mx-auto`; subtítulo `.be-header__subtitle` em `.col-lg-8`; grid `.row.mt-5.justify-content-center.be-grid`; attach library; `id="beneficios-estudantes"`; preservar `attributes`/`content_attributes`/`title_*`; **não** reusar classes `.block-diferenciais-quem-somos` / `.dqs-*` / `.block-nossos-diferenciais` / `.nd-*`) em `themes/custom/default/templates/block/block--block-beneficios-estudantes.html.twig`
- [X] T021 [P] [US1] Implementar Twig do paragraph (coluna `.col-12.col-md-6.col-lg-3.mb-4`; card `.be-card.text-center.d-flex.flex-column.align-items-center.h-100`; ícone `.img-fluid` + `loading="lazy"`; título `h3.be-card__title`; texto `.be-card__text`; omitir ícone/título/texto vazios) em `themes/custom/default/templates/paragraph/paragraph--card-icon-text-p.html.twig`
- [X] T022 [US1] Implementar fallbacks no Twig do bloco (omitir `h2`/subtítulo vazios; zero cards → só cabeçalho se houver; sem truncar lista >4; sem fatal) em `themes/custom/default/templates/block/block--block-beneficios-estudantes.html.twig`
- [X] T023 [US1] Validar SC-001 e cenários US1 (desktop lg+: título/subtítulo/4 cards/ícones/altura) conforme `specs/022-beneficios-estudantes/quickstart.md` seção B e `specs/022-beneficios-estudantes/contracts/beneficios-estudantes-render.md` (conteúdo de teste manual ou seed US5)

**Checkpoint**: visitante desktop vê a seção completa no layout de referência (MVP).

---

## Phase 4: User Story 2 — Visitante tablet/mobile vê grade adaptada (Priority: P1)

**Goal**: viewport md — 2 cards/linha (`col-md-6`); mobile estreito — 1/linha (`col-12`); legível; sem scroll horizontal causado pelo bloco.  
**Independent Test Criteria**: abrir `/para-estudantes` em ≤575.98px e 768–991px (SC-002).

- [X] T024 [US2] Confirmar/ajustar CSS responsivo (sem overflow-x; tipografia legível; ícones ≤64px; cards `min-height` preservado) sob `.block-beneficios-estudantes` em `themes/custom/default/assets/css/beneficios-estudantes.css`
- [X] T025 [US2] Confirmar classes Bootstrap do card (`.col-12.col-md-6.col-lg-3`) no Twig do paragraph em `themes/custom/default/templates/paragraph/paragraph--card-icon-text-p.html.twig`
- [X] T026 [US2] Validar SC-002 / cenários US2 (mobile 1/linha; md 2/linha; sem scroll horizontal) conforme `specs/022-beneficios-estudantes/quickstart.md` seção B itens 2–3

**Checkpoint**: grade adaptada em tablet/mobile sem regressão desktop.

---

## Phase 5: User Story 4 — Bloco aparece só em Para Estudantes, abaixo do hero (Priority: P1)

**Goal**: bloco só em `/para-estudantes` (`content_full`); home / PE / QS sem `beneficios_estudantes`; ordem vertical hero (`021`, região `banner`) → benefícios; View `vagas` e instância PE intactas.  
**Independent Test Criteria**: comparar `/para-estudantes`, `<front>`, `/para-empresas` e `/quem-somos`; confirmar ordem hero → benefícios (SC-004).

- [X] T027 [US4] Confirmar YAML do placement (path `/para-estudantes`, região `content_full`, weight `0`, UUID alinhado ao seed) em `config/sync/block.block.default_beneficiosestudantes.yml`
- [X] T028 [P] [US4] Confirmar que configs/Twig/CSS do hero `021`, PE benefícios e home diferenciais permanecem inalterados em `config/sync/block.block.default_views_block__banners_block_para_estudantes.yml`, `config/sync/block_content.type.diferenciais_quem_somos.yml`, `themes/custom/default/templates/views/views-view-unformatted--banners--block-para-estudantes.html.twig` e escopo CSS (sem seletores em `.hero-estudantes-wrapper` / `.block-diferenciais-quem-somos` / `.block-nossos-diferenciais`)
- [X] T029 [US4] Validar SC-004 / cenários US4 (presença só em `/para-estudantes`; ordem hero → benefícios; ausência em home/PE/QS) conforme `specs/022-beneficios-estudantes/quickstart.md` seção D

**Checkpoint**: isolamento de rota e ordem de leitura garantidos.

---

## Phase 6: User Story 3 — Editor gerencia conteúdo sem código (Priority: P1)

**Goal**: editor com permissão edita título, subtítulo, ícones, títulos/textos dos cards e ordem; lista ilimitada; card sem ícone permanece legível; mudanças refletem em `/para-estudantes` sem deploy de código.  
**Independent Test Criteria**: editar no painel, salvar, recarregar `/para-estudantes` (&lt; 5 min — SC-003).

- [X] T030 [US3] Incluir permissões create/edit/delete do bundle `beneficios_estudantes` nas roles que já gerenciam block content e exportar diffs em `config/sync/user.role.*.yml` afetados
- [X] T031 [US3] Revisar form displays (widgets, labels, lista paragraphs default `card_icon_text_p`, obrigatoriedade editorial do título do card se aplicável) em `config/sync/core.entity_form_display.block_content.beneficios_estudantes.default.yml` e `config/sync/core.entity_form_display.paragraph.card_icon_text_p.default.yml`
- [X] T032 [US3] Confirmar que nenhum copy institucional fica hardcoded como única fonte de verdade (placeholders Twig só para ausência) em `themes/custom/default/templates/block/block--block-beneficios-estudantes.html.twig` e `themes/custom/default/templates/paragraph/paragraph--card-icon-text-p.html.twig`
- [X] T033 [US3] Validar SC-003 / cenários US3 (edição &lt;5 min; card sem ícone legível; reorder/add/remove) conforme `specs/022-beneficios-estudantes/quickstart.md` seção C

**Checkpoint**: conteúdo editorial gerenciável sem código.

---

## Phase 7: User Story 5 — Deploy reproduz estrutura e seed em outro ambiente (Priority: P1)

**Goal**: `custom_configs_update_11035` idempotente — ensure types/fields/displays; seed UUID `a8b9c0d1-e2f3-4456-a789-0bcdef123456` + 4 cards + ícones `icon-1…4.png` → `public://`; ensure placement; **nunca** duplicar nem sobrescrever editorial divergente; fluxo `cim` → `updb` → `cim` → `cr`; `cex` versiona estrutural.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; 2ª `updb` sem duplicatas; editorial preservado (SC-005, SC-006, SC-007).

- [X] T034 [P] [US5] Versionar assets PNG seed dos 4 ícones em `modules/custom/custom_configs/assets/beneficios-estudantes/icon-1.png` … `icon-4.png`
- [X] T035 [US5] Implementar `custom_configs_update_11035` idempotente (ensure `card_icon_text_p` + fields + displays; ensure `beneficios_estudantes` + fields/handler lista → `card_icon_text_p` + displays; seed `BlockContent` UUID `a8b9c0d1-e2f3-4456-a789-0bcdef123456` + título/subtítulo Assumptions + 4 `card_icon_text_p` + cópia de ícones → `public://` **somente se vazios/ausentes**; ensure placement `default_beneficiosestudantes` região `content_full` / pages `/para-estudantes` / weight `0`; mensagem Drush created/skipped; **nunca** sobrescrever editorial divergente; **nunca** alterar PE/QS/home/hero `021`/View `vagas`; ausência de PNG → cards sem ícone sem falhar o update) em `modules/custom/custom_configs/custom_configs.install`
- [X] T036 [P] [US5] Extrair/reusar helpers privados (ensure type/fields/displays / seed block+paragraphs / copy assets / ensure placement) no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T037 [US5] Exportar/confirmar configs estruturais com `drush cex -y` para os YAMLs listados em `specs/022-beneficios-estudantes/data-model.md` sob `config/sync/` (tipos, instances, displays, placement, `user.role.*`)
- [X] T038 [US5] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (SC-005/SC-006) conforme `specs/022-beneficios-estudantes/quickstart.md` seções A e E e `specs/022-beneficios-estudantes/contracts/deploy-beneficios-estudantes.md`
- [X] T039 [US5] Validar fallbacks pós-seed (título/subtítulo vazios omitidos; zero cards → só cabeçalho; card sem ícone; SC-007) e isolamento pós-deploy conforme `specs/022-beneficios-estudantes/quickstart.md` seções D e E

**Checkpoint**: deploy 100% automatizado; `/para-estudantes` reproduzível sem admin manual.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, specify-rules, reforço opcional da regra de deploy, isolamento visual (hero 021 / PE / home) e aceite final SC-001–SC-008.

- [X] T040 [P] Atualizar §3.6 (e §3.1.1 / rota estudantes se aplicável) documentando `beneficios_estudantes`, `card_icon_text_p`, placement `content_full` `/para-estudantes`, UUID, hook `11035` e convivência com hero `021` + View `vagas` em `PRD.md`
- [X] T041 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para refletir feature `022-beneficios-estudantes` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T042 [P] Reforçar `.cursor/rules/drupal-deploy-configs.mdc` **somente se** T007 identificar lacuna vs. FR-018–FR-020; caso contrário, no-op documentado
- [X] T043 Executar validação final completa (SC-001–SC-008; isolamento hero/PE/home; checklist deploy) com `specs/022-beneficios-estudantes/quickstart.md` e `specs/022-beneficios-estudantes/checklists/requirements.md`
- [X] T044 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US4 (Phase 5) → US3 (Phase 6) → US5 (Phase 7) → Polish (Phase 8)
- US2 depende do markup/CSS base de US1 (T020–T022)
- US4 valida sobretudo o Foundational (T016); pode rodar em paralelo com US1 após Phase 2 (aceite completo após seed US5)
- US3 depende de form displays (T013/T014) + Twig com fallbacks (T022); ideal após seed mínimo (US5) ou conteúdo manual de teste
- US5 (hook/cex/assets) precisa dos YAMLs da Phase 2; validar pós-Twig mínimo (T020–T021)
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

- **Setup**: T002–T005 ∥ T007 após T001; T006 após T001
- **Foundational**: T009 ∥ T010 após T008; T011 após T009; T012 após T010; T013 ∥ após T011; T014/T015 após T012; T016 após UUID/plugin definido; T017 por último
- **US1**: T019 (CSS) em paralelo com início de T020 após T018; T021 ∥ T020 (arquivos distintos); T022 após markup estável; T023 por último
- **US2 ∥ US4**: T024–T025 com T027–T028 após T020/T021
- **US3**: após T014 + T022; melhor com seed de US5
- **US5**: T034 ∥ preparo de T035; T036 junto a T035; T037–T039 após hook
- **Polish**: T040 ∥ T041 ∥ T042; depois T043–T044

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (desktop cabeçalho + grid 4 cards em `/para-estudantes`) — valor principal da feature
2. **Mobile + isolamento de rota**: US2 + US4
3. **Editorial + deploy**: US3 (roles/form) + US5 (`11035` + assets + cim/updb/cim/cr + cex)
4. **Fechamento**: Polish (PRD §3.6 + specify-rules + aceite SC-001–SC-008)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T044`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias por prioridade P1: US1 → US2 → US4 → US3 → US5 (todas P1 na spec; ordem reflete dependências de implementação)
