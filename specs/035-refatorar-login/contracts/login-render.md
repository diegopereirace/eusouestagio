# Contract: Renderização do Card de Login (`/user/login`)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-10-26
**Consumidor**: tema `default` (template suggestion `page--user--login.html.twig` + CSS)
**Produtor**: rota core `user.login` (`system_main_block` / `user_login_form`) + regiões `header`, `highlighted`, `content`, `footer_first`

## Objetivo

Contrato de apresentação estável para visitante em `/user/login` e revisão visual frente ao pedido do usuário (card centralizado). Não é API HTTP. **Isolamento**: este contrato **não** se aplica a `/user/password` nem `/user/register` (suggestions próprias, não tocadas).

## Estrutura DOM esperada (desktop)

```html
<body class="page-user-login ...">
  <div id="page-wrapper">
    <div id="page">
      <header id="header" class="header container">
        <nav>{{ page.header }}</nav> <!-- idêntico ao resto do site -->
      </header>

      <main class="login-page">
        <div class="container py-5">
          <div class="row justify-content-center align-items-center login-page__row">
            <div class="col-12 col-md-6 col-lg-4">
              <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 login-card">
                <h1 class="login-card__title">Acesse sua conta</h1>

                <!-- page.highlighted, achatado via display:contents -->
                <div id="block-default-messages" class="block" style="order:1">
                  <!-- mensagens de erro/sucesso, se houver -->
                </div>

                <div class="region-content" style="order:2">
                  <form id="user-login-form" class="user-login-form">
                    <div class="form-item">
                      <input class="form-control form-control-lg rounded-3" placeholder="Digite seu e-mail ou usuário" name="name">
                    </div>
                    <div class="form-item">
                      <input class="form-control form-control-lg rounded-3" placeholder="Sua senha" name="pass" type="password">
                    </div>
                    <div class="captcha"><!-- reCAPTCHA, se ativo --></div>
                    <div class="form-actions">
                      <button class="btn btn-primary btn-lg w-100 mt-4">Entrar</button>
                    </div>
                  </form>
                </div>

                <div id="block-default-primarylocaltasks" style="display:none"><!-- aba "Entrar", oculta --></div>
                <div id="block-default-secondarylocaltasks" style="order:3">
                  <ul class="tabs">
                    <li><!-- "Criar nova conta" — display:none, regra já existente --></li>
                    <li><a class="nav-link--user-password">Esqueceu a senha?</a></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      <footer class="site-footer-custom pt-5">
        <div class="container">{{ page.footer_first }}</div>
      </footer>
    </div>
  </div>
</body>
```

Notas:
- `#block-default-pagetitle` (bloco de título padrão da rota) é renderizado pelo core dentro de `page.content`, mas fica `display: none` sob `.page-user-login` — não removido do DOM, só oculto.
- A ordem visual final (mensagens → formulário → "Esqueceu a senha?") é **puramente CSS** (`display: contents` + `order`); a ordem real no DOM é mensagens/tabs (região `highlighted`) antes do formulário (região `content`) — ver [research.md](../research.md) R3.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente sob `.page-user-login` |
| Library | `default/login_card` → `assets/css/components/login.css`, anexada via `#attached` no form alter |
| Wrapper grid | `.container.py-5` → `.row.justify-content-center.align-items-center` (`min-height: ~70vh`) → `.col-12.col-md-6.col-lg-4` |
| Card | `.card.border-0.shadow-sm.rounded-4.p-4.p-lg-5` |
| Título | Poppins semibold/bold, `h1.login-card__title`, acima dos campos |
| Inputs (`name`, `pass`) | `form-control form-control-lg rounded-3`, placeholders definidos via Form API |
| Botão submit | `btn btn-primary btn-lg w-100 mt-4`, fundo `var(--brand-orange)` (`#ff8a22`), texto branco negrito |
| Link "Esqueceu a senha?" | discreto, centralizado, abaixo do botão, cor azul padrão do tema, ícone nativo (`::before`) oculto |
| Aba "Criar nova conta" | `display: none` (regra já existente, preservada) |
| Aba "Entrar" | `display: none` (nova regra, decisão R4) |
| CAPTCHA (`.captcha`) | margem superior/inferior equivalente a `mb-3`, sem comprimir/distorcer o card |

## Contrato responsivo

| Viewport | Layout |
|----------|--------|
| ≥768px (`md+`) | card `col-lg-4` centralizado, padding `p-lg-5` |
| <768px | card `col-12`, padding reduzido `p-4`, mesma hierarquia visual |

## Contrato de comportamento (preservado, sem regressão)

| Item | Regra |
|------|-------|
| Redirecionamento pós-login por papel | `_custom_panel_login_redirect` continua no `#submit`, sem alteração de lógica |
| `destination` na query string | continua tendo prioridade sobre o redirecionamento por papel (early return já existente) |
| Flood control / validação de credenciais | inalterados (core `user.login`) |
| `/user/password`, `/user/register` | visual atual mantido; suggestions próprias não tocadas por esta feature |

## Contrato de fallback

| Estado | Comportamento |
|--------|----------------|
| Sem mensagens de erro | `#block-default-messages` permanece no DOM mas vazio (core já trata `status_messages` vazio sem markup visível) |
| CAPTCHA desativado | `.captcha` ausente; card não fica com espaço vazio quebrando o layout |
| JavaScript desabilitado | reordenação via CSS continua funcionando (sem dependência de JS) |
| Erro de template/CSS ausente | formulário continua funcional (sem card), nunca quebra o fluxo de login — proibido erro Twig fatal |

## Não garantido por este contrato

- Paridade pixel-perfect com o Figma fora dos tokens listados (±2px).
- Comportamento de provedores de autenticação externos (ex.: SSO), caso existam, fora do escopo desta feature.
