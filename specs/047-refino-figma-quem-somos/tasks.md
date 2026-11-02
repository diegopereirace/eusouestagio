# Tasks: Refino Visual "Quem Somos" — Conformidade com Figma

**Input**: Artefatos de design em `specs/047-refino-figma-quem-somos/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/quem-somos-refino-render.md`, `quickstart.md`  
**Branch sugerida**: `feat/refino-figma-quem-somos` (a partir de `main`)  
**Predecessor**: features `009`–`015` (banner, Sobre nós, Missão/Visão, Diferenciais, Impact Numbers, CTA v1)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md` (sem PHPUnit)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/047-refino-figma-quem-somos` (via `.specify/feature.json`); template alinhado a `specs/015-cta-v1-quem-somos/tasks.md` / `specs/043-nossa-metodologia-ui/tasks.md`  
**Config estrutural**: 3 Image Styles novos + displays; `custom_configs_update_11069` idempotente; origem `drush cex`; destino `cim` → `updb` → `cim` → `cr`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar Twig/CSS/displays/assets/hook antes de mutar tema, config ou `custom_configs`.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/quem-somos-refino-render.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/047-refino-figma-quem-somos/` e feature ativa `047-refino-figma-quem-somos` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar Twig do banner (coluna `.banner-quem-somos__media`, `<picture>` com source mobile atual, `eager`/`fetchpriority="high"`) em `themes/custom/default/templates/views/views-view-unformatted--banners--block-quem-somos.html.twig`
- [X] T003 [P] Inventariar Twig do node (foto Sobre nós via `file_url`/URI cru, Missão/Visão `background-image` URI cru, `loading="lazy"`) em `themes/custom/default/templates/content/node--quem-somos.html.twig`
- [X] T004 [P] Inventariar CSS das seções alvo em `themes/custom/default/assets/css/banner-quem-somos.css`, `themes/custom/default/assets/css/layout-sobre-nos.css`, `themes/custom/default/assets/css/quem-somos-missao-visao.css`, `themes/custom/default/assets/css/diferenciais-quem-somos.css` e `themes/custom/default/assets/css/cta-v1.css`
- [X] T005 [P] Inventariar displays com `image_style: ''` em `config/sync/core.entity_view_display.node.quem_somos.default.yml` e `config/sync/core.entity_view_display.paragraph.diferencial_simples_p.default.yml`; confirmar ausência dos styles `quem_somos_*` / `diferencial_simples_icon` sob `config/sync/image.style.*.yml`
- [X] T006 Confirmar último hook `custom_configs_update_11068` → próximo livre `11069` em `modules/custom/custom_configs/custom_configs.install`; inventariar assets provisórios `icon-1.png`…`icon-8.png` em `modules/custom/custom_configs/assets/diferenciais-quem-somos/` e padrão `|image_style()` no tema (`themes/custom/default/templates/block/block--block-cto.html.twig`, `themes/custom/default/default.theme`)

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: criar os 3 Image Styles e aplicar `image_style` nos displays alvo (defensivo) — pré-requisito compartilhado de US2/US3/US4/US6. Congelar escopo negativo.

**⚠️ CRITICAL**: Nenhuma user story de implementação começa antes desta fase (US1 usa só Twig/CSS do banner, mas o inventário/escopo desta fase evita regressão e define a superfície de config).

- [X] T007 Congelar escopo negativo: **não** criar content/block/paragraph types; **não** alterar Impact Numbers estruturalmente; **não** editar `core/`/`vendor/`; **não** introduzir pipeline SCSS; **não** usar `--brand-orange` global; **não** alterar CSS de `cta-v1-vagas.css` / `cta-v1-para-estudantes.css`; **não** mudar copy/botões do banner além da visibilidade da mídia
- [X] T008 [P] Criar Image Style `quem_somos_sobre_nos` (`image_scale_and_crop` 864×538, anchor `center-center`) via admin/API e exportar em `config/sync/image.style.quem_somos_sobre_nos.yml`
- [X] T009 [P] Criar Image Style `quem_somos_missao_visao` (`image_scale` width 1920, height null/upscale false) via admin/API e exportar em `config/sync/image.style.quem_somos_missao_visao.yml`
- [X] T010 [P] Criar Image Style `diferencial_simples_icon` (`image_scale` 160×160) via admin/API e exportar em `config/sync/image.style.diferencial_simples_icon.yml`
- [X] T011 Aplicar `image_style: quem_somos_sobre_nos` em `field_imagem` e `image_style: quem_somos_missao_visao` em `field_imagem_desktop` no view display e exportar em `config/sync/core.entity_view_display.node.quem_somos.default.yml`
- [X] T012 Aplicar `image_style: diferencial_simples_icon` em `field_image` do paragraph e exportar em `config/sync/core.entity_view_display.paragraph.diferencial_simples_p.default.yml`
- [X] T013 Confirmar/exportar configs estruturais com `drush cex -y` (Image Styles + displays) sob `config/sync/` — sem inventar YAML na mão além do fluxo admin/API → cex

**Checkpoint**: `cim` traz os 3 styles e displays; front ainda pode mostrar URI cru no Twig até US2/US3; ícones ainda provisórios até US4/US6.

---

## Phase 3: User Story 1 — Visitante mobile não carrega a imagem do banner (Priority: P1) 🎯 MVP

**Goal**: viewport `<768px` — coluna `.banner-quem-somos__media` oculta (`d-none d-md-block`) e **zero** request de arte do banner; desktop preserva LCP `eager` + `fetchpriority="high"` (FR-004/005 / SC-001).  
**Independent Test Criteria**: DevTools Network Img em `<768px` sem request de arte; `≥768px` imagem desktop visível (`quickstart.md` seção B / `contracts/quem-somos-refino-render.md` §1).

- [X] T014 [US1] Inverter `<picture>` para `<source media="(min-width: 768px)" srcset="…">` + `<img src>` placeholder data-URI 1×1 com `alt` preservado, `loading="eager"` e `fetchpriority="high"`; remover source `max-width:767.98px` em `themes/custom/default/templates/views/views-view-unformatted--banners--block-quem-somos.html.twig` (research R1)
- [X] T015 [P] [US1] Adicionar `d-none d-md-block` (ou equivalente) na coluna `.banner-quem-somos__media` em `themes/custom/default/templates/views/views-view-unformatted--banners--block-quem-somos.html.twig`
- [X] T016 [P] [US1] Ajustar CSS complementar do banner **somente se** necessário para layout com coluna oculta no mobile (sem alterar copy/botões/tokens) em `themes/custom/default/assets/css/banner-quem-somos.css`
- [X] T017 [US1] Validar SC-001 e cenários US1 (mobile zero request; desktop LCP intacto; copy/CTAs idênticos) conforme `specs/047-refino-figma-quem-somos/quickstart.md` seção B e `specs/047-refino-figma-quem-somos/contracts/quem-somos-refino-render.md`

**Checkpoint**: visitante mobile não baixa a arte do banner — MVP de performance.

---

## Phase 4: User Story 2 — Visitante vê a foto de "Sobre nós" otimizada (Priority: P1)

**Goal**: foto `field_imagem` via Image Style `quem_somos_sobre_nos`, `loading="lazy"`, decorações `::before`/`::after` intactas, gap texto↔imagem conforme Figma (FR-006–008 / SC-002).  
**Independent Test Criteria**: `src` aponta para `/files/styles/quem_somos_sobre_nos/...`; anel verde + círculo laranja; gap ±4px (`quickstart.md` seção C).

- [X] T018 [US2] Trocar URL crua da foto Sobre nós por `|image_style('quem_somos_sobre_nos')` (ou helper equivalente) mantendo `loading="lazy"` e markup sem elementos decorativos extras em `themes/custom/default/templates/content/node--quem-somos.html.twig` (research R3)
- [X] T019 [P] [US2] Validar/ajustar padding/gap texto↔imagem sob seletores `.sobre-nos` / layout existente (±4px Figma; sem scroll horizontal) em `themes/custom/default/assets/css/layout-sobre-nos.css` — **manter** `::before` anel verde e `::after` círculo laranja
- [X] T020 [US2] Validar SC-002 e cenários US2 (URL style, lazy, decorações, gap) conforme `specs/047-refino-figma-quem-somos/quickstart.md` seção C

**Checkpoint**: Sobre nós serve derivativo otimizado sem regressão visual das decorações.

---

## Phase 5: User Story 3 — Visitante vê o efeito parallax em Missão/Visão (Priority: P1)

**Goal**: `background-attachment: fixed` em `≥768px` e `scroll` em `<768px`; fundo via Image Style `quem_somos_missao_visao`; overlay + `min-height: 380px` preservados (FR-009–012 / SC-003).  
**Independent Test Criteria**: computed `fixed` no desktop e `scroll` no mobile; URL `/files/styles/quem_somos_missao_visao/...` (`quickstart.md` seção D).

- [X] T021 [US3] Gerar URL estilizada `quem_somos_missao_visao` para o `background-image` inline de Missão/Visão (campo vazio → sem erro / condicional existente) em `themes/custom/default/templates/content/node--quem-somos.html.twig`
- [X] T022 [US3] Aplicar `background-attachment: scroll` na base e `fixed` em `@media (min-width: 768px)` sob `.quem-somos-missao-visao`, mantendo `cover`/`center`/overlay/`min-height: 380px` em `themes/custom/default/assets/css/quem-somos-missao-visao.css` (research R4)
- [X] T023 [US3] Validar SC-003 e cenários US3 (parallax desktop; fallback mobile; contraste; URL style) conforme `specs/047-refino-figma-quem-somos/quickstart.md` seção D

**Checkpoint**: parallax perceptível no desktop sem jank no mobile.

---

## Phase 6: User Story 4 — Visitante vê os ícones finais e maiores em Nossos Diferenciais (Priority: P2)

**Goal**: 8 ícones finais (quando assets entregues), caixa 80×80 desktop / 64×64 mobile, Image Style `diferencial_simples_icon`, grid 2×4 centralizado (FR-013–016 / SC-004).  
**Independent Test Criteria**: caixa 80/64; URL com `diferencial_simples_icon` + `lazy`; não provisórios se assets finais presentes (`quickstart.md` seção E).

- [X] T024 [P] [US4] Substituir PNGs provisórios pelos finais (quando disponíveis) em `modules/custom/custom_configs/assets/diferenciais-quem-somos/` — se ausentes, manter `icon-1.png`…`icon-8.png` e seguir só com style + tamanho CSS (Assumption)
- [X] T025 [US4] Aumentar caixa do ícone para `80×80px` em `@media (min-width: 768px)` e `64×64px` abaixo; preservar proporção/`object-fit`; grid 2 cols mobile / 4 cols desktop com título/subtexto centralizados em `themes/custom/default/assets/css/diferenciais-quem-somos.css`
- [X] T026 [P] [US4] Confirmar que o Twig/formatter do paragraph já consome o `image_style` do display (ou ajustar template se URI crua) em `themes/custom/default/templates/block/block--block-diferenciais-quem-somos.html.twig` e templates paragraph relacionados sob `themes/custom/default/templates/paragraph/`
- [X] T027 [US4] Validar SC-004 e cenários US4 (ícones, caixa 80/64, style+lazy, grid) conforme `specs/047-refino-figma-quem-somos/quickstart.md` seção E — sync runtime `public://` fica na US6

