# Tasks: Refino Visual "Para Empresas" — Conformidade com Figma

**Input**: Artefatos de design em `specs/052-refino-figma-para-empresas/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/para-empresas-refino-render.md`, `contracts/deploy-para-empresas-refino.md`, `quickstart.md`  
**Branch sugerida**: `feat/refino-figma-para-empresas` (a partir de `main`)  
**Predecessors**: `019-para-empresas-page`, `020-depoimentos-empresas`, `042-nossos-diferenciais-ui`, `047-refino-figma-quem-somos` (padrão de refino), `11028` (shell `page`)  
**Hook**: `custom_configs_update_11076`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md` (sem PHPUnit)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/052-refino-figma-para-empresas` (via `.specify/feature.json`); template alinhado a `specs/047-refino-figma-quem-somos/tasks.md`  
**Config estrutural**: esperado **nenhum** YAML novo; origem `drush cex` só se houver diff; destino `cim` → `updb` → `cim` → `cr`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar Twig/CSS/JS/libraries/hook antes de mutar tema ou `custom_configs`.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/para-empresas-refino-render.md`, `contracts/deploy-para-empresas-refino.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/052-refino-figma-para-empresas/` e feature ativa `052-refino-figma-para-empresas` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar Twig do banner PE (carousel classes, `data-bs-*`, CTAs overlay, `<picture>` / placeholder) em `themes/custom/default/templates/views/views-view--banners--block-para-empresas.html.twig`
- [X] T003 [P] Inventariar library `banner_para_empresas` vs `banner_carousel` (JS `banner-carousel.js`, CSS home) em `themes/custom/default/default.libraries.yml` e `themes/custom/default/assets/js/banner-carousel.js`
- [X] T004 [P] Inventariar CSS do banner PE (breakpoint CTAs `991.98px`, seletores `body.node--type-para-empresas`) em `themes/custom/default/assets/css/banner-para-empresas.css`
- [X] T005 [P] Inventariar CSS Nossos Diferenciais (cores `#4a5d73`, decos `.nd-media__deco-*`, alinhamento) em `themes/custom/default/assets/css/nossos-diferenciais.css`
- [X] T006 [P] Inventariar Depoimentos (dots `.is-active`, sync `data-carousel-dot`) em `themes/custom/default/assets/css/depoimentos-carousel.css` e `themes/custom/default/assets/js/carousel-center.js`
- [X] T007 Inventariar CTA PE: ausência de override, template genérico e padrão de preprocess/role (estudantes/vagas) em `themes/custom/default/templates/block/block--block-cta-v1.html.twig`, `themes/custom/default/templates/block/block--default-ctav1paraestudantes.html.twig`, `themes/custom/default/default.theme`; confirmar último hook `custom_configs_update_11075` → próximo livre `11076` em `modules/custom/custom_configs/custom_configs.install`

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: congelar escopo negativo e confirmar superfície de mudança (sem tipos novos; path `/para-empresas`; hook livre) antes das user stories.

**⚠️ CRITICAL**: Nenhuma user story de implementação começa antes desta fase.

- [X] T008 Congelar escopo negativo: **não** criar content/block/paragraph types; **não** editar `core/`/`vendor/`; **não** introduzir pipeline SCSS; **não** anexar CSS `banner-carousel` ao PE; **não** redesenhar Metodologia / Benefícios / O Que Fazemos / header / footer; **não** alterar visual da home; **não** mutar token global `--brand-orange`; **não** colocar lógica B2B no template genérico `block--block-cta-v1.html.twig`
- [X] T009 Confirmar que `body.path-para-empresas` (ou classes de path equivalentes) está disponível na página `/para-empresas` pós-`11028` e que seletores legado `node--type-para-empresas` não casam — anotar no inventário sob `themes/custom/default/assets/css/banner-para-empresas.css` / DevTools
- [X] T010 Confirmar UUID CTA PE `a7b8c9d0-e1f2-4345-a678-90abcdef0123`, placement `default_ctav1paraempresas`, e rota canônica `/painel/empresa/vagas/nova` (seed/PRD) antes do hook — referência em `modules/custom/custom_configs/custom_configs.install` / data-model

**Checkpoint**: escopo congelado; path e UUID confirmados; pronto para US1.

---

## Phase 3: User Story 1 — Banner PE com paridade de carrossel da home (Priority: P1) 🎯 MVP

