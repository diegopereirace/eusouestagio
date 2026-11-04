# Tasks: Refino Figma Contato (bleed + cards) e tipografia de títulos

**Input**: Artefatos de design em `specs/058-refino-figma-contato/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/contato-refino-render.md`, `contracts/section-title-token.md`, `quickstart.md`  
**Branch sugerida**: `feat/refino-figma-contato` (a partir de `main`)  
**Predecessors**: `017-pagina-contato`, `018-contato-node`, `047`/`052`/`053` (padrão refino Figma), `057-seed-arquivos-imutaveis`  
**Hook**: nenhum esperado; `custom_configs_update_11089` só se display/fields faltarem  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md` (sem PHPUnit)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/058-refino-figma-contato` (via `.specify/feature.json`); template alinhado a `specs/053-refino-figma-para-estudantes/tasks.md`  
**Config estrutural**: só tema por padrão (`drush cr`); `drush cex` / `11089` apenas se surgir gap estrutural

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar Twig/CSS/libraries/alter Contato + headers de seção antes de mutar tema.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/contato-refino-render.md`, `contracts/section-title-token.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/058-refino-figma-contato/` e feature ativa `058-refino-figma-contato` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar markup atual do Node Contato (wrapper `.container`, `row`/`col-lg-*`, H2, painel, classes BEM) em `themes/custom/default/templates/node/node--contato--full.html.twig`
- [X] T003 [P] Inventariar CSS Contato (título Poppins/clamp, painel radius 24px, cards/botão/inputs, media queries) em `themes/custom/default/assets/css/layout-contato.css`
- [X] T004 [P] Inventariar library `layout_contato` e `global-styling` (ponto de anexar token) em `themes/custom/default/default.libraries.yml`
- [X] T005 [P] Inventariar injeção de cards (omit empty, `mailto:`, `wa.me`+DDI 55, classes `layout-contato-node__shortcut*`) em `modules/custom/custom_configs/custom_configs.module` (`custom_configs_webform_submission_form_alter`)
- [X] T006 [P] Inventariar padrão full-bleed sem `100vw` (Missão/Visão / Impact Numbers / `:has(...)` em `#main.container`) em `themes/custom/default/assets/css/style.css` (e CSS de blocos relacionados)
- [X] T007 Inventariar tipografia atual dos headers de seção públicos (`.nd-header__title`, `.nm-header__title`, `.oqf-header__title`, `.cf-header__title`, `.be-header__title`, `.oe-header__title`, `.je-header__title`, `.dqs-header__title`) e último hook `11088` → próximo livre `11089` em `themes/custom/default/assets/css/*.css` + `modules/custom/custom_configs/custom_configs.install`

**Checkpoint**: inventário alinhado a plan/research/data-model/contracts; nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: congelar escopo negativo e confirmar superfície de mudança (sem CT/fields novos; path `/contato`; UUID seed; sem rewrite de assets 057) antes das user stories.

**⚠️ CRITICAL**: Nenhuma user story de implementação começa antes desta fase.

- [X] T008 Congelar escopo negativo: **não** criar content/block/paragraph types nem fields/storages; **não** editar `core/`/`vendor/`; **não** introduzir pipeline SCSS; **não** usar `width: 100vw` / `margin-right: calc(50% - 50vw)`; **não** redesenhar header/footer/FAB WhatsApp; **não** alterar YAML estrutural do webform `contato`; **não** reescrever bytes de `field_imagem` (057); **não** forçar token em heroes/banners/microtítulos/CTAs/nav/footer/labels; **não** resetar `h1–h6` globais em massa em `themes/custom/default/assets/css/style.css`
- [X] T009 Confirmar rota `/contato` (Node UUID seed `a4b5c6d7-e8f9-4012-b345-d6e7f8a9b0c1`, bundle `contato`, view mode `full`), presença de `field_email` / `field_phone_wpp` / `field_imagem` / `field_text_simple` / `field_text_simple_long` / webform, e referência de margem do header/container do tema — anotar sob DevTools + `specs/058-refino-figma-contato/data-model.md`
- [X] T010 Confirmar que o Twig legado `themes/custom/default/templates/block/block--block-layout-contato.html.twig` **não** é a superfície canônica de `/contato` (Node full é a fonte) e que mudanças desta feature focam `node--contato--full` + CSS escopado `.node--contato` / `.layout-contato-node` (FR-013)

