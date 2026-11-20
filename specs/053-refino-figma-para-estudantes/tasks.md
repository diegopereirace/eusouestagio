# Tasks: Refino Visual "Para Estudantes" — Conformidade com Figma

**Input**: Artefatos de design em `specs/053-refino-figma-para-estudantes/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/para-estudantes-refino-render.md`, `contracts/deploy-para-estudantes-refino.md`, `quickstart.md`  
**Branch sugerida**: `feat/refino-figma-para-estudantes` (a partir de `main`)  
**Predecessors**: `021`–`027`, `052-refino-figma-para-empresas` (paridade de carrossel)  
**Hook**: `custom_configs_update_11077`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md` (sem PHPUnit)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/053-refino-figma-para-estudantes` (via `.specify/feature.json`); template alinhado a `specs/052-refino-figma-para-empresas/tasks.md`  
**Config estrutural**: YAML novo do Hero estudantes + `status: false` do perfil via `drush cex`; destino `cim` → `updb` → `cim` → `cr`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar Twig/CSS/libraries/placements/hook antes de mutar tema ou `custom_configs`.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/para-estudantes-refino-render.md`, `contracts/deploy-para-estudantes-refino.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/053-refino-figma-para-estudantes/` e feature ativa `053-refino-figma-para-estudantes` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar Twig do banner estudantes (classes carousel, `data-bs-*`, CTAs, `<picture>` / LCP) em `themes/custom/default/templates/views/views-view--banners--block-para-estudantes.html.twig`
- [X] T003 [P] Inventariar library `banner_para_estudantes` vs PE (`banner_carousel_js` + `banner_carousel_cinematic`, **sem** CSS `banner-carousel` da home) em `themes/custom/default/default.libraries.yml`
- [X] T004 [P] Inventariar CSS do banner estudantes (breakpoint CTAs atual `991.98px`) e espelho PE em `themes/custom/default/assets/css/banner-para-estudantes.css` e `themes/custom/default/assets/css/banner-para-empresas.css`
- [X] T005 [P] Inventariar ordem de regiões em `themes/custom/default/templates/layout/page.html.twig` (`content_full_after_banner`: banner → `content_full` → `highlighted`) e preprocess em `themes/custom/default/default.theme`
- [X] T006 [P] Inventariar Hero Search de `/vagas` (plugin, Twig, library, placement) e ausência em `/para-estudantes` em `config/sync/block.block.default_custom_banners_vagas_hero_search.yml`, `themes/custom/default/templates/block/block--vagas-hero-search.html.twig`, `modules/custom/custom_banners/`
- [X] T007 Inventariar benefícios (Twig paragraph + CSS), perfil (`status` do placement), CTA vão (`#main-wrapper`) e último hook `11076` → próximo `11077` em `themes/custom/default/templates/paragraphs/paragraph--card-icon-text-p.html.twig`, `themes/custom/default/assets/css/beneficios-estudantes.css`, `config/sync/block.block.default_perfildestaqueestudante.yml`, `themes/custom/default/assets/css/cta-v1-para-estudantes.css`, `modules/custom/custom_configs/custom_configs.install`

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: congelar escopo negativo e confirmar superfície de mudança (sem tipos novos; path `/para-estudantes`; UUIDs; hook livre) antes das user stories.

**⚠️ CRITICAL**: Nenhuma user story de implementação começa antes desta fase.

