# Quickstart: Refatoração da página de Login — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Comandos: `docker compose exec drupal drush <cmd>` a partir da raiz do projeto.

Modelo: [data-model.md](data-model.md). Render: [contracts/login-render.md](contracts/login-render.md).

## Pré-requisitos

1. Stack local no ar.
2. Branch com `page--user--login.html.twig`, `assets/css/components/login.css`, `default.libraries.yml` (+ `login_card`) e `custom_panel.module` (hook estendido) commitados.
3. **Nenhuma** config estrutural nova — sem `cim`/`updb` necessários (ver [research.md](research.md) R8).

---

## A) Deploy (apenas cache — sem config estrutural)

### Qualquer ambiente

```bash
git pull
docker compose exec drupal drush cr
```

Não há `drush cex`/`cim`/`updb` nesta feature (checklist "Diretriz permanente" de `drupal-deploy-configs.mdc` não se aplica — zero alteração em content types/fields/views/placements).

---

## B) Visitante desktop (US1 → SC-001, SC-002, SC-003)

1. Abrir `/user/login` em viewport ≥768px (deslogado).
2. Confirmar card branco centralizado (`shadow-sm`, `rounded-4`), não mais ocupando 100% da largura.
3. Confirmar título "Acesse sua conta" acima dos campos (Poppins semibold).
4. Confirmar campos `name`/`pass` com `form-control-lg`, cantos arredondados, placeholders ("Digite seu e-mail ou usuário", "Sua senha").
5. Confirmar botão "Entrar" largura total do card, fundo laranja da marca, texto branco negrito.
6. Confirmar link "Esqueceu a senha?" discreto, centralizado, **abaixo** do botão, azul.
7. Confirmar que a aba "Criar nova conta" **não** aparece (regra preservada) e que não há aba "Entrar" duplicada acima do card.

---

## C) Login válido (US2 → SC-002, SC-005)

1. Preencher credenciais válidas de cada papel (candidato, empresa, moderador/administrator) e enviar.
2. Confirmar redirecionamento para o painel correspondente (`/painel/estudante/perfil`, `/painel/empresa/perfil`, `/painel/moderador/candidaturas`), sem regressão.
3. Repetir com `?destination=/alguma-rota` na URL de login — confirmar que o destino tem prioridade sobre o redirecionamento por papel.

---

## D) Erros e CAPTCHA (US3 → SC-004)

1. Enviar credenciais inválidas — confirmar que a mensagem de erro aparece **dentro** do card, acima dos campos, sem quebrar o layout.
2. Com reCAPTCHA ativo (`captcha.captcha_point.user_login_form` habilitado), confirmar espaçamento adequado (`mb-3` equivalente) entre captcha, campos e botão — sem compressão/distorção do card.

---

## E) Mobile (US4 → SC-001)

1. Viewport <768px: card ocupa `col-12`, padding interno reduzido, mesma hierarquia visual do desktop (título → campos → botão → link).
2. Sem scroll horizontal causado pelo card.

---

## F) Twig puro / Form API centralizada (US5 → SC-006)

1. Revisar `page--user--login.html.twig`: confirmar ausência de `if`/lógica sobre estado do formulário (só existência de região, igual ao `page.html.twig` original).
2. Revisar `custom_panel.module`: confirmar que `custom_panel_form_user_login_form_alter()` é o único ponto injetando classes/placeholders/`#attached` do formulário de login (nenhuma duplicação em `default.theme`).

---

## G) Isolamento de rota (sem regressão)

1. `/user/password`: visual atual mantido (sem card novo).
2. `/user/register`: visual atual mantido (sem card novo).
3. Qualquer outra rota do site: `page.html.twig` compartilhado inalterado (diff vazio nesse arquivo).

---

## Checklist "deploy fácil"

| # | Critério | OK? |
|---|----------|-----|
| 1 | Zero alteração em `config/sync/` | |
| 2 | `page.html.twig` e `form.html.twig` do core sem diff | |
| 3 | `drush cr` suficiente para publicar (sem `cim`/`updb`) | |
| 4 | `/user/password` e `/user/register` sem regressão visual | |
| 5 | Redirecionamento por papel (`_custom_panel_login_redirect`) sem regressão | |
| 6 | CSS anexado apenas em `/user/login` (library isolada) | |

---

## Próximo passo

Se a validação do plano estiver OK: `/speckit-tasks` → implementar na ordem do plan.
