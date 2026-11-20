# Tasks: Refatorar UI — Nossa Metodologia

**Input**: Artefatos de design em `specs/043-nossa-metodologia-ui/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/block-render-ui.md`, `contracts/deploy-nossa-metodologia-ui.md`, `quickstart.md`  
**Branch sugerida**: `feat/nossa-metodologia-ui` (a partir de `main`)  
**Predecessor**: `005-nossa-metodologia` (bloco/paragraph/seed/placements); vizinho visual Home: `042-nossos-diferenciais-ui`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md` (sem PHPUnit)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/043-nossa-metodologia-ui` (via `.specify/feature.json`); template alinhado a `specs/042-nossos-diferenciais-ui/tasks.md`  
**Config estrutural**: nenhuma — sem `hook_update_N`, sem `drush cex`/`cim`/`updb` obrigatórios; publicar com Git + `drush cr`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar Twig/CSS legado NM, library padrão 042 e placements Home/PE antes de mutar o tema.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/block-render-ui.md`, `contracts/deploy-nossa-metodologia-ui.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/043-nossa-metodologia-ui/` e feature ativa `043-nossa-metodologia-ui` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar Twig do bloco (header `text-center text-md-end`, `.nm-etapas`, `.nm-passos`, omit vazios, ausência de `attach_library`) em `themes/custom/default/templates/block/block--block-nossa-metodologia.html.twig`
- [X] T003 [P] Inventariar Twig do paragraph (`.nm-passo` / `__icon` / `__title`) em `themes/custom/default/templates/paragraph/paragraph--metodologia-passo-p.html.twig`
- [X] T004 [P] Inventariar bloco legado NM em `themes/custom/default/assets/css/style.css` (~linhas 3245–3452: título navy/700, `.nm-etapas`/`flex: 1 1 0`, setas `top: 48px`, breakpoints ≤767.98 / ≤991.98)
- [X] T005 [P] Inventariar padrão de library dedicada (ex.: `nossos_diferenciais`) em `themes/custom/default/default.libraries.yml` e CSS irmão `themes/custom/default/assets/css/nossos-diferenciais.css`
- [X] T006 Confirmar placements inalterados (mesma entidade UUID `1f9b40ca-aa77-4e7e-a450-5aa94888e274`) em `config/sync/block.block.default_nossametodologia.yml` e `config/sync/block.block.default_nossametodologiaparaempresas.yml` (ou nomes equivalentes) — anotar gaps em `specs/043-nossa-metodologia-ui/contracts/deploy-nossa-metodologia-ui.md` se houver

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar escopo negativo e criar a superfície CSS/library dedicada — pré-requisito de todas as user stories. Sem CMS novo.

**⚠️ CRITICAL**: Nenhuma user story de código começa antes desta fase.

- [X] T007 Congelar escopo negativo: **não** criar fields/block types/paragraphs; **não** editar `config/sync/` obrigatório; **não** criar `hook_update_N` em `modules/custom/custom_configs/custom_configs.install`; **não** redesenhar outros blocos da Home (`nossos_diferenciais`, vagas, hero); **não** editar `core/` nem `vendor/`; **não** introduzir pipeline SCSS
- [X] T008 [P] Registrar library `nossa_metodologia` → `assets/css/nossa-metodologia.css` com dependência `default/bootstrap` em `themes/custom/default/default.libraries.yml` (padrão R1 / 042)
- [X] T009 Criar arquivo CSS dedicado vazio/esqueleto Mobile First (comentário de escopo `.block-nossa-metodologia` / `.nm-*`) em `themes/custom/default/assets/css/nossa-metodologia.css`
- [X] T010 Anexar `{{ attach_library('default/nossa_metodologia') }}` no Twig do bloco em `themes/custom/default/templates/block/block--block-nossa-metodologia.html.twig`
- [X] T011 Migrar regras `.block-nossa-metodologia` / `.nm-*` de `themes/custom/default/assets/css/style.css` (~3245–3452) para `themes/custom/default/assets/css/nossa-metodologia.css` e remover o bloco legado de `style.css` (deixar comentário-ponteiro de uma linha se útil) — sem duplicata de seletores

**Checkpoint**: library carrega no HTML após `drush cr`; estilos NM só no CSS dedicado; pronto para tipografia/wrappers/setas.

---

## Phase 3: User Story 1 — Visitante vê a seção alinhada ao Figma (Priority: P1) 🎯 MVP

**Goal**: desktop ≥992px — título preto SemiBold 30/38/−0.3, cabeçalho alinhado à direita, wrappers `.nm-etapas` ~770px e `.nm-passos` ~1160px centralizados (FR-002–006/008/010–012 / SC-001/002/003).  
**Independent Test Criteria**: Home ≥992px side-by-side com Figma; título `#000` / ~600; boxes DevTools ≤770px / ≤1160px; checklist ≥90% (`quickstart.md` seções B+C / `contracts/block-render-ui.md`).