**Checkpoint**: diferenciais nítidos no tamanho Figma; troca de arquivo em `public://` via hook na US6.

---

## Phase 7: User Story 5 — Visitante vê o botão secundário do CTA com contorno (Priority: P2)

**Goal**: botão “Cadastrar gratuitamente” com `border: 1px solid #0F172A` sob `.block-cta-v1` apenas; primário e card intactos (FR-018/019 / SC-006 / SC-009).  
**Independent Test Criteria**: borda visível no secundário; primário `#FD7B1A` inalterado; CTAs vagas/estudantes intactos (`quickstart.md` seções G+I).

- [X] T028 [US5] Adicionar `border: 1px solid #0F172A` em `.block-cta-v1 .cta-v1__btn--secondary` (manter fundo `#FFFFFF` e texto `#0F172A`; hover coerente) em `themes/custom/default/assets/css/cta-v1.css` — **não** editar `themes/custom/default/assets/css/cta-v1-vagas.css` nem `themes/custom/default/assets/css/cta-v1-para-estudantes.css` (research R6)
- [X] T029 [US5] Validar SC-006 e cenários US5 (outline desktop/mobile; primário intacto) conforme `specs/047-refino-figma-quem-somos/quickstart.md` seção G

**Checkpoint**: hierarquia visual do CTA alinha ao Figma sem vazar para outras rotas.

