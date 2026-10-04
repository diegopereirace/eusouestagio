# Tasks: Hero Search — Página de Vagas

**Input**: Artefatos de design em `specs/027-vagas-hero-search/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/vagas-hero-search-render.md`, `contracts/deploy-vagas-hero-search.md`, `quickstart.md`  
**Branch**: `feature-vagas-new` (ou branch ativa alinhada à feature)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/027-vagas-hero-search` (via `.specify/feature.json`); template alinhado a `specs/026-cta-final-estudantes/tasks.md`  
**Hook**: `custom_configs_update_11044` (último existente: `11043`)

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar hero home (`custom_banners_hero_search`), View `vagas` `page_1`, placement `highlighted` e último hook antes de plugin/CSS/`11044`/cex.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/vagas-hero-search-render.md`, `contracts/deploy-vagas-hero-search.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/027-vagas-hero-search/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar plugin/theme hook/Twig do hero home (padrão form GET + pills + resolução de termos; **não alterar de forma regressiva**) em `modules/custom/custom_banners/src/Plugin/Block/HeroSearchBlock.php`, `modules/custom/custom_banners/custom_banners.module` e `modules/custom/custom_banners/templates/block--hero-search.html.twig`
- [X] T003 [P] Inventariar library/CSS/JS do hero home (`default/hero_search` → `hero-search.css` / `hero-search.js`) em `themes/custom/default/default.libraries.yml`, `themes/custom/default/assets/css/components/hero-search.css` e `themes/custom/default/assets/js/hero-search.js` — **não mutar nesta feature**
- [X] T004 [P] Inventariar filtros expostos de `page_1` (path `vagas`; presentes: `nid`, `cursos`, `estado`, `cidade`, `escolaridade`, `regime`; **ausente**: `title`) e `exposed_block: true` em `config/sync/views.view.vagas.yml`
- [X] T005 [P] Inventariar placements irmãos: hero home `default_custom_banners_hero_search` (`highlighted`, `-50`, `<front>`) e formulário exposto legado `default_formularioexpostovagaspage_1` (`status: false`) em `config/sync/block.block.default_custom_banners_hero_search.yml` e `config/sync/block.block.default_formularioexpostovagaspage_1.yml`
- [X] T006 Confirmar último hook `custom_configs_update_11043` → próximo livre **`11044`** em `modules/custom/custom_configs/custom_configs.install`
- [X] T007 [P] Confirmar que `.cursor/rules/drupal-deploy-configs.mdc` já cobre FR-024–026 (`hook_update_N` + `cex` + fluxo `cim`→`updb`→`cim`→`cr`); anotar lacuna só se houver gap factual vs. `specs/027-vagas-hero-search/spec.md`

**Checkpoint**: inventário alinhado a plan/research/data-model; hook alvo = `11044`; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar restrições compartilhadas — plugin/CSS/placement **distintos** do hero home; **não** reativar formulário exposto; **não** redesenhar cards/contador/filtros laterais; alvos fixos documentados. Pré-requisito de todas as user stories.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T008 Congelar escopo negativo do hero home: **não** editar `modules/custom/custom_banners/src/Plugin/Block/HeroSearchBlock.php`, `modules/custom/custom_banners/templates/block--hero-search.html.twig`, `themes/custom/default/assets/css/components/hero-search.css`, `themes/custom/default/assets/js/hero-search.js` nem `config/sync/block.block.default_custom_banners_hero_search.yml` de forma regressiva
- [X] T009 [P] Congelar escopo negativo de listagem/legado: **não** reativar `default_formularioexpostovagaspage_1`; **não** redesenhar cards/contador/filtros laterais; **não** alterar displays `block_1`/`block_2`/`block_3` da View salvo diff involuntário de `cex` (revisar) — ver `config/sync/block.block.default_formularioexpostovagaspage_1.yml` e `config/sync/views.view.vagas.yml`
- [X] T010 [P] Confirmar região correta `highlighted` (acima do conteúdo da View page) e que `default_page_title` não cobre `/vagas` (hero pode usar `h1`) conforme `config/sync/block.block.default_page_title.yml` e assumptions em `specs/027-vagas-hero-search/spec.md`
- [X] T011 Documentar alvos fixos a implementar (plugin `custom_banners_vagas_hero_search`; theme hook `custom_banners_vagas_hero_search`; placement id `default_custom_banners_vagas_hero_search`; UUID `a1b2c3d4-e5f6-4789-a012-bcdef0123456`; região `highlighted`; weight `-50`; pages `/vagas`; library `default/vagas_hero_search`; classe `.vagas-hero-search`; filtro View identifier `title`; hook `11044`) em `specs/027-vagas-hero-search/data-model.md` / `contracts/vagas-hero-search-render.md` — sem criar YAML ainda

**Checkpoint**: restrições claras; pronto para plugin/Twig/CSS isolados + hook sem risco de regressão na home.

---

## Phase 3: User Story 1 — Visitante vê o Hero Search no topo de /vagas (Priority: P1) 🎯 MVP

**Goal**: bloco hero em `/vagas` com título/subtítulo fixos, formulário de três campos + “Buscar Vagas”, pills de curso (sem “Ver todas”), markup/CSS alinhados ao Figma (wrapper 1280px, paddings 48/32/40, pílula, botão `#58A83C`) — plugin/Twig/library distintos do home.  
**Independent Test Criteria**: abrir `/vagas` ≥768px com bloco posicionado e confirmar hero acima da listagem, copy correta e ausência de “Ver todas” (SC-001 / quickstart B). Aceite visual completo após placement US5.