- [X] T012 [US1] Aplicar tipografia do cabeçalho (título `#000000`, Poppins 600, 30px, lh 38px, ls −0.3px; subtítulo Poppins coerente alinhado ao título) sob `.nm-header__title` / `.nm-header__subtitle` em `themes/custom/default/assets/css/nossa-metodologia.css` — **remover** `var(--brand-navy)` e weight 700 do título
- [X] T013 [P] [US1] Confirmar utilitários Bootstrap do cabeçalho (`text-center text-md-end`) e ritmo vertical generoso da seção (`.container` / padding) em `themes/custom/default/templates/block/block--block-nossa-metodologia.html.twig` e `themes/custom/default/assets/css/nossa-metodologia.css` (FR-002–004)
- [X] T014 [US1] Aplicar wrappers de linha em `@media (min-width: 992px)`: `.nm-etapas` `max-width: 770px` + centralização; `.nm-passos` `max-width: 1160px` + centralização; altura suficiente (~48px chevrons / ~154px passos) em `themes/custom/default/assets/css/nossa-metodologia.css` (FR-005/006/008; R3)
- [X] T015 [P] [US1] Adicionar classes utilitárias leves nos wrappers (`mx-auto` / flex helpers) no Twig **somente se** ajudarem o contrato sem aninhamento profundo — em `themes/custom/default/templates/block/block--block-nossa-metodologia.html.twig` (R6/R8)
- [X] T016 [US1] Validar SC-001/SC-002/SC-003 e cenários US1 (tipografia + alinhamento + larguras) conforme `specs/043-nossa-metodologia-ui/quickstart.md` seções B e C

**Checkpoint**: visitante desktop reconhece a seção Figma — MVP visual da feature.

---

## Phase 4: User Story 2 — Ritmo e hierarquia das duas linhas (Priority: P1)

**Goal**: chevrons contidos no wrapper 770px (não esticados a 1280px); passos/setas distribuídos no 1160px; setas verdes no centro vertical dos círculos (FR-007 / SC-003).  
**Independent Test Criteria**: DevTools — itens sem `flex: 1 1 0` + `max-width: none` que estiquem além do wrapper; setas alinhadas ao eixo dos ícones 96px (`quickstart.md` seção C item 3).

- [X] T017 [US2] Refatorar flex da linha superior: remover esticamento (`flex: 1 1 0` / `max-width: none`) de `.nm-etapas__item`; usar `justify-content: space-between` / gap equilibrado **dentro** do wrapper 770px em `themes/custom/default/assets/css/nossa-metodologia.css` (R3)
- [X] T018 [P] [US2] Refatorar flex da linha inferior: itens de `.nm-passos` distribuídos com espaço equilibrado dentro do wrapper 1160px (sem ocupar 1280px do container) em `themes/custom/default/assets/css/nossa-metodologia.css`
- [X] T019 [US2] Alinhar setas verdes ao centro vertical dos círculos (`.nm-passo__icon` 96px): preferir `align-items: center` no eixo dos ícones; eliminar âncora `top: 48px` relativa ao rótulo; preservar `::after` decorativo SVG em `themes/custom/default/assets/css/nossa-metodologia.css` (R5 / FR-007)
- [X] T020 [P] [US2] Confirmar markup do passo (`.nm-passo` / `__icon` / `__title`; omit vazios) em `themes/custom/default/templates/paragraph/paragraph--metodologia-passo-p.html.twig` — alterar só se classes faltarem ao contrato `contracts/block-render-ui.md`
- [X] T021 [US2] Validar cenários US2 / quickstart C (chevrons ≤770px; passos ≤1160px; setas no centro dos círculos) em `specs/043-nossa-metodologia-ui/quickstart.md` seção C

