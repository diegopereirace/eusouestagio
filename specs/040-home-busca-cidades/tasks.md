# Tasks: Busca da Home — Autocomplete de Cidades

**Input**: Artefatos de design em `specs/040-home-busca-cidades/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/home-search-cidade.md`, `contracts/cidades-autocomplete.md`, `contracts/deploy-home-busca-cidades.md`, `quickstart.md`  
**Branch sugerida**: `feat/home-busca-autocomplete-cidades`  
**Predecessor**: `039-seed-vagas-validacao` (seed de vagas com cidades/UFs para QA)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md` (sem PHPUnit)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/040-home-busca-cidades` (via `.specify/feature.json`); template alinhado a `specs/035-refatorar-login/tasks.md` / `specs/027-vagas-hero-search/tasks.md`  
**Config estrutural**: nenhuma — sem `hook_update_N`, sem `drush cex`/`cim`/`updb` obrigatórios; publicar com `git pull` + `drush cr`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar hero home (`custom_banners_hero_search`), autocomplete de cursos, filtros View `cidade`/`estado`, CSS/JS `hero_search` e copy residual do hero `/vagas` antes de mutar código.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/home-search-cidade.md`, `contracts/cidades-autocomplete.md`, `contracts/deploy-home-busca-cidades.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/040-home-busca-cidades/` e feature ativa `040-home-busca-cidades` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar `HeroSearchBlock` (`PILLS` com Remoto, `loadRegimeOptions()`, `#regime_options`, attach `default/hero_search`) em `modules/custom/custom_banners/src/Plugin/Block/HeroSearchBlock.php`
- [X] T003 [P] Inventariar Twig home (select `name="regime"`, label “Cidade ou Remoto”, campo cursos com `data-autocomplete-path`) e theme hook (`regime_options`) em `modules/custom/custom_banners/templates/block--hero-search.html.twig` e `modules/custom/custom_banners/custom_banners.module`
- [X] T004 [P] Inventariar padrão de endpoint cursos (`CursosAutocompleteController`, rota `/api/cursos/autocomplete`, LIMIT 10, max-age 300) em `modules/custom/custom_banners/src/Controller/CursosAutocompleteController.php` e `modules/custom/custom_banners/custom_banners.routing.yml`
- [X] T005 [P] Inventariar library/CSS/JS `default/hero_search` e behavior atual (`input[data-autocomplete-path]`) em `themes/custom/default/default.libraries.yml`, `themes/custom/default/assets/css/components/hero-search.css` e `themes/custom/default/assets/js/hero-search.js`
- [X] T006 [P] Inventariar copy residual “Cidade ou Remoto” no hero `/vagas` e confirmar filtros expostos `cidade`/`estado` da View (sem alterar schema) em `modules/custom/custom_banners/templates/block--vagas-hero-search.html.twig` e `config/sync/views.view.vagas.yml`
- [X] T007 Confirmar escopo negativo de deploy: zero `hook_update_N` novo (último livre permanece pós-`11061`), zero YAML novo em `config/sync/`, sem apagar termo Remoto da taxonomia — anotar em `specs/040-home-busca-cidades/contracts/deploy-home-busca-cidades.md` se houver gap factual

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar restrições compartilhadas — não redesenhar hero `/vagas` (027); não alterar View/taxonomia; não tocar `core/`/`vendor/`; alvos fixos do endpoint e do form documentados. Pré-requisito de todas as user stories.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T008 Congelar escopo negativo: **não** redesenhar CSS/layout de `themes/custom/default/assets/css/components/vagas-hero-search.css` nem plugin `VagasHeroSearchBlock`; **não** alterar filtros/schema de `config/sync/views.view.vagas.yml`; **não** apagar termo Remoto em taxonomia `regime`; **não** criar `hook_update_N` em `modules/custom/custom_configs/custom_configs.install`; **não** editar `core/` nem `vendor/`
- [X] T009 [P] Documentar alvos fixos a implementar (rota `GET /api/cidades/autocomplete`; controller `CidadesAutocompleteController`; form Home `name="cidade"` + hidden `name="estado"`; label/placeholder **“Cidade”**; label JSON `{Cidade} - {UF} - ({N})`; pills sem Remoto; deploy = `drush cr`) em `specs/040-home-busca-cidades/data-model.md` / contratos — sem criar código ainda
- [X] T010 [P] Confirmar que filtros View `cidade` (`field_cidade` CONTAINS) e `estado` (`field_estados`) já existem em `page_1` e bastam para o contrato GET de `specs/040-home-busca-cidades/contracts/home-search-cidade.md` (sem cex)

