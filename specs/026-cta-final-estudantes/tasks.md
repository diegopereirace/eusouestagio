# Tasks: Bloco CTA Final — Para Estudantes

**Input**: Artefatos de design em `specs/026-cta-final-estudantes/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/cta-final-estudantes-render.md`, `contracts/deploy-cta-final-estudantes.md`, `quickstart.md`  
**Branch**: `feature-para-estudantes` (ou branch ativa alinhada à feature)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/026-cta-final-estudantes` (via `.specify/feature.json`); template alinhado a `specs/025-vagas-cards-estudantes/tasks.md`  
**Hook**: plano/spec citam `11042`, mas `custom_configs_update_11042` **já existe** (título `/vagas`); próximo livre = **`custom_configs_update_11043`**

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar Twig/CSS global `cta_v1`, helpers de seed PE empresas, composição `/para-estudantes` (weights 0–3) e último hook antes de tema/`11043`/cex.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/cta-final-estudantes-render.md`, `contracts/deploy-cta-final-estudantes.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/026-cta-final-estudantes/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar Twig global do CTA claro (classes `cta-v1__*`, omit empty, attach `default/cta_v1`) em `themes/custom/default/templates/block/block--block-cta-v1.html.twig` — **referência estrutural; não alterar de forma regressiva**
- [X] T003 [P] Inventariar library/CSS clara (`default/cta_v1` → `cta-v1.css`, fundo `#D3E4FE`) em `themes/custom/default/default.libraries.yml` e `themes/custom/default/assets/css/cta-v1.css` — **não mutar tokens/seletores nesta feature**
- [X] T004 [P] Inventariar helper de seed CTA PE empresas (UUID `a7b8c9d0-…`, campos vazios only) e placements irmãos em `modules/custom/custom_configs/custom_configs.install` (`_custom_configs_seed_cta_v1_para_empresas`), `config/sync/block.block.default_ctav1quemsomos.yml` e `config/sync/block.block.default_ctav1paraempresas.yml`
- [X] T005 Confirmar último hook `custom_configs_update_11042` → próximo livre **`11043`** (corrigir referências `11042` desta feature nos artefatos SDD na Polish) em `modules/custom/custom_configs/custom_configs.install`
- [X] T006 [P] Inventariar composição `/para-estudantes` em `content_full` (benefícios w0 → jornada w1 → perfil w2 → `default_views_block__vagas_block_3` w3; CTA ausente) e ausência de `default_ctav1paraestudantes` em `config/sync/block.block.default_beneficiosestudantes.yml`, `config/sync/block.block.default_jornadaestudante.yml`, `config/sync/block.block.default_perfildestaqueestudante.yml`, `config/sync/block.block.default_views_block__vagas_block_3.yml`
- [X] T007 [P] Confirmar que `.cursor/rules/drupal-deploy-configs.mdc` já cobre FR-019 (`hook_update_N` + `cex` + fluxo `cim`→`updb`→`cim`→`cr`); anotar lacuna só se houver gap factual vs. `specs/026-cta-final-estudantes/spec.md`

**Checkpoint**: inventário alinhado a plan/research/data-model; hook alvo = `11043`; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar restrições compartilhadas — **zero** block type / field storage / field instance novos; reuso total de `cta_v1` + quatro campos; **não** tocar Twig/CSS global do CTA claro nem blocos 021–025 / CTAs QS/PE. Pré-requisito de todas as user stories.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T008 Confirmar reuso do bundle `cta_v1` e campos `field_text_simple` / `field_text_simple_long` / `field_link` / `field_link_2` (sem YAML estrutural novo além do placement futuro) conforme `specs/026-cta-final-estudantes/data-model.md` e `config/sync/block_content.type.cta_v1.yml`
- [X] T009 [P] Congelar escopo negativo de apresentação: **não** editar `themes/custom/default/templates/block/block--block-cta-v1.html.twig` nem `themes/custom/default/assets/css/cta-v1.css` nesta feature
- [X] T010 [P] Congelar escopo negativo de placements/conteúdo: **não** alterar YAMLs/Twig/CSS de 021–025, `default_ctav1quemsomos`, `default_ctav1paraempresas` nem View `vagas` / displays `page_1` / `block_3`
- [X] T011 Documentar alvos fixos a implementar (UUID bloco `e1f2a3b4-c5d6-4789-d012-3ef012345678`; UUID placement `f2a3b4c5-d6e7-4890-e123-4f0123456789`; id `default_ctav1paraestudantes`; weight `4`; pages `/para-estudantes`; suggestion `block--default-ctav1paraestudantes.html.twig`; library `default/cta_v1_para_estudantes`) em `specs/026-cta-final-estudantes/data-model.md` / `contracts/cta-final-estudantes-render.md` — sem criar YAML ainda

