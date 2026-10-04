# Tasks: Listagem Vertical de Vagas (`/vagas`)

**Input**: Artefatos de design em `specs/028-vagas-lista-vertical/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/vagas-lista-vertical-render.md`, `contracts/deploy-vagas-lista-vertical.md`, `quickstart.md`  
**Branch**: `feature-vagas-new` (ou branch ativa alinhada à feature)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/028-vagas-lista-vertical` (via `.specify/feature.json`); template alinhado a `specs/027-vagas-hero-search/tasks.md`  
**Hook**: `custom_configs_update_11045` (último existente: `11044`)

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar View `page_1`, Twigs laranja atuais, ausência de `field_vaga_destaque` e último hook antes de Twig/CSS/`11045`/cex.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/vagas-lista-vertical-render.md`, `contracts/deploy-vagas-lista-vertical.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/028-vagas-lista-vertical/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar baseline de `page_1` (`items_per_page: 12`, `use_ajax: true`, pager `full`, `row_class: col-12 col-md-6 col-lg-4`, sorts herdados `created` DESC, Fields + Custom Text `nothing`) em `config/sync/views.view.vagas.yml`
- [X] T003 [P] Inventariar Twigs atuais de `page_1` (card laranja `.item-vaga--destaque`, wrappers `row g-4` / cols Bootstrap) em `themes/custom/default/templates/views/views-view-field--vagas--page-1--nothing.html.twig`, `themes/custom/default/templates/views/views-view--vagas--page-1.html.twig` e `themes/custom/default/templates/views/views-view-unformatted--vagas--page-1.html.twig`
- [X] T004 [P] Confirmar ausência de `field.storage.node.field_vaga_destaque` / instance no bundle `vagas` e que booleans existentes são só em `user` (não reutilizáveis) sob `config/sync/field.storage.*` e `config/sync/field.field.node.vagas.*`
- [X] T005 [P] Inventariar CSS compartilhado dos cards laranja (`:is(.css-vagas-home, .css-vagas-page) .item-vaga--destaque`) e libraries do tema em `themes/custom/default/assets/css/` e `themes/custom/default/default.libraries.yml` — **não** reutilizar `.item-vaga--destaque` na listagem
- [X] T006 Confirmar último hook `custom_configs_update_11044` → próximo livre **`11045`** em `modules/custom/custom_configs/custom_configs.install`
- [X] T007 [P] Confirmar Hero Search `027` (placement `default_custom_banners_vagas_hero_search`, `highlighted`, `-50`, `/vagas`) e ausência de placements “Recomendado para você” / “Melhore seu currículo” em `config/sync/block.block.default_custom_banners_vagas_hero_search.yml` e `config/sync/block.block.*.yml`

**Checkpoint**: inventário alinhado a plan/research/data-model; hook alvo = `11045`; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar restrições compartilhadas — isolamento Home/`block_3`/similares/Hero 027; classes novas `.item-vaga--lista`; sem Composer/`views_infinite_scroll`; sem sidebar Figma; alvos fixos documentados. Pré-requisito de todas as user stories.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T008 Congelar escopo negativo dos cards laranja: **não** editar `themes/custom/default/templates/views/views-view-field--vagas--block-1--nothing.html.twig`, `themes/custom/default/templates/views/views-view-field--vagas--block-3--nothing.html.twig`, Twigs de `block_2` nem regras regressivas de `.item-vaga--destaque` sob `.css-vagas-home` / PE
- [X] T009 [P] Congelar escopo negativo de Hero/legado: **não** alterar plugin/Twig/CSS/placement do Hero Search `027`; **não** reativar `default_formularioexpostovagaspage_1`; **não** implementar sidebar de filtros Figma nem blocos “Recomendado…” / “Melhore…” — ver `config/sync/block.block.default_custom_banners_vagas_hero_search.yml` e `config/sync/block.block.default_formularioexpostovagaspage_1.yml`
- [X] T010 [P] Confirmar que `views_infinite_scroll` permanece fora do escopo (pager `full` + AJAX basta) e que filtros expostos de `page_1` (`title`, `cidade`, `cursos`, etc.) **não** serão removidos (Hero 027 depende deles) em `composer.json` e `config/sync/views.view.vagas.yml`
- [X] T011 Documentar alvos fixos a implementar (field `field_vaga_destaque`; classes `.item-vaga--lista` / `vaga-lista__*`; library `default/vagas_lista_vertical`; CSS `vagas-lista-vertical.css`; View `page_1`: 5/página, AJAX, sorts destaque+created, `row_class` lista; hook `11045`; helpers ensure field/View/cleanup) em `specs/028-vagas-lista-vertical/data-model.md` / `contracts/vagas-lista-vertical-render.md` — sem criar YAML ainda

**Checkpoint**: restrições claras; pronto para Twig/CSS isolados + field/View/hook sem risco de regressão Home/PE/Hero.

---

## Phase 3: User Story 1 — Visitante vê lista vertical de cards brancos em `/vagas` (Priority: P1) 🎯 MVP

**Goal**: abandonar grid de 3 colunas laranja em `page_1`; lista vertical centralizada de cards brancos (`.item-vaga--lista`) com library/CSS dedicados — sem badge/sort ainda.  
**Independent Test Criteria**: abrir `/vagas` desktop — cards brancos empilhados, container centralizado, ausência do grid laranja de 3 colunas (SC-001 / quickstart B). Aceite visual completo após CSS + wrappers; dados/badge finais nas US seguintes.

- [X] T012 [US1] Reescrever wrapper da View `page_1`: trocar `.view-content.row.g-4` por container de lista (ex. `.view-content.vagas-lista`) com `max-width` ~560–720px centralizado; attach library `default/vagas_lista_vertical` se aplicável via Twig; **manter** `css-vagas-page` em `themes/custom/default/templates/views/views-view--vagas--page-1.html.twig` conforme `specs/028-vagas-lista-vertical/contracts/vagas-lista-vertical-render.md`
- [X] T013 [P] [US1] Remover cols Bootstrap de grid (`col-md-6 col-lg-4`) do unformatted: uma row = bloco full width da lista em `themes/custom/default/templates/views/views-view-unformatted--vagas--page-1.html.twig`
- [X] T014 [US1] Reescrever Custom Text Twig de `page_1` com shell do card vertical branco (raiz `.item-vaga.item-vaga--lista`; BEM `vaga-lista__*`; **nunca** `.item-vaga--destaque` nesta rota; estrutura inner/logo/body/actions) em `themes/custom/default/templates/views/views-view-field--vagas--page-1--nothing.html.twig` conforme contrato de render
- [X] T015 [US1] Registrar library `vagas_lista_vertical` → `assets/css/components/vagas-lista-vertical.css` (deps Bootstrap alinhadas ao tema) em `themes/custom/default/default.libraries.yml`
- [X] T016 [P] [US1] Criar CSS encapsulado (fundo branco; padding; radius; borda/sombra sutil; stack vertical; container lista centralizado; layout horizontal logo+dados+CTA; **somente** sob `.css-vagas-page` / `.item-vaga--lista`; **não** tocar regras laranja de Home/PE) em `themes/custom/default/assets/css/components/vagas-lista-vertical.css`
- [X] T017 [US1] Garantir attach da library só em `page_1` (preprocess View/field ou `attach_library` no wrapper) em `themes/custom/default/default.theme` e/ou `themes/custom/default/templates/views/views-view--vagas--page-1.html.twig`
- [X] T018 [US1] Validar SC-001 / cenários US1 (lista vertical branca; sem grid 3 cols; sem `.item-vaga--destaque` na listagem; container centralizado) conforme `specs/028-vagas-lista-vertical/quickstart.md` seção B (após `drush cr`)

**Checkpoint**: visitante desktop vê lista vertical branca — MVP visual (badge/dados/pager nas fases seguintes).

---

## Phase 4: User Story 2 — Visitante identifica vagas em Destaque (Priority: P1)

**Goal**: campo booleano `field_vaga_destaque`; ordenação destaques primeiro; badge verde `#58A83C` + borda + modifier `.item-vaga--lista--destaque` quando marcado.  
**Independent Test Criteria**: marcar 1 vaga como Destaque → posição, badge e borda em `/vagas` (SC-002 / quickstart C). Requer field (helper) + sort na View (ensure ou admin local) + markup/CSS de badge.