- [X] T012 [US1] Registrar theme hook `custom_banners_vagas_hero_search` (variáveis `action`, `values`, `pills`; template `block--vagas-hero-search`) em `modules/custom/custom_banners/custom_banners.module`
- [X] T013 [P] [US1] Criar Block Plugin `VagasHeroSearchBlock` (id `custom_banners_vagas_hero_search`; admin label “Hero: busca de vagas (/vagas)”; `build()` com `#theme`, `Url::fromRoute('view.vagas.page_1')`, attach **só** `default/vagas_hero_search`, cache contexts `url.path` + `url.query_args`, tags `taxonomy_term_list:curso`; **não** reutilizar/condicionar `HeroSearchBlock`) em `modules/custom/custom_banners/src/Plugin/Block/VagasHeroSearchBlock.php`
- [X] T014 [US1] Implementar Twig dedicado (classe raiz `.vagas-hero-search` / `#vagas-hero-search`; `h1` título + subtítulo fixos pt-BR; form `method="get"` + três fields + botão “Buscar Vagas”; lista de pills; **proibido** “Ver todas”; **nunca** classes `.hero-search*`) em `modules/custom/custom_banners/templates/block--vagas-hero-search.html.twig` conforme `specs/027-vagas-hero-search/contracts/vagas-hero-search-render.md`
- [X] T015 [US1] Registrar library `vagas_hero_search` → `assets/css/components/vagas-hero-search.css` (deps Bootstrap alinhadas ao tema; **sem** JS obrigatório v1) em `themes/custom/default/default.libraries.yml`
- [X] T016 [P] [US1] Criar CSS encapsulado (fundo branco; wrapper `max-width: 1280px`; padding `48/32/40`; título ~1062px Poppins bold; subtítulo ~715px `#45464D`; barra pílula + sombra; inputs sem chrome Bootstrap; ícones; botão `#58A83C` ~56px; pills outline azul claro flex-wrap; **somente** sob `.vagas-hero-search`; **não** tocar `hero-search.css`) em `themes/custom/default/assets/css/components/vagas-hero-search.css`
- [X] T017 [US1] Validar SC-001 / cenários US1 (hero acima da listagem; copy; 3 campos; pills; sem “Ver todas”; tokens Figma) conforme `specs/027-vagas-hero-search/quickstart.md` seção B (após placement US5 + `drush cr`)

**Checkpoint**: visitante desktop vê o hero — MVP de produto (aceite completo pós-US5).

---

## Phase 4: User Story 2 — Visitante busca por cargo, cidade e curso (Priority: P1)

**Goal**: form GET envia `title`, `cidade`, `cursos`; View `page_1` passa a filtrar por título (contains); pré-preenchimento a partir da query; combinação AND; submit vazio utilizável.  
**Independent Test Criteria**: submeter form com valores conhecidos — query string + listagem filtrada (SC-003 / SC-004 / quickstart C). Requer filtro `title` (helper + US5) + hero renderizável (US1).

