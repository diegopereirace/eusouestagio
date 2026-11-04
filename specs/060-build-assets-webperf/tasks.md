# Tasks: Build de assets do tema e agregação CSS/JS (WebPerf)

**Input**: Artefatos de design em `specs/060-build-assets-webperf/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/theme-build-pipeline.md`, `contracts/system-performance.md`, `contracts/lcp-hero-home.md`, `contracts/deploy-build-assets-webperf.md`, `quickstart.md`  
**Branch sugerida**: `main` (workflow do projeto) ou `feat/build-assets-webperf`  
**Predecessors**: `046-breakpoints-tema`, `001-banners-busca-home`  
**Hook**: `custom_configs_update_11090` (garantir `system.performance` css/js preprocess = TRUE)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md` (sem PHPUnit)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/060-build-assets-webperf` (via `.specify/feature.json`); template alinhado a `specs/059-cadastro-candidato-legal/tasks.md`  
**Config estrutural**: `system.performance.yml` via `drush cex`; destino `cim` → `updb` → `cim` → `cr`; `dist/` versionado (produção sem Node)

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar tema/libraries/performance/LCP/hook antes de mutar código.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/theme-build-pipeline.md`, `contracts/system-performance.md`, `contracts/lcp-hero-home.md`, `contracts/deploy-build-assets-webperf.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/060-build-assets-webperf/` e feature ativa `060-build-assets-webperf` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar todas as libraries próprias com paths `assets/css/**` e `assets/js/**` (e confirmar terceiros `/libraries/` intactos: `bootstrap`, `fontawesome`, `jquery_mask`) em `themes/custom/default/default.libraries.yml`
- [X] T003 [P] Inventariar árvore de fontes legadas (~31 CSS + ~12 JS) em `themes/custom/default/assets/css/` e `themes/custom/default/assets/js/`
- [X] T004 [P] Inventariar estado atual `css.preprocess: false` / `js.preprocess: false` (compress true; `cache.page.max_age: 0`) em `config/sync/system.performance.yml`
- [X] T005 [P] Inventariar markup LCP do 1º slide (`fetchpriority`/`loading`) em `themes/custom/default/templates/views/views-view--banners--block-home.html.twig`
- [X] T006 Confirmar último hook `custom_configs_update_11089` e que o próximo livre é `11090` em `modules/custom/custom_configs/custom_configs.install`
- [X] T007 [P] Confirmar ausência de `package.json`/Vite no tema e regras atuais de ignore em `themes/custom/default/` + `.gitignore` (raiz)

**Checkpoint**: inventário alinhado a plan/research/data-model/contracts; nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: congelar escopo negativo e confirmar decisões de pipeline/hook/gitignore antes das user stories.

**⚠️ CRITICAL**: Nenhuma user story de implementação começa antes desta fase.

- [X] T008 Congelar escopo negativo: **não** editar `core/`/`vendor/`; **não** minificar `/libraries/`; **não** criar CT/fields/storages; **não** alterar `cache.page.max_age` nem redesign visual; **não** exigir Node em produção; **não** WebP/AVIF/critical CSS/service worker; **não** seed File (057 N/A); imagens permanecem em `themes/custom/default/assets/images/`
- [X] T009 Confirmar decisões canônicas (Vite multi-entry sem hash no filename; `src/` → `dist/`; versionar `dist/`; ignorar `node_modules/`; hook só preprocess TRUE) conforme `specs/060-build-assets-webperf/research.md` (R1–R5) e `specs/060-build-assets-webperf/plan.md`
- [X] T010 Confirmar candidato LCP = View `banners`/`block_home` (não `custom_banners_hero_search`) e contrato em `specs/060-build-assets-webperf/contracts/lcp-hero-home.md`

**Checkpoint**: escopo congelado; decisões de pipeline/LCP/hook confirmadas; pronto para US1.

---

## Phase 3: User Story 1 — Desenvolvedor altera CSS/JS e vê build minificado (Priority: P1) 🎯 MVP

**Goal**: Pipeline Vite no tema `default` com `src/` → `dist/` minificado, scripts `dev`/`watch`/`build`, e `default.libraries.yml` apontando 100% das libraries próprias para `dist/` (FR-001–FR-007 / SC-001 / SC-002).  
**Independent Test Criteria**: alterar seletor/string em `src/`; `npm run dev` atualiza `dist/`; `npm run build` exit 0 ≤60s; HTML anexa paths `dist/` (não `assets/css|js` legado) — `quickstart.md` seção A / `contracts/theme-build-pipeline.md`.

- [X] T011 [US1] Criar `package.json` com Vite + PostCSS/cssnano + sass (opcional) e scripts `dev`/`watch`/`build` (Node LTS ≥20) em `themes/custom/default/package.json`
- [X] T012 [US1] Criar Vite multi-entry (um entry por CSS/JS próprio; saída estável sem hash; `src/css|js` → `dist/css|js`) em `themes/custom/default/vite.config.mjs` (research R1/R3)
- [X] T013 [P] [US1] Adicionar `node_modules/` em `themes/custom/default/.gitignore` e `**/node_modules/` (ou equivalente) em `.gitignore` na raiz — **não** ignorar `themes/custom/default/dist/` (research R4)
- [X] T014 [US1] Migrar fontes legadas `assets/css/**` → `src/css/**` e `assets/js/**` → `src/js/**` (espelho de árvore; imagens `assets/images/**` intactas) em `themes/custom/default/src/`
- [X] T015 [US1] Executar `npm install` + `npm run build` no tema e gerar artefatos minificados versionáveis em `themes/custom/default/dist/css/` e `themes/custom/default/dist/js/`
- [X] T016 [US1] Atualizar **todas** as libraries próprias para paths `dist/css/...` e `dist/js/...` (manter `bootstrap`/`fontawesome`/`jquery_mask` em `/libraries/`) em `themes/custom/default/default.libraries.yml` (FR-006 / FR-007 / SC-001)
- [X] T017 [US1] Remover paths canônicos legados: deixar de referenciar `assets/css`/`assets/js` nas libraries e remover (ou esvaziar) fontes antigas após build verde em `themes/custom/default/assets/css/` e `themes/custom/default/assets/js/`
- [X] T018 [US1] Validar watch (`npm run dev` ou `npm run watch`): mudança em `src/` atualiza `dist/` sem minificar à mão; confirmar SC-002 (build ≤60s) conforme `specs/060-build-assets-webperf/quickstart.md` seção A e `specs/060-build-assets-webperf/contracts/theme-build-pipeline.md`
- [X] T019 [US1] Smoke anexação: home + 1 página com library migrada (ex. `/vagas` ou `/cadastro/candidato`) carregam CSS/JS de `dist/` (ou agregado derivado); behaviors Drupal preservados — `specs/060-build-assets-webperf/quickstart.md` seção D

**Checkpoint**: pipeline utilizável; libraries próprias 100% em `dist/` — MVP de WebPerf no tema.

---

## Phase 4: User Story 2 — Visitante recebe CSS/JS agregados (Priority: P1)

**Goal**: `custom_configs_update_11090` idempotente + `drush cex` deixam `css.preprocess`/`js.preprocess` TRUE; home anônima com assets agregados; reexecução no-op (FR-008 / FR-009 / FR-013 / SC-003 / SC-004 / SC-008).  
**Independent Test Criteria**: pós-`updb`/`cim`/`cr`, config TRUE; HTML home com URLs agregadas vs baseline fragmentado; 2º `updb` sem erro — `quickstart.md` seção B / `contracts/system-performance.md`.

- [X] T020 [US2] Implementar `custom_configs_update_11090` idempotente (load editable `system.performance`; set `css.preprocess`/`js.preprocess` TRUE só se necessário; **não** alterar `cache.page.max_age` nem compress) em `modules/custom/custom_configs/custom_configs.install` (research R5 / `contracts/system-performance.md`)
- [X] T021 [US2] Executar na origem `docker compose exec drupal drush updb -y` e confirmar preprocess TRUE + mensagem de status do `11090`
- [X] T022 [US2] Executar `docker compose exec drupal drush cex -y` e versionar preprocess true em `config/sync/system.performance.yml` (FR-009)
- [X] T023 [US2] Validar SC-003/SC-004: home anônima com cache limpo mostra CSS/JS elegíveis agregados; smoke visual seções principais; se JS frágil quebrar, marcar **só** esse arquivo com `preprocess: false` em `themes/custom/default/default.libraries.yml` e documentar em `specs/060-build-assets-webperf/quickstart.md` (research R6)
- [X] T024 [US2] Reexecutar `docker compose exec drupal drush updb -y` e validar no-op seguro (SC-008) conforme `specs/060-build-assets-webperf/contracts/system-performance.md`

**Checkpoint**: agregação nativa ativa via hook + sync — sem passo manual no admin de produção.

---

## Phase 5: User Story 3 — Navegador prioriza imagem LCP do hero (Priority: P1)

**Goal**: 1º slide do banner home com `loading="eager"` + `fetchpriority="high"`; slides seguintes podem ficar lazy; auditar e só corrigir lacuna (FR-010 / SC-005).  
**Independent Test Criteria**: inspecionar `<img>` do 1º slide em `/`; atributos presentes; se banner desligado → N/A — `quickstart.md` seção C / `contracts/lcp-hero-home.md`.

- [X] T025 [US3] Auditar markup do 1º slide (`loop.first` → eager+high; demais → lazy) em `themes/custom/default/templates/views/views-view--banners--block-home.html.twig`; corrigir só se houver lacuna (baseline já conforme)
- [X] T026 [US3] Confirmar que `custom_banners_hero_search` não é candidato LCP de imagem (sem alteração de atributos de imagem ali) e documentar conformidade ou correção em `specs/060-build-assets-webperf/quickstart.md` seção C
- [X] T027 [US3] Validar SC-005 no browser (home com banner ativo) conforme `specs/060-build-assets-webperf/contracts/lcp-hero-home.md`

**Checkpoint**: LCP hero priorizado ou conformidade documentada.

---

## Phase 6: User Story 4 — Novo contribuinte sobe o pipeline em minutos (Priority: P2)

**Goal**: Quickstart fiel aos scripts reais; install+build ≤15 min com Node já instalado (FR-011 / SC-007).  
**Independent Test Criteria**: seguir só o quickstart; `npm install` + `npm run build` exit 0; watch documentado — `quickstart.md` seção A.

- [X] T028 [US4] Alinhar comandos/paths reais (`dev`/`watch`/`build`, Node ≥20, `src/`/`dist/`) em `specs/060-build-assets-webperf/quickstart.md` após o pipeline US1 existir
- [X] T029 [US4] Validar fluxo de novo contribuinte (install + build + watch) e checklist de PR (`src/` alterado ⇒ `dist/` no mesmo commit; sem `node_modules/`) em `specs/060-build-assets-webperf/quickstart.md` seções A e F (SC-007)

**Checkpoint**: DX documentado e reproduzível sem histórico do projeto.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Objetivo**: FR-014, smoke SC-006, PRD cirúrgico, SPECKIT, receita destino, aceite SC-001–SC-008.

- [X] T030 [P] Executar FR-014 (`npx skills find webperf` / `npx skills find vite`) e registrar resultado (skill adotada **ou** “nenhuma skill adequada — não bloqueia”) em `specs/060-build-assets-webperf/quickstart.md` seção E
- [X] T031 [P] Atualizar `PRD.md` de forma **cirúrgica** só se o time exigir menção a preprocess/build do tema (guardião de escopo); senão anotar N/A no quickstart/PR
- [X] T032 [P] Confirmar bloco SPECKIT em `.cursor/rules/specify-rules.mdc` aponta para `060-build-assets-webperf` com `spec.md` / `plan.md` / `tasks.md`
- [X] T033 Executar limpeza de cache (`docker compose exec drupal drush cr`) após libraries/`dist`/hook/config
- [X] T034 Smoke regressão SC-006: `/` + página com library migrada + `/libraries/` Bootstrap/FA intactos conforme `specs/060-build-assets-webperf/quickstart.md` seção D
- [X] T035 Documentar/validar receita destino `cim` → `updb` (`11090`) → `cim` → `cr` (Node **não** exigido no servidor; `dist/` no Git) em `specs/060-build-assets-webperf/quickstart.md` / `contracts/deploy-build-assets-webperf.md`
- [X] T036 Executar validação final completa (SC-001–SC-008; edges: erro de sintaxe no build, library legada residual, preprocess override local, banner ausente N/A, 2º updb) com `specs/060-build-assets-webperf/quickstart.md` e `specs/060-build-assets-webperf/checklists/requirements.md`

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → Polish (Phase 7)
- US1 (pipeline Vite + libraries) é MVP e desbloqueia smoke real de agregação sobre artefatos `dist/`
- US2 (preprocess) pode iniciar após Foundational em paralelo com finalização de US1 **se** agentes distintos (hook/config vs tema); preferível após T016 para medir agregação com paths finais
- US3 (LCP) é independente de US1/US2 após Foundational (Twig isolado)
- US4 depende de US1 (scripts/paths reais no quickstart)
- Polish após US1–US4

## Dependency Graph

```text
Phase1 → Phase2 (escopo + Vite multi-entry + LCP candidato + hook 11090)
           ├→ US1 (P1) Vite + src/dist + libraries ── MVP
           │     └→ US4 (P2) quickstart DX alinhado
           │     └→ US2 (P1) preferível pós-libraries dist/
           ├→ US2 (P1) 11090 + cex system.performance
           ├→ US3 (P1) audit LCP Twig (∥ US1/US2)
           └→ Polish (FR-014, PRD, cr, smoke, cim/updb/cim/cr, SC-001–SC-008)
```

## Parallel Execution Opportunities

- **Setup**: T002–T005 ∥ T007 após T001; T006 após ou em paralelo com inventário do tema
- **Foundational**: T009 ∥ T010 após T008
- **US1**: T011 → T012 → T013 [P] pode ∥ início de T014; T014 → T015 → T016 → T017; T018 → T019
- **US2**: T020 → T021 → T022 → T023 → T024 (sequencial Drush/config); pode ∥ US3
- **US3**: T025 → T026 → T027; pode ∥ US1 (Twig) e ∥ US2
- **US4**: após T018/T019; T028 → T029
- **Polish**: T030 ∥ T031 ∥ T032; depois T033 → T034 → T035 → T036

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (Vite + migração + libraries `dist/`) — minificação automatizada
2. **Agregação**: US2 (hook `11090` + `cex` + smoke HTML agregado)
3. **LCP**: US3 (auditoria/documentação do hero — correção só se lacuna)
4. **DX**: US4 (quickstart fiel + PR checklist)
5. **Fechamento**: Polish — FR-014, PRD cirúrgico, `cr`, receita destino, SC-001–SC-008

## Format Validation

- Todas as tarefas usam checkbox, ID sequencial (`T001`…`T036`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; validação manual no quickstart)
- Ordem de histórias: US1–US3 (P1) → US4 (P2) → Polish
