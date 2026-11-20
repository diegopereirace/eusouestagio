# Tasks: Restaurar Benefícios Estudantes — Layout Figma (split + colagem circular)

**Input**: Artefatos de design em `specs/055-restaurar-beneficios-estudantes/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/beneficios-estudantes-render.md`, `contracts/deploy-beneficios-estudantes.md`, `quickstart.md`  
**Branch sugerida**: `main` (workflow do projeto)  
**Predecessors**: `022` / `053` (4 cards); `054` (split Figma + `11081`); `11079`/`11080` (regressão histórica)  
**Hook**: `custom_configs_update_11082` — **nota**: plan/spec citam `11081`, mas `11081` já existe (feature 054); próximo livre verificado no código = `11082`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md` (sem PHPUnit)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/055-restaurar-beneficios-estudantes` (via `.specify/feature.json`); template alinhado a `specs/054-beneficios-estudantes-figma/tasks.md`  
**Baseline verificado (origem)**: canônico `_custom_configs_beneficios_estudantes_canonical_items()` já retorna 6 itens Figma; instances badge/CTA/imagem já no bundle; Twig/CSS = split 054 parcial (colagem = 1 `<img>`; sem `#eef5ff`, sem título bicolor, sem cards brancos, colagem oculta &lt;992); assets `icon-1`…`icon-6` + `collage.png` presentes — faltam faces da órbita  
**Config estrutural**: zero storage/instance nova; `drush cex` só se displays mudarem; destino `cim` → `updb` → `cim` → `cr`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar Twig/CSS/helpers/assets/hook antes de mutar tema ou `custom_configs`.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/beneficios-estudantes-render.md`, `contracts/deploy-beneficios-estudantes.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/055-restaurar-beneficios-estudantes/` e feature ativa `055-restaurar-beneficios-estudantes` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar Twig do bloco (split 054, título único, colagem = 1 `<img>`, sem bicolor/órbita) em `themes/custom/default/templates/block/block--block-beneficios-estudantes.html.twig`
- [X] T003 [P] Inventariar Twig do paragraph (`.be-item` horizontal, `col-12 col-md-6`, sem chrome de card branco) em `themes/custom/default/templates/paragraph/paragraph--card-icon-text-p.html.twig`
- [X] T004 [P] Inventariar CSS/library `beneficios_estudantes` (fundo atual, lista flat, colagem oculta &lt;992) em `themes/custom/default/assets/css/beneficios-estudantes.css` e `themes/custom/default/default.libraries.yml`
- [X] T005 [P] Inventariar helpers `_custom_configs_ensure_beneficios_estudantes_structure` / `_custom_configs_seed_beneficios_estudantes` / `_custom_configs_beneficios_estudantes_canonical_items` / `_custom_configs_ensure_beneficios_estudantes_placement` e confirmar último hook `11081` → próximo livre `11082` em `modules/custom/custom_configs/custom_configs.install`
- [X] T006 Inventariar assets `icon-1.png`…`icon-6.png` + `collage.png` e ausência de `collage-center.png` / `collage-sat-1.png`…`collage-sat-5.png` em `modules/custom/custom_configs/assets/beneficios-estudantes/`

**Checkpoint**: inventário alinhado a plan/research/data-model e ao gap 055 (órbita, `#eef5ff`, cards, bicolor); nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: congelar escopo negativo e confirmar UUID/placement/fields/hook livre antes das user stories.

**⚠️ CRITICAL**: Nenhuma user story de implementação começa antes desta fase.

- [X] T007 Congelar escopo negativo: **não** criar block/paragraph/content types novos nem field **storage**/instance paralelo; **não** editar `core/`/`vendor/`; **não** introduzir pipeline SCSS nem JS novo; **não** redesenhar banner / Hero Search / jornada / `block_3` / CTA final `026`; **não** alterar visual de `/para-empresas`, `/quem-somos`, home ou Benefícios PE; **não** remover `beneficios_estudantes` / `card_icon_text_p`; **não** editar YAML de `config/sync` à mão para inventar config; **não** expor CMS foto-a-foto da órbita
- [X] T008 Confirmar UUID bloco `a8b9c0d1-e2f3-4456-a789-0bcdef123456`, placement `default_beneficiosestudantes` (`content_full`, weight `0`, pages `/para-estudantes`, `label_display: 0`) e instances já presentes `field_text_simple_2` / `field_link` / `field_image` — referência em `config/sync/block.block.default_beneficiosestudantes.yml`, `config/sync/field.field.block_content.beneficios_estudantes.*.yml`, `modules/custom/custom_configs/custom_configs.install`
- [X] T009 Confirmar canônico atual = 6 itens Figma em `_custom_configs_beneficios_estudantes_canonical_items()` e que o próximo hook livre é `11082` (pós-`11081` da 054) em `modules/custom/custom_configs/custom_configs.install`

