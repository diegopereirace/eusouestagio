# Tasks: Benefícios Estudantes — Layout Figma (split + colagem)

**Input**: Artefatos de design em `specs/054-beneficios-estudantes-figma/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/beneficios-estudantes-figma-render.md`, `contracts/deploy-beneficios-estudantes-figma.md`, `quickstart.md`  
**Branch sugerida**: `main` (workflow do projeto)  
**Predecessors**: `022-beneficios-estudantes`, `053-refino-figma-para-estudantes` (mesmo bloco/placement; esta feature **substitui** layout/copy de 4 cards)  
**Hook**: `custom_configs_update_11081` (`11078`–`11080` históricos)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md` (sem PHPUnit)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/054-beneficios-estudantes-figma` (via `.specify/feature.json`); template alinhado a `specs/053-refino-figma-para-estudantes/tasks.md`  
**Config estrutural**: instances `field_text_simple_2` / `field_link` / `field_image` + displays via ensure no hook + `drush cex`; destino `cim` → `updb` → `cim` → `cr`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar Twig/CSS/helpers/assets/hook antes de mutar tema ou `custom_configs`.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/beneficios-estudantes-figma-render.md`, `contracts/deploy-beneficios-estudantes-figma.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/054-beneficios-estudantes-figma/` e feature ativa `054-beneficios-estudantes-figma` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar Twig atual do bloco (cabeçalho centrado, grade sem badge/CTA/colagem) em `themes/custom/default/templates/block/block--block-beneficios-estudantes.html.twig`
- [X] T003 [P] Inventariar Twig do paragraph (`.be-card`, `col-lg-3`, ícone no topo) em `themes/custom/default/templates/paragraph/paragraph--card-icon-text-p.html.twig`
- [X] T004 [P] Inventariar CSS/library `beneficios_estudantes` (chrome de card `#F8F9FF`, min-height 310px) em `themes/custom/default/assets/css/beneficios-estudantes.css` e `themes/custom/default/default.libraries.yml`
- [X] T005 [P] Inventariar helpers existentes `_custom_configs_ensure_beneficios_estudantes_structure` / `_custom_configs_ensure_beneficios_estudantes_displays` / `_custom_configs_seed_beneficios_estudantes` / `_custom_configs_fill_beneficios_estudantes_icons` / `_custom_configs_ensure_beneficios_estudantes_placement` e último hook `11077` → próximo livre `11078` em `modules/custom/custom_configs/custom_configs.install`
- [X] T006 Inventariar assets atuais `icon-1.png`…`icon-4.png` (ausência de `icon-5`/`icon-6`/`collage.png`) e storages reutilizáveis `field_text_simple_2` / `field_link` / `field_image` em `modules/custom/custom_configs/assets/beneficios-estudantes/` e `config/sync/field.storage.block_content.*.yml`

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: congelar escopo negativo e confirmar UUID/placement/storages/hook livre antes das user stories.

**⚠️ CRITICAL**: Nenhuma user story de implementação começa antes desta fase.

- [X] T007 Congelar escopo negativo: **não** criar block/paragraph/content types novos nem field **storage** paralelo; **não** editar `core/`/`vendor/`; **não** introduzir pipeline SCSS nem JS novo; **não** redesenhar banner / Hero Search / jornada / `block_3` / CTA final `026`; **não** alterar visual de `/para-empresas`, `/quem-somos`, home ou Benefícios PE; **não** remover `beneficios_estudantes` / `card_icon_text_p`; **não** editar YAML de `config/sync` à mão para inventar config
- [X] T008 Confirmar UUID bloco `a8b9c0d1-e2f3-4456-a789-0bcdef123456`, placement `default_beneficiosestudantes` (`content_full`, weight `0`, pages `/para-estudantes`, `label_display: 0`) e que storages `field_text_simple_2` / `field_link` / `field_image` já existem em `block_content` — referência em `config/sync/block.block.default_beneficiosestudantes.yml`, `modules/custom/custom_configs/custom_configs.install`, `config/sync/field.storage.block_content.*.yml`
- [X] T009 Confirmar que instances atuais do bundle **não** incluem badge/CTA/colagem e que o próximo hook livre é `11078` (pós-`11077`) em `config/sync/field.field.block_content.beneficios_estudantes.*.yml` e `modules/custom/custom_configs/custom_configs.install`

