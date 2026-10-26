# Research: 035-refatorar-login

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-26

Todas as decisões priorizam: **zero alteração em `page.html.twig`/`form.html.twig`**, **zero config estrutural nova**, e **separação Twig puro vs. lógica em PHP/CSS** pedida explicitamente no input do usuário.

---

## R1 — Mecanismo de isolamento por rota: template suggestion de página

**Decision**: criar `templates/layout/page--user--login.html.twig`, aproveitando a suggestion `page__user__login` que o core **já gera automaticamente** (`SystemThemeHooks::themeSuggestionsPage()` em `core/modules/system/src/Hook/SystemThemeHooks.php:172-187`, via `theme_get_suggestions($path_args, 'page')` sobre `path.current`).

**Rationale**: FR-002 exige que o wrapper só se aplique em `user.login` sem alterar `page.html.twig`. Como o core já resolve a suggestion por path (`user/login` → `page__user`, `page__user__login`), basta um arquivo novo — nenhuma linha de PHP/preprocess é necessária para a seleção de template. Isso também atende FR-012 "da melhor forma possível": a decisão condicional ("qual template usar") já é resolvida pelo core antes do Twig, não dentro dele.

**Alternatives considered**:
- `hook_preprocess_page()` setando uma flag (`is_login_route`) e `{% if %}` dentro do `page.html.twig` compartilhado (padrão já usado em `content_full_after_banner` para `/para-estudantes`) — rejeitado: FR-002 exige explicitamente não alterar `page.html.twig`; o padrão de suggestion é estritamente mais isolado (zero risco de regressão nas demais rotas, nenhum diff no arquivo compartilhado).
- `hook_theme_suggestions_page_alter()` customizado em `custom_panel.module` — rejeitado: redundante, o core já entrega a suggestion certa sem código extra.

---

## R2 — Escopo CSS: classe de body já existente

**Decision**: usar `.page-user-login` como seletor raiz de todo o CSS da feature. Nenhum código novo necessário — a classe já é gerada pelo core (`theme_suggestions_html` → `theme_get_suggestions($path_args, 'html')`, mesmo mecanismo do R1, aplicado à tag `<body>`) e já está em uso em `style.css:3161-3165`.

**Rationale**: FR-011; evita duplicar lógica de detecção de rota em dois lugares (JS/PHP) quando o core já resolve isso para classes de body.

**Alternatives considered**: nova classe custom via `hook_preprocess_html()` — rejeitado (redundante; risco de colidir/duplicar com a classe nativa).

---

## R3 — Reposicionamento da tab nativa "Esqueceu a senha?" sem JS

**Decision**: `.page-user-login .region-highlighted { display: contents; }` + `order` em CSS nos blocos filhos (`#block-default-messages`, `.region-content`, `#block-default-primarylocaltasks`, `#block-default-secondarylocaltasks`), com `.login-card` como `display: flex; flex-direction: column`.

**Rationale**: FR-007. O link "Esqueceu a senha?" é a local task nativa `user.pass` (`core/modules/user/user.links.task.yml`), renderizada pelo bloco `default_secondary_local_tasks` na região `highlighted` — **antes** de `page.content` no DOM, e fora do array `$form` (não é alcançável via Form API). `display: contents` remove a caixa do wrapper de região sem remover seus filhos da árvore de acessibilidade/DOM, permitindo que cada bloco vire item flex direto do `.login-card` e seja reordenado individualmente com `order`.

**Alternatives considered**:
- Mover a tab via JavaScript (`appendChild`) — rejeitado: acopla UX a script, risco de flash de conteúdo não estilizado (FOUC), e o pedido do usuário é explicitamente CSS + Form API, não JS novo.
- Duplicar `{{ page.highlighted }}` no Twig, uma vez no topo (mensagens) e outra embaixo (tabs) — rejeitado: blocos são render arrays; chamar a região duas vezes gera renderização duplicada de todo o conteúdo (mensagens apareceriam duas vezes), não apenas da tab desejada.
- Mudar o placement das local tasks (`region: content`, `weight` alto) só para o tema `default` — rejeitado: a região `highlighted` é compartilhada por **todas** as rotas do site (breadcrumbs, mensagens, buscas); mudar weight/região do bloco `local_tasks_block` afetaria todas as páginas, não é escopável por rota via config de bloco sem visibility condicionais complexas — violaria FR-011.