- [X] T019 [US2] Implementar helper `_custom_configs_ensure_field_vaga_destaque()` (storage boolean cardinality 1 default `0` + instance no bundle `vagas` label “Destaque”; reexecução = no-op) em `modules/custom/custom_configs/custom_configs.install`
- [X] T020 [US2] Implementar helper `_custom_configs_ensure_vagas_page_1_lista_vertical()` parcial de sorts: override `defaults.sorts = FALSE`; sorts `field_vaga_destaque` DESC + `created` DESC em `page_1`; **não** alterar `block_1`/`block_2`/`block_3` em `modules/custom/custom_configs/custom_configs.install`
- [X] T021 [US2] Completar Twig do card: se `field_vaga_destaque == 1` → modifier `.item-vaga--lista--destaque` + badge `vaga-lista__badge` (estrela FA + “Destaque”); senão omitir badge/modifier em `themes/custom/default/templates/views/views-view-field--vagas--page-1--nothing.html.twig`
- [X] T022 [P] [US2] Estilizar badge/borda de destaque (verde `#58A83C`; badge absoluto topo-direita; borda verde no card; sem badge em não-destaque) em `themes/custom/default/assets/css/components/vagas-lista-vertical.css`
- [X] T023 [US2] Validar SC-002 / cenários US2 (ordem destaque primeiro; badge+borda; sem badge quando desmarcado; só created quando todos sem destaque) conforme `specs/028-vagas-lista-vertical/quickstart.md` seção C (após field/sort via US6/`updb` ou ensure local + `drush cr`)

