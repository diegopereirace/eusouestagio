# Tasks: Refatorar UI — Nossos Diferenciais (Home)

**Input**: Artefatos de design em `specs/042-nossos-diferenciais-ui/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/block-render-ui.md`, `contracts/deploy-nossos-diferenciais-ui.md`, `quickstart.md`  
**Branch sugerida**: `feat/nossos-diferenciais-ui` (a partir de `main`)  
**Predecessor**: `004-nossos-diferenciais` (bloco/paragraph/seed/placements); isolado de `013-diferenciais-quem-somos`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md` (sem PHPUnit)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/042-nossos-diferenciais-ui` (via `.specify/feature.json`); template alinhado a `specs/041-home-busca-inteligente/tasks.md` / `specs/013-diferenciais-quem-somos/tasks.md`  
**Config estrutural**: nenhuma — sem `hook_update_N`, sem `drush cex`/`cim`/`updb` obrigatórios; publicar com Git + `drush cr`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar Twig/CSS legado ND, library padrão 013 e placements Home/PE antes de mutar o tema.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/block-render-ui.md`, `contracts/deploy-nossos-diferenciais-ui.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/042-nossos-diferenciais-ui/` e feature ativa `042-nossos-diferenciais-ui` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar Twig do bloco (header, grid `col-lg-6`, `.nd-media` sem fatias, omit vazios) em `themes/custom/default/templates/block/block--block-nossos-diferenciais.html.twig`
- [X] T003 [P] Inventariar Twig do paragraph (`.nd-item` + `d-flex`/`gap-3`, ícone/título/desc) em `themes/custom/default/templates/paragraph/paragraph--diferencial-item-p.html.twig`
- [X] T004 [P] Inventariar bloco legado ND em `themes/custom/default/assets/css/style.css` (~linhas 3243–3398: título navy/700, subtítulo 300, `::before`/`::after`, cores `nth-child`)
- [X] T005 [P] Inventariar padrão de library dedicada (ex.: `diferenciais_quem_somos`) em `themes/custom/default/default.libraries.yml` e CSS irmão `themes/custom/default/assets/css/diferenciais-quem-somos.css`
- [X] T006 Confirmar placements inalterados (mesma entidade UUID) em `config/sync/block.block.default_nossosdiferenciais.yml` e `config/sync/block.block.default_nossosdiferenciais_pe.yml` (ou nomes equivalentes) — anotar gaps em `specs/042-nossos-diferenciais-ui/contracts/deploy-nossos-diferenciais-ui.md` se houver

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar escopo negativo e criar a superfície CSS/library dedicada — pré-requisito de todas as user stories. Sem CMS novo; sem pasta de fatias no tema (pivot R3).

**⚠️ CRITICAL**: Nenhuma user story de código começa antes desta fase.

- [X] T007 Congelar escopo negativo: **não** criar fields/block types/paragraphs; **não** editar `config/sync/` obrigatório; **não** criar `hook_update_N` em `modules/custom/custom_configs/custom_configs.install`; **não** tocar Twig/CSS/library de `diferenciais_quem_somos`; **não** editar `core/` nem `vendor/`; **não** introduzir pipeline SCSS
- [X] T008 [P] Registrar library `nossos_diferenciais` → `assets/css/nossos-diferenciais.css` com dependência `default/bootstrap` em `themes/custom/default/default.libraries.yml` (padrão R1 / 013)
- [X] T009 Criar arquivo CSS dedicado vazio/esqueleto Mobile First (comentário de escopo `.block-nossos-diferenciais` / `.nd-*`) em `themes/custom/default/assets/css/nossos-diferenciais.css`
- [X] T010 [P] ~~Criar pasta de fatias no tema~~ **Cancelado (pivot)**: colagem completa vive em `field_image` (CMS); sem `assets/images/nossos-diferenciais/` — ver `contracts/block-render-ui.md`
- [X] T011 Anexar `{{ attach_library('default/nossos_diferenciais') }}` no Twig do bloco em `themes/custom/default/templates/block/block--block-nossos-diferenciais.html.twig`
- [X] T012 Migrar regras `.block-nossos-diferenciais` / `.nd-*` de `themes/custom/default/assets/css/style.css` (~3243–3398) para `themes/custom/default/assets/css/nossos-diferenciais.css` e remover o bloco legado de `style.css` (deixar comentário-ponteiro de uma linha se útil) — sem duplicata de seletores

**Checkpoint**: library carrega no HTML após `drush cr`; estilos ND só no CSS dedicado; pronto para refatorar tipografia/colagem.

---

## Phase 3: User Story 1 — Visitante vê a seção alinhada ao Figma (Priority: P1) 🎯 MVP

**Goal**: desktop ≥992px — título preto SemiBold 30/38/−0.3, subtítulo Regular 14/20 max-width ~663px, grade `col-lg-7`/`col-lg-5` com colagem CMS + decos à esquerda e lista à direita (FR-002–005/007–008/011–014 / SC-001/002).  
**Independent Test Criteria**: Home ≥992px side-by-side com Figma; título `#000` / ~600; colagem CMS; checklist ≥90% (`quickstart.md` seções B+C / `contracts/block-render-ui.md`).