**Goal**: `#banner-carousel-para-empresas` reusa init JS da home (`banner-carousel` + dependency library), atributos `data-bs-pause="hover"`, artes reais sem placeholder `500×600`, desktop overlay íntegro (FR-001/004/005 / SC-001 / SC-008).  
**Independent Test Criteria**: side-by-side `/` vs `/para-empresas` (transição/setas); Network sem `500×600`; home sem regressão (`quickstart.md` seção B / `contracts/para-empresas-refino-render.md` §1).

- [X] T011 [US1] Adicionar dependency da library `banner_carousel` (JS init, **sem** CSS da home) em `banner_para_empresas` em `themes/custom/default/default.libraries.yml` (research R1)
- [X] T012 [US1] Garantir classe `banner-carousel` no `.carousel` PE + `data-bs-pause="hover"` (e atributos Bootstrap alinhados ao contrato) em `themes/custom/default/templates/views/views-view--banners--block-para-empresas.html.twig`
- [X] T013 [P] [US1] Substituir seletores `body.node--type-para-empresas` por `body.path-para-empresas` (ou wrappers já isolados) nas regras de região full-width em `themes/custom/default/assets/css/banner-para-empresas.css` (research R3 / FR-005)
- [X] T014 [US1] Auditar Twig do banner para **proibir** placeholder genérico `500×600` e preservar fallback mobile=desktop + LCP `eager`/`fetchpriority` no 1º slide em `themes/custom/default/templates/views/views-view--banners--block-para-empresas.html.twig` (research R4)
- [X] T015 [US1] Validar SC-001/SC-008 e cenários US1 (paridade transição/setas; sem placeholder; overlay desktop; home intacta) conforme `specs/052-refino-figma-para-empresas/quickstart.md` seção B e `specs/052-refino-figma-para-empresas/contracts/para-empresas-refino-render.md` §1

**Checkpoint**: visitante percebe carrossel PE equivalente à home — MVP visual.

---

## Phase 4: User Story 2 — CTAs do banner com largura uniforme em &lt;768px (Priority: P1)

**Goal**: breakpoint CTAs de `991.98px` → `767.98px`; em `<768px` botões com larguras iguais (±2px) lado a lado ou empilhados com gap uniforme; `≥768px` overlay preservado (FR-002/003 / SC-002).  
**Independent Test Criteria**: viewports 402×872 e ~767px — medir larguras/gap; desktop overlay íntegro (`quickstart.md` seção C).

- [X] T016 [US2] Mover regras de empilhamento/fora-do-overlay dos CTAs de `@media (max-width: 991.98px)` para `@media (max-width: 767.98px)` em `themes/custom/default/assets/css/banner-para-empresas.css` (research R2)
- [X] T017 [US2] Em `<768px`, aplicar flex em `.banner-para-empresas__ctas` com filhos `flex: 1 1 0` (larguras iguais) + `gap` uniforme; permitir `flex-wrap` → empilhar `flex: 1 1 100%` em viewports muito estreitos em `themes/custom/default/assets/css/banner-para-empresas.css`
- [X] T018 [US2] Validar SC-002 e cenários US2 (larguras ±2px; gap; desktop ≥768px intacto) conforme `specs/052-refino-figma-para-empresas/quickstart.md` seção C

**Checkpoint**: CTAs mobile alinhados ao breakpoint 768px do site/Figma.

---

## Phase 5: User Story 3 — Nossos Diferenciais alinhados ao Figma (Priority: P1)

**Goal**: texto descritivo `#4E4E4E`; desktop com anel verde atrás + dot navy na frente e alinhamento topo ±4px; mobile `<768px` sem decos absolutas / sem scroll-x (FR-006–009 / SC-003 / SC-004).  
**Independent Test Criteria**: computed `#4E4E4E`; checklist camadas Figma; mobile sem overflow (`quickstart.md` seção D).

- [X] T019 [US3] Alterar `.nd-header__subtitle` e `.nd-item__desc` de `#4a5d73` para `#4E4E4E` (títulos cicláveis intactos) em `themes/custom/default/assets/css/nossos-diferenciais.css` (research R5)
- [X] T020 [P] [US3] Em `@media (max-width: 767.98px)`, ocultar `.nd-media__deco-circle`, `.nd-media__deco-dot`, `.nd-media__deco-ring` e reduzir padding especial da mídia para fluxo imagem→textos sem overflow em `themes/custom/default/assets/css/nossos-diferenciais.css`
- [X] T021 [P] [US3] Ajuste fino desktop: `align-items: start` / gap para topo imagem↔lista ±4px; manter z-index anel atrás (`0`) e dot na frente (`2`) em `themes/custom/default/assets/css/nossos-diferenciais.css`
- [X] T022 [US3] Validar SC-003/SC-004 e cenários US3 (cor, camadas, alinhamento, mobile sem scroll-x) conforme `specs/052-refino-figma-para-empresas/quickstart.md` seção D

