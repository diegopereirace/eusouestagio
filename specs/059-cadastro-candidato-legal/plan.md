# Implementation Plan: Cadastro de Candidato (UX) e Páginas Legais

**Branch**: `main` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo; setup executado manualmente a partir de `.specify/feature.json` → `specs/059-cadastro-candidato-legal`)  
**Date**: 2026-11-04  
**Spec**: [spec.md](spec.md) | **Checklist**: [checklists/requirements.md](checklists/requirements.md)  
**Predecessors**: `058-refino-figma-contato`, `035-refatorar-login`, `008-rodape-redesign`, `057-seed-arquivos-imutaveis`  
**Hook**: `custom_configs_update_11089` (seed Páginas Básicas legais + aliases + desligar banner View legado)  
**Input**: Especificação em `specs/059-cadastro-candidato-legal/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar **duas frentes** na mesma feature, sem passos manuais em produção:

1. **Páginas legais**: seed idempotente de Nodes `page` “Termos de Uso” e “Política de Privacidade” com aliases estritos `/termos-de-uso` e `/politica-de-privacidade`, corpo HTML placeholder (só se vazio), sem sobrescrever editorial (padrão Contato / Para Estudantes).
2. **UX `/cadastro/candidato`**: page suggestion com banner `<picture>` (desktop/mobile) + card centralizado; classes Bootstrap grandes / botão laranja / aceite com links reais via Form API; desligar View `banners` `block_2` nesta rota para evitar dois banners; alinhar redirect pós-cadastro com login automático para `custom_panel.painel_estudante_perfil` (`/painel/estudante/perfil`).

Padrão visual e de Form API espelha `035-refatorar-login`. Sem Figma. Sem redesenhar `/cadastro/empresa`.

## Technical Context

| Item | Valor |
|------|--------|
| Language/Version | PHP 8.3+ / 8.4, Drupal 11, Twig 3, CSS3 (Bootstrap 5 / Barrio) |
| Primary Dependencies | `custom_configs_users` (`CandidatoRegistrationForm`), tema `default`, `path_alias`, CAPTCHA/reCAPTCHA já existentes. **Sem** Composer novo |
| Storage | Nodes `page` (seed); `field.field.user.user.field_termo` (description com links); placement `default_views_block__banners_block_2` desabilitado/removido desta rota. Sem CT/field storage novos |
| Testing | Manual via [quickstart.md](quickstart.md); sem PHPUnit dedicado |
| Target Platform | `/cadastro/candidato`, `/termos-de-uso`, `/politica-de-privacidade`; desktop ≥992px e mobile ≤575px |
| Performance | Library CSS dedicada anexada só nesta rota; banner LCP com `loading="eager"` + dimensões intrínsecas; assets estáticos do tema (sem File entity obrigatório) |
| Constraints | Sem `core/`/`vendor/`; Twig só estrutura (banner + card); classes/aceite/library via Form API; escopo CSS à rota; política 057 se houver seed de File; idempotência de seed |
| Scale/Scope | 1 page Twig + 1 CSS + library; alter/form do candidato; `hook_update_N` 11089; cex de field_termo + block placement; PRD §10 / rodapé |

**Estado atual verificado (2026-11-04):**

- Último hook: **`custom_configs_update_11088`** → próximo livre **`11089`** (058 não criou hook).
- Rota: `custom_configs_users.candidato_registration` → `/cadastro/candidato`, form ID `custom_configs_users_candidato_registration_form`, `_user_is_logged_in: FALSE`.
- Form já tem seções (`section_acesso`, `section_pessoal`, `section_endereco`, formação etc.), CAPTCHA condicional (`Settings::get('disable_captcha')`), `field_termo` lendo **description** de `FieldConfig` (hoje `href="#"`).
- Submit sucesso com login automático: `setRedirect('custom_configs_users.candidato_perfil')` → **`/meu-perfil`** — **diverge** da rota canônica do painel `custom_panel.painel_estudante_perfil` → `/painel/estudante/perfil` (login 035 já usa o painel). Feature **corrige** o redirect (FR-016 / SC-005).
- Banner legado: `block.block.default_views_block__banners_block_2` região `banner`, pages `/cadastro/candidato`, `status: true`.
- Rodapé já aponta para `/termos-de-uso` e `/politica-de-privacidade` (008); páginas **ainda não seedadas**.
- Login card: `page--user--login.html.twig` + `default/login_card` + alter em `custom_panel` — referência de card/Form API.
- Suggestion de página para path `/cadastro/candidato`: nativa `page__cadastro__candidato` → `page--cadastro--candidato.html.twig`.
- Cor marca: spec `#FD7B1A`; token global `--brand-orange: #ff8a22` — nesta página override local no submit (como Contato).
- `mattpocock/skills`: não instalado; CSS plano do repo.

## Constitution Check

Não há `.specify/memory/constitution.md`. Gates: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + `estagio-fluxo-dev.mdc` + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist; zero `[NEEDS CLARIFICATION]` |
| Sem `core/` / `vendor/` | PASS | tema `default`, `custom_configs` / `custom_configs_users`, `config/sync` |
| Reuso / sem storages novos | PASS | Nodes `page` + field `field_termo` existente |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | seed 11089 + cex field description + block placement |
| Seeds Entity API / não sobrescrever editorial / 057 | PASS | body só se vazio; assets banner estáticos no tema (sem File seed) |
| Clean URLs | PASS | aliases estritos; links rodapé/checkbox |
| Performance / CSS isolado | PASS | library só em `/cadastro/candidato` |
| Contrib first / custom mínimo | PASS | Form API + page suggestion; CAPTCHA inalterado |
| PRD guardião | PASS | atualizar §10 rotas legais + nota cadastro (cirúrgico) |
| Reinício cache | A executar na implementação | `cim`/`updb`/`cr` |