**Checkpoint**: restrições claras; pronto para controller/bloco/assets sem risco de config/taxonomia.

---

## Phase 3: User Story 1 — Visitante vê a barra de busca alinhada ao Figma (Priority: P1)

**Goal**: caixa global do hero home aproxima ~896×86 no desktop; campos curso/cidade e botão Buscar proporcionados; mobile usável sem scroll horizontal; assets só via library `default/hero_search`.  
**Independent Test Criteria**: Home ≥992px e ≤575.98px — comparar com Figma; sem overflow-x da barra (SC-001 / quickstart B). Aceite visual pleno após campo cidade existir (US2 Twig).

- [X] T011 [US1] Ajustar CSS da barra home (proporção alvo desktop ~896×86; flex dos fields/botão; stack mobile sem overflow horizontal; seletores sob `.hero-search`) em `themes/custom/default/assets/css/components/hero-search.css` conforme FR-001 e `specs/040-home-busca-cidades/plan.md` (R7)
- [X] T012 [P] [US1] Bump de versão/cache-bust da library `hero_search` (se o tema versionar assets) em `themes/custom/default/default.libraries.yml` — **não** criar library nova; **não** anexar em `global-styling`
- [X] T013 [US1] Validar SC-001 / cenários US1 (desktop ~896×86; mobile usável; assets via library + `drush cr`) conforme `specs/040-home-busca-cidades/quickstart.md` seção B (ideal após Twig US2)

**Checkpoint**: layout home alinhado ao Figma — incremento visual independente do match accent-insensitive.

---

## Phase 4: User Story 2 — Visitante busca por cidade via autocomplete (Priority: P1) 🎯 MVP

**Goal**: slot Home deixa de ser select `regime` e passa a input **Cidade** com autocomplete (`/api/cidades/autocomplete`); sugestões só com vagas publicadas; match sem acento/case; label `Cidade - UF - (N)`; submit → `cidade` (+ `estado` hidden na seleção); JS limpa `estado` se o texto mudar.  
**Independent Test Criteria**: digitar `sao paulo` / `FORTALEZA`; selecionar → Buscar → listagem filtrada; cidade sem vagas ausente (SC-002 / SC-003 / SC-005 / quickstart C).