- [X] T008 Congelar escopo negativo: **não** criar content/block/paragraph types nem fields; **não** editar `core/`/`vendor/`; **não** introduzir pipeline SCSS; **não** anexar CSS `banner-carousel` da home ao banner estudantes; **não** redesenhar jornada / `block_3` / header / footer; **não** alterar visual de `/para-empresas`, `/vagas` ou home; **não** deletar entidade `perfil_destaque_estudante`; **não** alterar CTAs de Quem Somos / Para Empresas; **não** mutar token global `--brand-orange`
- [X] T009 Confirmar `body.path-para-estudantes` / `content_full_after_banner` na rota e que o shell `#main-wrapper` fica entre `content_full` e o footer — anotar sob `themes/custom/default/templates/layout/page.html.twig` / DevTools
- [X] T010 Confirmar UUIDs e ids canônicos: benefícios `a8b9c0d1-e2f3-4456-a789-0bcdef123456`; perfil conteúdo `c0d1e2f3-a4b5-4678-c901-2def01234567` / placement `default_perfildestaqueestudante`; CTA `e1f2a3b4-c5d6-4789-d012-3ef012345678`; Hero novo UUID `e7f8a9b0-c1d2-4345-b678-0530abcdef01` / id `default_custom_banners_vagas_hero_search_estudantes`; plugin `custom_banners_vagas_hero_search` habilitado — referência em `modules/custom/custom_configs/custom_configs.install` / `config/sync/` / data-model
- [X] T011 Confirmar assets versionados `modules/custom/custom_configs/assets/beneficios-estudantes/icon-1.png` … `icon-4.png` e helpers existentes `_custom_configs_seed_beneficios_estudantes` / `_custom_configs_fill_beneficios_estudantes_icons` em `modules/custom/custom_configs/custom_configs.install`

**Checkpoint**: escopo congelado; path e UUIDs confirmados; pronto para US1.

---

## Phase 3: User Story 1 — Banner com paridade de Para Empresas (Priority: P1) 🎯 MVP

**Goal**: `#banner-carousel-para-estudantes` reusa init JS + transição cinematográfica de PE (`banner-carousel` + `banner-carousel--cinematic`), intervalo `7000`, pause on hover; CTAs saem do overlay em `767.98px` com larguras iguais; sem CSS da home (FR-001 / SC-001).  
**Independent Test Criteria**: side-by-side `/para-estudantes` vs `/para-empresas` (transição/setas/CTAs); viewport &lt;768px larguras ±2px; PE sem regressão (`quickstart.md` seção B / `contracts/para-estudantes-refino-render.md` §1).

- [X] T012 [US1] Adicionar dependencies `default/banner_carousel_js` + `default/banner_carousel_cinematic` (manter `bootstrap`; **não** anexar CSS `banner-carousel` da home) em `banner_para_estudantes` em `themes/custom/default/default.libraries.yml` (research R1)
- [X] T013 [US1] Acrescentar classes `banner-carousel` + `banner-carousel--cinematic` (+ `banner-carousel--single` se um slide) e atributos `data-bs-ride` / `data-bs-interval="7000"` / `data-bs-touch="true"` / `data-bs-pause="hover"` no `.carousel` em `themes/custom/default/templates/views/views-view--banners--block-para-estudantes.html.twig`
- [X] T014 [US1] Separar media queries: ≤991.98px só aspect/setas; ≤767.98px CTAs `position: static` + flex larguras iguais (`flex: 1 1 0`, empilhar `1 1 100%` ~&lt;380px), espelhando PE, em `themes/custom/default/assets/css/banner-para-estudantes.css` (research R2)
- [X] T015 [US1] Validar SC-001 e cenários US1 (paridade transição/setas/CTAs; mobile &lt;768; PE intacto; LCP 1º slide) conforme `specs/053-refino-figma-para-estudantes/quickstart.md` seção B e `specs/053-refino-figma-para-estudantes/contracts/para-estudantes-refino-render.md` §1

**Checkpoint**: visitante percebe carrossel de estudantes equivalente ao de PE — MVP visual.

---

## Phase 4: User Story 2 — Hero Search abaixo do banner (Priority: P1)

**Goal**: `page.html.twig` renderiza `highlighted` entre banner e `content_full` só quando `content_full_after_banner`; Hero reusa o mesmo plugin/Twig/library de `/vagas` (placement criado no hook US6) (FR-002–004 / SC-002).  
**Independent Test Criteria**: após placement existir, Hero logo abaixo do banner; submit → `/vagas` com query; `/vagas` sem regressão (`quickstart.md` seção C / contract §2).  
**Nota**: validação E2E completa desta história depende do placement garantido em US6 (`T033`/`T035`).