**Checkpoint**: restrições claras; pronto para Twig/CSS isolados + hook sem risco de regressão no CTA claro.

---

## Phase 3: User Story 1 — Visitante vê o CTA final em Para Estudantes (Priority: P1) 🎯 MVP

**Goal**: faixa escura full-bleed em `/para-estudantes` com gradiente `#023C62`→`#011A2B`, textos brancos centralizados, paddings 64/40, container ~1280px, dois botões (primário `#FD7B1A`; secundário outline branco), suggestion Twig + library dedicada — **sem** carregar `default/cta_v1`.  
**Independent Test Criteria**: abrir `/para-estudantes` ≥768px com bloco publicado e comparar faixa/copy/botões com Figma / `contracts/cta-final-estudantes-render.md` (SC-001 / quickstart B). Conteúdo de teste manual ou seed US6.

- [X] T012 [US1] Registrar library `cta_v1_para_estudantes` → `assets/css/cta-v1-para-estudantes.css` (deps Bootstrap alinhadas a `cta_v1`) em `themes/custom/default/default.libraries.yml`
- [X] T013 [P] [US1] Criar CSS encapsulado (fundo full-bleed linear `#023C62`→`#011A2B`; padding seção `64px`/`40px`; textos brancos centralizados; título Poppins semibold/bold `h2`; primário `#FD7B1A` texto branco sem borda; secundário transparente `1px solid #FFFFFF` texto branco; **somente** sob `.block-cta-v1--para-estudantes` / `#block-default-ctav1paraestudantes`; **não** tocar `cta-v1.css`) em `themes/custom/default/assets/css/cta-v1-para-estudantes.css`
- [X] T014 [US1] Implementar Twig suggestion do placement (classe raiz `block-cta-v1--para-estudantes`; wrapper `section.cta-v1.cta-v1--dark` + `.container`; markup `cta-v1__title` / `__subtitle` / `__actions` / `__btn--primary|secondary`; attach **só** `default/cta_v1_para_estudantes`; **não** attach `default/cta_v1`; espelhar leitura de campos do Twig global) em `themes/custom/default/templates/block/block--default-ctav1paraestudantes.html.twig`
- [X] T015 [US1] Implementar fallbacks omit empty (título/corpo/botão sem URI; omitir `.cta-v1__actions` se sem botões; **não** renderizar faixa se tudo vazio; preservar `attributes`/`content_attributes`/`title_*`) no mesmo Twig e contra `specs/026-cta-final-estudantes/contracts/cta-final-estudantes-render.md`
- [X] T016 [US1] Validar SC-001 / cenários US1 (gradiente full-bleed; padding ≈64/40; copy seed; botões lado a lado desktop; último bloco antes do footer) conforme `specs/026-cta-final-estudantes/quickstart.md` seção B (após `drush cr`; conteúdo via seed US6 ou bloco de teste)

**Checkpoint**: visitante desktop vê a faixa de conversão — MVP de produto (aceite completo pós-seed US6).

---

## Phase 4: User Story 2 — Visitante mobile vê botões empilhados (Priority: P1)

**Goal**: viewport ≤575.98px — botões empilham; desktop md+ lado a lado com gap; sem scroll horizontal causado pelo bloco.  
**Independent Test Criteria**: `/para-estudantes` ≤575.98px e ≥768px — SC-002 / quickstart C.

- [X] T017 [US2] Confirmar/ajustar CSS responsivo (sem overflow-x; tipografia legível; paddings preservados; full-bleed estável) sob `.block-cta-v1--para-estudantes` em `themes/custom/default/assets/css/cta-v1-para-estudantes.css`
- [X] T018 [P] [US2] Confirmar classes Bootstrap das ações (`.d-grid.gap-2.d-md-flex.justify-content-md-center.gap-md-3`) no Twig da suggestion em `themes/custom/default/templates/block/block--default-ctav1paraestudantes.html.twig`
- [X] T019 [US2] Validar SC-002 / cenários US2 (empilhamento mobile; lado a lado desktop; sem scroll horizontal) conforme `specs/026-cta-final-estudantes/quickstart.md` seção C

**Checkpoint**: CTAs legíveis em mobile sem regressão desktop.

---

## Phase 5: User Story 3 — Visitante usa os botões de ação (Priority: P1)

**Goal**: primário → `/cadastro/candidato`; secundário → `/vagas`; URLs/textos vêm dos fields Link (sem hardcode no Twig); editor pode alterar após seed.  
**Independent Test Criteria**: clicar nos dois botões — SC-003 / quickstart D.

