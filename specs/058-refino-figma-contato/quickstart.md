# Quickstart: Refino Figma Contato + tipografia de seção

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-04

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`). Em produção: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

Modelo: [data-model.md](data-model.md). Contratos: [contracts/contato-refino-render.md](contracts/contato-refino-render.md), [contracts/section-title-token.md](contracts/section-title-token.md).

## Pré-requisitos

1. Stack local no ar; `$settings['config_sync_directory'] = 'config/sync'`.
2. Features `017`/`018` aplicadas; `/contato` responde 200 com Node Contato + webform.
3. Branch com tema (Twig/CSS/libraries) commitado; hook `11089` só se existir na entrega.

---

## A) Deploy

### Origem (após implementar)

```bash
docker compose exec drupal drush cr
# Se houver alteração estrutural / hook 11089:
docker compose exec drupal drush updb -y
docker compose exec drupal drush cex -y
docker compose exec drupal drush cr
git status
```

### Destino (só tema)

```bash
git pull
docker compose exec drupal drush cr
```

### Destino (com config/hook)

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cim -y
docker compose exec drupal drush cr
```

**Gates**: `/contato` reflete Figma sem edição manual; segunda `updb` (se houver) = no-op.

---

## B) Bleed + alinhamento (US1 → SC-001, SC-002)

1. Viewport ≥992px (ideal 1440) em `/contato`.
2. Fundo `#E5EEFF` do painel **toca** a borda direita (sem faixa branca).
3. Margem esquerda do título/formulário ≈ conteúdo do header (desvio ≤8px @1440).
4. Colunas com altura coerente.

---

## C) Linha de ações (US2 → SC-003)

1. Desktop: submit + card E-mail + card WhatsApp na mesma fileira (±10% vs 180×56 / 280×70 / 214×70).
2. Submit: `#FD7B1A`, texto branco.
3. Cards: borda `#C6C6CD`, valor `#45464D`; clique → `mailto:` / `wa.me`.
4. Esvaziar um field no Node → card some; layout restante ok.

---

## D) Painel (US3)

1. Imagem acima; título/texto centralizados sobre `#E5EEFF`.
2. Sem imagem: textos permanecem; sem textos: imagem centralizada.

---

## E) Mobile (US4 → SC-004)

1. Viewports 375 e 768: form acima do painel; painel 100%; submit full-width; sem scroll horizontal do layout.

---

## F) Tipografia Contato (US5 → SC-007)

1. Inspecionar “Envie sua mensagem”: Inter, 600, 30px, 38px, −0.3px, `#000000`.
2. Sem Poppins/`clamp` neste título.

---

## G) Tipografia global (US6 → SC-008)

1. Amostrar ≥5 páginas públicas (Home, Quem Somos, Para Estudantes, Para Empresas, Contato; opcional Vagas).
2. Headers de seção no escopo = mesmo token; heroes/cards/CTAs/nav/footer fora.
3. Benefícios: métricas do token; spans coloridos podem manter cor.

---

## H) Webform (US7 → SC-005)

1. Envio válido → sucesso + submission.
2. E-mail inválido / obrigatório vazio → bloqueio amigável.
3. Inputs desktop ~57px de altura, borda fina, radius suave.

---

## I) Regressão

Header, footer, FAB WhatsApp, home e outras rotas: smoke visual — layout Contato não vaza; tipografia de seção não altera heroes/microtítulos.