**Checkpoint**: destaque visual e ordenação operacionais sobre o conjunto filtrado.

---

## Phase 5: User Story 3 — Visitante lê os dados essenciais no card e age (Priority: P1)

**Goal**: card exibe título, empresa, local, regime, bolsa, carga, tags (até 3 + `+N`), data relativa “Publicada há …”, CTA “Candidatura Rápida” (destaque) / “Ver Detalhes” (padrão) → canonical da vaga; logo via `user_picture`.  
**Independent Test Criteria**: comparar card com node no admin; CTA navega para a vaga (SC-001 parcial / quickstart D).

- [X] T024 [US3] Completar markup de dados no Twig Custom Text (logo `field_empresa_u.entity.user_picture` ~64×64 ou omitir; título; meta empresa+cidade+UF; pill regime; facts bolsa `field_text_simple` + carga `field_horarios`; tags de `field_text_simple_multiple_2` máx. 3 + `+N`, fallback `field_cursos_t`; omitir seções vazias) em `themes/custom/default/templates/views/views-view-field--vagas--page-1--nothing.html.twig` conforme `specs/028-vagas-lista-vertical/contracts/vagas-lista-vertical-render.md`
- [X] T025 [US3] Implementar CTAs: destaque → `vaga-lista__cta--rapida` “Candidatura Rápida” (sólido); padrão → outline “Ver Detalhes”; ambos `path('entity.node.canonical', {node: id})` em `themes/custom/default/templates/views/views-view-field--vagas--page-1--nothing.html.twig`
- [X] T026 [US3] Preprocess: calcular string “Publicada há …” com `date.formatter` → `formatTimeDiffSince($node->getCreatedTime())` e passar variável ao Twig de `page_1` em `themes/custom/default/default.theme`
- [X] T027 [P] [US3] Ajustar CSS de meta/regime/facts/tags/CTA (sólido escuro vs outline; tipografia; gaps; responsivo md+/mobile) sob `.item-vaga--lista` em `themes/custom/default/assets/css/components/vagas-lista-vertical.css`
- [X] T028 [US3] Validar cenários US3 / edges (sem logo/tags/horário/empresa; CTA canônico; rótulos CTA) conforme `specs/028-vagas-lista-vertical/quickstart.md` seção D

**Checkpoint**: card detalhado utilizável e alinhado ao contrato de dados.

---

## Phase 6: User Story 4 — Visitante navega mais resultados via AJAX (Priority: P1)

**Goal**: `page_1` com **5** itens/página, `use_ajax: true`, pager `full` (sem `views_infinite_scroll`); avançar carrega via AJAX.  
**Independent Test Criteria**: ≥6 vagas → ≤5 na 1ª carga; pager AJAX sem full reload síncrono (SC-003 / SC-004 / quickstart E).