**Checkpoint**: escopo congelado; path/UUID/fields confirmados; pronto para US1.

---

## Phase 3: User Story 1 — Painel direito sem margem na borda (Priority: P1) 🎯 MVP

**Goal**: Em ≥992px, formulário alinhado à margem do header; painel `#E5EEFF` encostado na borda direita da viewport (bleed assimétrico sem `100vw`); colunas com altura coerente (FR-001–004 / SC-001 / SC-002).  
**Independent Test Criteria**: viewport ≥992px (ideal 1440) em `/contato` — fundo do painel toca a borda direita; margem esquerda do título/form ≈ header (desvio ≤8px); sem faixa branca residual (`quickstart.md` seção B / `contracts/contato-refino-render.md`).

- [X] T011 [US1] Remover `.container` que corta ambas as colunas do wrapper; introduzir estrutura assimétrica (grid/flex full-width com `__form-col` + `__media-col`) alinhada ao contrato DOM em `themes/custom/default/templates/node/node--contato--full.html.twig` (research R1)
- [X] T012 [US1] Liberar shell `#main.container` só em `body.path-contato` / `:has(.node--contato)` (`max-width: none`, paddings laterais 0 no shell) **sem** `100vw`, espelhando Missão/Visão, em `themes/custom/default/assets/css/layout-contato.css` e/ou `themes/custom/default/assets/css/style.css` (research R1)
- [X] T013 [US1] Implementar CSS do bleed: coluna esquerda com padding/margin-left = gutter do container do tema; coluna direita com fundo `#E5EEFF` até a borda direita útil; colunas esticadas na mesma linha (≥992px) em `themes/custom/default/assets/css/layout-contato.css`
- [X] T014 [US1] Validar SC-001/SC-002 e cenários US1 (bleed, alinhamento esquerdo, altura das colunas) conforme `specs/058-refino-figma-contato/quickstart.md` seção B e `specs/058-refino-figma-contato/contracts/contato-refino-render.md`

**Checkpoint**: visitante vê o painel colado na borda direita — MVP visual principal.

---

## Phase 4: User Story 2 — Linha de ações (enviar + cards) (Priority: P1)

**Goal**: Desktop: botão Enviar (~180×56, `#FD7B1A`, texto branco) + cards E-mail (~280×70) e WhatsApp (~214×70) na mesma fileira; borda `#C6C6CD`; valor `#45464D`; links `mailto:` / `wa.me`; omit empty preservado (FR-006–009 / FR-015 / SC-003).  
**Independent Test Criteria**: fileira horizontal desktop; dimensões ±10%; clique nos cards; esvaziar field → card some (`quickstart.md` seção C / contract).

- [X] T015 [P] [US2] Revisar markup/classes dos shortcuts no alter (BEM `--email`/`--whatsapp` já existentes) **somente se** CSS precisar de gancho extra — sem mudar origem dos fields — em `modules/custom/custom_configs/custom_configs.module`
- [X] T016 [US2] Ajustar CSS de `.form-actions` / submit / `.layout-contato-node__shortcut*` para dimensões, cores e gap do Figma; vencer `.btn.btn-primary.form-control` no submit; fileira única no desktop em `themes/custom/default/assets/css/layout-contato.css` (research R3)
- [X] T017 [US2] Validar SC-003 e cenários US2 (proporções, cores, `mailto:`/`wa.me`, omit empty) conforme `specs/058-refino-figma-contato/quickstart.md` seção C e `specs/058-refino-figma-contato/contracts/contato-refino-render.md`