- [X] T016 [US2] Em `content_full_after_banner`, renderizar `page.highlighted` **entre** `page.banner` e `page.content_full` e **não** renderizá-lo de novo mais abaixo em `themes/custom/default/templates/layout/page.html.twig` (research R3)
- [X] T017 [P] [US2] Confirmar que o Twig/library do Hero (`block--vagas-hero-search.html.twig`, `vagas_hero_search`, IDs fixos, GET `title`/`cidade`/`cursos` → `view.vagas.page_1`) permanece único e reutilizável sem fork em `themes/custom/default/templates/block/block--vagas-hero-search.html.twig` e `themes/custom/default/default.libraries.yml`
- [X] T018 [US2] Validar estrutura/ordem do markup (região `highlighted` após banner) e, pós-US6, SC-002 / FR-004 conforme `specs/053-refino-figma-para-estudantes/quickstart.md` seção C e `specs/053-refino-figma-para-estudantes/contracts/para-estudantes-refino-render.md` §2

**Checkpoint**: ordem de regiões pronta para o segundo placement do Hero.

---

## Phase 5: User Story 3 — Benefícios alinhados ao Figma (Priority: P1)

**Goal**: cards com ícone ~64px no topo à esquerda; título `font-weight: 700`; descrição `#45464D`; grade 1/2/4 já existente preservada (FR-005–007 / SC-003). Copy legado (subtítulo seed `11035`, “Filtros Inteligentes”) corrigida no hook US6.  
**Independent Test Criteria**: desktop 4 cards numa linha; tablet 2; mobile 1; ícone topo-esquerda; cor/peso tipográficos (`quickstart.md` seção D / contract §3).

- [X] T019 [US3] Trocar alinhamento central (`text-center` / `align-items-center`) por topo-esquerda (`text-start` / `align-items-start`); manter `col-12 col-md-6 col-lg-3` em `themes/custom/default/templates/paragraphs/paragraph--card-icon-text-p.html.twig` (research R4)
- [X] T020 [P] [US3] Ajustar `.be-card__text` para `#45464D` e `.be-card__title` para `font-weight: 700` (caixa 64px, borda/sombra e grid intactos) em `themes/custom/default/assets/css/beneficios-estudantes.css`
- [X] T021 [US3] Validar SC-003 e cenários US3 (grade, ícone, tipografia; copy canônica pós-US6) conforme `specs/053-refino-figma-para-estudantes/quickstart.md` seção D e `specs/053-refino-figma-para-estudantes/contracts/para-estudantes-refino-render.md` §3

**Checkpoint**: cards de benefícios fecham lacunas visuais do Figma.

---

## Phase 6: User Story 4 — Perfil em destaque fora da superfície pública (Priority: P1)

**Goal**: placement `default_perfildestaqueestudante` com `status: false`; entidade de conteúdo permanece (FR-008 / SC-004).  
**Independent Test Criteria**: texto “Seu perfil em destaque” ausente; `cget … status` → false; 2ª updb no-op (`quickstart.md` seção E).  
**Nota**: implementação via início do `custom_configs_update_11077`; expansão do hook (Hero + benefícios) em US6.

- [X] T022 [US4] Criar `custom_configs_update_11077` idempotente que carrega `default_perfildestaqueestudante` e aplica `setStatus(FALSE)` + save (no-op se já false; mensagem clara se config ausente; **não** deletar `block_content`) em `modules/custom/custom_configs/custom_configs.install` (research R6)
- [X] T023 [US4] Validar SC-004 (seção ausente no HTML; status false; reexecução segura) conforme `specs/053-refino-figma-para-estudantes/quickstart.md` seção E e `specs/053-refino-figma-para-estudantes/contracts/para-estudantes-refino-render.md` §4 — após `drush updb` (pode ser o mesmo run de US6)

