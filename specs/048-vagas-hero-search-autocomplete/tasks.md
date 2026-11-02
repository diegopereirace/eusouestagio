# Tasks: Hero de busca de /vagas — Autocomplete e refino visual (Figma)

**Input**: Artefatos de design em `specs/048-vagas-hero-search-autocomplete/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/vagas-autocomplete-api.md`, `contracts/vagas-hero-ui.md`, `quickstart.md`  
**Branch sugerida**: `feat/vagas-hero-search-autocomplete`  
**Predecessor**: `027-vagas-hero-search` (bloco/hero), `040-home-busca-cidades` / `041-home-busca-inteligente` (endpoints cidade/curso)  
**Testes automatizados**: PHPUnit Unit solicitado no plano — `VagasSuggestionMatcherTest`; validação manual via `quickstart.md` (sem suíte JS)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/048-vagas-hero-search-autocomplete` (via `.specify/feature.json`); template alinhado a `specs/040-home-busca-cidades/tasks.md` / `specs/027-vagas-hero-search/tasks.md`  
**Config estrutural**: **nenhuma** — filtro título OU empresa em código (`custom_panel_views_query_alter`); **sem** `hook_update_N`, **sem** `drush cex`; publicar com `git pull` + receita `cim`→`updb`→`cim`→`cr` (cim/updb no-op) + `drush cr`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar bloco `/vagas`, endpoints 040/041, filtro `title` em `custom_panel`, library/CSS/JS atuais e Figma antes de mutar código.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/vagas-autocomplete-api.md`, `contracts/vagas-hero-ui.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/048-vagas-hero-search-autocomplete/` e feature ativa `048-vagas-hero-search-autocomplete` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar `VagasHeroSearchBlock` (pré-preenchimento canônico, pills, attach `default/vagas_hero_search`) em `modules/custom/custom_banners/src/Plugin/Block/VagasHeroSearchBlock.php`
- [X] T003 [P] Inventariar Twig atual (3 inputs sem `data-ac-*`, placeholder "Cidade", sem "Ver todas") em `modules/custom/custom_banners/templates/block--vagas-hero-search.html.twig` e theme hook em `modules/custom/custom_banners/custom_banners.module`
- [X] T004 [P] Inventariar padrão 040/041 (`CidadesAutocompleteController` / `CursosAutocompleteController`, `CacheableJsonResponse`, `cache.data` TTL 300, `SearchTextNormalizer`, LIMIT 10) em `modules/custom/custom_banners/src/Controller/CidadesAutocompleteController.php`, `modules/custom/custom_banners/src/Controller/CursosAutocompleteController.php` e `modules/custom/custom_banners/custom_banners.routing.yml`
- [X] T005 [P] Inventariar filtro `title` atual (`custom_panel_views_pre_view` + `custom_panel_views_query_alter` só no título, fold `translate(lower())`) em `modules/custom/custom_panel/custom_panel.module` (~L313–421)
- [X] T006 [P] Inventariar library/CSS/JS `default/vagas_hero_search` v1.1.0 (só submit-normalizer + CSS pílula 999 px) em `themes/custom/default/default.libraries.yml`, `themes/custom/default/assets/js/vagas-hero-search.js` e `themes/custom/default/assets/css/components/vagas-hero-search.css`
- [X] T007 Confirmar escopo negativo de deploy: zero `hook_update_N` (último `11070`; `11071` livre), zero alteração em `config/sync/views.view.vagas.yml`, zero mudança de contrato 040/041 — anotar gaps em `specs/048-vagas-hero-search-autocomplete/plan.md` / Assumptions da spec se necessário

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar restrições compartilhadas — não tocar Home; não alterar View YAML; não criar hook/cex; alvos fixos de rota, matcher, Twig e library documentados. Pré-requisito de todas as user stories.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T008 Congelar escopo negativo: **não** editar `themes/custom/default/assets/js/hero-search.js`, `themes/custom/default/assets/css/components/hero-search.css` nem `modules/custom/custom_banners/templates/block--hero-search.html.twig`; **não** alterar `config/sync/views.view.vagas.yml`; **não** criar `hook_update_N` em `modules/custom/custom_configs/custom_configs.install`; **não** mudar contratos de `CidadesAutocompleteController` / `CursosAutocompleteController`; **não** editar `core/` nem `vendor/`
- [X] T009 [P] Documentar alvos fixos a implementar (rota `GET /api/vagas/autocomplete`; `VagasSuggestionMatcher` + `VagasAutocompleteController`; filtro título OU empresa via `EXISTS` em `field_vaga_empresa`; Twig `data-ac-endpoint`/`data-ac-kind`; JS `vagas-hero-autocomplete.js`; library v1.2.0; CSS Figma 12 px / botão `#58A83C` / pills `#EFF4FF` / "Ver todas"; deploy = `drush cr`) em `specs/048-vagas-hero-search-autocomplete/data-model.md` / contratos — sem criar código ainda
- [X] T010 [P] Confirmar relação canônica `field_vaga_empresa` (não `field_empresa_u`) e que `custom_panel_views_pre_view()` já neutraliza o filtro nativo `title` em `modules/custom/custom_panel/custom_panel.module` / `config/sync/field.storage.node.field_vaga_empresa.yml`