- [X] T020 [US3] Confirmar markup dos botões (`href` via `primary_item.getUrl()` / `secondary_item.getUrl()`; rótulo = `title` do link; omitir se URI vazia; **sem** URL hardcoded) em `themes/custom/default/templates/block/block--default-ctav1paraestudantes.html.twig`
- [X] T021 [US3] Validar SC-003 / cenários US3 (Cadastre-se → `/cadastro/candidato`; Explorar Vagas → `/vagas`; edição de link reflete após cache) conforme `specs/026-cta-final-estudantes/quickstart.md` seção D (após seed US6 ou conteúdo de teste)

**Checkpoint**: navegação de conversão com Clean URLs.

---

## Phase 6: User Story 4 — Isolamento: Quem Somos e Para Empresas não mudam (Priority: P1)

**Goal**: design escuro **somente** nesta instância; CTAs claros de `/quem-somos` e `/para-empresas` intactos; placement ausente em home/outras rotas.  
**Independent Test Criteria**: comparar QS / PE / PE-estudantes / home — SC-004 / quickstart E.

- [X] T022 [US4] Confirmar diff zero (ou sem mudanças regressivas) em `themes/custom/default/templates/block/block--block-cta-v1.html.twig` e `themes/custom/default/assets/css/cta-v1.css`; library clara **não** attachada no Twig da suggestion
- [X] T023 [P] [US4] Confirmar que placements/YAMLs `default_ctav1quemsomos` e `default_ctav1paraempresas` e blocos 021–025 permanecem inalterados sob `config/sync/block.block.*` e tema (seletores CSS só sob escopo PE estudantes)
- [X] T024 [US4] Validar SC-004 / cenários US4 (QS card claro `#D3E4FE`; PE empresas visual anterior; home/outras sem placement) conforme `specs/026-cta-final-estudantes/quickstart.md` seção E

**Checkpoint**: isolamento visual absoluto — sem vazamento para CTAs claros.

---

## Phase 7: User Story 5 — Editor gerencia conteúdo sem código (Priority: P1)

**Goal**: editor com permissão de `cta_v1` edita título, corpo e dois links **só** da instância PE estudantes; QS/PE empresas inalterados; sem roles/fields novos (YAGNI).  
**Independent Test Criteria**: editar no painel, salvar, recarregar `/para-estudantes` &lt; 3 min — SC-005 / quickstart F.

- [X] T025 [US5] Confirmar que o form display existente de `cta_v1` já expõe os quatro campos (sem YAML novo de display/roles) em `config/sync/core.entity_form_display.block_content.cta_v1.default.yml` e roles já cobertas por 015/019
- [X] T026 [P] [US5] Confirmar independência de instâncias (UUID próprio `e1f2a3b4-…`; info admin “CTA v1 Para Estudantes”; edição não altera QS/PE) após seed em `modules/custom/custom_configs/custom_configs.install` / Entity API
- [X] T027 [US5] Validar SC-005 / cenários US5 (edição &lt;3 min; só instância PE estudantes muda) conforme `specs/026-cta-final-estudantes/quickstart.md` seção F

**Checkpoint**: copy/destinos gerenciáveis sem deploy de código.

---

## Phase 8: User Story 6 — Deploy reproduz seed e placement (Priority: P1)

**Goal**: `custom_configs_update_11043` idempotente — seed UUID `e1f2a3b4-…` com copy Figma (só ausente/campos vazios); ensure placement `default_ctav1paraestudantes` (`content_full`, weight `4`, `/para-estudantes`, UUID placement `f2a3b4c5-…`); origem `drush cex`; destino `cim`→`updb`→`cim`→`cr`.  
**Independent Test Criteria**: fluxo de deploy + 2ª `updb` — SC-006 / SC-007 / SC-008 / SC-009 / quickstart A+G / contrato deploy.