- [X] T013 [US1] Aplicar tipografia do cabeçalho (título `#000000`, Poppins 600, 30px, lh 38px, ls −0.3px; subtítulo 400, 14px, lh 20px, max-width ~663px; paddings verticais generosos) sob `.nd-header__title` / `.nd-header__subtitle` em `themes/custom/default/assets/css/nossos-diferenciais.css` — **remover** `var(--brand-navy)` e weight 700/300 do título/subtítulo
- [X] T014 [US1] Markup da colagem no Twig: wrapper `.nd-media` + decos `.nd-media__deco-*` (`aria-hidden`) + `.nd-media__img` via `image_style('wide')` / placeholder; **sem** `.nd-media__slice` — em `themes/custom/default/templates/block/block--block-nossos-diferenciais.html.twig`
- [X] T015 [P] [US1] Estilizar colagem (CMS ~1324×718 `object-fit`/`aspect-ratio`; decos atrás da foto; `pointer-events: none`; `overflow: hidden` na seção) em `themes/custom/default/assets/css/nossos-diferenciais.css`
- [X] T016 [US1] Confirmar grade desktop (`row align-items-center nd-grid` + `col-lg-7`/`col-lg-5`; cabeçalho acima da grade; espaçamento entre colunas) no Twig e CSS em `themes/custom/default/templates/block/block--block-nossos-diferenciais.html.twig` e `themes/custom/default/assets/css/nossos-diferenciais.css`
- [X] T017 [US1] Validar SC-001/SC-002 e cenários US1 (tipografia + layout + colagem) conforme `specs/042-nossos-diferenciais-ui/quickstart.md` seções B e C

**Checkpoint**: visitante desktop reconhece a seção Figma — MVP visual da feature.

---

## Phase 4: User Story 2 — Lista de diferenciais legível e com hierarquia de cor (Priority: P1)

**Goal**: cada item ícone | título+descrição; títulos bold com cores cicláveis verde → navy → laranja; descrição longa sem overflow (FR-006/009 / SC-004).  
**Independent Test Criteria**: inspecionar ≥3 itens na ordem editorial; cores `.field__item:nth-child(3n+k)`; gap consistente (`quickstart.md` seção D).

- [X] T018 [US2] Confirmar markup do item (`.nd-item` + `d-flex align-items-start gap-3`; `.nd-item__icon` / `__body` / `__title` / `__desc`; omit vazios) em `themes/custom/default/templates/paragraph/paragraph--diferencial-item-p.html.twig` — alterar só se classes faltarem ao contrato
- [X] T019 [P] [US2] Reforçar estilos da lista (gap; títulos `font-weight: 700`; cores via `.field__item:nth-child(3n+k)` + `var(--brand-green|navy|orange)`; `overflow-wrap`; `display: contents` nos wrappers) em `themes/custom/default/assets/css/nossos-diferenciais.css`
- [X] T020 [US2] Validar cenários US2 / quickstart D (ícone|texto; ciclo de cores; descrição longa sem overflow) em `specs/042-nossos-diferenciais-ui/quickstart.md` seção D

**Checkpoint**: lista persuasiva com hierarquia de cor fiel ao Figma.

---

## Phase 5: User Story 3 — Experiência mobile sem quebra (Priority: P1)

**Goal**: &lt;992px empilha cabeçalho → mídia → lista; colagem escala sem overflow; ring oculto em viewport estreita (FR-015/016 / SC-003).  
**Independent Test Criteria**: Home ≤575px e ~768px; zero scroll horizontal da seção (`quickstart.md` seção E).

- [X] T021 [US3] Implementar base Mobile First e breakpoint `min-width: 992px` (coluna única na base; `col-lg-7`/`col-lg-5` no lg+) em `themes/custom/default/assets/css/nossos-diferenciais.css` (R6)
- [X] T022 [P] [US3] Ocultar `.nd-media__deco-ring` em viewport estreita; escalar `.nd-media` com padding seguro; `overflow: hidden` na seção em `themes/custom/default/assets/css/nossos-diferenciais.css`
- [X] T023 [US3] Validar SC-003 / cenários US3 (empilhamento; sem overflow; ring oculto) conforme `specs/042-nossos-diferenciais-ui/quickstart.md` seção E

**Checkpoint**: mobile legível sem regressão de overflow pela colagem.

---

## Phase 6: User Story 4 — Editor continua gerenciando só o conteúdo (Priority: P2)

**Goal**: editor altera título/subtítulo/itens/imagem no CMS; colagem CMS atualiza; decos intactos; placeholder sem imagem; sem exigir arte de fundo (FR-001/018 / SC-005/006).  
**Independent Test Criteria**: editar no admin + Home; `/para-empresas` mesma apresentação (`quickstart.md` seções F+G).

