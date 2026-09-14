# Tasks: Layout “Sobre nós” (wrap texto + imagem)

**Input**: Artefatos de design em `specs/010-layout-sobre-nos/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/sobre-nos-render.md`, `quickstart.md`  
**Branch**: `dev`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` ausente neste repo; `FEATURE_DIR` = `specs/010-layout-sobre-nos`; template alinhado a `specs/009-banner-quem-somos/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar Twig/CSS/displays/hook atuais antes de alterar tema ou `custom_configs`.

- [X] T001 Confirmar artefatos SDD e feature ativa (`specs/010-layout-sobre-nos/`, `.specify/feature.json`)
- [X] T002 [P] Inventariar markup atual da 1ª seção (grid `col-md-7`/`col-md-5`) em `themes/custom/default/templates/content/node--quem-somos.html.twig`
- [X] T003 [P] Inventariar padrão de library isolada (`banner_quem_somos`) em `themes/custom/default/default.libraries.yml` e `themes/custom/default/assets/css/banner-quem-somos.css`
- [X] T004 [P] Inventariar form/view displays e field instances do bundle em `config/sync/core.entity_form_display.node.quem_somos.default.yml`, `config/sync/core.entity_view_display.node.quem_somos.default.yml` e `config/sync/field.field.node.quem_somos.*`
- [X] T005 Confirmar último hook `custom_configs_update_11012` e próximo livre `11013` em `modules/custom/custom_configs/custom_configs.install`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar reuso de fields canônicos e display `default` (sem `full` novo) — pré-requisito de todas as user stories. Zero field storage novo.

- [X] T006 Confirmar reuso (sem criar storages paralelos) de `field_titulo`, `field_text_long_formatted` e `field_imagem` nas instances em `config/sync/field.field.node.quem_somos.field_titulo.yml`, `config/sync/field.field.node.quem_somos.field_text_long_formatted.yml` e `config/sync/field.field.node.quem_somos.field_imagem.yml`
- [X] T007 Confirmar que o form “Primeiro Bloco” e o view `default` expõem os três campos (não ocultos de forma a impedir o Twig) em `config/sync/core.entity_form_display.node.quem_somos.default.yml` e `config/sync/core.entity_view_display.node.quem_somos.default.yml`
- [X] T008 Confirmar decisão YAGNI: permanecer no view mode `default` (não criar `full`) conforme `specs/010-layout-sobre-nos/research.md` (R3) e `specs/010-layout-sobre-nos/plan.md`

**Checkpoint**: após esta fase, US1–US5 podem avançar; Twig/CSS/hook não dependem de schema novo.

---

## Phase 3: User Story 1 — Visitante vê “Sobre nós” com texto envolvendo a imagem (Priority: P1)

**Goal**: viewport ≥768px — título + imagem `float-md-end` + único corpo WYSIWYG contornando a foto; proporção ~432×269; HTML formatado preservado; 2ª seção isolada por clearfix.  
**Independent Test Criteria**: abrir `/quem-somos` ≥768px e confirmar float/wrap vs Figma (`contracts/sobre-nos-render.md`).