**Checkpoint**: escopo congelado; UUID/placement/fields/hook confirmados; pronto para US1.

---

## Phase 3: User Story 1 — Visitante vê o layout Figma restaurado (Priority: P1) 🎯 MVP

**Goal**: Em desktop ≥992px, `/para-estudantes` mostra fundo `#eef5ff`, badge, título bicolor canônico, subtítulo, 6 cards brancos (ícone à esquerda) em grade 2×3, CTA laranja com seta e colagem circular estruturada (centro + 5 satélites + anel pontilhado) à direita; grade pública de 4 cards centrados deixa de ser a UI (FR-001–004, FR-006–008, FR-014 / SC-001, SC-002, SC-005).  
**Independent Test Criteria**: Abrir `/para-estudantes` em viewport ≥992px; comparar cabeçalho, 6 cards, CTA e órbita com o frame Figma (`quickstart.md` seção B / `contracts/beneficios-estudantes-render.md`).  
**Nota**: seed/migração/recreate e sync de faces da órbita ficam completos em US4 (`11082`); nesta fase markup/CSS devem renderizar com omit-empty e fallback `collage.png`.

- [X] T010 [P] [US1] Versionar assets da órbita `collage-center.png` + `collage-sat-1.png`…`collage-sat-5.png` (manter `collage.png` como fallback e `icon-1`…`icon-6`) em `modules/custom/custom_configs/assets/beneficios-estudantes/`
- [X] T011 [US1] Atualizar Twig do bloco: título bicolor (spans `.be-header__title-part--dark` / `--blue` só se título canônico `Muito mais do que encontrar um estágio.`); CTA com indicação visual de seta; colagem estruturada `.be-collage` (`.be-collage__ring` + `.be-collage__center` via `field_image` + `.be-collage__sat--1`…`--5` com paths públicos fixos; fallback `.be-collage__img` se faces ausentes); omit-empty; preservar `attributes` / `content_attributes` / title hooks — conforme `contracts/beneficios-estudantes-render.md` em `themes/custom/default/templates/block/block--block-beneficios-estudantes.html.twig` (research R4/R5/R9)
- [X] T012 [P] [US1] Ajustar Twig do paragraph para classes de card claro (manter layout horizontal `.be-item` / ícone ~40–48px; `col-12 col-md-6`; sem `col-lg-3`) em `themes/custom/default/templates/paragraph/paragraph--card-icon-text-p.html.twig` (research R3)
- [X] T013 [US1] Atualizar CSS sob `.block-beneficios-estudantes`: fundo seção `#eef5ff`; tipografia badge (uppercase se necessário) / título bicolor `#07132E`+`#005B9F` / subtítulo; cards brancos com radius + gap ~16px; CTA laranja `var(--brand-orange)` + seta; órbita (anel pontilhado + centro + satélites posicionados); `loading` lazy — em `themes/custom/default/assets/css/beneficios-estudantes.css` (research R3/R5/R9)
- [X] T014 [US1] Validar SC-001/SC-002/SC-005 e cenários US1 (fundo, badge, título bicolor, 6 cards, CTA, órbita à direita; ausência da grade 4 cards centrados) conforme `specs/055-restaurar-beneficios-estudantes/quickstart.md` seção B e `specs/055-restaurar-beneficios-estudantes/contracts/beneficios-estudantes-render.md` — pós-US4 para seed/faces canônicos completos

**Checkpoint**: markup/CSS do layout Figma desktop restaurado — MVP visual (seed/órbita via US4).

---

## Phase 4: User Story 2 — Visitante mobile/tablet lê sem quebra (Priority: P1)

**Goal**: Em ~768px e ~375px a seção permanece legível sem scroll horizontal; itens 1 coluna em estreito (até 2 ≥768 se estável); colagem abaixo do conteúdo em 576–991 e **omitida** &lt;576; CTA acionável → `/cadastro/candidato` (FR-005 / SC-003, SC-004).  
**Independent Test Criteria**: Abrir `/para-estudantes` em ~768px e ~375px; rolar Benefícios; clicar CTA (`quickstart.md` seção C / contract responsivo).