**Post-design**: gates mantidos. Trade-offs em [research.md](research.md) e Complexity Tracking. Sem violação injustificada.

## Architecture

```text
/cadastro/candidato (form route)
├─ page--cadastro--candidato.html.twig
│    ├─ banner <picture> (desktop/mobile) + overlay + H1 fixo
│    └─ card centralizado → page.highlighted + page.content ({{ form }})
├─ library default/cadastro_candidato → cadastro-candidato.css
└─ Form API (buildForm e/ou form_alter)
     ├─ inputs lg + labels fw-bold
     ├─ field_termo links reais + form-check
     ├─ submit btn-primary lg w-100
     └─ #attached library
          + redirect submit → custom_panel.painel_estudante_perfil

/termos-de-uso  → node:page (UUID fixo)  ← update 11089
/politica-de-privacidade → node:page (UUID fixo) ← update 11089

Rodapé Twig (já aponta) ──resolvido──► aliases seed
```

## Design Decisions

1. **Page suggestion** `page--cadastro--candidato.html.twig` (padrão 035): banner + card; header/footer do site; ocultar `default_page_title` só nesta rota via CSS.
2. **Banner estático no Twig** com assets em `themes/custom/default/assets/images/cadastro-candidato/` (`hero-desktop.webp|jpg`, `hero-mobile.webp|jpg`); fallback CSS sólido + título se imagem 404 (R2).
3. **Desligar** `default_views_block__banners_block_2` (`status: false` ou visibility vazia) via config + ensure no 11089 — evita dois banners (FR-013).
4. **Form classes**: reforçar no `CandidatoRegistrationForm::buildForm` e/ou `hook_form_custom_configs_users_candidato_registration_form_alter` em `custom_configs_users.module` (preferir alter para `#attached` + aceite, espelhando 035); não tocar templates de elemento do core.
5. **Aceite**: atualizar description de `field.field.user.user.field_termo` para links `/termos-de-uso` e `/politica-de-privacidade` com `target="_blank"` + `rel="noopener noreferrer"`; form já aplica Xss filter com tag `a`. Empresa continua com título hardcoded próprio — sem regressão.
6. **Redirect**: no submit de sucesso com `user_login_finalize`, trocar para `custom_panel.painel_estudante_perfil` (alinha FR-016 / login 035). Fluxos verify-mail / pending approval permanecem `<front>`.
7. **Seed legal**: helpers `_custom_configs_ensure_legal_page($uuid, $title, $alias, $body_html)` reusando padrão Contato/Para Estudantes; body só se field vazio; alias: se já existe → **reusar** Node alvo (não apagar conteúdo alheio cegamente — ver R4); UUIDs fixos documentados em data-model.
8. **Hook `11089`**: ensure duas páginas + ensure aliases + ensure banner block desligado.
9. **Origem**: `drush cex -y` (field_termo + block) + `drush updb -y` (dev) + `drush cr`.
10. **Destino**: `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr`.
11. **Fora**: cadastro empresa; regras CPF/paragraphs; copy jurídico definitivo; Layout Builder; SCSS toolchain.

## Project Structure

### Documentation (this feature)

```text
specs/059-cadastro-candidato-legal/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── cadastro-candidato-render.md
│   ├── legal-pages-seed.md
│   └── deploy-cadastro-candidato-legal.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
  templates/layout/page--cadastro--candidato.html.twig   # novo
  assets/css/components/cadastro-candidato.css           # novo
  assets/images/cadastro-candidato/                      # placeholders desktop/mobile
  default.libraries.yml                                  # + cadastro_candidato

modules/custom/custom_configs/
  custom_configs.install                                 # update_11089 + helpers legais / banner off

modules/custom/custom_configs/modules/custom_configs_users/
  src/Form/CandidatoRegistrationForm.php                 # classes + redirect painel
  custom_configs_users.module                            # form_alter (#attached / aceite) se não couber só no Form

config/sync/
  field.field.user.user.field_termo.yml                  # description com URLs reais
  block.block.default_views_block__banners_block_2.yml   # status false (via cex)

PRD.md                                                   # §10 rotas legais + cadastro
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. `/speckit-tasks` → tasks ordenadas
3. Implementação: seed 11089 → Twig/CSS/library → Form API + redirect → cex → PRD → cim/updb/cr
4. Validação [quickstart.md](quickstart.md)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Correção de redirect `/meu-perfil` → `/painel/estudante/perfil` | FR-016 / SC-005 e paridade com login 035; `/meu-perfil` é legado paralelo | Manter `/meu-perfil` e só “documentar” (rejeitado: quebra critério da spec) |
| `#FD7B1A` local vs `--brand-orange` `#ff8a22` | Spec e Contato usam `#FD7B1A` no CTA; não mutar token global | Mutar `--brand-orange` site-wide |
| Banner estático no tema (não File/CMS) | Placeholders versionados, zero 057, LCP simples; Assumption da spec | Seed File + field editável (fora MVP) |
| Alias conflitante = reuso/no-op (não delete agressivo) | Edge case da spec; evita apagar Node editorial alheio | Migrar/deletar alias legado como Contato `/contato` |
| `mattpocock/skills` ausente | Pacote não instalado; CSS plano do repo | Bloquear feature |