- [X] T014 [US2] Criar `CidadesAutocompleteController` (agregar `node` `type=vagas` + `status=1` + `field_cidade` não vazio; grupo cidade+UF; filtrar `q` normalizado accent/case-insensitive em PHP; `q` &lt; 2 → `[]`; máx. 10; JSON `{value,label,estado}`; cache público max-age 60–300; try/catch → `[]`) em `modules/custom/custom_banners/src/Controller/CidadesAutocompleteController.php` conforme `specs/040-home-busca-cidades/contracts/cidades-autocomplete.md`
- [X] T015 [P] [US2] Registrar rota `GET /api/cidades/autocomplete` (`_controller` → `CidadesAutocompleteController::autocomplete`; `_permission: access content`) em `modules/custom/custom_banners/custom_banners.routing.yml`
- [X] T016 [US2] Alterar `HeroSearchBlock::build()`: remover `loadRegimeOptions()` / `#regime_options` / cache tag `taxonomy_term_list:regime` ligados ao select; manter attach `default/hero_search` e pills/cursos; **não** enviar `regime` no form) em `modules/custom/custom_banners/src/Plugin/Block/HeroSearchBlock.php`
- [X] T017 [US2] Atualizar theme hook `custom_banners_hero_search` (remover variável `regime_options` se não for mais usada) em `modules/custom/custom_banners/custom_banners.module`
- [X] T018 [US2] Substituir select de regime por input `name="cidade"` (label/placeholder **“Cidade”**; `data-autocomplete-path="/api/cidades/autocomplete"`) + hidden `name="estado"` em `modules/custom/custom_banners/templates/block--hero-search.html.twig` conforme `specs/040-home-busca-cidades/contracts/home-search-cidade.md`
- [X] T019 [US2] Estender `hero-search.js`: no `select` do autocomplete, se o item tiver `estado`, preencher o hidden `estado` do form; ao `input`/`change` do campo cidade sem nova seleção, limpar `estado`; preservar comportamento do autocomplete de cursos em `themes/custom/default/assets/js/hero-search.js`
- [X] T020 [US2] Validar SC-002 / SC-003 / SC-005 e quickstart C (endpoint `GET /api/cidades/autocomplete?q=sao`; formato label; N ≥ 1; submit com `cidade`/`estado`; degradação texto livre sem JS) em `specs/040-home-busca-cidades/quickstart.md`

**Checkpoint**: visitante descobre vagas por cidade — MVP de produto da feature.

---

## Phase 5: User Story 3 — Visitante não vê mais a tag “Remoto” (Priority: P1)

**Goal**: pills sob a busca mostram só cursos (TI, Administração, Design, Marketing); Remoto some só da lista hardcoded; taxonomia `regime` intacta.  
**Independent Test Criteria**: Home sem pill “Remoto”; pills de curso presentes quando os termos existem (SC-004 parcial / quickstart D).

- [X] T021 [US3] Remover entrada `'Remoto' => …` de `HeroSearchBlock::PILLS` (manter só cursos `vid=curso`; resolução runtime inalterada; omitir termo ausente) em `modules/custom/custom_banners/src/Plugin/Block/HeroSearchBlock.php` conforme FR-010–FR-012 / research R6
- [X] T022 [P] [US3] Confirmar markup das pills no Twig home (lista só a partir de `pills`; sem hardcode “Remoto”) em `modules/custom/custom_banners/templates/block--hero-search.html.twig`
- [X] T023 [US3] Validar SC-004 / cenários US3 (ausência de Remoto; pills de curso; clique → `/vagas?cursos=…`) conforme `specs/040-home-busca-cidades/quickstart.md` seção D

**Checkpoint**: UI de atalhos só com cursos; regime permanece no sistema/listagem.

---

## Phase 6: User Story 4 — Deploy 100% automatizado (Priority: P1)

**Goal**: destino ativa layout, autocomplete e ausência da pill só com Git + `drush cr` (esteira `cim`→`updb`→`cim`→`cr` aceitável como no-op); zero admin; CSS/JS via libraries; rota via módulo.  
**Independent Test Criteria**: ambiente “frio” só com comandos versionados — repetir B–D (SC-004 / FR-013–FR-015 / quickstart A+E / contrato deploy).

- [X] T024 [US4] Confirmar que CSS/JS estão só em `themes/custom/default/assets/**` + `default.libraries.yml` e que rota/form/pills vivem em `modules/custom/custom_banners/**` (sem CSS/JS no admin; sem YAML inventado em `config/sync/`) contra `specs/040-home-busca-cidades/contracts/deploy-home-busca-cidades.md`
- [X] T025 [US4] Executar limpeza de cache local (`docker compose exec -T drupal vendor/bin/drush cr`) e gates pós-deploy (rota 200; assets `hero_search`; Home sem Remoto no label/pill) conforme `specs/040-home-busca-cidades/quickstart.md` seções A e E
- [X] T026 [US4] Validar isolamento: filtro `regime` na listagem/sidebar permanece; autocomplete de cursos da Home intacto; hero `/vagas` sem redesign de layout — só copy se já alinhada no Polish (`specs/040-home-busca-cidades/quickstart.md` seção F)