- [X] T015 [US2] Completar media queries em `themes/custom/default/assets/css/beneficios-estudantes.css`: ≥992 split com órbita à direita; 576–991.98 conteúdo full + colagem abaixo redimensionada; &lt;576 omitir `.be-split__visual` / `.be-collage`; itens 1 coluna em estreito / até 2 ≥768; `overflow-x` controlado; CTA full-width no mobile — sem afetar PE/QS/home (research R6; **diferente** da 054 que ocultava &lt;992)
- [X] T016 [US2] Validar SC-003/SC-004 e cenários US2 (legibilidade; sem overflow; CTA → `/cadastro/candidato`; colagem não impede leitura) conforme `specs/055-restaurar-beneficios-estudantes/quickstart.md` seção C e `specs/055-restaurar-beneficios-estudantes/contracts/beneficios-estudantes-render.md`

**Checkpoint**: responsivo alinhado ao contrato 055; conversão mobile preservada.

---

## Phase 5: User Story 4 — Deploy automatizado recupera instância e placement (Priority: P1)

**Goal**: `custom_configs_update_11082` garante structure/seed (6 itens / fill-if-empty / migração só legado 4 cards / recreate UUID se ausente), copia faces da órbita → `public://`, ensure placement ativo; origem `updb` → `cex` (se display) → `cr`; destino sem Block Layout manual (FR-009–010 / SC-005–SC-008).  
**Independent Test Criteria**: ambiente com 4 cards legado e/ou bloco ausente → `updb`; 2ª `updb` no-op; `cex` só se displays mudarem (`quickstart.md` seções A+E / deploy contract).

- [X] T017 [US4] Garantir que `_custom_configs_beneficios_estudantes_canonical_items()` permanece com os 6 benefícios Figma (ordem/copy/ícones do Escopo) e que `_custom_configs_seed_beneficios_estudantes` aplica state transitions de `data-model.md` (título/subtítulo legados 4-cards → canônico; lista exatamente 4 títulos ∈ legado → substituir pelos 6; badge/CTA/`field_image` fill-if-empty; recreate UUID se ausente; preservar editorial divergente) em `modules/custom/custom_configs/custom_configs.install` (research R7)
- [X] T018 [US4] Adicionar helper idempotente de sync/cópia dos assets da órbita (`collage-center.png`, `collage-sat-1`…`5`, fallback `collage.png`) de `modules/custom/custom_configs/assets/beneficios-estudantes/` → `public://beneficios-estudantes/` e usá-lo no seed/`field_image` central quando vazio — em `modules/custom/custom_configs/custom_configs.install` (research R5/R8)
- [X] T019 [US4] Criar `custom_configs_update_11082` idempotente que orquestra ensure structure → seed/migração/recreate → sync assets órbita → ensure placement `default_beneficiosestudantes` ativo (`content_full` / `/para-estudantes`; reativar se desabilitado) em `modules/custom/custom_configs/custom_configs.install` (research R8; contrato `contracts/deploy-beneficios-estudantes.md`)
- [X] T020 [US4] Executar na origem `drush updb -y` → `drush cex -y` → `drush cr`; versionar em `config/sync/` **somente** se form/view display do bundle mudar — **não** inventar YAML à mão; **não** criar storage/instance nova — conforme `specs/055-restaurar-beneficios-estudantes/contracts/deploy-beneficios-estudantes.md`
- [X] T021 [US4] Validar SC-005/SC-006/SC-007/SC-008 (6 itens canônicos ou preservação editorial; recreate se UUID ausente; 2ª updb no-op; zero admin manual; faces em `public://` ou fallback) conforme `specs/055-restaurar-beneficios-estudantes/quickstart.md` seções A e E

**Checkpoint**: produção recebe layout+seed+órbita só com Git + `cim` → `updb` → `cim` → `cr`.

---

## Phase 6: User Story 3 — Editor atualiza copy e itens sem código (Priority: P2)

**Goal**: Editor com permissão de blocos altera badge, título, subtítulo, itens (ícone/título/texto) e rótulo/URI do CTA no painel; mudanças refletem em `/para-estudantes` sem deploy; satélites da órbita permanecem arte de seed (não editáveis individualmente) (FR-001 campos / US3).  
**Independent Test Criteria**: Editar bloco no painel, salvar, recarregar landing (`quickstart.md` seção D).  
**Dependência**: displays do bundle já confirmados/exportados em US4 (T020).

- [X] T022 [US3] Confirmar no form display do bundle que badge (`field_text_simple_2`), título, subtítulo, `field_itens_lista`, CTA (`field_link`) e foto central (`field_image`) estão editáveis com labels claros; paragraph `card_icon_text_p` mantém ícone/título/texto; **sem** fields para satélites — revisar `config/sync/core.entity_form_display.block_content.beneficios_estudantes.default.yml` e `config/sync/core.entity_form_display.paragraph.card_icon_text_p.default.yml` (ajustar via admin/API + `drush cex` se necessário)
- [X] T023 [US3] Validar cenários US3 (editar título de item e rótulo/URI do CTA → refletidos na landing; título editorial não canônico = cor única sem split forçado; sem UI para editar cada foto da órbita) conforme `specs/055-restaurar-beneficios-estudantes/quickstart.md` seção D