**Checkpoint**: linha de ações fecha fidelidade Figma do rodapé do formulário.

---

## Phase 5: User Story 3 — Painel de especialistas conforme Figma (Priority: P1)

**Goal**: Coluna direita com imagem + título + texto centralizados sobre `#E5EEFF`; desktop = face de coluna (não card flutuante com radius 24px na edge); omit empty de imagem/textos (FR-010).  
**Independent Test Criteria**: comparar painel com frame Figma; sem imagem/textos → conteúdo restante centralizado (`quickstart.md` seção D / contract).

- [X] T018 [US3] Garantir markup do painel (`__panel`, `__image` lazy, `__panel-title`, `__panel-text`) e omit de campos vazios em `themes/custom/default/templates/node/node--contato--full.html.twig` (research R4)
- [X] T019 [US3] Refatorar CSS do painel: fundo na coluna; flex center; remover “caixa” radius 24px que conflita com bleed na borda direita; tipografia do título/texto do painel (não confundir com token de seção) em `themes/custom/default/assets/css/layout-contato.css`
- [X] T020 [US3] Validar cenários US3 (centralização, omit empty imagem/textos) conforme `specs/058-refino-figma-contato/quickstart.md` seção D e `specs/058-refino-figma-contato/contracts/contato-refino-render.md`

**Checkpoint**: metade direita da composição fecha fidelidade Figma.

---

## Phase 6: User Story 4 — Empilhamento mobile usable (Priority: P1)

**Goal**: Em &lt;992px: form acima do painel; painel 100% largura; ações wrap sem overflow-x; submit 100% da coluna (FR-011 / SC-004).  
**Independent Test Criteria**: viewports 375 e 768; sem scroll horizontal do layout Contato (`quickstart.md` seção E / contract).

- [X] T021 [US4] Implementar `@media (max-width: 991.98px)`: ordem form → painel; painel full-width; `.form-actions` wrap; submit `width: 100%`; revisar `flex-wrap: nowrap` legado que cause overflow em `themes/custom/default/assets/css/layout-contato.css` (research R2)
- [X] T022 [US4] Validar SC-004 e cenários US4 (375/768; tablet 768–991; sem overflow-x) conforme `specs/058-refino-figma-contato/quickstart.md` seção E e `specs/058-refino-figma-contato/contracts/contato-refino-render.md`

**Checkpoint**: mobile/tablet usáveis sem o bleed assimétrico de desktop.

---

## Phase 7: User Story 5 — Título Contato no token tipográfico (Priority: P1)

**Goal**: “Envie sua mensagem” = Inter 600 / 30px / 38px / ls −0.3px / `#000`; classe canônica `.ese-section-title`; sem Poppins/`clamp` neste título (FR-016–017 / SC-007).  
**Independent Test Criteria**: inspeção computada do H2 em `/contato` bate 100% o token (`quickstart.md` seção F / `contracts/section-title-token.md`).

- [X] T023 [US5] Criar CSS do token `.ese-section-title` (Inter 600 / 30px / 38px / −0.3px / `#000`) em `themes/custom/default/assets/css/ese-section-title.css` (arquivo novo; research R5)
- [X] T024 [US5] Anexar `ese-section-title.css` via library leve ou dependência de `global-styling` / tema base em `themes/custom/default/default.libraries.yml` para disponibilidade nas páginas públicas
- [X] T025 [US5] Aplicar classe `ese-section-title` no H2 “Envie sua mensagem” em `themes/custom/default/templates/node/node--contato--full.html.twig` e remover tipografia divergente (Poppins/`clamp`/`#0f172a`) de `.layout-contato-node__title` em `themes/custom/default/assets/css/layout-contato.css`
- [X] T026 [US5] Validar SC-007 e cenários US5 conforme `specs/058-refino-figma-contato/quickstart.md` seção F e `specs/058-refino-figma-contato/contracts/section-title-token.md`

