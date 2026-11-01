# Tasks: Home — Busca Inteligente (curso × cidade)

**Input**: Artefatos de design em `specs/041-home-busca-inteligente/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/cursos-autocomplete.md`, `contracts/cidades-autocomplete.md`, `contracts/home-search-crossfilter.md`, `contracts/deploy-home-busca-inteligente.md`, `quickstart.md`  
**Branch sugerida**: `feat/home-busca-inteligente`  
**Predecessor**: `040-home-busca-cidades` (form Home + endpoints + library `hero_search`); QA com seeds `039-seed-vagas-validacao`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md` (sem PHPUnit)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/041-home-busca-inteligente` (via `.specify/feature.json`); template alinhado a `specs/040-home-busca-cidades/tasks.md`  
**Config estrutural**: nenhuma — sem `hook_update_N`, sem `drush cex`/`cim`/`updb` obrigatórios; publicar com `git pull` + `drush cr`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar hero Home (`custom_banners_hero_search`), endpoints 040, índice/cache e assets `hero_search` antes de mutar código.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/cursos-autocomplete.md`, `contracts/cidades-autocomplete.md`, `contracts/home-search-crossfilter.md`, `contracts/deploy-home-busca-inteligente.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/041-home-busca-inteligente/` e feature ativa `041-home-busca-inteligente` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar `CursosAutocompleteController` (params atuais, LIMIT 10, cache tags/contexts) em `modules/custom/custom_banners/src/Controller/CursosAutocompleteController.php`
- [X] T003 [P] Inventariar `CidadesAutocompleteController` e rota `/api/cidades/autocomplete` em `modules/custom/custom_banners/src/Controller/CidadesAutocompleteController.php` e `modules/custom/custom_banners/custom_banners.routing.yml`
- [X] T004 [P] Inventariar behavior `hero-search.js` (autocomplete cursos/cidade, hidden `estado`, delay) em `themes/custom/default/assets/js/hero-search.js`
- [X] T005 [P] Inventariar Twig Home (inputs `cursos`/`cidade`/`estado`, submit GET `/vagas`) e CSS/library em `modules/custom/custom_banners/templates/block--hero-search.html.twig`, `themes/custom/default/assets/css/components/hero-search.css` e `themes/custom/default/default.libraries.yml`
- [X] T006 Confirmar escopo negativo de deploy: zero `hook_update_N` novo, zero YAML novo em `config/sync/`, hero `/vagas` intacto — anotar gaps em `specs/041-home-busca-inteligente/contracts/deploy-home-busca-inteligente.md` se houver

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar restrições compartilhadas e entregar o índice cacheável `vaga × cidade × UF × curso` usado por ambas as user stories de cruzamento. Pré-requisito de todas as user stories de implementação.

**⚠️ CRITICAL**: Nenhuma user story de código começa antes desta fase.

- [X] T007 Congelar escopo negativo: **não** redesenhar CSS/layout do hero `/vagas` (`vagas-hero-search.css` / `VagasHeroSearchBlock`); **não** alterar filtros/schema de `config/sync/views.view.vagas.yml`; **não** criar `hook_update_N` em `modules/custom/custom_configs/custom_configs.install`; **não** editar `core/` nem `vendor/`; **não** tornar os campos curso/cidade obrigatórios em conjunto
- [X] T008 [P] Documentar alvos fixos (params `cidade`/`estado` em cursos; param `curso` em cidades; `q` vazio só com constraint; debounce probe 300ms; delay AC 80ms; hints copy; deploy = `drush cr`) em `specs/041-home-busca-inteligente/data-model.md` / contracts — sem criar código ainda
- [X] T009 Criar `VagaLocationCourseIndex` (triples agregados cidade/UF/curso/count + norms; cache.data 1h; tags `node_list:vagas` + `taxonomy_term_list:curso`; só vagas `status=1`) em `modules/custom/custom_banners/src/Controller/VagaLocationCourseIndex.php` conforme `specs/041-home-busca-inteligente/research.md` (R2) e `data-model.md`

**Checkpoint**: índice pronto; restrições claras; pronto para estender controllers/JS sem risco de config.

---

## Phase 3: User Story 1 — Visitante busca só por curso ou só por cidade (Priority: P1)

**Goal**: preservar submissão GET com apenas curso, apenas cidade, ambos ou nenhum; cruzamento não pode tornar campos obrigatórios juntos (FR-001 / SC-001).  
**Independent Test Criteria**: só curso → `/vagas?cursos=…`; só cidade → `/vagas?cidade=…` (+ `estado` se selecionado); ambos vazios → listagem padrão (`quickstart.md` seção B).

- [X] T010 [US1] Confirmar que o form Home mantém `cursos`, `cidade` e `estado` todos opcionais (sem `required` / sem JS bloqueando submit) em `modules/custom/custom_banners/templates/block--hero-search.html.twig`
- [X] T011 [US1] Garantir que o botão Buscar permanece sempre ativo (mesmo com campo oposto `disabled` por cruzamento) em `themes/custom/default/assets/js/hero-search.js` e markup Twig
- [X] T012 [US1] Validar SC-001 / quickstart B (só curso; só cidade; ambos; nenhum) após JS/endpoints estáveis — `specs/041-home-busca-inteligente/quickstart.md` seção B

**Checkpoint**: regressão de busca unária/vazia coberta — base segura para cruzamento.

---

## Phase 4: User Story 2 — Cruzamento restringe sugestões (Priority: P1) 🎯 MVP

**Goal**: autocomplete de curso restringido por cidade(+UF); autocomplete de cidade restringido por curso; `(N)` filtrado; match accent/case-insensitive; `q` vazio permitido só com constraint (probe) (FR-002/003/006 / SC-002).  
**Independent Test Criteria**: selecionar cidade seedada → cursos só da localização; selecionar curso → cidades só daquele curso com N filtrado; endpoints com params (`quickstart.md` seção C).

- [X] T013 [US2] Estender `CursosAutocompleteController`: ler `cidade`/`estado`; sem constraint listar só cursos com ≥1 vaga publicada; com constraint filtrar via `VagaLocationCourseIndex`; permitir `q` vazio com constraint (top 10); cache contexts `url.query_args:q|cidade|estado` + tags `taxonomy_term_list:curso` + `node_list:vagas` em `modules/custom/custom_banners/src/Controller/CursosAutocompleteController.php` conforme `specs/041-home-busca-inteligente/contracts/cursos-autocomplete.md`
- [X] T014 [P] [US2] Estender `CidadesAutocompleteController`: ler `curso`; com constraint agregar via índice com N filtrado; permitir `q` vazio com constraint; contexts `url.query_args:q|curso`; label `{Cidade} - {UF} - ({N})` em `modules/custom/custom_banners/src/Controller/CidadesAutocompleteController.php` conforme `specs/041-home-busca-inteligente/contracts/cidades-autocomplete.md`
- [X] T015 [US2] Fazer o autocomplete JS enviar constraints do campo oposto nos query args (`cidade`/`estado` → cursos; `curso` → cidades) e cache key `path|q|cidade|estado|curso` em `themes/custom/default/assets/js/hero-search.js` conforme `specs/041-home-busca-inteligente/contracts/home-search-crossfilter.md`
- [X] T016 [US2] Validar SC-002 / quickstart C (cruzamento seedado + `GET /api/cursos/autocomplete?q=&cidade=…&estado=…` + `GET /api/cidades/autocomplete?q=&curso=…`) em `specs/041-home-busca-inteligente/quickstart.md` seção C

**Checkpoint**: sugestões cruzadas com vagas reais — MVP de produto da feature.

---

## Phase 5: User Story 3 — Campo sem opções é desativado (Priority: P1)

**Goal**: probe do oposto com `q` vazio + constraint; se `[]` → `disabled`, limpa valor (+ UF se cidade), mostra hint; esvaziar origem reabilita (FR-004/005 / SC-003).  
**Independent Test Criteria**: forçar combinação sem opções → disable + aviso; limpar origem → reabilita (`quickstart.md` seção D).

- [X] T017 [US3] Adicionar markup de hints traduzíveis sob os campos curso e cidade (copy: “Sem cursos com vagas nesta cidade.” / “Sem cidades com vagas para este curso.”; `hidden` por padrão) em `modules/custom/custom_banners/templates/block--hero-search.html.twig`
- [X] T018 [P] [US3] Estilizar estado `is-disabled` do field e `.hero-search__hint` (visível só quando aplicável; layout 040 preservado) em `themes/custom/default/assets/css/components/hero-search.css`
- [X] T019 [US3] Implementar probe do oposto: debounce 300ms na digitação; imediato no `select`; abort XHR anterior; se `[]` com constraint → disable + limpar + hint; se origem vazia → reabilitar + esconder hint em `themes/custom/default/assets/js/hero-search.js` conforme `contracts/home-search-crossfilter.md`
- [X] T020 [P] [US3] Bump de versão/cache-bust da library `hero_search` (se o tema versionar assets) em `themes/custom/default/default.libraries.yml` — **não** criar library nova; **não** anexar em `global-styling`
- [X] T021 [US3] Validar SC-003 / quickstart D (disable + hint; limpar origem reabilita; espaços → sem constraint efetiva) em `specs/041-home-busca-inteligente/quickstart.md` seção D

**Checkpoint**: UX de “quando não fizer sentido, desative” coberta.

---

## Phase 6: User Story 4 — Autocomplete continua rápido (Priority: P1)

**Goal**: latência percebida ≤1s (meta ≤300ms em cache hit); probe não bloqueia digitação no origem; delay AC 80ms; debounce probe 300ms (FR-007 / SC-004).  
**Independent Test Criteria**: digitar 2+ chars com/sem constraint ≤1s; digitar enquanto probe do outro campo ocorre (`quickstart.md` seção E).

- [X] T022 [US4] Confirmar delay jQuery UI autocomplete = 80ms e que o probe usa timer separado (300ms) sem `disabled` no campo origem durante o XHR em `themes/custom/default/assets/js/hero-search.js`
- [X] T023 [US4] Confirmar abort de XHR anterior (AC e probe) e cache client por chave com constraints para evitar requests redundantes em `themes/custom/default/assets/js/hero-search.js`
- [X] T024 [US4] Validar SC-004 / quickstart E (dropdown ≤1s; digitação fluida durante probe) em `specs/041-home-busca-inteligente/quickstart.md` seção E

**Checkpoint**: requisito explícito de latência atendido sem spinner obrigatório.

---

## Phase 7: User Story 5 — Deploy automatizado (Priority: P1)

**Goal**: destino ativa cruzamento e avisos só com Git + `drush cr`; CSS/JS via libraries; lógica em módulo custom; zero admin; sem `hook_update_N`/YAML novo (FR-008/010 / SC-005).  
**Independent Test Criteria**: ambiente “frio” só com comandos versionados — repetir B–E (`quickstart.md` seções A+F / contrato deploy).

- [X] T025 [US5] Confirmar que CSS/JS estão só em `themes/custom/default/assets/**` + `default.libraries.yml` e que índice/controllers vivem em `modules/custom/custom_banners/**` (sem YAML inventado em `config/sync/`) contra `specs/041-home-busca-inteligente/contracts/deploy-home-busca-inteligente.md`
- [X] T026 [US5] Executar limpeza de cache local (`docker compose exec -T drupal vendor/bin/drush cr`) e gates pós-deploy (rotas 200 com novos params; library `default/hero_search`; Home aplica cruzamento) conforme `specs/041-home-busca-inteligente/quickstart.md` seções A e F
- [X] T027 [US5] Validar isolamento: hero `/vagas` intacto; View `vagas` sem mudança de schema; busca GET unária preservada — `specs/041-home-busca-inteligente/quickstart.md` + FR-009

**Checkpoint**: feature reproduzível sem cliques no painel.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, specify-rules, aceite final SC-001–SC-005 e edges do spec.

- [X] T028 [P] Atualizar `PRD.md` de forma cirúrgica (§3.6 / §5 / módulos): hero Home com cruzamento curso↔cidade; params opcionais nos endpoints `/api/cursos/autocomplete` e `/api/cidades/autocomplete`
- [X] T029 [P] Atualizar bloco SPECKIT em `.cursor/rules/specify-rules.mdc` para feature `041-home-busca-inteligente` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T030 Executar validação final completa (SC-001–SC-005; edges: espaços, JS off, homônimos+UF, N filtrado, cache tags) com `specs/041-home-busca-inteligente/quickstart.md` e `specs/041-home-busca-inteligente/checklists/requirements.md`
- [X] T031 Limpar cache Drupal após Twig/CSS/JS/controllers (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2, inclui índice) → US1 (Phase 3) → US2 (Phase 4, MVP) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → Polish (Phase 8)
- US1 é sobretudo garantia de regressão (Twig/JS submit); validação T012 após US2/US3 estáveis
- US2: T013 e T014 em paralelo (controllers distintos); T015 após ou junto aos endpoints; T016 por último
- US3: T017 + T018 em paralelo; T019 depende de Twig hints + endpoints US2; T020 paralelo a CSS; T021 por último
- US4 refina/valida o mesmo `hero-search.js` de T015/T019 — executar após US3 (mesmo arquivo; não paralelizar edições conflitantes)
- US5 valida o conjunto US1–US4; não adiciona config/hook
- Polish (PRD + specify-rules) após código estável
- Nenhuma história exige `config/sync` nem `hook_update_N`

## Dependency Graph

```text
Phase1 → Phase2 (escopo + VagaLocationCourseIndex)
           ├→ US1 (P1) regressão submit 1 campo ─────────────┐
           └→ US2 (P1) MVP constraints nos endpoints + JS ───┤
                └→ US3 (P1) probe disable/hint Twig/CSS/JS ──┤
                     └→ US4 (P1) latência debounce/abort ────┤
                          └→ US5 (P1) gates Git + drush cr ──┴→ Polish (PRD)
```

## Parallel Execution Opportunities

- **Setup**: T002–T005 em paralelo após T001; T006 após inventário
- **Foundational**: T007 → T008 paralelo a preparação; T009 após alvos documentados
- **US1**: T010 e T011 em paralelo; T012 após US2/US3
- **US2**: T013 e T014 em paralelo; T015 após endpoints; T016 por último
- **US3**: T017 e T018 em paralelo; T019 após T017 + US2; T020 paralelo a T018; T021 por último
- **US4**: T022 → T023 (mesmo JS, sequencial) → T024
- **US5**: T025 → T026 → T027
- **Polish**: T028 e T029 em paralelo; depois T030 → T031

## Implementation Strategy

1. **MVP**: Phase 1–2 + US2 (índice + constraints nos endpoints + JS enviando params) — valor de negócio “busca inteligente”
2. **UX de bloqueio**: US3 (disable + hint) — completo o pedido “desative quando não fizer sentido”
3. **Regressão + latência**: US1 (submit unário) + US4 (debounce/abort) — fecham qualidade
4. **Deploy**: US5 — `drush cr` + gates; zero admin / zero hook
5. **Fechamento**: Polish — PRD §3.6/§5, specify-rules, SC-001–SC-005

## Format Validation

- Todas as tarefas usam checkbox, ID sequencial (`T001`…`T031`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; validação manual no quickstart)
- Ordem de histórias: US1 (P1) → US2 (P1 MVP) → US3 (P1) → US4 (P1) → US5 (P1)
- Status: tarefas marcadas `[X]` — implementação já presente no código (controllers, índice, JS/Twig/CSS, PRD) alinhada ao `tasks.md` anterior enxuto