**Checkpoint**: marketing edita copy/CTA sem código; órbita permanece brand art versionada.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Objetivo**: regressão seções fora de escopo, PRD cirúrgico, cache e aceite SC-001–SC-008.

- [X] T024 [P] Smoke de regressão: banner, Hero Search, jornada, `block_3` e CTA final permanecem; `/para-empresas`, `/quem-somos` e home sem impacto da library — conforme `specs/055-restaurar-beneficios-estudantes/quickstart.md` seção F (FR-012/FR-013)
- [X] T025 [P] Atualizar `PRD.md` §3.6 de forma cirúrgica: seção Benefícios = fundo `#eef5ff` + badge + título bicolor + 6 cards brancos + CTA + colagem/órbita; citar hook `11082`; não reescrever a composição inteira da página (FR-011)
- [X] T026 [P] Confirmar bloco SPECKIT em `.cursor/rules/specify-rules.mdc` aponta para `055-restaurar-beneficios-estudantes` com `spec.md` / `plan.md` / `tasks.md`
- [X] T027 Executar limpeza de cache Drupal após Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr` ou equivalente)
- [X] T028 Executar validação final completa (SC-001–SC-008; edges: &lt;6 itens, &gt;6 itens, ícone ausente, faces faltando em `public://`, CTA vazio, título não canônico, convivência com CTA final `026`) com `specs/055-restaurar-beneficios-estudantes/quickstart.md` e `specs/055-restaurar-beneficios-estudantes/checklists/requirements.md`

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US4 (Phase 5) → US3 (Phase 6) → Polish (Phase 7)
- US1 (Twig/CSS/assets órbita desktop) é MVP e pode ser validado visualmente com fallback `collage.png` até o sync de US4
- US2 (CSS responsivo 576/992) depende do markup/CSS base de US1; arquivos distintos do hook
- US4 (hook `11082` + `updb`/`cex`) desbloqueia seed canônico em destinos legados e faces da órbita para US1/US3
- US3 (editor) depende dos form displays confirmados em US4
- Polish após US1–US4 (+ US3)

## Dependency Graph

```text
Phase1 → Phase2 (escopo + UUID + fields + hook 11082)
           ├→ US1 (P1) assets órbita + Twig bicolor/órbita + CSS cards/#eef5ff ── MVP
           │     └→ US2 (P1) media queries 576–991 colagem abaixo / omit <576
           └→ US4 (P1) seed ensure + sync órbita + update_11082 + updb/cex/cr
                 └→ US3 (P2) validar edição no painel (displays já no sync)
                      └→ Polish (regressão, PRD, cr, SC-001–SC-008)
                           ↳ revalida US1/US2 pós-seed/faces
```

## Parallel Execution Opportunities

- **Setup**: T002–T005 em paralelo após T001; T006 após inventário tema/hook
- **Foundational**: T008 ∥ T009 após T007
- **US1**: T010 [assets órbita] ∥ T012 [paragraph Twig]; T011 [block Twig] → T013 [CSS]; T014 após US4 para seed/faces completos
- **US2**: T015 após T013; T016 após seed (US4) preferencialmente
- **US4**: T017 → T018 → T019 → T020 → T021 (sequencial no mesmo `.install`)
- **US3**: T022 → T023 após T020
- **Polish**: T024 ∥ T025 ∥ T026; depois T027–T028

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (Twig bicolor + órbita + CSS `#eef5ff`/cards + assets faces)
2. **Responsivo**: US2 (colagem 576–991 abaixo; omit &lt;576; grade 1/2 colunas)
3. **Deploy**: US4 (`11082` ensure seed + sync órbita + `updb`/`cex`/`cr`)
4. **Editorial**: US3 (confirmar painel + aceite)
5. **Fechamento**: Polish — regressão, PRD §3.6, `drush cr`, SC-001–SC-008

## Format Validation

- Todas as tarefas usam checkbox `- [ ]`, ID sequencial (`T001`…`T028`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; validação manual no quickstart)
- Ordem de histórias: US1 (P1 MVP) → US2 (P1) → US4 (P1 deploy) → US3 (P2 editor)
- Hook corrigido para `11082` (plan citava `11081`, já consumido pela 054)
