# Implementation Plan: Refatoração da página de Login (`/user/login`)

**Branch**: `feature-vaga` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` ausente neste repo, setup executado manualmente)
**Date**: 2026-10-26
**Spec**: [spec.md](spec.md)
**Input**: Especificação em `specs/035-refatorar-login/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Transformar `/user/login` (rota core `user.login`) em um card centralizado (fundo branco, sombra sutil, cantos arredondados), sem tocar `page.html.twig` nem `form.html.twig` do core. Mecanismo central: **template suggestion de página** `page--user--login.html.twig` — Drupal já gera `page__user__login` automaticamente (via `theme_get_suggestions()` a partir de `path.current` em `SystemThemeHooks::themeSuggestionsPage()`), e o body já recebe a classe `page-user-login` pelo mesmo mecanismo em `theme_suggestions_html`. Logo, **nenhuma lógica condicional nova** é necessária no tema — apenas um template dedicado e isolado para essa rota. Classes Bootstrap, placeholders e `#attached library` do formulário são centralizados no hook já existente `custom_panel_form_user_login_form_alter()`. Reposicionamento do link "Esqueceu a senha?" (tab nativa `user.pass`) para o rodapé do card é feito 100% em CSS via `display: contents` (achata a region `highlighted` dentro do flex do card) + `order`, sem JS e sem duplicar regiões.

## Technical Context

**Language/Version**: PHP 8.4, Drupal 11.4+, Twig 3, CSS3 (Bootstrap 5.3 via `bootstrap_barrio`)
**Primary Dependencies**: Drupal core (`user`, `system`, `captcha`/`recaptcha` já ativos); tema `default`; módulo custom `custom_panel`. **Nenhuma dependência Composer nova.**
**Storage**: N/A — feature 100% de apresentação (Twig + CSS + Form API); nenhuma config estrutural (fields/content types/views/block placements) é criada ou alterada.
**Testing**: validação manual via [quickstart.md](quickstart.md); sem suite PHPUnit dedicada.
**Target Platform**: site público Drupal, rota `/user/login` (desktop + mobile, breakpoint Bootstrap `md` 768px).
**Project Type**: Drupal theme (`themes/custom/default`) + módulo custom (`modules/custom/custom_panel`).
**Performance Goals**: CSS em library dedicada, anexada **apenas** nesta rota via `#attached` no form alter (zero peso extra nas demais páginas); sem JS novo.
**Constraints**: sem `core/`/`vendor/`; sem reescrever `form.html.twig`/`page.html.twig`; sem novo content type/field/view/placement (logo, sem `hook_update_N`/`drush cex` — nada em `config/sync` muda); Twig da página de login só com marcação estrutural (grid/card), zero `if` de estado de formulário; `/user/password` e `/user/register` inalterados; pt-BR.
**Scale/Scope**: 1 template Twig novo (`page--user--login.html.twig`), 1 CSS novo + 1 entrada em `default.libraries.yml`, extensão pontual de 1 hook existente (`custom_panel_form_user_login_form_alter`) em `custom_panel.module`.

**Estado atual verificado (2026-10-26):**