**Checkpoint**: fluxo Identidade → Performance + passos sem esticamento nem setas “penduradas”.

---

## Phase 5: User Story 3 — Experiência mobile sem quebra (Priority: P1)

**Goal**: &lt;992px wrappers 100%; mobile coluna; tablet omite/reorienta setas horizontais no wrap; zero overflow (FR-009/013/014 / SC-004).  
**Independent Test Criteria**: Home ≤575px e ~768px; zero scroll horizontal da seção (`quickstart.md` seção D).

- [X] T022 [US3] Garantir base Mobile First: max-widths 770/1160 **somente** em `min-width: 992px`; abaixo = 100% do container em `themes/custom/default/assets/css/nossa-metodologia.css` (FR-009/013; R3)
- [X] T023 [P] [US3] Preservar empilhamento em coluna mobile (≤767.98px) para chevrons e passos; reorientar setas 90° como comportamento atual; sem overflow horizontal em `themes/custom/default/assets/css/nossa-metodologia.css` (FR-014)
- [X] T024 [US3] Em wrap de passos &lt;992px: ocultar setas horizontais que quebram o fluxo; tablet coerente sem setas “sobrando” em `themes/custom/default/assets/css/nossa-metodologia.css` (US3 cenário 3)
- [X] T025 [US3] Validar SC-004 / cenários US3 (100% wrappers; coluna; setas no wrap) conforme `specs/043-nossa-metodologia-ui/quickstart.md` seção D

**Checkpoint**: mobile/tablet legíveis sem regressão de overflow pelas max-widths desktop.

---

## Phase 6: User Story 4 — Editor continua gerenciando só o conteúdo (Priority: P2)

**Goal**: editor altera título/subtítulo/etapas/passos no CMS; apresentação consome fields existentes; omit vazios; reuso `/para-empresas` (FR-001/017/018 / SC-006/007).  
**Independent Test Criteria**: editar no admin + Home; PE mesma apresentação (`quickstart.md` seções E+F).

- [X] T026 [US4] Confirmar que Twig consome só fields existentes (`field_text_simple`, `field_text_simple_long`, `field_image`, `field_metodologia_passos`) e omite título/subtítulo/etapas/passos vazios sem erro em `themes/custom/default/templates/block/block--block-nossa-metodologia.html.twig`
- [X] T027 [P] [US4] Confirmar que setas/conectores **não** dependem de campos CMS (CSS `::after` apenas) em `themes/custom/default/assets/css/nossa-metodologia.css` e `themes/custom/default/templates/block/block--block-nossa-metodologia.html.twig` (FR-001)
- [X] T028 [US4] Validar SC-006/SC-007 e cenários US4 (edição editorial; sem etapas/passos; reuso `/para-empresas`) conforme `specs/043-nossa-metodologia-ui/quickstart.md` seções E e F

**Checkpoint**: fluxo editorial da 005 preservado; apresentação compartilhada Home + PE.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Objetivo**: a11y, CSS limpo, deploy `drush cr`, PRD/specify-rules, aceite final SC-001–SC-007 e edges.