**Checkpoint**: Contato é o caso canônico do token de seção.

---

## Phase 8: User Story 6 — Títulos de seção consistentes no site (Priority: P1)

**Status**: **REVERTIDO** — decisão de produto: token somente em `/contato`; demais títulos de seção mantêm tipografia local.

**Goal** (original): Headers de seção públicos listados herdam o mesmo token (aliases CSS); preservar spans de cor locais (ex. Benefícios); exclusões heroes/cards/CTAs/nav/footer (FR-018 / SC-008).  
**Override**: aliases removidos de `ese-section-title.css`; CSS dos blocos restaurado; library do token anexada só em `layout_contato`. SC-008 N/A.

- [X] T027 [US6] ~~Aliases globais~~ → escopo restrito a Contato em `ese-section-title.css` + library `layout_contato`
- [X] T028 [P] [US6] Tipografia local restaurada nos CSS dos blocos (nd/nm/oqf/cf/be/oe/je/dqs)
- [X] T029 [US6] SC-008 N/A — contrato atualizado em `contracts/section-title-token.md`

**Checkpoint**: token tipográfico isolado em `/contato`; demais títulos inalterados.

---

## Phase 9: User Story 7 — Envio do formulário sem regressão (Priority: P2)

**Goal**: Webform Contato continua enviando com sucesso/validação; inputs ~57px, borda fina, radius suave; mensagens sem overflow/sobreposição com cards (FR-012 / FR-014 / SC-005).  
**Independent Test Criteria**: envio válido → sucesso; e-mail inválido/obrigatório vazio → bloqueio; inputs desktop ~57px (`quickstart.md` seção H / contract).

- [X] T030 [US7] Ajustar CSS dos inputs/textarea/select sob `.layout-contato-node__webform` (altura ~57px linha; borda fina; radius ~8px) **sem** alterar YAML do webform em `themes/custom/default/assets/css/layout-contato.css` (research R8)
- [X] T031 [US7] Validar SC-005 e cenários US7 (sucesso, erros, legibilidade; mensagens não quebram layout) conforme `specs/058-refino-figma-contato/quickstart.md` seção H e `specs/058-refino-figma-contato/contracts/contato-refino-render.md`

**Checkpoint**: refino visual não compromete a função principal da página.

---

## Phase 10: Polish & Cross-Cutting Concerns

**Objetivo**: regressão fora de escopo, PRD cirúrgico se necessário, cache, aceite SC-001–SC-008; hook/`cex` só se gap estrutural surgir.

- [X] T032 [P] Smoke de regressão: header, footer, FAB WhatsApp, home e outras rotas — layout Contato não vaza; tipografia de seção não altera heroes/microtítulos/CTAs — conforme `specs/058-refino-figma-contato/quickstart.md` seção I
- [X] T033 [P] Atualizar `PRD.md` §3.6 de forma **cirúrgica** somente se o contrato visual publicado de Contato / tipografia de seção mudar de forma relevante para editores; caso contrário, no-op documentado na task
- [X] T034 [P] Confirmar bloco SPECKIT em `.cursor/rules/specify-rules.mdc` aponta para `058-refino-figma-contato` com `spec.md` / `plan.md` / `tasks.md`
- [X] T035 Se (e somente se) a implementação descobrir display/fields incompletos: criar `custom_configs_update_11089` idempotente + `drush updb -y` + `drush cex -y` em `modules/custom/custom_configs/custom_configs.install` e `config/sync/`; senão, pular com nota “sem hook”
- [X] T036 Executar limpeza de cache Drupal após Twig/CSS/libraries (`docker compose exec drupal drush cr` ou equivalente)
- [X] T037 Executar validação final completa (SC-001–SC-008; edges: card único, imagem/textos vazios, scrollbar desktop, título longo, viewport intermediário) com `specs/058-refino-figma-contato/quickstart.md` e `specs/058-refino-figma-contato/checklists/requirements.md`