- `custom_panel_form_user_login_form_alter()` já existe em `modules/custom/custom_panel/custom_panel.module` (linhas 24–30) e só adiciona `_custom_panel_login_redirect` ao `#submit` — será estendido, não recriado (preserva FR-010).
- `.page-user-login` já é gerado automaticamente pelo core (`theme_suggestions_html` → `theme_get_suggestions($path_args, 'html')`) — já usado hoje em `style.css:3161-3165` para ocultar a aba "Criar nova conta" (`.nav-link--user-register`). **Nenhum código novo necessário para esse body class.**
- `page__user__login` é gerado automaticamente pelo core (`SystemThemeHooks::themeSuggestionsPage()`, `core/modules/system/src/Hook/SystemThemeHooks.php:172-187`, mesmo princípio de `theme_get_suggestions`). Basta criar `templates/layout/page--user--login.html.twig` — Drupal já prioriza essa suggestion sobre `page.html.twig` nessa rota, sem tocar o template compartilhado.
- Classes de tab nativas confirmadas em `bootstrap_barrio/css/components/bootstrap-icons.css`: `.nav-link--user-login` (aba "Entrar", ativa/redundante nesta página), `.nav-link--user-register` (já oculta), `.nav-link--user-password` (= "Esqueceu a senha?" / `user.pass`, local task definida em `core/modules/user/user.links.task.yml`).
- Blocos na região `highlighted` do tema `default` (pesos crescentes): `default_messages` (`system_messages_block`, w-6), `default_primary_local_tasks` (w-5), `default_secondary_local_tasks` (w-4) — todos sem `visibility` restrita, portanto renderizam em `/user/login`. Outros blocos dessa região (busca de vagas, ações admin, view de vagas) têm visibilidade própria e não aparecem nessa rota — comportamento herdado, não requer ajuste.
- Região `content` do tema `default` tem **dois** blocos: `default_content` (`system_main_block` — o formulário) e `default_page_title` (título da rota, ex. "Acessar"/"Log in") — este último será ocultado **somente** em `.page-user-login` via CSS (não removido globalmente), para dar lugar ao título customizado "Acesse sua conta" definido estaticamente no Twig da página.
- `captcha.captcha_point.user_login_form.yml` confirma `captchaType: recaptcha/reCAPTCHA` ativo no formulário — CSS precisa prever espaçamento para o wrapper `.captcha` (FR-008), sem assumir que está sempre presente.
- `--brand-orange: #ff8a22;` e `--brand-navy: #003060;` já definidas em `style.css:11-13` — reutilizar via `var(--brand-orange)` (equivalente ao `#FD7B1A` citado no pedido do usuário; evita duplicar token de cor).
- `default.libraries.yml` usa convenção `assets/css/components/*.css` para libraries de página/feature mais recentes (`hero-search`, `vagas-detalhe`, `empresa-pagina`) — nova library segue o mesmo padrão.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + `checklists/requirements.md` OK, zero `[NEEDS CLARIFICATION]` |
| Sem `core/` / `vendor/` | PASS | só `themes/custom/default` e `modules/custom/custom_panel` |
| `page.html.twig` / `form.html.twig` do core intactos | PASS | suggestion dedicada `page--user--login.html.twig`; nenhum override de `form.html.twig` |
| Deploy de config estrutural (`drupal-deploy-configs.mdc`) | N/A | nenhum field/content type/view/display/placement criado ou alterado; `config/sync` não muda; sem `hook_update_N` necessário |
| Twig sem lógica condicional de estado (FR-012) | PASS | template da página só com marcação estrutural (grid/card) + 2 `if` defensivos de existência de região (mesmo padrão de `page.html.twig` original) |
| Injeção de classes/atributos do form centralizada (FR-013) | PASS | único ponto: `custom_panel_form_user_login_form_alter()` |
| CSS escopado (FR-011) | PASS | seletor raiz `.page-user-login`; library anexada só nesta rota |
| PRD guardião (`estagio-prd-guardian.mdc`) | N/A (confirmar na implementação) | alteração em `*.module` é puramente de apresentação (classes/placeholders/attach de library), sem schema/serviço novo — tratar como mudança de frontend; perguntar explicitamente antes do commit se o editor entender que há impacto de produto |
| Reinício de cache (`estagio-reiniciar-servicos.mdc`) | A executar na implementação | `drush cr` após publicar Twig/CSS/hook (sem `cim`/`updb`, pois não há config estrutural) |

**Post-design**: nenhuma violação; a única "complexidade" (técnica de `display: contents` para reposicionar a tab nativa) está documentada abaixo com alternativas rejeitadas.

## Design Decisions

1. **Template suggestion de página** `page--user--login.html.twig` (não editar `page.html.twig`): mecanismo nativo do core (`page__user__login`), zero acoplamento com as demais rotas.
2. **Header/footer replicados** (não `include`) a partir da estrutura atual de `page.html.twig` (região `header`, região `footer_first`) para manter a identidade visual do site (nav, logo, rodapé) — sem sidebars, `content_full`, `featured_*` ou layout de painel, que não se aplicam a `/user/login`.
3. **Card/grid**: `.container.py-5` → `.row.justify-content-center.align-items-center` (`min-height: 70vh`) → `.col-12.col-md-6.col-lg-4` → `.card.border-0.shadow-sm.rounded-4.p-4.p-lg-5` com classe de escopo `login-card`.
4. **Título estático** `<h1 class="login-card__title">{{ 'Acesse sua conta'|t }}</h1>` direto no Twig da página (texto fixo, sem lógica — não é "lógica condicional" vedada pela FR-012) + ocultação do bloco `default_page_title` só em `.page-user-login` via CSS (evita título duplicado "Acessar" vs. "Acesse sua conta").
5. **Mensagens + tabs nativas**: `{{ page.highlighted }}` e `{{ page.content }}` são emitidos como estão (mesmo padrão do `page.html.twig` original) dentro do `.login-card`, que vira `display:flex; flex-direction:column`. Para permitir que as mensagens de status fiquem acima do formulário (natural, ordem de peso já é essa) e a tab "Esqueceu a senha?" fique **abaixo** do botão de envio, `.page-user-login .region-highlighted { display: contents; }` achata o wrapper da região, promovendo `#block-default-messages`, `#block-default-primarylocaltasks` e `#block-default-secondarylocaltasks` a itens flex diretos do `.login-card`, cada um com seu próprio `order` CSS:
   - `#block-default-messages` → `order: 1` (antes do formulário)
   - `.region-content` (form) → `order: 2`
   - `#block-default-primarylocaltasks` (aba "Entrar") → `display: none` (redundante nesta página; não remove a rota, só oculta visualmente, mesmo padrão já aplicado à aba de registro)
   - `#block-default-secondarylocaltasks` → `order: 3`, com `.nav-link--user-register` oculto (regra já existente, preservada) e `.nav-link--user-password` restilizado como link azul discreto centralizado (ícone `::before` do Barrio ocultado via `content: none`)
