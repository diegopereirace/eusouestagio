# Tasks: Bloco Missão e Visão

**Input**: Artefatos de design em `specs/011-bloco-missao-visao/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/missao-visao-render.md`, `quickstart.md`  
**Branch**: `dev`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` ausente neste repo; `FEATURE_DIR` = `specs/011-bloco-missao-visao`; template alinhado a `specs/010-layout-sobre-nos/tasks.md` / `specs/009-banner-quem-somos/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar padrões de block+paragraph, Twig Barrio, libraries e último hook antes de criar config/tema/seed.

- [X] T001 Confirmar artefatos SDD e feature ativa (`specs/011-bloco-missao-visao/`, `.specify/feature.json`)
- [X] T002 [P] Inventariar padrão de block type + ERR lista em `config/sync/block_content.type.nossos_diferenciais.yml`, `config/sync/field.storage.block_content.field_diferenciais_lista.yml` e `config/sync/field.field.block_content.nossos_diferenciais.field_diferenciais_lista.yml`
- [X] T003 [P] Inventariar storages reutilizáveis `field.storage.block_content.field_image.yml`, `field.storage.paragraph.field_text_simple.yml` e `field.storage.paragraph.field_text_simple_long.yml` em `config/sync/`
- [X] T004 [P] Inventariar suggestion Twig/library de bloco irmão em `themes/custom/default/templates/block/block--block-nossos-diferenciais.html.twig` e `themes/custom/default/default.libraries.yml`
- [X] T005 Confirmar último hook `custom_configs_update_11013` e próximo livre `11014` em `modules/custom/custom_configs/custom_configs.install`; inventariar padrão de assets em `modules/custom/custom_configs/assets/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar tipos, storage novo `field_itens_lista`, instances, displays e placement — pré-requisito de todas as user stories. Reusar storages canônicos; único storage novo justificado.

- [X] T006 Criar paragraph type `missao_visao_item_p` em `config/sync/paragraphs.paragraphs_type.missao_visao_item_p.yml`
- [X] T007 Criar block type `missao_visao` em `config/sync/block_content.type.missao_visao.yml`
- [X] T008 Criar storage ERR `field_itens_lista` (target paragraph, cardinality **2**) em `config/sync/field.storage.block_content.field_itens_lista.yml`
- [X] T009 [P] Criar field instances do paragraph (reuso `field_text_simple` + `field_text_simple_long`) em `config/sync/field.field.paragraph.missao_visao_item_p.field_text_simple.yml` e `config/sync/field.field.paragraph.missao_visao_item_p.field_text_simple_long.yml`
- [X] T010 [P] Criar field instances do bloco (reuso `field_image` + instance `field_itens_lista` → `missao_visao_item_p`) em `config/sync/field.field.block_content.missao_visao.field_image.yml` e `config/sync/field.field.block_content.missao_visao.field_itens_lista.yml`
- [X] T011 [P] Criar form/view displays do paragraph em `config/sync/core.entity_form_display.paragraph.missao_visao_item_p.default.yml` e `config/sync/core.entity_view_display.paragraph.missao_visao_item_p.default.yml`
- [X] T012 Criar form/view displays do bloco em `config/sync/core.entity_form_display.block_content.missao_visao.default.yml` e `config/sync/core.entity_view_display.block_content.missao_visao.default.yml`
- [X] T013 Criar placement `default_missaovisao` (tema `default`, região `content_full`, weight `0`, pages `/quem-somos`, `label_display: '0'`, plugin `block_content:b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e`) em `config/sync/block.block.default_missaovisao.yml`

**Checkpoint**: após esta fase, US1–US5 podem avançar; Twig/CSS/seed consomem os YAMLs acima (seed de conteúdo vem em US5).

---

## Phase 3: User Story 1 — Visitante vê Missão e Visão em Quem Somos (Priority: P1)

**Goal**: viewport ≥768px — faixa full-width com imagem de fundo, overlay escuro, duas colunas Missão | Visão, tipografia Poppins branca, divisória sutil no 1º item.  
**Independent Test Criteria**: abrir `/quem-somos` ≥768px com bloco publicado e comparar estrutura/overlay/colunas/tipografia com Figma (`contracts/missao-visao-render.md`).

- [X] T014 [US1] Registrar library `missao_visao` apontando para o CSS dedicado em `themes/custom/default/default.libraries.yml`
- [X] T015 [P] [US1] Criar CSS base encapsulado (tokens `--mv-overlay`; `min-height: 380px`; `background-size/position`; overlay `::before`; tipografia Poppins; fallback fundo escuro) em `themes/custom/default/assets/css/block-missao-visao.css`
- [X] T016 [US1] Criar Twig do bloco: wrapper `.block-missao-visao`, `background-image` inline quando houver URI, attach library, iterar `field_itens_lista` em grid `.container`/`.row`/`.col-md-6` com título H2 + texto, preservando `attributes`/`title_prefix`/`title_suffix` em `themes/custom/default/templates/block/block--block-missao-visao.html.twig`
- [X] T017 [P] [US1] Criar Twig de paragraph opcional só se o display field exigir markup de coluna isolado em `themes/custom/default/templates/paragraph/paragraph--missao-visao-item-p.html.twig` (YAGNI — colunas emitidas em T016)
- [X] T018 [US1] Implementar fallbacks no Twig (zero itens → omitir `.row`; 1 item → uma coluna; sem título/descrição → omitir H2/`<p>`; sem imagem → sem inline `background-image`) em `themes/custom/default/templates/block/block--block-missao-visao.html.twig`
- [X] T019 [P] [US1] Estilizar desktop (divisória no 1º `.col-md-6` / `.block-missao-visao__col--first` em `md+`; contraste texto branco sobre overlay) sob `.block-missao-visao` em `themes/custom/default/assets/css/block-missao-visao.css`
- [X] T020 [US1] Validar SC-001 / cenários US1 em viewport ≥768px conforme `specs/011-bloco-missao-visao/quickstart.md` e `specs/011-bloco-missao-visao/contracts/missao-visao-render.md`

---

## Phase 4: User Story 2 — Visitante mobile vê empilhamento legível (Priority: P1)

**Goal**: viewport &lt;768px — itens empilhados, legíveis, sem overflow-x; divisória desktop ausente.  
**Independent Test Criteria**: `/quem-somos` ≤767.98px — empilhamento, legibilidade e ausência de scroll horizontal do bloco.

- [X] T021 [US2] Ajustar CSS mobile (empilhamento `col-md-6`; desligar `border-end`/divisória abaixo de `md`; conter overflow) sob `.block-missao-visao` em `themes/custom/default/assets/css/block-missao-visao.css`
- [X] T022 [US2] Confirmar classes Bootstrap responsivas e markup sem forçar duas colunas no mobile em `themes/custom/default/templates/block/block--block-missao-visao.html.twig`
- [X] T023 [US2] Validar SC-002 / cenários US2 em viewport ≤767.98px conforme `specs/011-bloco-missao-visao/quickstart.md`

---

## Phase 5: User Story 4 — Bloco aparece só em Quem Somos (Priority: P1)

**Goal**: placement `content_full` limitado a `/quem-somos`; home e demais internas sem o bloco.  
**Independent Test Criteria**: comparar `/quem-somos`, `<front>`, `/para-estudantes` e `/contato` após `cim`/`cr`.

- [X] T024 [US4] Confirmar YAML do placement (plugin UUID `b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e`, região `content_full`, pages `/quem-somos`, `label_display: '0'`) em `config/sync/block.block.default_missaovisao.yml`
- [X] T025 [US4] Validar SC-004 (visível só em Quem Somos; home/internas limpas) conforme `specs/011-bloco-missao-visao/quickstart.md`

---

## Phase 6: User Story 3 — Editor gerencia conteúdo sem código (Priority: P1)

**Goal**: form do bloco expõe imagem + até 2 itens (título/descrição); cardinality 2 impede 3º; alterações refletem em `/quem-somos` após cache.  
**Independent Test Criteria**: editar o bloco no painel, salvar e recarregar `/quem-somos`; tentar 3º paragraph.

- [X] T026 [US3] Confirmar form display com `field_image` + `field_itens_lista` (widget paragraphs, max 2) e forms do item com `field_text_simple`/`field_text_simple_long` em `config/sync/core.entity_form_display.block_content.missao_visao.default.yml` e `config/sync/core.entity_form_display.paragraph.missao_visao_item_p.default.yml`
- [X] T027 [US3] Confirmar que alterações editoriais de textos/imagem/ordem aparecem no Twig público via entidades do bloco em `themes/custom/default/templates/block/block--block-missao-visao.html.twig` e rota `/quem-somos`
- [X] T028 [US3] Validar SC-003 e cenários US3 (edição &lt;5 min; cardinality 2; publish) conforme `specs/011-bloco-missao-visao/quickstart.md`

---

## Phase 7: User Story 5 — Deploy reproduz estrutura e seed em outro ambiente (Priority: P1)

**Goal**: `custom_configs_update_11014` idempotente garante tipos/fields/displays, seed (UUID fixo + 2 paragraphs + imagem asset) e placement; fluxo `cim` → `updb` → `cr`; `cex` versiona estrutural.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; segunda `updb` sem duplicatas; conteúdo editorial preservado.

- [X] T029 [P] [US5] Versionar asset de fundo do seed em `modules/custom/custom_configs/assets/missao-visao/fundo-missao-visao.jpg`
- [X] T030 [US5] Implementar `custom_configs_update_11014` idempotente (ensure types/fields/displays; seed bloco UUID `b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e` + 2 paragraphs com textos Assumptions + imagem só se ausentes; **nunca** sobrescrever conteúdo editorial; garantir placement/visibilidade se ainda ausente) em `modules/custom/custom_configs/custom_configs.install`
- [X] T031 [P] [US5] Extrair/reusar helpers privados de ensure/seed no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T032 [US5] Exportar/confirmar configs estruturais com `drush cex -y` para os YAMLs listados em `specs/011-bloco-missao-visao/data-model.md` sob `config/sync/` (types, storage, instances, displays, `block.block.default_missaovisao.yml`; `user.role.*` só se cex mostrar diff)
- [X] T033 [US5] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cr` e reexecutar `updb` (SC-005/SC-006) conforme `specs/011-bloco-missao-visao/quickstart.md`

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, isolamento CSS (banner 009 + Sobre nós 010 intactos) e aceite final SC-001–SC-008.