**Checkpoint**: escopo congelado; UUID/placement/storages/hook confirmados; pronto para US1.

---

## Phase 3: User Story 1 — Visitante vê o novo layout de benefícios (Priority: P1) 🎯 MVP

**Goal**: Em desktop ≥992px, `/para-estudantes` mostra badge + título + subtítulo alinhados à esquerda, 6 itens em lista 2 colunas (ícone à esquerda, sem card-box), CTA laranja e colagem à direita; grade antiga de 4 cards centrados deixa de ser a UI pública (FR-001–003, FR-005–007 / SC-001, SC-002, SC-005).  
**Independent Test Criteria**: Abrir `/para-estudantes` em viewport ≥992px; comparar cabeçalho, 6 itens, CTA e colagem com o frame Figma (`quickstart.md` seção B / `contracts/beneficios-estudantes-figma-render.md`).  
**Nota**: seed canônico dos 6 itens/CTA/colagem fica completo em US4 (`11078`); nesta fase o markup/CSS deve renderizar os campos com omit-empty.

- [X] T010 [US1] Versionar assets `icon-1.png`…`icon-6.png` (atualizar/estender os 4 atuais) e `collage.png` (arte composta circular) em `modules/custom/custom_configs/assets/beneficios-estudantes/`
- [X] T011 [US1] Reescrever Twig do bloco para split `.be-split`: badge (`field_text_simple_2`), título, subtítulo `text-start`, grid de itens, CTA (`.be-cta` / `.be-cta__btn` omit se `field_link` vazio) e coluna visual `.be-collage` (`field_image` omit empty); preservar `attributes` / `content_attributes` / title hooks — conforme `contracts/beneficios-estudantes-figma-render.md` em `themes/custom/default/templates/block/block--block-beneficios-estudantes.html.twig` (research R2/R4/R8)
- [X] T012 [P] [US1] Reescrever Twig do paragraph para layout horizontal `.be-item` (ícone ~40–48px à esquerda + título/texto); classes de coluna `col-12 col-md-6` **sem** `col-lg-3`; remover dependência de chrome `.be-card` — em `themes/custom/default/templates/paragraph/paragraph--card-icon-text-p.html.twig` (research R3)
- [X] T013 [US1] Atualizar CSS sob `.block-beneficios-estudantes`: tipografia badge/título/subtítulo; lista 2 colunas; neutralizar borda/fundo/min-height de card; CTA laranja `var(--brand-orange)`; split desktop com colagem à direita; `loading` lazy nos imgs — em `themes/custom/default/assets/css/beneficios-estudantes.css` (research R3/R4/R8)
- [X] T014 [US1] Validar SC-001/SC-002/SC-005 e cenários US1 (badge, título Figma, 6 itens na ordem quando seedados, CTA, colagem à direita; ausência da grade 4 cards centrados) conforme `specs/054-beneficios-estudantes-figma/quickstart.md` seção B e `specs/054-beneficios-estudantes-figma/contracts/beneficios-estudantes-figma-render.md` — pós-US4 para seed canônico completo

**Checkpoint**: markup/CSS do layout Figma desktop prontos — MVP visual (seed canônico via US4).

---

## Phase 4: User Story 2 — Visitante em tablet/mobile lê os seis benefícios (Priority: P1)

**Goal**: Em ~768px e ~375px a seção permanece legível sem scroll horizontal; itens 2 colunas ≥768 / 1 coluna &lt;768; colagem ocultada &lt;992; CTA acionável → `/cadastro/candidato` (FR-004 / SC-003, SC-004).  
**Independent Test Criteria**: Abrir `/para-estudantes` em ~768px e ~375px; rolar Benefícios; clicar CTA (`quickstart.md` seção C / contract responsivo).

- [X] T015 [US2] Completar media queries em `themes/custom/default/assets/css/beneficios-estudantes.css`: ocultar `.be-split__visual` / `.be-collage` &lt;992px; itens `col` 2 colunas ≥768 e 1 coluna &lt;768; conteúdo largura plena; `overflow-x` da seção controlado — sem afetar PE/QS/home (research R4)
- [X] T016 [US2] Validar SC-003/SC-004 e cenários US2 (legibilidade mobile/tablet; sem overflow; CTA → `/cadastro/candidato`; colagem não impede leitura) conforme `specs/054-beneficios-estudantes-figma/quickstart.md` seção C e `specs/054-beneficios-estudantes-figma/contracts/beneficios-estudantes-figma-render.md`