**Checkpoint**: restrições claras; pronto para backend/Twig/assets sem risco de config/Home.

---

## Phase 3: User Story 1 — Visitante encontra vaga por cargo ou empresa (Priority: P1) 🎯 MVP

**Goal**: Input 1 com autocomplete assíncrono (`/api/vagas/autocomplete`); listagem `/vagas?title=` casa título **OU** empresa (accent-insensitive); sugestão escolhida → busca com ≥ 1 resultado (SC-003).  
**Independent Test Criteria**: digitar `est` no Input 1 → dropdown ≤ 10; escolher sugestão → Buscar → listagem com título/empresa correspondente; `q=e` → sem request; quickstart §3–§4 / SC-001–SC-003.

### Tests (PHPUnit — solicitado no plano)

- [X] T011 [P] [US1] Criar `VagasSuggestionMatcherTest` (acento/caixa, prefixo antes de substring, `vaga` antes de `empresa`, dedupe, limite 10, `%`/`_`/aspas literais, `q` &lt; 2 → `[]`, `q` &gt; 80 truncado) em `modules/custom/custom_banners/tests/src/Unit/VagasSuggestionMatcherTest.php` (padrão de `SearchTextNormalizerTest.php`)

### Implementation

- [X] T012 [P] [US1] Criar classe pura `final` `VagasSuggestionMatcher` (corte 80, normalize via `SearchTextNormalizer`, `mb_strpos` literal, ranking, dedupe, `array_slice` 10) em `modules/custom/custom_banners/src/VagasSuggestionMatcher.php` conforme `specs/048-vagas-hero-search-autocomplete/data-model.md` §5
- [X] T013 [US1] Criar `VagasAutocompleteController::autocomplete` (listas cacheadas títulos + empresas com ≥ 1 vaga via `node__field_vaga_empresa`; `CacheableJsonResponse`; TTL 300; tags `node_list:vagas`/`node_list:empresa`; try/catch → `[]`; `access content`) em `modules/custom/custom_banners/src/Controller/VagasAutocompleteController.php` conforme `specs/048-vagas-hero-search-autocomplete/contracts/vagas-autocomplete-api.md`
- [X] T014 [P] [US1] Registrar rota `GET /api/vagas/autocomplete` (`custom_banners.vagas_autocomplete`, `_permission: access content`, methods GET) em `modules/custom/custom_banners/custom_banners.routing.yml`
- [X] T015 [US1] Estender bloco `title` em `custom_panel_views_query_alter()` para `(translate(lower(node_field_data.title)…) LIKE :t OR EXISTS (… field_vaga_empresa … emp.status=1 … LIKE :t))` com fold compartilhado; manter `escapeLike`; **não** alterar `views.view.vagas.yml` em `modules/custom/custom_panel/custom_panel.module`
- [X] T016 [US1] Passar `#endpoints` (`Url::fromRoute` das 3 rotas) e `#view_all_url` em `VagasHeroSearchBlock::build()` e declarar variáveis no theme hook `custom_banners_vagas_hero_search` em `modules/custom/custom_banners/src/Plugin/Block/VagasHeroSearchBlock.php` e `modules/custom/custom_banners/custom_banners.module`
- [X] T017 [US1] Adicionar `data-ac-endpoint` / `data-ac-kind="vagas"` no Input 1 (`name=title`) em `modules/custom/custom_banners/templates/block--vagas-hero-search.html.twig` conforme `specs/048-vagas-hero-search-autocomplete/contracts/vagas-hero-ui.md`
- [X] T018 [US1] Implementar núcleo do autocomplete Input 1 em JS novo (`Drupal.behaviors.vagasHeroAutocomplete` + `once()`; debounce 300 ms; `AbortController` + token; cache `Map` ≤ 50; DOM/`textContent` + `<mark>`; adapter `kind=vagas` com badge Vaga/Empresa; seleção preenche sem submit) em `themes/custom/default/assets/js/vagas-hero-autocomplete.js`
- [X] T019 [US1] Bump library `vagas_hero_search` para `version: 1.2.0` e incluir `assets/js/vagas-hero-autocomplete.js` (deps `core/drupal`, `core/once`, `default/bootstrap`; **sem** jQuery UI) em `themes/custom/default/default.libraries.yml`
- [X] T020 [US1] Validar US1 / SC-001–SC-003: `drush cr`; PHPUnit Unit; curls do endpoint; lote sugestão→listagem; DevTools debounce/abort — conforme `specs/048-vagas-hero-search-autocomplete/quickstart.md` §§1–4 e §5.1–5.2

