# Data Model: Refatoração da página de Login (`/user/login`)

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-26

Esta feature **não cria nem altera entidades Drupal** (sem content type, field, view, block_content ou placement novo). O "modelo" abaixo documenta a superfície de apresentação (Twig/CSS/Form API) que substitui o conceito de entidade para efeitos de rastreabilidade.

## 1. Template suggestion (novo)

| Aspecto | Valor |
|---------|--------|
| Arquivo | `themes/custom/default/templates/layout/page--user--login.html.twig` |
| Suggestion | `page__user__login` (gerada automaticamente pelo core a partir de `path.current` = `user/login`) |
| Escopo | somente rota `user.login`; não afeta `page.html.twig` nem outras suggestions (`page__user__password`, `page__user__register`) |
| Regiões consumidas | `header`, `highlighted`, `content`, `footer_first` (mesmas regiões do `page.html.twig`, sem sidebars/painel/content_full/featured) |
| Lógica Twig permitida | apenas `{% if page.header %}` / `{% if page.footer_first %}` (existência de região) — **nenhum** `if` sobre estado de formulário/erro |

## 2. Classes/seletores CSS (novo arquivo `assets/css/components/login.css`)

| Seletor | Papel |
|---------|-------|
| `.page-user-login` | raiz de escopo (body class nativa do core — já existente, reaproveitada) |
| `.login-card` | wrapper `.card` (flex column; `order` dos filhos) |
| `.login-card__title` | `<h1>` "Acesse sua conta" |
| `.page-user-login .region-highlighted` | `display: contents` (achata região para permitir `order` nos blocos filhos) |
| `#block-default-messages` | `order: 1` (mensagens de status, topo do card) |
| `.region-content` | `order: 2` (formulário) |
| `#block-default-primarylocaltasks` | `display: none` (aba "Entrar", redundante) |
| `#block-default-secondarylocaltasks` | `order: 3` (wrapper da aba "Esqueceu a senha?") |
| `.nav-link--user-register` | `display: none` (já existente — preservado, não duplicar regra) |
| `.nav-link--user-password` | restilizado como link azul discreto centralizado |
| `#block-default-pagetitle` | `display: none` (some só nesta rota; bloco global intacto) |
| `.captcha` | margens (`mb-3`/`mt-3` equivalentes) |

## 3. Form API — único ponto de injeção

| Aspecto | Valor |
|---------|--------|
| Hook | `custom_panel_form_user_login_form_alter()` (existente, estendido — **não** criar novo hook) |
| Arquivo | `modules/custom/custom_panel/custom_panel.module` |
| Adições | `#attached['library'][] = 'default/login_card'`; classes/placeholder em `form['name']` e `form['pass']`; classes em `form['actions']['submit']` |
| Preservado | `$form['#submit'][] = '_custom_panel_login_redirect';` (redirecionamento por papel, FR-010) |

| Campo | Classes adicionadas | Placeholder |
|-------|----------------------|--------------|
| `name` | `form-control form-control-lg rounded-3` | "Digite seu e-mail ou usuário" |
| `pass` | `form-control form-control-lg rounded-3` | "Sua senha" |
| `actions.submit` | `btn btn-primary btn-lg w-100 mt-4` | — |

## 4. Library (nova entrada em `default.libraries.yml`)

```yaml
login_card:
  css:
    component:
      assets/css/components/login.css: {}
  dependencies:
    - default/bootstrap
```

Anexada via `#attached` apenas dentro de `custom_panel_form_user_login_form_alter()` — nunca em `global-styling`.

## 5. Relacionamentos (quem consome o quê)

```text
Rota user.login (core)
  ├── suggestion automática → page__user__login
  │     └── template page--user--login.html.twig (novo)
  │           ├── page.header            (reaproveitado, sem mudança)
  │           ├── page.highlighted        → display:contents (CSS) → messages / tabs
  │           ├── page.content            → system_main_block (user_login_form)
  │           │     └── custom_panel_form_user_login_form_alter() (estendido)
  │           │           ├── #attached library default/login_card
  │           │           ├── classes/placeholder name, pass
  │           │           └── classes actions.submit
  │           └── page.footer_first       (reaproveitado, sem mudança)
  └── body class .page-user-login (automática, core) → raiz do CSS escopado
```

## 6. Regras de validação / fallback (equivalente a "edge cases")

| Estado | Comportamento |
|--------|----------------|
| CAPTCHA desativado (`captcha.captcha_point.user_login_form` desabilitado) | card renderiza normalmente, sem o espaço de `.captcha` — regra CSS não assume presença fixa |
| Erro de login (usuário/senha inválidos) | mensagem renderiza via `#block-default-messages`, `order: 1`, dentro do card, acima do form |
| `destination` presente na URL (ex.: redirecionado de página protegida) | comportamento de `_custom_panel_login_redirect` preservado (early return já existente) |
| Papel do usuário após login (candidato/empresa/moderador) | redirecionamento preservado, nenhuma mudança nesta feature |

## 7. Config YAML (checklist)

**Nenhum arquivo em `config/sync/` é criado, alterado ou precisa de `drush cex`.** Reutilizados sem alteração:

- `captcha.captcha_point.user_login_form.yml`
- `block.block.default_messages.yml`
- `block.block.default_primary_local_tasks.yml`
- `block.block.default_secondary_local_tasks.yml`
- `block.block.default_content.yml` (inclui `default_page_title`)

## Fora do modelo

- Qualquer content type, field, view, paragraph ou block_content novo.
- Alteração em `user.routing.yml`, `user.links.task.yml` ou qualquer rota/local task do core.
- Alteração nos placements de bloco existentes (`config/sync`).
