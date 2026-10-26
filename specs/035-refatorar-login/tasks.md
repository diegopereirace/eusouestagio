# Tasks: Refatoração da página de Login (`/user/login`)

**Input**: Artefatos de design em `specs/035-refatorar-login/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/login-render.md`, `quickstart.md`  
**Branch**: `feature-vaga` (workflow do projeto)  
**Predecessor**: `034-empresa-pagina-publica`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md` (sem PHPUnit)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/035-refatorar-login` (via `.specify/feature.json`); template alinhado a `specs/031-vagas-detalhe-refino/tasks.md`  
**Config estrutural**: nenhuma — sem `hook_update_N`, sem `drush cex`/`cim`/`updb`; publicar com `drush cr`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar o estado atual de `/user/login` (template compartilhado, hook de redirect, CSS da aba de registro, library do tema) antes de implementar.

- [x] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/login-render.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/035-refatorar-login/` e feature ativa `035-refatorar-login` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [x] T002 [P] Inventariar markup a replicar só de header (`#header.header.container` + `page.header`) e footer (`.site-footer-custom.pt-5` + `page.footer_first`) em `themes/custom/default/templates/layout/page.html.twig` (linhas ~70–89 e ~173–185); anotar o que **não** copiar: sidebars, `sidebar_painel`, `banner`, `content_full`, `featured_*`, `dump()`, layout de painel
- [x] T003 [P] Inventariar `custom_panel_form_user_login_form_alter()` (só `$form['#submit'][] = '_custom_panel_login_redirect'`) e o early return de `destination` em `_custom_panel_login_redirect()` em `modules/custom/custom_panel/custom_panel.module` (linhas 27–56) — preservar ambos sem alterar a lógica de papéis
- [x] T004 [P] Inventariar regra já existente `.page-user-login .tabs .nav-link--user-register` em `themes/custom/default/assets/css/style.css` (linhas ~3161–3165), tokens `:root` `--brand-orange` / `--brand-navy` e Poppins no mesmo arquivo, e o padrão de library `empresa_pagina` em `themes/custom/default/default.libraries.yml`
- [x] T005 [P] Confirmar escopo negativo de config: zero arquivo novo ou alterado em `config/sync/`, zero `hook_update_N` em `modules/custom/custom_configs/custom_configs.install`, e ausência de `hook_form_user_login_form_alter` em `themes/custom/default/default.theme` (research R8 / FR-013)

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar isolamento de rota e publicar a library `default/login_card` anexada só no formulário de login, sem ainda estilizar o card. Pré-requisito de todas as user stories.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [x] T006 Congelar escopo negativo: **não** editar `themes/custom/default/templates/layout/page.html.twig`, **não** criar override de `form.html.twig`, **não** tocar `core/` nem `vendor/`, **não** criar `page--user--password.html.twig` / `page--user--register.html.twig`, **não** alterar `config/sync/` nem `modules/custom/custom_configs/custom_configs.install`, **não** mover a regra `.nav-link--user-register` para fora de `themes/custom/default/assets/css/style.css`
- [x] T007 [P] Registrar library `login_card` (css component `assets/css/components/login.css`, dependency `default/bootstrap`, sem JS) em `themes/custom/default/default.libraries.yml`, no mesmo formato de `empresa_pagina` — **não** anexar em `global-styling`
- [x] T008 [P] Criar `themes/custom/default/assets/css/components/login.css` com comentário de escopo e seletor raiz `.page-user-login` (arquivo válido, ainda sem regras visuais do card) para a library T007 resolver o asset
- [x] T009 [P] Estender `custom_panel_form_user_login_form_alter()` em `modules/custom/custom_panel/custom_panel.module` apenas com `$form['#attached']['library'][] = 'default/login_card'`, mantendo `$form['#submit'][] = '_custom_panel_login_redirect'` intacto e sem duplicar o hook em `themes/custom/default/default.theme`

**Checkpoint**: `/user/login` carrega `login.css` só nesta rota; redirect por papel inalterado; `page.html.twig` sem diff; card ainda não existe.

---

## Phase 3: User Story 1 — Visitante acessa a tela de login (Priority: P1) 🎯 MVP