- [X] T024 [US4] Confirmar que Twig consome só fields existentes (`field_text_simple`, `field_text_simple_long`, `field_image`, `field_diferenciais_lista`) e omite título/subtítulo vazios; placeholder `.nd-media__placeholder` se sem imagem em `themes/custom/default/templates/block/block--block-nossos-diferenciais.html.twig`
- [X] T025 [P] [US4] Confirmar que decos **não** dependem de campos CMS (CSS/spans apenas); colagem vem só de `field_image` em `themes/custom/default/templates/block/block--block-nossos-diferenciais.html.twig` e `themes/custom/default/assets/css/nossos-diferenciais.css`
- [X] T026 [US4] Validar SC-005/SC-006 e cenários US4 (edição editorial; troca de âncora; placeholder; reuso `/para-empresas`) conforme `specs/042-nossos-diferenciais-ui/quickstart.md` seções F e G

**Checkpoint**: fluxo editorial da 004 preservado; apresentação compartilhada Home + PE.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Objetivo**: a11y, CSS limpo, deploy `drush cr`, PRD/specify-rules, aceite final SC-001–SC-006 e edges.

- [X] T027 [P] Revisar a11y do contrato (âncora com `alt` + `loading="lazy"` + `width`/`height`; decos `aria-hidden`; contraste dos títulos coloridos) em `themes/custom/default/templates/block/block--block-nossos-diferenciais.html.twig` e `themes/custom/default/assets/css/nossos-diferenciais.css` (FR-017)
- [X] T028 [P] Auditar CSS (Mobile First; sem aninhamento profundo; sem seletores vazando para `.block-diferenciais-quem-somos`; zero regras ND restantes em `style.css`) em `themes/custom/default/assets/css/nossos-diferenciais.css` e `themes/custom/default/assets/css/style.css` (FR-016)
- [X] T029 Executar limpeza de cache local e gates de deploy (library `default/nossos_diferenciais` no HTML; Home + `/para-empresas` atualizadas; zero admin) com `docker compose exec -T drupal vendor/bin/drush cr` conforme `specs/042-nossos-diferenciais-ui/contracts/deploy-nossos-diferenciais-ui.md` e `quickstart.md` seções A+H
- [X] T030 [P] Atualizar `PRD.md` de forma cirúrgica (seção Nossos Diferenciais / Home): apresentação alinhada ao Figma; library dedicada; sem CMS novo
- [X] T031 [P] Atualizar bloco SPECKIT em `.cursor/rules/specify-rules.mdc` para feature `042-nossos-diferenciais-ui` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T032 Executar validação final completa (SC-001–SC-006; edges: título/lista vazios; N>3 cores; imagem oversized; zoom) com `specs/042-nossos-diferenciais-ui/quickstart.md` e `specs/042-nossos-diferenciais-ui/checklists/requirements.md`

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3, MVP) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → Polish (Phase 7)
- US1 entrega tipografia + colagem + grade — base visual; US2 refine a lista no mesmo CSS; US3 depende dos seletores de fatias/colagem de US1
- US4 é sobretudo validação editorial/reuso — após markup estável de US1
- Polish (a11y, auditoria CSS, `drush cr`, PRD) após US1–US4
- Nenhuma história exige `config/sync` nem `hook_update_N`

## Dependency Graph

```text
Phase1 → Phase2 (library + CSS dedicado + migrate style.css + attach)
           └→ US1 (P1) tipografia + Twig colagem + CSS colagem/grade ── MVP
                ├→ US2 (P1) lista / cores nth-child (mesmo CSS ± paragraph)
                ├→ US3 (P1) Mobile First + hide slices (depende markup US1)
                └→ US4 (P2) validação editorial + PE
                     └→ Polish (a11y, audit, drush cr, PRD, specify-rules)
```

## Parallel Execution Opportunities

- **Setup**: T002–T005 em paralelo após T001; T006 após inventário
- **Foundational**: T008 e T010 em paralelo após T007; T009 antes ou junto de T012; T011 após T008; T012 após T009
- **US1**: T014 (Twig) e T013 (tipografia CSS) em paralelo; T015 após T014; T016 após markup/CSS; T017 por último
- **US2**: T018 e T019 em paralelo; T020 por último
- **US3**: T021 → T022 (mesmo CSS; T022 pode ser [P] se edição isolada de slices); T023 por último
- **US4**: T024 e T025 em paralelo; T026 por último
- **Polish**: T027/T028/T030/T031 em paralelo; T029 e T032 após código estável

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (library dedicada, tipografia Figma, colagem composta, grade desktop)
2. **Lista**: US2 — cores cicláveis e hierarquia tipográfica dos itens
3. **Mobile**: US3 — empilhamento + hide slices + zero overflow
4. **Editorial**: US4 — confirmar CMS inalterado e reuso PE
5. **Fechamento**: Polish — a11y, auditoria CSS, `drush cr`, PRD, specify-rules, SC-001–SC-006

## Format Validation

- Todas as tarefas usam checkbox, ID sequencial (`T001`…`T032`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; validação manual no quickstart)
- Ordem de histórias: US1 (P1 MVP) → US2 (P1) → US3 (P1) → US4 (P2)