---

## R4 — Aba "Entrar" (local task ativa): ocultar

**Decision**: ocultar `#block-default-primarylocaltasks` (que contém `.nav-link--user-login`) via `display: none` sob `.page-user-login`, no mesmo padrão já usado para a aba "Criar nova conta" (`.nav-link--user-register`, preservado sem alteração).

**Rationale**: FR-007 fala em "demais links auxiliares nativos do formulário" devendo seguir o tratamento discreto; a aba "Entrar" aponta para a própria página atual (link redundante com o próprio card/botão de submit) e não agrega valor visual ao card elegante pedido. Mantém paridade com a decisão já tomada no projeto para a aba de registro.

**Alternatives considered**: manter a aba "Entrar" visível e estilizá-la como pill ativa — rejeitado: não mencionado no pedido do usuário, adiciona ruído visual ao card que o Figma descrito não prevê.

---

## R5 — Título "Acesse sua conta" vs. bloco de título padrão

**Decision**: título estático em Twig (`<h1 class="login-card__title">`) + ocultar `#block-default-pagetitle` apenas em `.page-user-login` via CSS (o bloco `default_page_title` continua ativo/intacto nas demais rotas).

**Rationale**: FR-003. O bloco `default_page_title` (região `content`, visível globalmente) renderia o título padrão da rota (`user.login` = "Log in"/"Acessar" conforme tradução), que colide/duplica com o título de boas-vindas pedido. Texto fixo em Twig não é "lógica condicional" (FR-012 veda `if`/cálculo/laço sobre estado do formulário, não marcação estática).

**Alternatives considered**: alterar o `_title` da rota `user.login` via route subscriber — rejeitado: é rota do core (`user.routing.yml`), alterar título de rota core para fins puramente visuais é desproporcional e foge do escopo (Fora: "qualquer alteração em core/").

---

## R6 — Classes Bootstrap/placeholders: único ponto em `custom_panel_form_user_login_form_alter()`

**Decision**: estender o hook já existente (não criar um novo) para adicionar, nesta ordem: `#attached library`, classes/placeholder em `name`/`pass`, classes em `actions.submit`.

**Rationale**: FR-004/FR-005/FR-006/FR-013; a submissão de redirecionamento por papel (FR-010) já vive nesse hook — centralizar evita duplicar `hook_form_user_login_form_alter` em dois módulos/arquivos (proibido por FR-013).

**Alternatives considered**: novo hook em `default.theme` (`default_form_user_login_form_alter`) — rejeitado explicitamente pelo FR-013 ("sem duplicar a mesma responsabilidade no tema").

---

## R7 — Biblioteca CSS isolada, anexada só nesta rota

**Decision**: `default/login_card` → `assets/css/components/login.css`, com `#attached library` adicionado dentro do próprio `custom_panel_form_user_login_form_alter()` (o form só existe nessa rota, então o attach já é implicitamente escopado).

**Rationale**: FR-011; Performance Goals do plan — evita que o CSS do login seja baixado em todas as páginas (diferente de se fosse colocado em `global-styling`/`style.css`).

**Alternatives considered**: adicionar ao `style.css` global existente — rejeitado (aumenta payload global para uma única rota de baixíssimo tráfego recorrente); `hook_preprocess_page()` anexando a library por path — rejeitado (ponto de injeção duplicado em relação ao form alter, quando o form alter já cobre 100% dos casos em que a página é renderizada).

---

## R8 — Nenhuma config estrutural / nenhum `hook_update_N`

**Decision**: feature não cria nem altera content types, fields, views, displays ou block placements; `config/sync` permanece intocado; não se aplica o fluxo de `drupal-deploy-configs.mdc` (hook idempotente + `drush cex`).

**Rationale**: FR-015 ("rota e fluxo de autenticação não devem ser alterados — apenas apresentação"); todo o trabalho é Twig + CSS + Form API, que não passam por `config/sync`. Deploy fica restrito a `git pull` + `drush cr` (cache de Twig/registry/CSS).

**Alternatives considered**: nenhuma — item de verificação, não havia alternativa de design aqui, apenas confirmação do escopo antes da implementação.