**Goal**: em `/user/login`, o formulário fica num card branco centralizado, com título "Acesse sua conta", link "Esqueceu a senha?" abaixo do botão e aba "Entrar" oculta — sem alterar as demais rotas.  
**Independent Test Criteria**: deslogado, viewport ≥768px em `/user/login` — card `shadow-sm` / `rounded-4` centralizado (não 100% da largura), h1 "Acesse sua conta" em Poppins semibold, link "Esqueceu a senha?" discreto e centralizado abaixo do botão, aba "Criar nova conta" e aba "Entrar" ausentes (SC-001 parcial / FR-001–FR-003 / FR-007 / FR-009 / quickstart B).

- [x] T010 [US1] Criar `themes/custom/default/templates/layout/page--user--login.html.twig` (suggestion nativa `page__user__login`) replicando header e footer de `page.html.twig` e, no meio, `<main class="login-page">` → `.container.py-5` → `.row.justify-content-center.align-items-center.login-page__row` → `.col-12.col-md-6.col-lg-4` → `.card.border-0.shadow-sm.rounded-4.p-4.p-lg-5.login-card` com `<h1 class="login-card__title">{{ 'Acesse sua conta'|t }}</h1>`, `{{ page.highlighted }}` e `{{ page.content }}` dentro do card, conforme `specs/035-refatorar-login/contracts/login-render.md`; únicos `{% if %}` permitidos: existência de `page.header` e `page.footer_first` — zero `if` de estado de formulário, erro ou CAPTCHA (FR-012)
- [x] T011 [P] [US1] Em `themes/custom/default/assets/css/components/login.css`, sob `.page-user-login`: `.login-page__row { min-height: 70vh; }`; `.login-card` como `display: flex; flex-direction: column`; `.login-card__title` com `font-family: 'Poppins', sans-serif` e `font-weight: 600`; ocultar `#block-default-pagetitle`; `.region-highlighted { display: contents; }`; `#block-default-messages { order: 1; }`; `.region-content { order: 2; }`; `#block-default-primarylocaltasks { display: none; }`; `#block-default-secondarylocaltasks { order: 3; text-align: center; }`; `.nav-link--user-password` discreto, centralizado, cor `var(--brand-navy)`, com `::before { content: none; }` — **não** redeclarar `.nav-link--user-register` (já em `style.css`)
- [x] T012 [US1] Limpar cache (`docker compose exec -T drupal vendor/bin/drush cr`) e validar cenários US1 / quickstart B (card centralizado, título, link abaixo do botão, abas "Entrar" e "Criar nova conta" ocultas, header/footer do site presentes) em `specs/035-refatorar-login/quickstart.md`

**Checkpoint**: visitante vê o card de login centralizado — MVP visual da feature.

---

## Phase 4: User Story 2 — Visitante preenche e envia o formulário (Priority: P1)

**Goal**: campos grandes com placeholder, botão laranja de largura total e texto branco em negrito; login e redirect por papel seguem iguais.  
**Independent Test Criteria**: inputs `name`/`pass` com `form-control-lg`, `rounded-3` e placeholders; botão "Entrar" `w-100` com fundo `var(--brand-orange)` e texto branco negrito; credencial válida redireciona por papel e `?destination=` tem prioridade (SC-002 / SC-003 / SC-005 / FR-004–FR-006 / FR-010 / quickstart C).

- [x] T013 [US2] Em `custom_panel_form_user_login_form_alter()` de `modules/custom/custom_panel/custom_panel.module`, adicionar em `$form['name']` e `$form['pass']` as classes `#attributes['class']` `form-control form-control-lg rounded-3` e placeholders traduzíveis (`t('Digite seu e-mail ou usuário')` / `t('Sua senha')`); em `$form['actions']['submit']` as classes `btn btn-primary btn-lg w-100 mt-4` — sem remover `#attached` da library nem `#submit` de `_custom_panel_login_redirect`, e sem segundo hook em `themes/custom/default/default.theme`
- [x] T014 [P] [US2] Em `themes/custom/default/assets/css/components/login.css`, forçar `.page-user-login #user-login-form .form-actions .btn-primary` com `background-color` e `border-color` `var(--brand-orange)`, `color: #fff` e `font-weight: 700`, para o `btn-primary` do Barrio não permanecer azul (FR-006)
- [x] T015 [US2] Validar SC-002 / SC-003 / SC-005 e quickstart C (placeholders, altura `form-control-lg`, botão laranja full width, redirect candidato → `/painel/estudante/perfil`, empresa → `/painel/empresa/perfil`, moderador/administrator → `/painel/moderador/candidaturas`, e `?destination=` com prioridade) em `specs/035-refatorar-login/quickstart.md`