---

## Phase 8: User Story 6 — Deploy automatizado reproduz ícones, Image Styles e textos seed (Priority: P1)

**Goal**: `custom_configs_update_11069` idempotente (ensure styles + displays; sync ícones assets→`public://` + paragraphs só se provisórios/vazios; fill-if-empty textos do node); `drush cex`; destino `cim` → `updb` → `cim` → `cr` (FR-020–023 / SC-007/SC-008).  
**Independent Test Criteria**: ambiente atrasado + receita A do quickstart; 2ª `updb` no-op; editorial divergente preservado (`quickstart.md` seções A+H).

- [X] T030 [US6] Implementar `custom_configs_update_11069` idempotente: (a) ensure Image Styles `quem_somos_sobre_nos`, `quem_somos_missao_visao`, `diferencial_simples_icon`; (b) ensure `image_style` nos displays de `field_imagem` / `field_imagem_desktop` (node `quem_somos`) e `field_image` (paragraph `diferencial_simples_p`); (c) copiar assets de `modules/custom/custom_configs/assets/diferenciais-quem-somos/` → `public://` e atualizar os 8 paragraphs **somente se** URI provisória `icon-N.png` ou campo vazio; (d) preencher textos vazios do node `quem_somos` **nunca** sobrescrevendo editorial divergente — em `modules/custom/custom_configs/custom_configs.install`
- [X] T031 [P] [US6] Extrair/reusar helpers privados de ensure style/display/file/paragraph no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T032 [US6] Exportar/confirmar configs estruturais com `drush cex -y` para `config/sync/image.style.quem_somos_sobre_nos.yml`, `config/sync/image.style.quem_somos_missao_visao.yml`, `config/sync/image.style.diferencial_simples_icon.yml`, `config/sync/core.entity_view_display.node.quem_somos.default.yml` e `config/sync/core.entity_view_display.paragraph.diferencial_simples_p.default.yml`
- [X] T033 [US6] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (SC-007/SC-008) conforme `specs/047-refino-figma-quem-somos/quickstart.md` seções A e H
- [X] T034 [US6] Validar preservações pós-hook (editorial divergente intacto; ícones já finais não resetados; campos imagem vazios sem erro) conforme `specs/047-refino-figma-quem-somos/quickstart.md` seção H