- [X] T029 [P] Revisar a11y do contrato (`alt` + `loading="lazy"` nas imgs; setas/conectores decorativos não anunciados; contraste título `#000` sobre fundo claro; um único H2) em `themes/custom/default/templates/block/block--block-nossa-metodologia.html.twig` e `themes/custom/default/assets/css/nossa-metodologia.css` (FR-016)
- [X] T030 [P] Auditar CSS (Mobile First; sem aninhamento profundo; `object-fit: contain` em imagens oversized; zero regras NM restantes em `style.css`; escopo só `.block-nossa-metodologia` / `.nm-*`) em `themes/custom/default/assets/css/nossa-metodologia.css` e `themes/custom/default/assets/css/style.css` (FR-015)
- [X] T031 Executar limpeza de cache local e gates de deploy (library `default/nossa_metodologia` no HTML; Home + `/para-empresas` atualizadas; zero admin) com `docker compose exec -T drupal vendor/bin/drush cr` conforme `specs/043-nossa-metodologia-ui/contracts/deploy-nossa-metodologia-ui.md` e `quickstart.md` seções A+G
- [X] T032 [P] Atualizar `PRD.md` de forma cirúrgica (seção Nossa Metodologia / Home): apresentação alinhada ao Figma; library dedicada; sem CMS novo
- [X] T033 [P] Atualizar bloco SPECKIT em `.cursor/rules/specify-rules.mdc` para feature `043-nossa-metodologia-ui` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T034 Executar validação final completa (SC-001–SC-007; edges: título/linhas vazios; N>3; imagem oversized; zoom) com `specs/043-nossa-metodologia-ui/quickstart.md` e `specs/043-nossa-metodologia-ui/checklists/requirements.md`

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3, MVP) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → Polish (Phase 7)
- US1 entrega tipografia + wrappers desktop — base visual; US2 refine flex/setas no mesmo CSS
- US3 depende dos seletores de wrappers/setas de US1–US2 (Mobile First nos mesmos arquivos)
- US4 é sobretudo validação editorial/reuso — após markup estável de US1
- Polish (a11y, auditoria CSS, `drush cr`, PRD) após US1–US4
- Nenhuma história exige `config/sync` nem `hook_update_N`

## Dependency Graph

```text
Phase1 → Phase2 (library + CSS dedicado + migrate style.css + attach)
           └→ US1 (P1) tipografia + wrappers 770/1160 ── MVP
                ├→ US2 (P1) flex sem esticar + setas no centro dos círculos
                ├→ US3 (P1) Mobile First + coluna + hide setas no wrap
                └→ US4 (P2) validação editorial + PE
                     └→ Polish (a11y, audit, drush cr, PRD, specify-rules)
```

## Parallel Execution Opportunities

- **Setup**: T002–T005 em paralelo após T001; T006 após inventário
- **Foundational**: T008 e T009 em paralelo após T007; T010 após T008; T011 após T009
- **US1**: T012 e T013 em paralelo; T014 após tipografia/base; T015 [P] com T014 se Twig isolado; T016 por último
- **US2**: T017 e T018 em paralelo; T019 após flex dos passos; T020 [P] com T017–T019; T021 por último
- **US3**: T022 → T023/T024 (mesmo CSS; T023 [P] se edição isolada); T025 por último
- **US4**: T026 e T027 em paralelo; T028 por último
- **Polish**: T029/T030/T032/T033 em paralelo; T031 e T034 após código estável

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (library dedicada, tipografia Figma, wrappers 770/1160 desktop)
2. **Ritmo**: US2 — flex contido + setas no centro dos círculos
3. **Mobile**: US3 — 100% wrappers + coluna + setas no wrap
4. **Editorial**: US4 — confirmar CMS inalterado e reuso PE
5. **Fechamento**: Polish — a11y, auditoria CSS, `drush cr`, PRD, specify-rules, SC-001–SC-007

## Format Validation

- Todas as tarefas usam checkbox, ID sequencial (`T001`…`T034`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; validação manual no quickstart)
- Ordem de histórias: US1 (P1 MVP) → US2 (P1) → US3 (P1) → US4 (P2)