- [X] T018 [US2] Completar `build()` do plugin: ler query args `title`/`cidade`/`cursos` → `#values`; placeholders FR-007; **sem** autocomplete obrigatório no campo curso (v1) em `modules/custom/custom_banners/src/Plugin/Block/VagasHeroSearchBlock.php`
- [X] T019 [US2] Confirmar markup dos inputs (`name="title"|"cidade"|"cursos"`; `value` de `values`; labels visually-hidden; `action` = `/vagas`) em `modules/custom/custom_banners/templates/block--vagas-hero-search.html.twig` contra `specs/027-vagas-hero-search/contracts/vagas-hero-search-render.md`
- [X] T020 [US2] Implementar helper `_custom_configs_ensure_vagas_page_1_title_filter()` (adicionar filtro string `contains` em `node_field_data.title`, identifier `title`, exposed, required false, se ausente; **não** remover/alterar outros filtros de `page_1`) em `modules/custom/custom_configs/custom_configs.install`
- [X] T021 [US2] Validar SC-003 / SC-004 / cenários US2 (title / cidade / cursos / AND / submit vazio) conforme `specs/027-vagas-hero-search/quickstart.md` seção C (após filtro via US5/`updb` ou ensure local + `drush cr`)

**Checkpoint**: busca filtrada via GET mapeada aos filtros da View.

---

## Phase 5: User Story 3 — Visitante usa pill de curso (Priority: P1)

**Goal**: pills Tecnologia, Marketing, Administração, Engenharia, Saúde, Design, Direito → links `/vagas?cursos={nome_termo}`; resolução runtime (exato → prefixo); omitir termo ausente; sem “Ver todas”.  
**Independent Test Criteria**: clicar em cada pill visível — filtro `cursos` aplicado (SC-005 / quickstart D).

- [X] T022 [US3] Implementar resolução de pills no plugin (lista canônica só `curso`; match exato + prefixo case-insensitive no espírito de `HeroSearchBlock`; enviar **nome** do termo; omitir se ausente; **sem** TID hardcoded; **sem** criar termos) em `modules/custom/custom_banners/src/Plugin/Block/VagasHeroSearchBlock.php`
- [X] T023 [P] [US3] Confirmar markup das pills (`ul.vagas-hero-search__pills`; links `vagas-hero-search__pill`; omitir `<ul>` se lista vazia; **zero** “Ver todas”) em `modules/custom/custom_banners/templates/block--vagas-hero-search.html.twig`
- [X] T024 [US3] Validar SC-005 / cenários US3 (clique aplica `cursos`; pill sem termo omitida; sem “Ver todas”) conforme `specs/027-vagas-hero-search/quickstart.md` seção D

**Checkpoint**: atalhos de curso funcionais e seguros.

---

## Phase 6: User Story 4 — Visitante mobile usa a barra empilhada (Priority: P2)

**Goal**: viewport ≤575.98px — três inputs + botão empilham; pills com wrap; sem scroll horizontal do hero; desktop md+ em uma linha com divisórias.  
**Independent Test Criteria**: `/vagas` ≤575.98px e ≥768px — SC-002 / quickstart E.

- [X] T025 [US4] Implementar/ajustar CSS responsivo (stack mobile; row desktop com divisórias; pills wrap; sem overflow-x; tipografia legível; paddings preservados) sob `.vagas-hero-search` em `themes/custom/default/assets/css/components/vagas-hero-search.css`
- [X] T026 [P] [US4] Confirmar estrutura de fields/dividers no Twig compatível com stack/row (`vagas-hero-search__fields` / `__divider` / `__submit-wrap`) em `modules/custom/custom_banners/templates/block--vagas-hero-search.html.twig`
- [X] T027 [US4] Validar SC-002 / cenários US4 (empilhamento mobile; linha desktop; sem scroll horizontal) conforme `specs/027-vagas-hero-search/quickstart.md` seção E

**Checkpoint**: barra utilizável em mobile sem regressão desktop.

---

## Phase 7: User Story 5 — Deploy automatizado do placement (Priority: P1)

**Goal**: `custom_configs_update_11044` idempotente — ensure filtro `title` em `page_1` + placement `default_custom_banners_vagas_hero_search` (`highlighted`, weight `-50`, `/vagas`, UUID `a1b2c3d4-…`); origem `drush cex`; destino `cim`→`updb`→`cim`→`cr`; hero ausente fora de `/vagas`.  
**Independent Test Criteria**: fluxo de deploy + 2ª `updb` — SC-006 / SC-007 / SC-008 / SC-009 / quickstart A+F / contrato deploy.