**Checkpoint**: produção recebe o refino só com Git + cim/updb/cim/cr — zero admin manual.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Objetivo**: regressão Impact Numbers, isolamento de rotas, PRD §3.6, cache e aceite SC-001–SC-009.

- [X] T035 [P] Validar regressão Impact in Numbers (max-width `1280px`, padding `64px 40px`, fundo `#0F172A`, número `#FD7B1A`, texto `#FFFFFF`) sem alteração estrutural em `themes/custom/default/assets/css/impact-numbers.css` / bloco na página conforme `specs/047-refino-figma-quem-somos/quickstart.md` seção F (SC-005)
- [X] T036 [P] Validar isolamento SC-009 (home, `/para-empresas`, `/para-estudantes`; CTA vagas/estudantes sem outline desta feature) conforme `specs/047-refino-figma-quem-somos/quickstart.md` seção I
- [X] T037 [P] Atualizar `PRD.md` §3.6 de forma cirúrgica: ícones finais, Image Styles (`quem_somos_sobre_nos`, `quem_somos_missao_visao`, `diferencial_simples_icon`), parallax `fixed`/`scroll`, outline CTA, hook `11069`
- [X] T038 [P] Confirmar bloco SPECKIT em `.cursor/rules/specify-rules.mdc` aponta para `047-refino-figma-quem-somos` com `spec.md` / `plan.md` / `tasks.md`
- [X] T039 Executar limpeza de cache Drupal após Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)
- [X] T040 Executar validação final completa (SC-001–SC-009; edges: ícone vazio, campos imagem vazios, 2ª updb, checklist deploy fácil) com `specs/047-refino-figma-quem-somos/quickstart.md` e `specs/047-refino-figma-quem-somos/checklists/requirements.md`

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → US6 (Phase 8) → Polish (Phase 9)
- US1 é independente de Image Styles (só Twig/CSS banner) — pode iniciar logo após T007, mas Foundational (T008–T013) deve estar concluída antes de US2+
- US2 e US3 compartilham `node--quem-somos.html.twig` (sequenciar edições no mesmo arquivo ou consolidar numa passada)
- US4 CSS/Twig pode paralelizar com US5; sync `public://` depende de US6
- US6 (hook/cex/updb) precisa dos YAMLs da Phase 2 e dos assets de T024; validar pós-Twig/CSS mínimo (US1–US5)
- Polish após US1–US6