**Checkpoint**: perfil obsoleto oculto na landing; entidade reversível no banco.

---

## Phase 7: User Story 5 — CTA final colado no footer (Priority: P1)

**Goal**: vão CTA→footer = 0px em `body.path-para-estudantes` colapsando `#main-wrapper` / `.section`; sem tocar CTAs de outras rotas (FR-009 / SC-005).  
**Independent Test Criteria**: 0px entre fim do gradiente do CTA e início de `.site-footer-custom`; Quem Somos / PE intactos (`quickstart.md` seção F / contract §5).

- [X] T024 [US5] Em `body.path-para-estudantes`, zerar padding/margin de `#main-wrapper`, `#main`, `#main .section`; `margin-bottom: 0` em `#block-default-ctav1paraestudantes` e `.content-full--after-banner > *:last-child`; `margin-top: 0` em `.site-footer-custom` — **sem** `border-top` branco e **sem** editar `cta-v1.css` — em `themes/custom/default/assets/css/cta-v1-para-estudantes.css` (research R7)
- [X] T025 [US5] Validar SC-005 e cenários US5 (vão 0 desktop/mobile; `/quem-somos` e `/para-empresas` inalterados) conforme `specs/053-refino-figma-para-estudantes/quickstart.md` seção F e `specs/053-refino-figma-para-estudantes/contracts/para-estudantes-refino-render.md` §5

**Checkpoint**: faixa escura do CTA encontra o footer sem vão.

---

## Phase 8: User Story 6 — Deploy automatizado (Priority: P2)

**Goal**: completar `custom_configs_update_11077` (Hero placement + benefícios fill-if-empty/legado + reativar benefícios se disabled); `updb` → `cex` → `cr`; destino `cim` → `updb` → `cim` → `cr` (FR-010–011 / SC-006 / SC-007).  
**Independent Test Criteria**: receita A do quickstart; 2ª updb no-op; Hero aparece; copy editorial divergente preservada (`quickstart.md` seções A+C+D / deploy contract).

- [X] T026 [US6] Expandir `custom_configs_update_11077`: ensure placement `default_custom_banners_vagas_hero_search_estudantes` (UUID `e7f8a9b0-c1d2-4345-b678-0530abcdef01`, plugin `custom_banners_vagas_hero_search`, região `highlighted`, weight `-50`, pages `/para-estudantes`, status true; recriar se plugin errado; no-op se correto) em `modules/custom/custom_configs/custom_configs.install` (research R3/R9)
- [X] T027 [US6] No mesmo hook: garantir benefícios UUID `a8b9…` fill-if-empty (reusar `_custom_configs_seed_beneficios_estudantes` / `_custom_configs_fill_beneficios_estudantes_icons`); corrigir **somente** legado conhecido — subtítulo seed `11035` → frase Figma; título exato `Filtros Inteligentes` → `Filtros inteligentes`; reativar `default_beneficiosestudantes` se `status: false`; nunca sobrescrever copy editorial divergente — em `modules/custom/custom_configs/custom_configs.install` (research R5)
- [X] T028 [US6] Executar na origem `drush updb -y` → `drush cex -y` → `drush cr`; versionar `config/sync/block.block.default_custom_banners_vagas_hero_search_estudantes.yml` (novo) e `config/sync/block.block.default_perfildestaqueestudante.yml` (`status: false`) — **não** inventar YAML à mão — conforme `specs/053-refino-figma-para-estudantes/contracts/deploy-para-estudantes-refino.md`
- [X] T029 [US6] Validar SC-006/SC-007 (composição sem admin manual; 2ª updb no-op; Hero + benefícios + perfil oculto) conforme `specs/053-refino-figma-para-estudantes/quickstart.md` seções A, C, D, E

**Checkpoint**: produção recebe a composição só com Git + cim/updb/cim/cr — zero admin manual.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Objetivo**: regressão seções fora de escopo, PRD cirúrgico, cache e aceite SC-001–SC-007.