- [X] T028 [US5] Implementar helper `_custom_configs_ensure_vagas_hero_search_placement()` (id `default_custom_banners_vagas_hero_search`; plugin `custom_banners_vagas_hero_search`; tema `default`; região `highlighted`; weight **`-50`**; `label_display: '0'`; visibility `request_path` = `/vagas`; UUID config `a1b2c3d4-e5f6-4789-a012-bcdef0123456`; criar/atualizar sem duplicar; **não** tocar hero home nem reativar formulário exposto) em `modules/custom/custom_configs/custom_configs.install`
- [X] T029 [US5] Implementar `custom_configs_update_11044` (chamar `_custom_configs_ensure_vagas_page_1_title_filter()` + `_custom_configs_ensure_vagas_hero_search_placement()`; retornar mensagem Drush; idempotente; **não** alterar `block_1`/`block_3`, hero home, formulário exposto) em `modules/custom/custom_configs/custom_configs.install`
- [X] T030 [US5] Exportar configs estruturais com `drush cex -y` — versionar `config/sync/views.view.vagas.yml` (+ filtro `title` em `page_1`) e `config/sync/block.block.default_custom_banners_vagas_hero_search.yml`; **não** inventar YAML à mão; revisar diff para não regredir displays/placements irmãos
- [X] T031 [US5] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (idempotência SC-008) conforme `specs/027-vagas-hero-search/quickstart.md` seções A e F e `specs/027-vagas-hero-search/contracts/deploy-vagas-hero-search.md`
- [X] T032 [US5] Validar gates pós-deploy (hero primeiro em `/vagas`; filtro `title` presente; ausente em `<front>` e outras rotas; formulário exposto permanece desativado; textos 100% em código SC-009; SC-006/SC-007) conforme contrato de deploy e quickstart F

**Checkpoint**: destino reproduz hero + filtro sem admin manual; hook reentrante seguro.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: PRD §3.6 cirúrgico, specify-rules, isolamento home/listagem, aceite final SC-001–SC-009.

- [X] T033 [P] Atualizar §3.6 documentando plugin `custom_banners_vagas_hero_search`, filtro `title` em `page_1`, placement `default_custom_banners_vagas_hero_search` (`highlighted`, `-50`, `/vagas`), hook **`11044`** e library `vagas_hero_search` em `PRD.md`
- [X] T034 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para refletir feature `027-vagas-hero-search` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T035 [P] Amostrar regressão: hero home em `<front>` (`.hero-search` intacto; sem `.vagas-hero-search`); cards/filtros laterais de `/vagas`; formulário exposto desativado — sem regressão causada por esta feature
- [X] T036 Executar validação final completa (SC-001–SC-009; checklist deploy; edges de query/pills) com `specs/027-vagas-hero-search/quickstart.md` e `specs/027-vagas-hero-search/checklists/requirements.md`
- [X] T037 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → Polish (Phase 8)
- US2 depende do form/Twig base de US1 (T014) e do helper de filtro (T020); aceite completo após US5/`updb`
- US3 depende do plugin US1 (T013) para hospedar `resolvePills()`; Twig de pills (T014/T023)
- US4 depende do CSS/markup US1 (T014/T016)
- US5 (placement + hook + cex) precisa de plugin registrado (US1) e helper de filtro (T020); desbloqueia aceite visual de US1–US4
- Polish após US1–US5

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) MVP
                    ├→ US2 (P1) ──┐
                    ├→ US3 (P1) ──┤
                    ├→ US4 (P2) ──┤
                    └→ US5 (P1) ──┴→ Polish
                         ↑
                         └── usa helper filtro (T020) + plugin (T013)
```

## Parallel Execution Opportunities

- **Setup**: T002, T003, T004, T005, T007 em paralelo após T001; T006 após T001
- **Foundational**: T008, T009, T010 em paralelo após inventário; T011 após T008–T010
- **US1**: T012 → T013 e T015 em paralelo; T014 após T012; T016 em paralelo com T014 após T015; T017 por último (ideal pós-US5)
- **US2**: T018 e T019 em paralelo após T014; T020 paralelo a T018/T019; T021 após US5
- **US3**: T022 e T023 em paralelo após T013/T014; T024 após deploy/placement
- **US4**: T025 e T026 em paralelo após T016/T014; T027 por último
- **US5**: T028 após T013; T029 após T020+T028 → T030 → T031 → T032
- **Polish**: T033, T034, T035 em paralelo; depois T036–T037

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (plugin + Twig + library/CSS isolados) — valor visual principal
2. **Busca + pills + mobile**: US2 (params + ensure filtro) + US3 + US4
3. **Deploy**: US5 (`11044` + cex + `cim`→`updb`→`cim`→`cr`) — desbloqueia aceite em `/vagas`
4. **Fechamento**: Polish (PRD §3.6 + specify-rules + isolamento home + aceite SC-001–SC-009)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T037`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias: US1 → US2 → US3 → US4 (P2) → US5 (P1 deploy; após base de código para aceite)
