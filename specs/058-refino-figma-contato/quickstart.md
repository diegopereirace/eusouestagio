# Quickstart: Refino Figma Contato + tipografia Contato

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-04

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`). Em produção: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

Modelo: [data-model.md](data-model.md). Contratos: [contracts/contato-refino-render.md](contracts/contato-refino-render.md), [contracts/section-title-token.md](contracts/section-title-token.md).

## Pré-requisitos

1. Stack local no ar; `$settings['config_sync_directory'] = 'config/sync'`.
2. Features `017`/`018` aplicadas; `/contato` responde 200 com Node Contato + webform.
3. Branch com tema (Twig/CSS/libraries) **e** `config/sync/image.style.contato_painel.yml` commitados.

---

## A) Deploy

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
docker compose exec drupal drush cr
git status
```

### Destino (tema + Image Style — receita obrigatória desta feature)

Há config estrutural (`image.style.contato_painel`). **Não** usar só `drush cr`.

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush cr
```

Se no futuro existir `hook_update_N` (ex. `11089`):

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cim -y
docker compose exec drupal drush cr
```

**Gates**: `/contato` reflete Figma sem edição manual; Network da imagem usa `/styles/contato_painel/…`.

---

## B) Bleed + alinhamento (US1 → SC-001, SC-002)

1. Viewport ≥992px (ideal 1440) em `/contato`.
2. Fundo `#E5EEFF` do painel **toca** a borda direita (sem faixa branca).
3. Margem esquerda do título/formulário ≈ conteúdo do header (desvio ≤8px @1440).
4. Colunas com altura coerente.

---

## C) Linha de ações (US2 → SC-003)

1. Desktop: submit + card E-mail + card WhatsApp na mesma fileira quando couber (±10% vs 180×56 / 280×70 / 214×70); se a coluna estreitar, quebra sem overflow-x.
2. Submit: `#FD7B1A`, texto branco, altura ~56px.
3. Cards: borda `#C6C6CD`, valor `#45464D`; clique → `mailto:` / `wa.me`.
4. Esvaziar um field no Node → card some; layout restante ok.

---

## D) Painel (US3)

1. Imagem acima; título/texto centralizados sobre `#E5EEFF`.
2. Sem imagem: textos permanecem; sem textos: imagem centralizada.
3. `<img>` com `loading="lazy"`, `decoding="async"`, style `contato_painel`; `width`/`height` proporcionais ao asset.

---

## E) Mobile (US4 → SC-004)

1. Viewports 375 e 768: form acima do painel; painel 100%; submit full-width; sem scroll horizontal do layout.

---

## F) Tipografia Contato (US5 → SC-007)

1. Inspecionar “Envie sua mensagem”: Inter, 600, 30px, 38px, −0.3px, `#000000`.
2. Sem Poppins/`clamp` neste título.

---

## G) Tipografia fora de Contato (US6 → SC-008)

1. Amostrar headers de seção em Home / Quem Somos / Para Estudantes / Para Empresas (ex. `.nd-header__title`, `.be-header__title`).
2. Confirmar que **não** herdaram o token Contato (tipografia local preservada).

---

## H) Webform (US7 → SC-005)

1. Envio válido → sucesso + submission.
2. E-mail inválido / obrigatório vazio → bloqueio amigável.
3. Inputs desktop ~57px de altura, borda fina, radius suave.

---

## I) Regressão

Header, footer, FAB WhatsApp, home e outras rotas: smoke visual — layout Contato não vaza; tipografia Contato não altera heroes/microtítulos/headers de outros blocos.