## Dependency Graph

```text
Phase1 → Phase2 (Image Styles + displays + cex)
           ├→ US1 (P1) banner mobile zero-request ── MVP
           ├→ US2 (P1) Sobre nós Image Style + gap
           ├→ US3 (P1) Missão/Visão style + parallax
           ├→ US4 (P2) ícones 80/64 + assets
           ├→ US5 (P2) CTA outline
           └→ US6 (P1) hook 11069 + cim/updb/cim/cr
                └→ Polish (regressão Impact Numbers, PRD, cr, SC-001–SC-009)
```

## Parallel Execution Opportunities

- **Setup**: T002–T005 em paralelo após T001; T006 após inventário Twig/CSS
- **Foundational**: T008 ∥ T009 ∥ T010 após T007; T011 após T008+T009; T012 após T010; T013 por último
- **US1**: T014 → T015 (mesmo Twig; T015 [P] se editável em paralelo cuidadoso); T016 [P] CSS; T017 por último
- **US2 ∥ US3**: preferir sequenciar T018/T021 no mesmo Twig; T019 (CSS Sobre nós) ∥ T022 (CSS parallax); T020/T023 após cada história
- **US4 ∥ US5**: T024 ∥ T025 ∥ T028; T026 com T025; T027/T029 por último em cada história
- **US6**: T031 junto a T030; T032–T034 após hook
- **Polish**: T035 ∥ T036 ∥ T037 ∥ T038; depois T039–T040

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (banner mobile sem request) — valor imediato de performance
2. **Imagens + parallax**: US2 + US3 (styles no Twig + CSS)
3. **Fidelidade visual**: US4 (ícones/caixa) + US5 (outline CTA)
4. **Deploy**: US6 (`11069` + cex + cim/updb/cim/cr)
5. **Fechamento**: Polish — regressão Impact Numbers, isolamento, PRD, `drush cr`, SC-001–SC-009

## Format Validation

- Todas as tarefas usam checkbox `- [ ]`, ID sequencial (`T001`…`T040`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; validação manual no quickstart)
- Ordem de histórias: US1 (P1 MVP) → US2 (P1) → US3 (P1) → US4 (P2) → US5 (P2) → US6 (P1 deploy)

