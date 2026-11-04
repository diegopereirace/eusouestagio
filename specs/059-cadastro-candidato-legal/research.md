# Research: Cadastro de Candidato (UX) e Páginas Legais

**Data**: 2026-11-04 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **idempotência de seed**, **padrão Form API + page suggestion (035)**, **zero dois banners**, e **redirect canônico do painel**.

---

## R1 — Page suggestion + card (padrão 035)

**Decision**: Criar `themes/custom/default/templates/layout/page--cadastro--candidato.html.twig` usando a suggestion nativa `page__cadastro__candidato` (path `/cadastro/candidato`). Estrutura:

- Header + footer do site (mesmo espírito do login).
- Banner de topo fora do card (full-bleed relativo ao main).
- Card Bootstrap (`border-0 shadow-sm rounded-4`) em coluna limitada (`col-12 col-lg-6` ou similar) com `{{ page.highlighted }}` + `{{ page.content }}`.
- Título de rota do bloco `default_page_title` oculto via CSS em `.page-cadastro-candidato` (H1 do banner é a mensagem de valor).

**Rationale**: FR-007 / FR-015; Drupal já gera a suggestion via `theme_get_suggestions`; zero lógica no `page.html.twig` compartilhado.

**Alternatives considered**:
- Só CSS no `page.html.twig` global — rejeitado (acopla demais rotas).
- Wrapper só no `$form['#prefix']` — rejeitado (banner de página e title block ficam fora do form).

---

## R2 — Banner `<picture>` estático vs View legado

**Decision**:

1. Renderizar banner no Twig com `<picture>`: source mobile (~800×600), img desktop (~1920×400), overlay escuro, H1 *“Crie sua conta e encontre seu estágio ideal”*.
2. Assets em `themes/custom/default/assets/images/cadastro-candidato/` (placeholders estudantes/carreira).
3. `loading="eager"` + `fetchpriority="high"` se for LCP; `decoding="async"`.
4. Desligar placement `default_views_block__banners_block_2` (`status: false`) via `drush cex` + ensure no `11089` (FR-013).
5. Fallback CSS: fundo navy/sólido + título se URI quebrada.

**Rationale**: Spec assume substituição do View; assets de tema evitam File entity e política 057; View legado deixa de competir na região `banner`.

**Alternatives considered**:
- Manter View + só CSS — rejeitado (dois banners / copy CMS vs título fixo da spec).
- Seed File em `public://` — rejeitado para MVP (complexidade 057 sem field editorial).

---

## R3 — Form API: classes, aceite, library

**Decision**:

- Anexar `default/cadastro_candidato` via `#attached` no form (alter ou `buildForm`).
- Inputs texto/e-mail/senha: `form-control form-control-lg`; labels: classes `form-label fw-bold text-dark` (via `#title_display` / `#label_attributes` / wrapper).
- Submit: `btn btn-primary btn-lg w-100 mt-4`; CSS da página força background/border `#FD7B1A`.
- Checkbox: wrapper `form-check`, input `form-check-input`.
- Description de `field.field.user.user.field_termo` → links reais com `target="_blank" rel="noopener noreferrer"`; form já faz `Markup::create(Xss::filter(..., ['a',...]))`.
- Não alterar templates de elemento do core; `/cadastro/empresa` intocado (empresa hardcoda o próprio `#title` do termo).

**Rationale**: FR-008–FR-011 / FR-015; campo compartilhado — description só afeta quem a consome (candidato).

**Alternatives considered**:
- Hardcode do título só no Form (como empresa) sem cex — rejeitado (description no FieldConfig já é a fonte; sync via cex é o padrão deploy).
- Classes só em CSS por `input[type]` — rejeitado (menos previsível com selects/AJAX).

---

## R4 — Seed das páginas legais (idempotência + alias)

**Decision**: Helper `_custom_configs_ensure_legal_page()` (ou dois wrappers) em `custom_configs.install`, chamado por `custom_configs_update_11089`:

| Página | UUID fixo (proposta) | Alias |
|--------|----------------------|-------|
| Termos de Uso | `a1b2c3d4-e5f6-4789-a012-3456789abc01` | `/termos-de-uso` |
| Política de Privacidade | `a1b2c3d4-e5f6-4789-a012-3456789abc02` | `/politica-de-privacidade` |

Algoritmo:

1. Se Node com UUID existe → reusar; **não** sobrescrever `body` se não vazio.
2. Se UUID ausente e alias já aponta para um `page` publicado → **reusar** esse Node; garantir título se vazio; preencher body só se vazio; **não** criar segundo Node.
3. Se nada existe → criar `page` publicado + body placeholder HTML (h2 + parágrafos) + alias `langcode: pt-br`.
4. Se Node seed existe sem alias → criar alias faltante.
5. Reexecução = mensagens created/skipped; zero duplicata.

**Rationale**: FR-001–FR-004 / edge cases; diverge do Contato só no tratamento de alias alheio (Contato **deleta** legado de migração; aqui a spec manda reuso/no-op).

**Alternatives considered**:
- Copiar delete-agressivo de `_custom_configs_ensure_contato_page` — rejeitado (risco de apagar página editorial).
- Só `pathauto` / menu links — rejeitado (rodapé já hardcoda URLs; precisa Node real).

---

## R5 — Redirect pós-cadastro

**Decision**: Em `CandidatoRegistrationForm::submitForm`, no ramo `user_login_finalize`, usar `setRedirect('custom_panel.painel_estudante_perfil')` em vez de `custom_configs_users.candidato_perfil` (`/meu-perfil`). Demais ramos (e-mail verify / pending) permanecem `<front>`.

**Rationale**: FR-016 / SC-005; paridade com `_custom_panel_login_redirect` (035). `/meu-perfil` continua existindo como rota legada, fora do escopo de remoção.

**Alternatives considered**:
- Alias `/meu-perfil` → painel — rejeitado (duas rotas de produto; painel tem `_role: candidato` e layout do custom_panel).
- Só documentar divergência — rejeitado (SC-005 falharia).

---

## R6 — Deploy e número do hook

**Decision**:

- Hook **`custom_configs_update_11089`** (11088 é o último).
- Config exportável: `field.field.user.user.field_termo` + `block.block.default_views_block__banners_block_2`.
- Destino: `cim` → `updb` → `cim` → `cr`.
- Se só o hook rodasse sem cex do block, o ensure no 11089 ainda força `status: false` no placement.

**Rationale**: `drupal-deploy-configs.mdc`; seed de conteúdo não vive só em sync.

**Alternatives considered**: Só `updb` sem cex do field — rejeitado (description do aceite ficaria só no código do form, fora do sync).

---

## R7 — Cor `#FD7B1A` vs token global

**Decision**: Não alterar `--brand-orange` global. Sob `.page-cadastro-candidato` (ou `.cadastro-candidato-card`), forçar `.btn-primary` do submit para `#FD7B1A` / hover levemente mais escuro.

**Rationale**: Spec e Contato; evita regressão visual site-wide.

**Alternatives considered**: Atualizar token global para `#FD7B1A` — fora de escopo.

---

## R8 — CAPTCHA e validação

**Decision**: Não alterar ponto CAPTCHA nem `forms-validation.js` além do necessário para o checkbox continuar validando. CSS: margem no `.captcha` dentro do card. Local: `$settings['disable_captcha'] = TRUE` já suportado.

**Rationale**: FR-017; layout não pode quebrar o widget.

**Alternatives considered**: Remover CAPTCHA — rejeitado (segurança).