- [X] T009 [US1] Registrar library `layout_sobre_nos` apontando para o CSS dedicado em `themes/custom/default/default.libraries.yml`
- [X] T010 [P] [US1] Criar CSS base encapsulado (tokens `--sobre-nos-green`/`--sobre-nos-orange` #FD7B1A; Poppins; `max-width` ~432px; clearfix; sem mutar `--brand-orange` global) em `themes/custom/default/assets/css/layout-sobre-nos.css`
- [X] T011 [US1] Refatorar 1ª seção: abandonar grid duas colunas; markup `.sobre-nos` com título, `.sobre-nos__media` (imagem **antes** do corpo) com `position-relative float-md-end` + margens, e `.sobre-nos__body`; anexar library; manter 2ª seção `row-custom-2` intacta em `themes/custom/default/templates/content/node--quem-somos.html.twig`
- [X] T012 [US1] Implementar fallbacks no Twig (omitir `.sobre-nos__media` sem arquivo; omitir corpo vazio; clearfix efetivo para não vazar float) em `themes/custom/default/templates/content/node--quem-somos.html.twig`
- [X] T013 [P] [US1] Estilizar desktop wrap (fluxo texto à esquerda/abaixo; proporção imagem; tipografia seção) sob `.node--type-quem-somos .sobre-nos` em `themes/custom/default/assets/css/layout-sobre-nos.css`
- [X] T014 [US1] Validar SC-001/SC-003 e cenários US1 (wrap, proporção, HTML negrito/parágrafos) conforme `specs/010-layout-sobre-nos/quickstart.md`

---

## Phase 4: User Story 2 — Elementos geométricos acompanham a imagem (Priority: P1)

**Goal**: anel verde vazado ~185×185 (topo direito) e círculo laranja sólido (canto inferior direito) via CSS no wrapper da imagem; zero vazamento para outros nós.  
**Independent Test Criteria**: inspecionar wrapper em desktop/mobile; amostrar home + interna sem círculos fantasma.

- [X] T015 [P] [US2] Implementar decorativos `::before` (anel verde vazado ~185×185) e `::after` (círculo laranja sólido) com `pointer-events: none` em `.sobre-nos__media` em `themes/custom/default/assets/css/layout-sobre-nos.css`
- [X] T016 [US2] Garantir escopo estrito sob `.node--type-quem-somos` / `.sobre-nos` (decorativos só existem com wrapper de imagem) em `themes/custom/default/assets/css/layout-sobre-nos.css`
- [X] T017 [US2] Validar SC-007 / cenários US2 (geometria + isolamento home/interna) conforme `specs/010-layout-sobre-nos/quickstart.md` e `specs/010-layout-sobre-nos/contracts/sobre-nos-render.md`

---

## Phase 5: User Story 3 — Mobile empilha sem float quebrado (Priority: P1)

**Goal**: viewport &lt;768px — float desativado; legível; sem overflow-x; decorativos sem cobrir texto.  
**Independent Test Criteria**: `/quem-somos` ≤767.98px — empilhamento, legibilidade e ausência de scroll horizontal da seção.

- [X] T018 [US3] Ajustar CSS mobile (desligar float abaixo de `md`; conter overflow de decorativos; empilhamento legível) sob `.sobre-nos` em `themes/custom/default/assets/css/layout-sobre-nos.css`
- [X] T019 [US3] Confirmar classes responsivas `float-md-end` / margens `md+` no markup (sem float forçado em mobile) em `themes/custom/default/templates/content/node--quem-somos.html.twig`
- [X] T020 [US3] Validar SC-002 / cenários US3 em viewport ≤767.98px conforme `specs/010-layout-sobre-nos/quickstart.md`

---

## Phase 6: User Story 5 — Deploy reproduz estrutura e display sem painel manual (Priority: P1)

**Goal**: `custom_configs_update_11013` idempotente garante fields/displays; `cim` → `updb` → `cr`; `cex` se estrutural mudar; zero seed editorial.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; segunda `updb` sem duplicatas.

- [X] T021 [US5] Implementar `custom_configs_update_11013` idempotente (ensure instances `field_titulo` / `field_text_long_formatted` / `field_imagem` no bundle `quem_somos`; ensure form + view `default`; no-op se já corretos; **nunca** sobrescrever conteúdo editorial) em `modules/custom/custom_configs/custom_configs.install`
- [X] T022 [P] [US5] Extrair helpers privados de ensure (fields/displays) no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T023 [US5] Exportar/confirmar configs estruturais com `drush cex -y` apenas se form/view/instances mudarem — arquivos alvo em `config/sync/core.entity_form_display.node.quem_somos.default.yml`, `config/sync/core.entity_view_display.node.quem_somos.default.yml` e `config/sync/field.field.node.quem_somos.*`
- [X] T024 [US5] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cr` e reexecutar `updb` (SC-005/SC-006) conforme `specs/010-layout-sobre-nos/quickstart.md`

---

## Phase 7: User Story 4 — Editor mantém título, texto e imagem no CMS (Priority: P2)

**Goal**: formulário do node expõe os três campos do Primeiro Bloco; publicação reflete no layout wrap; ausência de imagem não quebra a página.  
**Independent Test Criteria**: editar node `quem_somos` no admin e recarregar `/quem-somos`; simular imagem ausente.

- [X] T025 [US4] Confirmar no form display que `field_titulo`, `field_text_long_formatted` e `field_imagem` permanecem editáveis no “Primeiro Bloco” em `config/sync/core.entity_form_display.node.quem_somos.default.yml` (pós-hook/cim)
- [X] T026 [US4] Confirmar que alteração editorial de texto/imagem aparece no layout wrap via Twig em `themes/custom/default/templates/content/node--quem-somos.html.twig` e rota `/quem-somos`
- [X] T027 [US4] Validar SC-004/SC-008 e cenários US4 (form visível; publish; fallback sem imagem) conforme `specs/010-layout-sobre-nos/quickstart.md`

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, aceite final e cache limpo sem regressão (banner 009 + 2ª seção).

- [X] T028 [P] Atualizar §3.1 (machine name `quem_somos`, não `quem-somos`) e acrescentar subseção de campos/layout wrap da página institucional em `PRD.md`
- [X] T029 Executar validação final completa (SC-001–SC-008; banner 009 intacto; 2ª seção sem herdar float; amostragem home/interna) com `specs/010-layout-sobre-nos/quickstart.md` e `specs/010-layout-sobre-nos/checklists/requirements.md`
- [X] T030 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US5 (Phase 6) → US4 (Phase 7) → Polish (Phase 8)
- US2 depende do wrapper `.sobre-nos__media` de US1 (T011)
- US3 depende do markup/CSS float de US1 (T011–T013); CSS mobile pode paralelizar com US2 após T011
- US5 (hook/deploy) independente do polish visual fino, mas validar pós-Twig mínimo; pode começar após Phase 2
- US4 valida sobretudo displays + fallbacks Twig; ideal após US5 (hook) e após T012
- Polish após US1–US5

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1)
                    ├→ US2 (P1)
                    ├→ US3 (P1)
                    ├→ US5 (P1) ──┐
                    └─────────────┴→ US4 (P2) → Polish
```

## Parallel Execution Opportunities

- **Setup**: T002, T003, T004 em paralelo após T001; T005 após T001
- **Foundational**: T006 e T007 em paralelo; T008 após leitura de research/plan
- **US1**: T010 (CSS base) em paralelo com início de T011 após T009; T013 após classes estáveis em T011
- **US2 ∥ US3**: T015–T016 com T018–T019 após T011
- **US5 ∥ visual**: T021–T022 após Phase 2, em paralelo a US2/US3 se Twig mínimo (T011) já existir para aceite conjunto
- **US4**: após T012 + T021 (displays garantidos)
- **Polish**: T028 em paralelo com preparo de T029

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (desktop wrap) — valor principal da feature
2. **Design + mobile**: US2 (geometria) + US3 (responsivo)
3. **Deploy**: US5 (`11013` + cim/updb/cr + cex se necessário)
4. **Editorial + fechamento**: US4 (form/fallbacks) + Polish (PRD §3.1 + aceite SC-001–SC-008)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T030`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias por prioridade: US1 → US2 → US3 → US5 (todas P1) → US4 (P2)