- [X] T030 [P] Smoke de regressão: jornada + `block_3` + “Ver todas as vagas” permanecem; home / PE / `/vagas` Hero sem regressão atribuível a esta feature — conforme `specs/053-refino-figma-para-estudantes/quickstart.md` seção G
- [X] T031 [P] Atualizar `PRD.md` §3.6 de forma cirúrgica: composição `/para-estudantes` = banner → Hero Search → benefícios → jornada → `block_3` → CTA; perfil fora da superfície pública; citar hook `11077`
- [X] T032 [P] Confirmar bloco SPECKIT em `.cursor/rules/specify-rules.mdc` aponta para `053-refino-figma-para-estudantes` com `spec.md` / `plan.md` / `tasks.md`
- [X] T033 Executar limpeza de cache Drupal após Twig/CSS/JS/config (`docker compose exec -T drupal vendor/bin/drush cr` ou equivalente)
- [X] T034 Executar validação final completa (SC-001–SC-007; edges: 1 slide banner, Hero em PE+vagas sem conflito de ID, ícones faltando em `public://`, viewport 768–991px) com `specs/053-refino-figma-para-estudantes/quickstart.md` e `specs/053-refino-figma-para-estudantes/checklists/requirements.md`

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → US6 (Phase 8) → Polish (Phase 9)
- US1 (banner) é MVP e independente de Hero/benefícios
- US2 (Twig ordem) antes de validar Hero E2E; placement concreto em US6
- US3 (CSS/Twig benefícios) ∥ US5 (CSS vão CTA) após Foundational — arquivos distintos; copy legado em US6
- US4 inicia o hook (`11077` perfil); US6 completa o mesmo hook (Hero + benefícios) + cex — **sequenciar** US4 → US6
- Polish após US1–US6

## Dependency Graph

```text
Phase1 → Phase2 (escopo + path + UUIDs)
           ├→ US1 (P1) banner library/Twig/CSS ── MVP
           ├→ US2 (P1) page.html.twig reorder highlighted
           ├→ US3 (P1) benefícios Twig/CSS          ⎫ paralelizáveis
           ├→ US5 (P1) CSS vão CTA→footer           ⎭ entre si (após Foundational)
           └→ US4 (P1) hook 11077 início (perfil false)
                 └→ US6 (P2) hook completo + updb/cex/cr
                      └→ Polish (regressão, PRD, cr, SC-001–SC-007)
                           ↳ revalida US2/US3/US4 pós-placement/seed
```

## Parallel Execution Opportunities

- **Setup**: T002–T006 em paralelo após T001; T007 após inventário tema
- **Foundational**: T009 ∥ T010 ∥ T011 após T008
- **US1**: T012 → T013; T014 [CSS]; T015 por último
- **US2**: T016 → T017 [P]; T018 após US6 para E2E completo
- **US3 ∥ US5**: T019–T020 ∥ T024; T021/T025 após cada história
- **US4 → US6**: T022 → T026 → T027 → T028 → T023/T029
- **Polish**: T030 ∥ T031 ∥ T032; depois T033–T034

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (paridade banner estudantes ↔ PE)
2. **Ordem da página**: US2 (highlighted entre banner e content_full)
3. **Mid-page + fechamento visual**: US3 (benefícios) ∥ US5 (vão CTA) em paralelo
4. **Ocultar legado**: US4 (início do `11077` — perfil `status: false`)
5. **Deploy**: US6 (Hero + benefícios no hook + `updb`/`cex`/`cr`)
6. **Fechamento**: Polish — regressão, PRD, `drush cr`, SC-001–SC-007

## Format Validation

- Todas as tarefas usam checkbox `- [ ]`, ID sequencial (`T001`…`T034`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; validação manual no quickstart)
- Ordem de histórias: US1 (P1 MVP) → US2 (P1) → US3 (P1) → US4 (P1) → US5 (P1) → US6 (P2 deploy)