**Checkpoint**: formulário usável e login por papel sem regressão.

---

## Phase 5: User Story 3 — Visitante lida com erros e CAPTCHA (Priority: P2)

**Goal**: mensagem de erro fica dentro do card, acima dos campos; o reCAPTCHA (quando ativo) tem respiro e não comprime o card.  
**Independent Test Criteria**: credencial inválida mostra o erro dentro do `.login-card` acima do form; `.captcha` com margem vertical ~1rem; card íntegro com ou sem CAPTCHA (SC-004 / FR-008 / quickstart D).

- [x] T016 [US3] Em `themes/custom/default/assets/css/components/login.css`, dar espaçamento a `#block-default-messages` dentro de `.login-card` (margem inferior antes do form) e a `.page-user-login .captcha` (`margin-top` e `margin-bottom: 1rem`), sem assumir que `.captcha` sempre existe e sem alterar `config/sync/captcha.captcha_point.user_login_form.yml`
- [x] T017 [US3] Validar SC-004 e quickstart D (erro de login dentro do card acima dos campos; reCAPTCHA com espaçamento, sem distorcer o card) em `specs/035-refatorar-login/quickstart.md`

**Checkpoint**: erro e CAPTCHA convivem com o card sem quebrar o layout.

---

## Phase 6: User Story 4 — Visitante acessa pelo celular (Priority: P2)

**Goal**: em viewport &lt;768px o card usa a largura confortável (`col-12`), padding interno reduzido e a mesma hierarquia do desktop, sem scroll horizontal.  
**Independent Test Criteria**: &lt;768px — `col-12`, padding `p-4`, ordem título → campos → botão → "Esqueceu a senha?", sem overflow-x (FR-014 / SC-001 / quickstart E).

- [x] T018 [US4] Em `themes/custom/default/assets/css/components/login.css`, adicionar `@media (max-width: 767.98px)` sob `.page-user-login` para garantir padding interno reduzido do `.login-card`, `min-height` da `.login-page__row` sem estourar a viewport e `overflow-x: hidden` no card — as classes `col-12` / `p-4` / `p-lg-5` já vêm do Twig de T010; não duplicar grid no CSS
- [x] T019 [US4] Validar quickstart E / FR-014 (viewport &lt;768px: card `col-12`, hierarquia visual igual ao desktop, sem scroll horizontal) em `specs/035-refatorar-login/quickstart.md`

**Checkpoint**: o mesmo card funciona no celular.

---

## Phase 7: User Story 5 — Desenvolvedor mantém o template Twig puro (Priority: P3)

**Goal**: o Twig da página de login só tem marcação estrutural; classes, placeholders e library ficam num único hook PHP.  
**Independent Test Criteria**: revisão de código — zero `if`/laço sobre estado do formulário no Twig; `custom_panel_form_user_login_form_alter()` é o único ponto de classes/placeholders/`#attached` (SC-006 / FR-012 / FR-013 / quickstart F).

- [x] T020 [US5] Revisar `themes/custom/default/templates/layout/page--user--login.html.twig` e remover qualquer `{% if %}`, `{% for %}` ou cálculo sobre erro, CAPTCHA, campos ou papéis; manter somente os `if` de existência de `page.header` e `page.footer_first`
- [x] T021 [P] [US5] Confirmar em `modules/custom/custom_panel/custom_panel.module` que classes, placeholders e `#attached` `default/login_card` existem só em `custom_panel_form_user_login_form_alter()`, e que `themes/custom/default/default.theme` não declara `hook_form_user_login_form_alter` nem `hook_form_alter` equivalente para `user_login_form`
- [x] T022 [US5] Validar SC-006 e quickstart F (Twig sem lógica de estado; Form API centralizada) em `specs/035-refatorar-login/quickstart.md`