- [X] T034 [P] Atualizar §3.6 (bullet `missao_visao`, fields, placement `content_full` `/quem-somos`, hook `11014`) e referência cruzada opcional em §3.1.0 Quem somos em `PRD.md`
- [X] T035 Executar validação final completa (SC-001–SC-008; zero/1 item; sem imagem; isolamento home/banner/Sobre nós; sem overflow mobile) com `specs/011-bloco-missao-visao/quickstart.md` e `specs/011-bloco-missao-visao/checklists/requirements.md`
- [X] T036 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US4 (Phase 5) → US3 (Phase 6) → US5 (Phase 7) → Polish (Phase 8)
- US2 depende do markup/CSS base de US1 (T016–T019)
- US4 valida sobretudo o Foundational (T013); pode rodar em paralelo com US1 após Phase 2 (aceite completo após seed US5)
- US3 depende de form displays (T011–T012) + Twig com fallbacks (T018); ideal após seed mínimo (US5) ou conteúdo manual de teste
- US5 (hook/asset/cex) independente do polish visual fino, mas precisa dos YAMLs da Phase 2; validar pós-Twig mínimo (T016)
- Polish após US1–US5

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1)
                    ├→ US2 (P1)
                    ├→ US4 (P1) ──┐
                    ├→ US3 (P1) ──┤
                    └→ US5 (P1) ──┴→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002, T003, T004 em paralelo após T001; T005 após T001
- **Foundational**: T006 → T007 → T008; depois T009 ∥ T010 ∥ T011; T012 após instances; T013 após block type + UUID definido
- **US1**: T015 (CSS base) em paralelo com início de T016 após T014; T017 YAGNI/paralelo; T019 após classes estáveis em T016
- **US2 ∥ US4**: T021–T022 com T024 após T016 (US4 YAML já em T013)
- **US3**: após T012 + T018; melhor com seed de US5 ou bloco de teste
- **US5**: T029 em paralelo com Twig/CSS; T030 após Phase 2 + T029; T031 junto a T030; T032–T033 após hook
- **Polish**: T034 em paralelo com preparo de T035

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (desktop Missão|Visão) — valor principal da feature
2. **Mobile + escopo de rota**: US2 + US4 (placement)
3. **Editorial + deploy**: US3 (form) + US5 (`11014` + asset + cim/updb/cr + cex)
4. **Fechamento**: Polish (PRD §3.6 + aceite SC-001–SC-008)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T036`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias por prioridade P1: US1 → US2 → US4 → US3 → US5 (todas P1 na spec; ordem reflete dependências de implementação)