**Checkpoint**: Input 1 + listagem título/empresa — MVP de produto da feature.

---

## Phase 4: User Story 2 — Visitante filtra por cidade e por curso (Priority: P1)

**Goal**: Inputs 2 e 3 com autocomplete reaproveitando `/api/cidades|cursos/autocomplete` sem mudança de contrato e **sem** cruzamento cidade×curso; placeholder Input 2 = "Cidade ou Remoto".  
**Independent Test Criteria**: `sao` → `{Cidade} - {UF} - (N)`; `adm` → cursos; buscar → `/vagas?cidade=&cursos=`; Input 3 não restringe pela cidade (quickstart §5.1 / SC-008 parcial).

- [X] T021 [US2] Adicionar `data-ac-endpoint` / `data-ac-kind` nos Inputs 2 (`cidades`) e 3 (`cursos`); placeholder e label visually-hidden do Input 2 → **"Cidade ou Remoto"** em `modules/custom/custom_banners/templates/block--vagas-hero-search.html.twig`
- [X] T022 [US2] Estender `vagas-hero-autocomplete.js` com adapters `cidades` (exibe `label`, preenche `value`) e `cursos` (exibe/preenche `label`/`value`); chamar endpoints **sem** params de cruzamento; campos independentes em `themes/custom/default/assets/js/vagas-hero-autocomplete.js`
- [X] T023 [US2] Validar US2: cidade+curso na URL; ausência de disable/cross-filter; contratos 040/041 inalterados (`curl` cidades/cursos) conforme `specs/048-vagas-hero-search-autocomplete/quickstart.md` §5.1 e §6

**Checkpoint**: três inputs com autocomplete; backends 040/041 intactos.

---

## Phase 5: User Story 3 — Visitante busca com qualquer combinação (Priority: P1)

**Goal**: nenhuma combinação obrigatória; GET para `/vagas` com params preenchidos normalizados; pré-preenchimento por URL sem abrir dropdown; Enter com dropdown fechado submete.  
**Independent Test Criteria**: matriz de 8 combinações (SC-004); vazios → `/vagas`; URL com query → inputs canônicos sem dropdown (FR-019); quickstart §5.3.

- [X] T024 [US3] Confirmar/ajustar `vagas-hero-search.js` (submit NFD/minúsculas → `location.assign`) para continuar enviando só params preenchidos e não conflitar com o behavior de autocomplete (Enter com item ativo vs. submit) em `themes/custom/default/assets/js/vagas-hero-search.js` e `themes/custom/default/assets/js/vagas-hero-autocomplete.js`
- [X] T025 [US3] Garantir que load com `?title=`/`?cidade=`/`?cursos=` pré-preenche via bloco existente **sem** disparar `input`/dropdown no boot em `modules/custom/custom_banners/src/Plugin/Block/VagasHeroSearchBlock.php` e `themes/custom/default/assets/js/vagas-hero-autocomplete.js`
- [X] T026 [US3] Validar matriz SC-004 (8 combinações + Enter sem dropdown + degradação sem JS / subscriber 302) conforme `specs/048-vagas-hero-search-autocomplete/quickstart.md` §5.3 e §5.6