**Checkpoint**: feature reproduzível sem cliques no painel.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Objetivo**: copy residual `/vagas`, PRD cirúrgico, specify-rules, aceite final SC-001–SC-005.

- [X] T027 [P] Alinhar label/placeholder “Cidade ou Remoto” → **“Cidade”** (somente texto; sem CSS/layout) em `modules/custom/custom_banners/templates/block--vagas-hero-search.html.twig` conforme research R9
- [X] T028 [P] Atualizar `PRD.md` de forma cirúrgica (§3.4 / §3.6 / rotas §5): hero home envia `cidade`/`estado` (não `regime`); pills sem Remoto; endpoint `/api/cidades/autocomplete`
- [X] T029 [P] Atualizar bloco SPECKIT em `.cursor/rules/specify-rules.mdc` para feature `040-home-busca-cidades` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T030 Executar validação final completa (SC-001–SC-005; edges do spec; checklist requirements) com `specs/040-home-busca-cidades/quickstart.md` e `specs/040-home-busca-cidades/checklists/requirements.md`
- [X] T031 Limpar cache Drupal após Twig/CSS/JS/rota (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4, MVP) → US3 (Phase 5) → US4 (Phase 6) → Polish (Phase 7)
- US1 (CSS) pode avançar em paralelo ao início de US2 (controller/rota) após Phase 2; aceite visual pleno após Twig do input cidade (T018)
- US2: T014 + T015 antes ou em paralelo; T016–T018 após decidir contrato do form; T019 após T018 (hidden `estado`); T020 por último
- US3 edita o mesmo `HeroSearchBlock.php` que T016 — executar T021 **depois** ou na mesma alteração sequencial de T016 (não paralelizar no mesmo arquivo)
- US4 valida o conjunto US1–US3; não adiciona config/hook
- Polish (copy `/vagas` + PRD) após código US2/US3 estável
- Nenhuma história exige `config/sync` nem `hook_update_N`

## Dependency Graph

```text
Phase1 → Phase2 (escopo negativo + alvos fixos)
           ├→ US1 (P1) CSS Figma ~896×86 ──────────────┐
           └→ US2 (P1) MVP endpoint + form + JS ───────┤
                └→ US3 (P1) remover pill Remoto ───────┤
                     └→ US4 (P1) gates Git + drush cr ─┴→ Polish (copy /vagas + PRD)
```

## Parallel Execution Opportunities

- **Setup**: T002–T006 em paralelo após T001; T007 após inventário
- **Foundational**: T008 → T009 e T010 em paralelo
- **US1**: T011 e T012 em paralelo → T013 (após T018 ideal)
- **US2**: T014 e T015 em paralelo; T017 paralelo a T016; T018 após T016/T017; T019 após T018; T020 por último
- **US3**: T021 sequencial ao T016 (mesmo PHP); T022 paralelo a validação visual; T023 após T021
- **US4**: T024 → T025 → T026
- **Polish**: T027, T028, T029 em paralelo; depois T030 → T031

## Implementation Strategy

1. **MVP**: Phase 1–2 + US2 (controller + rota + form cidade/estado + JS) — valor de negócio principal
2. **UI limpa**: US3 (sem pill Remoto) + US1 (CSS Figma) — podem fechar no mesmo PR após o form existir
3. **Deploy**: US4 — `drush cr` + gates; zero admin / zero hook
4. **Fechamento**: Polish — copy `/vagas`, PRD §3.4/§3.6/§5, specify-rules, SC-001–SC-005

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T031`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; validação manual no quickstart)
- Ordem de histórias: US1 (P1) → US2 (P1 MVP) → US3 (P1) → US4 (P1)