---

## Phase 11: Performance da imagem do painel (FR-010a)

**Objetivo**: servir `field_imagem` via Image Style `contato_painel` (scale 640 + WebP) com `loading="lazy"` e `decoding="async"`; sem URI crua; sem rewrite de seed (057).

- [X] T038 Criar Image Style `contato_painel` (scale width 640, upscale false, convert WebP) via Entity API / Drush e exportar com `drush cex` → `config/sync/image.style.contato_painel.yml`
- [X] T039 Atualizar markup da imagem em `themes/custom/default/templates/node/node--contato--full.html.twig`: `|image_style('contato_painel')`, `loading="lazy"`, `decoding="async"`, `width`/`height` 640×720
- [X] T040 Validar FR-010a: HTML com lazy/async; Network com `/styles/contato_painel/…`; omit se campo vazio; `drush cr`

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → US6 (Phase 8) → US7 (Phase 9) → Polish (Phase 10)
- US1 (bleed) é MVP e desbloqueia fidelidade estrutural de Contato
- US2 (cards) e US3 (painel) podem avançar em paralelo após T011 (markup base), em arquivos CSS/Twig distintos onde marcado `[P]`
- US4 (mobile) depende do layout de US1 (+ idealmente estilos de US2/US3 já no CSS Contato)
- US5 (token Contato) pode iniciar após Foundational em arquivos novos; idealmente após T011 se o H2 já estiver no Twig refatorado
- US6 (aliases globais) depende de T023–T024 (arquivo/library do token)
- US7 (inputs/envio) pode paralelizar com US2 no mesmo `layout-contato.css` com cuidado de merge; sequenciar após US2 se um único agente
- Polish após US1–US7

## Dependency Graph

```text
Phase1 → Phase2 (escopo + path + UUID)
           ├→ US1 (P1) Twig assimétrico + bleed CSS ── MVP
           │     ├→ US2 (P1) cards/botão CSS (+ alter opcional)
           │     ├→ US3 (P1) painel Twig/CSS
           │     └→ US4 (P1) mobile media queries
           ├→ US5 (P1) ese-section-title.css + H2 Contato
           │     └→ US6 (P1) aliases headers de seção
           └→ US7 (P2) inputs + smoke envio webform
                └→ Polish (regressão, PRD, cr, SC-001–SC-008)
```

## Parallel Execution Opportunities

- **Setup**: T002–T006 em paralelo após T001; T007 após inventário Contato
- **Foundational**: T009 ∥ T010 após T008
- **US1**: T011 → T012 ∥ T013; T014 por último
- **US2 ∥ US3** (após T011): T015 [P] ∥ T018; T016 / T019 em `layout-contato.css` — coordenar se mesmo arquivo; T017/T020 após cada história
- **US4**: após US1 (+ preferível pós US2/US3 CSS)
- **US5 → US6**: T023 → T024 → T025; depois T027 → T028 [P] nos CSS de bloco; T026/T029 validação
- **US7**: T030 após estilos de ações (US2) estabilizados; T031 por último na história
- **Polish**: T032 ∥ T033 ∥ T034; T035 se necessário; depois T036 → T037

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (bleed assimétrico Contato)
2. **Fidelidade Contato**: US2 (ações) ∥ US3 (painel) → US4 (mobile)
3. **Tipografia**: US5 (token + Contato) → US6 (padronização site-wide)
4. **Não-regressão funcional**: US7 (inputs + envio)
5. **Fechamento**: Polish — regressão, PRD cirúrgico se preciso, `drush cr`, SC-001–SC-008

## Format Validation

- Todas as tarefas usam checkbox `- [ ]`, ID sequencial (`T001`…`T037`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; validação manual no quickstart)
- Ordem de histórias: US1–US6 (P1) → US7 (P2) → Polish