**Checkpoint**: ND fecha lacunas de tipografia e mobile sem regressão das camadas `042`.

---

## Phase 6: User Story 4 — Depoimentos navegam pelos bullets (Priority: P1)

**Goal**: bullet ativo com `var(--brand-orange)`; clique N → slide N; sync pós-swipe; corrigir JS só se bug reproduzido (FR-010–012 / SC-005).  
**Independent Test Criteria**: 4 bullets em sequência; `is-active` + cor laranja; swipe sincroniza (`quickstart.md` seção E).

- [X] T023 [US4] Trocar cor de `.depoimentos-empresas__dot.is-active` de `var(--brand-navy)` para `var(--brand-orange)` (inativos `#c5cbd3`) em `themes/custom/default/assets/css/depoimentos-carousel.css` (research R6) — **sem** redefinir o token global
- [X] T024 [US4] Reproduzir QA de sync (clique N→slide N; swipe→`is-active`); se bug confirmado, corrigir pontualmente (`pointer-events`, flag `jumping`, `data-index`) em `themes/custom/default/assets/js/carousel-center.js` — sem reescrever o motor
- [X] T025 [US4] Validar SC-005 e cenários US4 (navegação, sync, contraste ativo) conforme `specs/052-refino-figma-para-empresas/quickstart.md` seção E e `specs/052-refino-figma-para-empresas/contracts/para-empresas-refino-render.md` §3

**Checkpoint**: social proof paginável e visualmente coerente com o Figma.

---

## Phase 7: User Story 5 — CTA final condicional por role `empresa` (Priority: P1)

**Goal**: override Twig PE + preprocess `is_empresa`; anônimo/sem empresa: cadastro ativo, botão 2 ausente do DOM; empresa: botão 1 desabilitado “Empresa Cadastrada” + “Abrir Vaga”; lazy se houver `<img>` (FR-013–017 / SC-006).  
**Independent Test Criteria**: matriz 3 perfis; botão 2 ausente no HTML para anônimo (`quickstart.md` seção F / contract §4).

- [X] T026 [US5] Implementar `default_preprocess_block__default_ctav1paraempresas` expondo `is_empresa` (autenticado + role `empresa`, critério do topo) em `themes/custom/default/default.theme` (research R7)
- [X] T027 [US5] Criar override `themes/custom/default/templates/block/block--default-ctav1paraempresas.html.twig`: botão 1 link vs `<button disabled>`/equivalente “Empresa Cadastrada”; botão 2 **somente** se `is_empresa` e URI `field_link_2` não vazia; `loading="lazy"` em `<img>` se existir — espelhar padrão de `block--default-ctav1paraestudantes.html.twig`
- [X] T028 [P] [US5] Estilizar estado desabilitado cinza-alaranjado fosco **somente** sob o escopo PE (não alterar CTA Quem Somos / vagas / estudantes) em `themes/custom/default/assets/css/cta-v1.css` ou CSS dedicado PE anexado à library do bloco
- [X] T029 [US5] Validar SC-006 e cenários US5 (3 sessões; ausência de markup do botão 2 sem role; fail-safe `field_link_2` vazio) conforme `specs/052-refino-figma-para-empresas/quickstart.md` seção F

**Checkpoint**: conversão B2B com controle de acesso no servidor.

---

## Phase 8: User Story 6 — Deploy automatizado do CTA legado + refinamentos (Priority: P1)

**Goal**: `custom_configs_update_11076` idempotente corrige legado “Falar com Especialista” → “Abrir Vaga” + URI canônica; `cex` se diff; destino `cim` → `updb` → `cim` → `cr` (FR-018–021 / SC-007).  
**Independent Test Criteria**: receita A do quickstart; 2ª `updb` no-op; editorial divergente preservado (`quickstart.md` seções A+G / deploy contract).