6. **Form API único ponto** (`custom_panel_form_user_login_form_alter()`): adiciona classes Bootstrap (`form-control form-control-lg rounded-3`) e placeholders aos campos `name`/`pass`, classes (`btn btn-primary btn-lg w-100 mt-4`) no `actions.submit`, e `#attached library` da nova library — sem duplicar essa responsabilidade no tema.
7. **CSS/library dedicada**: `default/login_card` → `assets/css/components/login.css`, anexada apenas via `#attached` no form alter (não em `global-styling`), seguindo o padrão de libraries isoladas por feature do tema.
8. **CAPTCHA (FR-008)**: regra CSS genérica `.page-user-login .captcha { margin-top: 1rem; margin-bottom: 1rem; }` — não assume presença fixa (ponto `user_login_form` está ativo hoje, mas a regra não quebra se for desativado).
9. **Sem mudança de config estrutural**: nenhum `hook_update_N`, nenhum `drush cex` — reforça o gate N/A de `drupal-deploy-configs.mdc`. Deploy = apenas `drush cr` (cache de Twig/CSS/registry de hooks).
10. **Fora**: `/user/password`, `/user/register` (suggestions próprias, `page__user__password` / `page__user__register`, não afetadas); qualquer lógica de autenticação/flood control.

## Project Structure

### Documentation (this feature)

```text
specs/035-refatorar-login/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   └── login-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
  templates/layout/page--user--login.html.twig   # novo (suggestion page__user__login)
  assets/css/components/login.css                 # novo
  default.libraries.yml                            # + login_card

modules/custom/custom_panel/
  custom_panel.module                              # estende custom_panel_form_user_login_form_alter()
```

Nenhum arquivo em `config/sync/` é criado ou alterado.

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Twig `page--user--login.html.twig` (grid/card, sem lógica condicional de estado)
3. CSS `login.css` (card, inputs, botão, tabs reposicionadas via `display: contents` + `order`, captcha, responsivo)
4. Extensão de `custom_panel_form_user_login_form_alter()` (classes, placeholders, `#attached`)
5. `drush cr` na origem; validação via [quickstart.md](quickstart.md)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|-----------------------|----------------|------------------------|
| `display: contents` para achatar `.region-highlighted` e reordenar blocos nativos via `order` | Única forma 100% CSS (sem JS, sem tocar core) de colocar a tab nativa "Esqueceu a senha?" abaixo do botão de envio, já que ela vive em `page.highlighted` (antes de `page.content` no DOM) e não no `$form` | JS para mover o nó no DOM (rejeitado: acopla comportamento visual a script, risco de FOUC); duplicar `{{ page.highlighted }}` no Twig (rejeitado: renderiza blocos duas vezes, efeitos colaterais de render array); mover local tasks para a região `content` via config de bloco (rejeitado: afeta layout do bloco em todas as páginas que usam `highlighted`, não é escopado) |
| Replicar markup de header/footer no novo template em vez de reaproveitar `page.html.twig` | Suggestion de página é um template completo e independente por definição do core; replicar é o padrão já aceito no projeto para esse mecanismo | Incluir `page.html.twig` e sobrescrever trechos via `embed`/`block` Twig — não suportado pela arquitetura de suggestions do Drupal (a suggestion substitui o arquivo inteiro, não é herança parcial) |