**Checkpoint**: separação Twig estrutural vs. Form API verificável por leitura de código.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: isolamento das outras rotas, checklist de deploy sem config, e aceite final SC-001–SC-006. Sem alteração de `PRD.md` (apresentação; o trecho novo em `*.module` não muda schema, rota nem serviço — perguntar ao editor só se ele julgar impacto de produto, conforme `.cursor/rules/estagio-prd-guardian.mdc`).

- [x] T023 [P] Validar isolamento quickstart G: `/user/password` e `/user/register` com visual atual; diff vazio em `themes/custom/default/templates/layout/page.html.twig` e ausência de override de `form.html.twig` em `themes/custom/default/`
- [x] T024 [P] Confirmar checklist "deploy fácil" de `specs/035-refatorar-login/quickstart.md`: diff vazio em `config/sync/`, sem `hook_update_N` em `modules/custom/custom_configs/custom_configs.install`, publicação só com `drush cr` (sem `cim`/`updb`/`cex`)
- [x] T025 [P] Confirmar que o bloco SPECKIT em `.cursor/rules/specify-rules.mdc` já aponta `specs/035-refatorar-login/tasks.md`; editar o arquivo só se o caminho da feature ativa estiver desatualizado
- [x] T026 Executar validação final SC-001–SC-006 (quickstart A–G + `specs/035-refatorar-login/checklists/requirements.md`), incluindo fallback sem CAPTCHA e com JavaScript desabilitado descritos em `specs/035-refatorar-login/contracts/login-render.md`
- [x] T027 Limpar cache Drupal após Twig/CSS/hook (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3, P1, MVP) → US2 (Phase 4, P1) → US3 (Phase 5, P2) → US4 (Phase 6, P2) → US5 (Phase 7, P3) → Polish (Phase 8)
- US1 depende do template suggestion e da library anexada (T007–T009); não depende das classes Bootstrap dos campos
- US2 depende do card US1 para o aceite visual do botão; o PHP de classes (T013) pode ser escrito em paralelo ao CSS do botão (T014) e, depois de T009, em paralelo ao CSS de US1
- US3 e US4 editam o mesmo `login.css` — sequenciais depois do CSS de US1/US2
- US5 é revisão dos arquivos já produzidos; não adiciona comportamento novo
- Polish só depois de US1–US5
- Nenhuma história exige `config/sync` nem `hook_update_N`

## Dependency Graph

```text
Phase1 → Phase2 (library + attach + escopo negativo)
           └→ US1 (P1) MVP card + título + link "Esqueceu a senha?"
                ├→ US2 (P1) classes/placeholders + botão laranja + redirect
                ├→ US3 (P2) mensagens + CAPTCHA          ─┐
                ├→ US4 (P2) mobile                        ├→ US5 (P3) revisão Twig/Form API → Polish
                └→ (US3/US4 sequenciais no mesmo login.css)
```

## Parallel Execution Opportunities

- **Setup**: T002–T005 em paralelo após T001
- **Foundational**: T007, T008 e T009 em paralelo após T006 (YAML, CSS e PHP em arquivos distintos)
- **US1**: T010 e T011 em paralelo (Twig e CSS; classes já fixadas no contrato) → T012
- **US2**: T013 e T014 em paralelo após T009/T010 → T015
- **US3**: T016 → T017 (mesmo CSS de US4; não paralelizar T016 com T018)
- **US4**: T018 → T019, depois do CSS de US3
- **US5**: T020 e T021 em paralelo → T022
- **Polish**: T023–T025 em paralelo; depois T026 → T027

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 — card centralizado, título "Acesse sua conta" e link "Esqueceu a senha?" no lugar, sem mexer no restante do site
2. **Login usável**: US2 — placeholders, botão laranja e redirect por papel intacto
3. **Robustez**: US3 (erro + CAPTCHA) e US4 (mobile)
4. **Manutenção**: US5 — Twig sem lógica de estado e um único hook
5. **Fechamento**: Polish — isolamento `/user/password` e `/user/register`, zero config, aceite SC-001–SC-006, `drush cr`

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T027`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; validação manual no quickstart)
- Ordem de histórias: US1 (P1) → US2 (P1) → US3 (P2) → US4 (P2) → US5 (P3)