- [X] T029 [US4] Completar helper `_custom_configs_ensure_vagas_page_1_lista_vertical()`: `items_per_page: 5`; `use_ajax: true`; pager `full`; `row_class: col-12` (ou vazio); manter `css_class` `css-vagas-page container`; **não** instalar Composer novo em `modules/custom/custom_configs/custom_configs.install`
- [X] T030 [P] [US4] Confirmar que wrappers Twig não reinstalam grid de 3 cols e que o pager permanece renderizado sob a lista em `themes/custom/default/templates/views/views-view--vagas--page-1.html.twig` e `themes/custom/default/templates/views/views-view-unformatted--vagas--page-1.html.twig`
- [X] T031 [US4] Validar SC-003 / SC-004 / cenários US4 (≤5 cards; pager AJAX; filtros Hero `?title=&cidade=&cursos=` ainda aplicam) conforme `specs/028-vagas-lista-vertical/quickstart.md` seção E (após ensure/US6 + `drush cr`)

**Checkpoint**: paginação AJAX com 5/página estável.

---

## Phase 7: User Story 5 — Editor marca vaga como Destaque (Priority: P1)

**Goal**: checkbox “Destaque” no form display `default` do bundle `vagas`; default desmarcado; valor persiste e reflete na listagem.  
**Independent Test Criteria**: editar vaga → marcar/desmarcar → salvar → form e `/vagas` coerentes (SC-005 / quickstart F).

- [X] T032 [US5] Estender `_custom_configs_ensure_field_vaga_destaque()` para garantir widget `boolean_checkbox` no form display `default` de `node.vagas` (componente editável; default off) em `modules/custom/custom_configs/custom_configs.install`
- [X] T033 [US5] Validar SC-005 / cenários US5 (checkbox visível; default desmarcado em nova vaga; persistência marcar/desmarcar; listagem reflete) conforme `specs/028-vagas-lista-vertical/quickstart.md` seção F (após field via US6/`updb` ou ensure local)

**Checkpoint**: editorial consegue operar Destaque sem admin de estrutura.

---

## Phase 8: User Story 6 — Deploy automatizado sem passos manuais (Priority: P1)

**Goal**: `custom_configs_update_11045` idempotente (field + form display + View `page_1` + cleanup blocos exclusos); origem `drush cex`; destino `cim`→`updb`→`cim`→`cr`.  
**Independent Test Criteria**: fluxo de deploy + 2ª `updb` — SC-006–SC-009 / quickstart A+G / contrato deploy.

- [X] T034 [US6] Implementar helper `_custom_configs_cleanup_vagas_out_of_scope_blocks()` (desabilitar/remover placements “Recomendado para você” / “Melhore seu currículo” restritos a `/vagas` se existirem; no-op se ausentes) em `modules/custom/custom_configs/custom_configs.install`
- [X] T035 [US6] Implementar `custom_configs_update_11045` (chamar ensure field+form, ensure View `page_1` lista vertical, cleanup blocos; retornar mensagem Drush; idempotente; **não** alterar hero `027`, `block_1`/`block_2`/`block_3`, formulário exposto) em `modules/custom/custom_configs/custom_configs.install`
- [X] T036 [US6] Exportar configs estruturais com `drush cex -y` — versionar `config/sync/field.storage.node.field_vaga_destaque.yml`, `config/sync/field.field.node.vagas.field_vaga_destaque.yml`, `config/sync/core.entity_form_display.node.vagas.default.yml`, `config/sync/views.view.vagas.yml` (`page_1`: 5, sorts, row_class, ajax); **não** inventar YAML à mão; revisar diff para **não** regredir `block_1`/`block_2`/`block_3` nem Hero 027
- [X] T037 [US6] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (idempotência SC-009) conforme `specs/028-vagas-lista-vertical/quickstart.md` seções A e G e `specs/028-vagas-lista-vertical/contracts/deploy-vagas-lista-vertical.md`
- [X] T038 [US6] Validar gates pós-deploy (field + checkbox; View 5/AJAX/sorts/lista; layout vertical; blocos exclusos ausentes; Hero 027 intacto; SC-007/SC-008) conforme contrato de deploy e quickstart G