- [X] T030 [US6] Implementar `custom_configs_update_11076` idempotente: load UUID `a7b8c9d0-e1f2-4345-a678-90abcdef0123`; se `field_link_2` legado (`Falar com Especialista` / vazio+URI legada) → `Abrir Vaga` + `internal:/painel/empresa/vagas/nova`; nunca sobrescrever título editorial divergente; mensagem clara se bloco/bundle ausente — em `modules/custom/custom_configs/custom_configs.install` (research R8)
- [X] T031 [P] [US6] Extrair/reusar helper privado de correção legado CTA PE no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T032 [US6] Executar na origem `drush updb -y` → `drush cex -y` → `drush cr`; confirmar se `config/sync` gerou diff (esperado: nenhum) e versionar só o necessário conforme `specs/052-refino-figma-para-empresas/contracts/deploy-para-empresas-refino.md`
- [X] T033 [US6] Validar SC-007 (deploy atrasado + 2ª updb no-op + preservação editorial) conforme `specs/052-refino-figma-para-empresas/quickstart.md` seções A e G

**Checkpoint**: produção recebe refinamentos só com Git + cim/updb/cim/cr — zero admin manual.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Objetivo**: regressão seções fora de escopo, PRD cirúrgico, cache e aceite SC-001–SC-008.

- [X] T034 [P] Smoke de regressão SC-008 (Metodologia, Benefícios, O Que Fazemos, header/footer, home) conforme `specs/052-refino-figma-para-empresas/quickstart.md` seção H
- [X] T035 [P] Atualizar `PRD.md` §3.1.0b / §3.6 de forma cirúrgica: CTAs PE por role `empresa`, refinamentos banner/ND/depoimentos, hook `11076`
- [X] T036 [P] Confirmar bloco SPECKIT em `.cursor/rules/specify-rules.mdc` aponta para `052-refino-figma-para-empresas` com `spec.md` / `plan.md` / `tasks.md`
- [X] T037 Executar limpeza de cache Drupal após Twig/CSS/JS/config (`docker compose exec -T drupal vendor/bin/drush cr` ou equivalente)
- [X] T038 Executar validação final completa (SC-001–SC-008; edges: 1 slide banner, 1 depoimento, multi-role com `empresa`, viewport 768–991px) com `specs/052-refino-figma-para-empresas/quickstart.md` e `specs/052-refino-figma-para-empresas/checklists/requirements.md`

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → US6 (Phase 8) → Polish (Phase 9)
- US1 e US2 compartilham `banner-para-empresas.css` / Twig do banner — sequenciar (US2 após US1)
- US3 (ND CSS) ∥ US4 (depoimentos) após Foundational — arquivos distintos
- US5 (CTA) independente de US3/US4; pode paralelizar após Foundational se recursos bastarem
- US6 (hook/cex/updb) após US5 (mesmo domínio CTA) e preferencialmente após Twig/CSS mínimos (US1–US5)
- Polish após US1–US6

## Dependency Graph

```text
Phase1 → Phase2 (escopo + path + UUID)
           ├→ US1 (P1) banner JS/library/Twig/path CSS ── MVP
           │     └→ US2 (P1) CTAs mobile 768px (mesmo CSS banner)
           ├→ US3 (P1) ND cor + mobile decos          ⎫ paralelizáveis
           ├→ US4 (P1) depoimentos cor + sync QA      ⎭ entre si
           ├→ US5 (P1) CTA Twig + preprocess + CSS
           └→ US6 (P1) hook 11076 + cex/updb
                └→ Polish (regressão, PRD, cr, SC-001–SC-008)
```

## Parallel Execution Opportunities

- **Setup**: T002–T006 em paralelo após T001; T007 após inventário tema
- **Foundational**: T009 ∥ T010 após T008
- **US1**: T011 → T012; T013 [P] CSS path; T014 Twig anti-placeholder; T015 por último
- **US2**: T016 → T017 → T018 (sequencial no mesmo CSS)
- **US3 ∥ US4**: T019–T021 ∥ T023–T024; T022/T025 após cada história
- **US5**: T026 → T027; T028 [P] CSS; T029 por último
- **US6**: T031 junto a T030; T032–T033 após hook
- **Polish**: T034 ∥ T035 ∥ T036; depois T037–T038

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (paridade carrossel PE ↔ home)
2. **Mobile banner**: US2 (breakpoint 768px + larguras iguais)
3. **Seções mid-page**: US3 (ND) + US4 (depoimentos) em paralelo
4. **Conversão B2B**: US5 (CTA por role)
5. **Deploy**: US6 (`11076` + cex + cim/updb/cim/cr)
6. **Fechamento**: Polish — regressão, PRD, `drush cr`, SC-001–SC-008

## Format Validation

- Todas as tarefas usam checkbox `- [ ]`, ID sequencial (`T001`…`T038`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; validação manual no quickstart)
- Ordem de histórias: US1 (P1 MVP) → US2 (P1) → US3 (P1) → US4 (P1) → US5 (P1) → US6 (P1 deploy)