**Checkpoint**: responsivo alinhado ao contrato; conversão mobile preservada.

---

## Phase 5: User Story 4 — Deploy automatizado da nova composição (Priority: P1)

**Goal**: `custom_configs_update_11078` garante instances/displays, migra legado 4→6, seed badge/título/subtítulo/CTA/colagem/ícones e placement ativo; origem `updb` → `cex` → `cr`; destino sem Block Layout manual (FR-008–009 / SC-005–SC-007).  
**Independent Test Criteria**: ambiente com 4 cards legado → `updb`; 2ª `updb` no-op; `cex` versiona instances/displays (`quickstart.md` seções A+E / deploy contract).

- [X] T017 [US4] Estender `_custom_configs_ensure_beneficios_estudantes_structure` / `_custom_configs_ensure_beneficios_estudantes_displays` para instances `field_text_simple_2` (label Badge), `field_link` (CTA) e `field_image` (colagem) + form/view displays (badge acima do título; link; imagem) **sem** storage novo — em `modules/custom/custom_configs/custom_configs.install` (research R2/R7)
- [X] T018 [US4] Atualizar `_custom_configs_seed_beneficios_estudantes` (e helpers de ícones) para: fill-if-empty badge/título/subtítulo/CTA/colagem; substituir **somente** legados conhecidos (título 022, subtítulos 11035/053, set de 4 cards); criar 6 paragraphs canônicos com `icon-1`…`icon-6`; copiar `collage.png` via `_custom_configs_seed_file_from_module_asset`; preservar editorial divergente — em `modules/custom/custom_configs/custom_configs.install` (research R5/R6; `data-model.md` state transitions)
- [X] T019 [US4] Criar `custom_configs_update_11078` idempotente que orquestra ensure structure/displays → seed/migração → ensure placement `default_beneficiosestudantes` ativo (`content_full` / `/para-estudantes`; reativar se `status: false`) em `modules/custom/custom_configs/custom_configs.install` (research R7)
- [X] T020 [US4] Executar na origem `drush updb -y` → `drush cex -y` → `drush cr`; versionar em `config/sync/` as instances `field.field.block_content.beneficios_estudantes.field_text_simple_2.yml` / `field_link.yml` / `field_image.yml` e displays `core.entity_form_display.block_content.beneficios_estudantes.default.yml` / `core.entity_view_display.block_content.beneficios_estudantes.default.yml` — **não** inventar YAML à mão — conforme `specs/054-beneficios-estudantes-figma/contracts/deploy-beneficios-estudantes-figma.md`
- [X] T021 [US4] Validar SC-005/SC-006/SC-007 (6 itens canônicos ou preservação editorial; 2ª updb no-op; zero admin manual; configs no sync) conforme `specs/054-beneficios-estudantes-figma/quickstart.md` seções A e E

**Checkpoint**: produção recebe layout+seed só com Git + `cim` → `updb` → `cim` → `cr`.

---

## Phase 6: User Story 3 — Editor atualiza copy e itens sem código (Priority: P2)

**Goal**: Editor com permissão de blocos altera badge, título, subtítulo, itens (ícone/título/texto) e rótulo/URI do CTA no painel; mudanças refletem em `/para-estudantes` sem deploy; colagem permanece arte única (não itemizada) (FR-001 campos / US3).  
**Independent Test Criteria**: Editar bloco no painel, salvar, recarregar landing (`quickstart.md` seção D).  
**Dependência**: displays do bundle já atualizados em US4 (T017/T020).