**Checkpoint**: destino reproduz field + View + layout sem admin manual; hook reentrante seguro.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Objetivo**: PRD §3.1/§3.6 cirúrgico, specify-rules, isolamento Home/PE/similares/Hero, aceite final SC-001–SC-009.

- [X] T039 [P] Atualizar `PRD.md` §3.1 (CT `vagas` + `field_vaga_destaque`) e §3.6 (View `page_1`: 5/página, lista vertical branca, sort destaque, hook **`11045`**; remover menção a grid 12 + cards laranja **nesta** rota) de forma cirúrgica — manter bullets Home/`block_3`/Hero 027
- [X] T040 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para refletir feature `028-vagas-lista-vertical` com caminhos `spec.md` / `plan.md` / `tasks.md` (já apontados; confirmar consistência)
- [X] T041 [P] Amostrar regressão: `<front>` / `/para-estudantes` / similares — cards laranja `.item-vaga--destaque` intactos; Hero Search em `/vagas` intacto; sem sidebar Figma nova; sem “Recomendado…” / “Melhore…”
- [X] T042 Executar validação final completa (SC-001–SC-009; checklist deploy; edges logo/tags/filtros Hero) com `specs/028-vagas-lista-vertical/quickstart.md` e `specs/028-vagas-lista-vertical/checklists/requirements.md`
- [X] T043 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → US6 (Phase 8) → Polish (Phase 9)
- US2 depende do shell Twig/CSS de US1 (T014/T016) e do helper de field (T019); sort View (T020) fecha ordenação; aceite completo após US6/`updb`
- US3 depende do Twig shell US1 (T014); CTAs/badge coexistêm com US2 (T021)
- US4 completa o helper View iniciado em US2 (T020 → T029); wrappers US1 devem permanecer sem grid
- US5 estende o ensure de field (T019 → T032); aceite editorial após field existir
- US6 (hook + cex + deploy) precisa dos helpers T019/T020/T029/T032/T034; desbloqueia aceite de US2–US5 em destino limpo
- Polish após US1–US6

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) MVP visual
                    ├→ US2 (P1) field + badge + sorts ──┐
                    ├→ US3 (P1) dados + CTA + data ─────┤
                    ├→ US4 (P1) 5/página + AJAX ────────┤
                    ├→ US5 (P1) form checkbox ──────────┤
                    └→ US6 (P1) hook 11045 + cex ───────┴→ Polish
                         ↑
                         └── usa helpers field/View/cleanup (T019/T020/T029/T032/T034)
```

## Parallel Execution Opportunities

- **Setup**: T002, T003, T004, T005, T007 em paralelo após T001; T006 após T001
- **Foundational**: T008, T009, T010 em paralelo; T011 após T008–T010
- **US1**: T012 e T013 em paralelo; T015 em paralelo; T014 após inventário; T016 após T015; T017 após T015; T018 por último
- **US2**: T019 e T020 em paralelo; T021 e T022 em paralelo após T014/T016; T023 após field/sort (ideal pós-US6)
- **US3**: T024/T025/T026 em sequência no Twig/preprocess; T027 paralelo a T026; T028 por último
- **US4**: T029 após T020; T030 paralelo a T029; T031 após ensure/US6
- **US5**: T032 após T019; T033 após field existir
- **US6**: T034 paralelo a finalização dos ensures; T035 após T019+T029+T032+T034 → T036 → T037 → T038
- **Polish**: T039, T040, T041 em paralelo; depois T042–T043

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (wrappers + card shell branco + library/CSS) — valor visual principal
2. **Destaque + dados + pager + form**: US2 (field + badge + sorts) + US3 (dados/CTA) + US4 (5/AJAX) + US5 (checkbox)
3. **Deploy**: US6 (`11045` + cex + `cim`→`updb`→`cim`→`cr`) — desbloqueia aceite em destino
4. **Fechamento**: Polish (PRD §3.1/§3.6 + specify-rules + isolamento + aceite SC-001–SC-009)

## Format Validation

- Todas as tarefas usam `- [X]`, ID sequencial (`T001`…`T043`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias: US1 → US2 → US3 → US4 → US5 → US6 (todas P1; deploy por último para fechar aceite)