**Checkpoint**: busca regressão-safe; form útil com e sem JS.

---

## Phase 6: User Story 4 — Visual fiel ao Figma (Priority: P1)

**Goal**: barra Figma (container 12 px, divisórias, botão verde embutido, pills `#EFF4FF`, link "Ver todas"); desktop ≥ 992 linha / mobile coluna; CSS só sob `.vagas-hero-search*`.  
**Independent Test Criteria**: comparar `/vagas` a 1280/768/375 com frames `196-1160` / `196-1186` / `196-1189` (SC-005 / SC-006 / quickstart §5.5).

- [X] T027 [P] [US4] Confirmar tokens container × botão no Figma `196-1186` (MCP/design) e anotar ajustes finais (padding 4 px, min-height 56 px, **sem** `overflow:hidden` no form) em `specs/048-vagas-hero-search-autocomplete/contracts/vagas-hero-ui.md` se divergir
- [X] T028 [US4] Incluir link **"Ver todas"** (`p.vagas-hero-search__view-all` → `a` com `href="{{ view_all_url|default(action) }}"`) após as pills em `modules/custom/custom_banners/templates/block--vagas-hero-search.html.twig`
- [X] T029 [US4] Reescrever CSS escopado (container `#fff`/12 px/sombra; mobile-first coluna → linha `@media (min-width: 992px)`; divisórias V/H; inputs sem borda; `:focus-within` no field; botão `#58A83C` raio 8 px; pills `#EFF4FF`/`9999px`/≈30 px; "Ver todas" `var(--brand-orange)`; estilos dropdown `.vagas-hero-search__suggestions*`; ajuste `@media (max-width: 575.98px)`) em `themes/custom/default/assets/css/components/vagas-hero-search.css`
- [X] T030 [US4] Validar SC-005 / SC-006 / pills → `/vagas?cursos=` / "Ver todas" → `/vagas` limpo em `specs/048-vagas-hero-search-autocomplete/quickstart.md` §5.5

**Checkpoint**: hero visualmente alinhado ao Figma sem vazar para `.hero-search`.

---

## Phase 7: User Story 5 — Navegação por teclado e ARIA (Priority: P2)

**Goal**: dropdown operável só com teclado; combobox/listbox/option + live region; Esc fecha sem limpar.  
**Independent Test Criteria**: setas / Enter / Esc / Tab; inspeção ARIA; fluxo completo sem mouse (SC-007 / quickstart §5.4).

- [X] T031 [US5] Completar ARIA 1.2 no JS (`role=combobox`, `aria-expanded`, `aria-controls`, `aria-autocomplete=list`, `aria-activedescendant`; listbox/option/`aria-selected`; live region `aria-live=polite`; ↑↓/Enter/Esc/Tab; blur/clique fora; `pageshow` persistido fecha) em `themes/custom/default/assets/js/vagas-hero-autocomplete.js` conforme `specs/048-vagas-hero-search-autocomplete/contracts/vagas-hero-ui.md`
- [X] T032 [US5] Validar teclado + ARIA + alvo de toque ≥ 44 px no mobile para opções/botão conforme `specs/048-vagas-hero-search-autocomplete/quickstart.md` §5.4

**Checkpoint**: autocomplete acessível; SC-007 atendido.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, nota na spec (D1/D6), regressão Home/040/041, cache final, aceite SC-001–SC-009.