- [X] T028 [US6] Implementar helper `_custom_configs_seed_cta_v1_para_estudantes()` (espelhar `_custom_configs_seed_cta_v1_para_empresas`: loadByProperties UUID; se existe popular **somente** campos vazios; se ausente `BlockContent::create` bundle `cta_v1` UUID `e1f2a3b4-c5d6-4789-d012-3ef012345678` info “CTA v1 Para Estudantes” + seed título/corpo/links; mensagem created/skipped; **nunca** sobrescrever editorial divergente) em `modules/custom/custom_configs/custom_configs.install`
- [X] T029 [P] [US6] Implementar helper `_custom_configs_ensure_cta_v1_para_estudantes_placement()` (id `default_ctav1paraestudantes`; plugin `block_content:e1f2a3b4-…`; tema `default`; região `content_full`; weight **`4`**; `label_display: '0'`; visibility `request_path` = `/para-estudantes`; UUID config `f2a3b4c5-d6e7-4890-e123-4f0123456789`; criar/atualizar sem duplicar; **não** tocar placements QS/PE/021–025) em `modules/custom/custom_configs/custom_configs.install`
- [X] T030 [US6] Implementar `custom_configs_update_11043` (verificar bundle `cta_v1` presente senão mensagem “rode cim antes”; chamar seed + ensure placement; retornar mensagem Drush; **não** alterar 021–025, CTAs QS/PE, View `vagas`, Node shell) em `modules/custom/custom_configs/custom_configs.install`
- [X] T031 [US6] Exportar configs estruturais com `drush cex -y` — versionar `config/sync/block.block.default_ctav1paraestudantes.yml` (e dependências se houver diff legítimo); **não** inventar YAML à mão
- [X] T032 [US6] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (idempotência SC-008) conforme `specs/026-cta-final-estudantes/quickstart.md` seções A e G e `specs/026-cta-final-estudantes/contracts/deploy-cta-final-estudantes.md`
- [X] T033 [US6] Validar gates pós-deploy (CTA último em `content_full` w4; copy seed; botões; fallbacks SC-009; ordem w0–w3 intacta; SC-006/SC-007) conforme contrato de deploy e quickstart G

**Checkpoint**: destino reproduz CTA sem admin manual; hook reentrante seguro.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Objetivo**: alinhar artefatos ao hook `11043`, PRD §3.6 cirúrgico, specify-rules, isolamento 021–025 + CTAs claros, aceite final SC-001–SC-009.

- [X] T034 [P] Atualizar §3.6 documentando instância `cta_v1` PE estudantes, placement `default_ctav1paraestudantes` weight `4`, UUID `e1f2a3b4-…`, hook **`11043`**, library `cta_v1_para_estudantes` e composição `/para-estudantes` (… → block_3 → CTA) em `PRD.md`
- [X] T035 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para refletir feature `026-cta-final-estudantes` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T036 [P] Corrigir referências de hook `11042`→`11043` (e mensagem Drush) em `specs/026-cta-final-estudantes/plan.md`, `specs/026-cta-final-estudantes/spec.md`, `specs/026-cta-final-estudantes/research.md`, `specs/026-cta-final-estudantes/quickstart.md` e `specs/026-cta-final-estudantes/contracts/deploy-cta-final-estudantes.md`
- [X] T037 [P] Amostrar regressão: hero/benefícios/jornada/perfil/vagas `block_3` em `/para-estudantes` + CTAs QS/PE + Home — sem regressão causada por esta feature
- [X] T038 Executar validação final completa (SC-001–SC-009; checklist deploy; edges omit empty) com `specs/026-cta-final-estudantes/quickstart.md` e `specs/026-cta-final-estudantes/checklists/requirements.md`
- [X] T039 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → US6 (Phase 8) → Polish (Phase 9)
- US2 depende do markup/CSS base de US1 (T013–T014)
- US3 depende dos botões no Twig US1 (T014/T020); aceite completo após seed US6
- US4 valida sobretudo Foundational (T009–T010) + escopo CSS US1; aceite visual após deploy/seed
- US5 depende do form display existente + instância seedada (US6) para aceite SC-005
- US6 (hook + cex + receita) precisa de Twig/CSS mínimos (US1) no código para o front refletir o seed
- Polish após US1–US6 (inclui correção documental `11042`→`11043`)

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) 🎯 MVP
                    ├→ US2 (P1)
                    ├→ US3 (P1) ──┐
                    ├→ US4 (P1) ──┤
                    ├→ US5 (P1) ──┤  (aceite após seed)
                    └→ US6 (P1) ──┴→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002 ∥ T003 ∥ T004 ∥ T006 ∥ T007 após T001; T005 após T001
- **Foundational**: T009 ∥ T010 após T008; T011 após T008
- **US1**: T013 (CSS) ∥ início de T014 após T012; T015 após markup estável; T016 por último (ideal pós-US6)
- **US2 ∥ US3 ∥ US4**: T017–T018 com T020 e T022–T023 após T014
- **US5**: T025 ∥ T026 após seed US6; T027 por último
- **US6**: T028 ∥ T029; depois T030 → T031 → T032 → T033
- **Polish**: T034 ∥ T035 ∥ T036 ∥ T037; depois T038–T039

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (Twig suggestion + library/CSS escura isolada) — valor visual principal
2. **Responsivo + navegação + isolamento**: US2 + US3 + US4
3. **Editorial + deploy**: US5 (verificação CMS) + US6 (`11043` + cex + `cim`→`updb`→`cim`→`cr`)
4. **Fechamento**: Polish (PRD §3.6 + specify-rules + alinhar docs ao `11043` + aceite SC-001–SC-009)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T039`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias: US1 → US2 → US3 → US4 → US5 → US6 (todas P1; ordem reflete dependências de implementação)