- [X] T022 [US3] Confirmar no form display do bundle que badge (`field_text_simple_2`), título, subtítulo, `field_itens_lista`, CTA (`field_link`) e colagem (`field_image` como arte única) estão editáveis com labels claros; paragraph `card_icon_text_p` mantém ícone/título/texto — revisar `config/sync/core.entity_form_display.block_content.beneficios_estudantes.default.yml` e `config/sync/core.entity_form_display.paragraph.card_icon_text_p.default.yml` (ajustar via admin/API + `drush cex` se necessário)
- [X] T023 [US3] Validar cenários US3 (editar título de item e rótulo/URI do CTA → refletidos na landing; sem UI para editar cada foto da órbita) conforme `specs/054-beneficios-estudantes-figma/quickstart.md` seção D

**Checkpoint**: marketing edita copy/CTA sem código; colagem permanece brand art versionada.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Objetivo**: regressão seções fora de escopo, PRD cirúrgico, cache e aceite SC-001–SC-007.

- [X] T024 [P] Smoke de regressão: banner, Hero Search, jornada, `block_3` e CTA final permanecem; `/para-empresas`, `/quem-somos` e home sem impacto da library — conforme `specs/054-beneficios-estudantes-figma/quickstart.md` seção F
- [X] T025 [P] Atualizar `PRD.md` §3.6 de forma cirúrgica: seção Benefícios = badge + título Figma + 6 itens lista + CTA + colagem desktop; citar hook `11078`; não reescrever a composição inteira da página
- [X] T026 [P] Confirmar bloco SPECKIT em `.cursor/rules/specify-rules.mdc` aponta para `054-beneficios-estudantes-figma` com `spec.md` / `plan.md` / `tasks.md`
- [X] T027 Executar limpeza de cache Drupal após Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr` ou equivalente)
- [X] T028 Executar validação final completa (SC-001–SC-007; edges: &lt;6 itens, &gt;6 itens, ícone ausente, colagem faltando em `public://`, CTA vazio, convivência com CTA final `026`) com `specs/054-beneficios-estudantes-figma/quickstart.md` e `specs/054-beneficios-estudantes-figma/checklists/requirements.md`

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US4 (Phase 5) → US3 (Phase 6) → Polish (Phase 7)
- US1 (Twig/CSS/assets desktop) é MVP e pode ser validado visualmente com conteúdo parcial até o seed de US4
- US2 (CSS responsivo) depende do markup/CSS base de US1; arquivos distintos do hook
- US4 (hook `11078` + `updb`/`cex`) desbloqueia seed canônico e displays para US3
- US3 (editor) depende dos form displays exportados em US4
- Polish após US1–US4 (+ US3)

## Dependency Graph

```text
Phase1 → Phase2 (escopo + UUID + storages + hook 11078)
           ├→ US1 (P1) assets + Twig bloco/paragraph + CSS split ── MVP
           │     └→ US2 (P1) media queries / ocultar colagem <992
           └→ US4 (P1) ensure fields/displays + seed 4→6 + updb/cex/cr
                 └→ US3 (P2) validar edição no painel (displays já no sync)
                      └→ Polish (regressão, PRD, cr, SC-001–SC-007)
                           ↳ revalida US1/US2 pós-seed canônico
```

## Parallel Execution Opportunities

- **Setup**: T002–T005 em paralelo após T001; T006 após inventário tema/hook
- **Foundational**: T008 ∥ T009 após T007
- **US1**: T010 [assets] ∥ T012 [paragraph Twig]; T011 [block Twig] → T013 [CSS]; T014 após US4 para seed completo
- **US2**: T015 após T013; T016 após seed (US4) preferencialmente
- **US4**: T017 → T018 → T019 → T020 → T021 (sequencial no mesmo `.install`)
- **US3**: T022 → T023 após T020
- **Polish**: T024 ∥ T025 ∥ T026; depois T027–T028

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (markup/CSS split + assets da colagem/ícones)
2. **Responsivo**: US2 (ocultar colagem &lt;992; grade 1/2 colunas)
3. **Deploy**: US4 (`11078` ensure + migração 4→6 + `updb`/`cex`/`cr`)
4. **Editorial**: US3 (confirmar painel + aceite)
5. **Fechamento**: Polish — regressão, PRD §3.6, `drush cr`, SC-001–SC-007

## Format Validation

- Todas as tarefas usam checkbox `- [ ]`, ID sequencial (`T001`…`T028`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; validação manual no quickstart)
- Ordem de histórias: US1 (P1 MVP) → US2 (P1) → US4 (P1 deploy) → US3 (P2 editor)