- [X] T033 [P] Atualizar `PRD.md` de forma cirúrgica: §3.6 hero `/vagas` (3 autocompletes, "Ver todas", placeholder "Cidade ou Remoto", filtro `title` com empresa via `custom_panel`); §10 rota `/api/vagas/autocomplete`; menção no módulo `custom_banners` (~L346)
- [X] T034 [P] Confirmar/atualizar nota em Assumptions de `specs/048-vagas-hero-search-autocomplete/spec.md` registrando D1/D6/F2 (filtro em código; sem hook/cex)
- [X] T035 [P] Atualizar bloco SPECKIT em `.cursor/rules/specify-rules.mdc` para feature `048-vagas-hero-search-autocomplete` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T036 Validar regressão SC-008 (Home `.hero-search` + jQuery UI; curls 040/041; filtros View `estado`/`escolaridade`/`regime`/`nid`; console limpo) em `specs/048-vagas-hero-search-autocomplete/quickstart.md` §6
- [X] T037 Executar validação final completa (SC-001–SC-009; edges do spec; falhas de rede/XSS literais; checklist requirements) com `specs/048-vagas-hero-search-autocomplete/quickstart.md` e `specs/048-vagas-hero-search-autocomplete/checklists/requirements.md`
- [X] T038 Limpar cache Drupal após Twig/CSS/JS/rota (`docker compose exec -T drupal vendor/bin/drush cr`) e registrar receita destino sem `cex`/hook em `specs/048-vagas-hero-search-autocomplete/quickstart.md` §7

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3, MVP) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → Polish (Phase 8)
- US1: testes T011 podem ser escritos em paralelo a T012; T013 depende de T012; T014 paralelo a T012/T013; T015 independente do endpoint (pode paralelizar com T012–T014); T016→T017→T018→T019→T020
- US2 depende do núcleo JS/Twig de US1 (T017–T018) e de `#endpoints` (T016)
- US3 depende dos três campos wireados (US1+US2) e do submit-normalizer existente
- US4 (CSS/"Ver todas") pode iniciar em paralelo ao backend US1 após Phase 2 (Twig link T028 após T016); aceite visual pleno após JS não quebrar layout do dropdown
- US5 refina o mesmo `vagas-hero-autocomplete.js` de US1/US2 — executar após comportamento base estável (após T022)
- Nenhuma história exige `config/sync` nem `hook_update_N`

## Dependency Graph

```text
Phase1 → Phase2 (escopo negativo + alvos fixos)
           ├→ US1 (P1) MVP matcher+API+filtro+AC Input1 ─┐
           │     └→ US2 (P1) AC cidade/curso ─────────────┤
           │           └→ US3 (P1) matriz GET/prefill ───┤
           ├→ US4 (P1) CSS Figma + "Ver todas" ──────────┤
           └→ (após US1/US2 JS) US5 (P2) teclado/ARIA ──┴→ Polish (PRD + regressão + cr)
```

## Parallel Execution Opportunities

- **Setup**: T002–T006 em paralelo após T001; T007 após inventário
- **Foundational**: T008 → T009 e T010 em paralelo
- **US1**: T011 ∥ T012; T014 ∥ T012/T013; T015 ∥ endpoint; depois T016 → T017 → T018 → T019 → T020
- **US2**: T021 → T022 → T023 (sequencial no Twig/JS compartilhados)
- **US3**: T024 ∥ T025 → T026
- **US4**: T027 ∥ início de T029; T028 após T016; T030 por último
- **US5**: T031 → T032 (mesmo arquivo JS)
- **Polish**: T033, T034, T035 em paralelo; depois T036 → T037 → T038

### Exemplo paralelo (US1 backend)

```text
Agent A: T011 + T012 (matcher + teste)
Agent B: T014 (rota) + esqueleto T013
Agent C: T015 (custom_panel filtro OR empresa)
```

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (matcher, endpoint, filtro título/empresa, autocomplete Input 1) — valor principal
2. **Paridade dos 3 campos**: US2 (cidade/curso) + US3 (matriz GET)
3. **Visual**: US4 (CSS Figma + "Ver todas") — pode avançar em paralelo ao backend após Phase 2
4. **A11y**: US5 (teclado/ARIA) após JS base
5. **Fechamento**: Polish — PRD §3.6/§10, nota D1/D6 na spec, specify-rules, SC-001–SC-009, `drush cr`

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T038`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Testes PHPUnit incluídos porque o plano solicita `VagasSuggestionMatcherTest` explicitamente
