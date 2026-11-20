# Quickstart: Depoimentos — Para Empresas — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-26

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com YAMLs (node type, fields, View, placement, CTA weight), Twig/CSS/JS, `custom_configs_update_11032` e assets em `modules/custom/custom_configs/assets/depoimentos/`.
3. Modelo: [data-model.md](data-model.md). Contratos: [contracts/](contracts/).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # config/sync + tema + custom_configs + PRD
# commit / push
```

### Destino (staging / prod / outro local)

Receita padrão (sem remoção de bundle com conteúdo):

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cim -y
docker compose exec drupal drush cr
```

**Gates pós-deploy:**

1. `drush updatedb:status` sem pendências de `custom_configs` (`11032` aplicado).
2. `/para-empresas` HTTP 200; carrossel visível com ≥4 cards acima do CTA.
3. `drush php:eval` ou UI: existem ≥4 nodes `depoimento` publicados (UUIDs seed).
4. Zero criação manual de tipo/View/placement no destino.

---

## B) Center mode + ordem (US1 → SC-001, SC-002, SC-003)

1. Viewport ≥992px em `/para-empresas`; rolar até abaixo de Benefícios.
2. Ordem DOM/visual: Benefícios → Depoimentos → CTA.
3. Card ativo centralizado; laterais parcialmente visíveis com opacidade menor.
4. Clicar dots / swipe → troca o ativo; todos os ≥4 itens alcançáveis.
5. Comparar card ativo com Figma (branco, ~16px, padding ~32px, avatar circular) ≥95% checklist.

---

## C) Conteúdo do card (US2)

1. Avatar (se seedado), nome, cargo/empresa, texto legível (~`#45464D`).
2. Sem foto em um item de teste → card sem broken image.

---

## D) Mobile (SC-004)

1. Viewport estreita: carrossel utilizável; **sem** scroll horizontal da página causado pelos cards.

---

## E) Editor (US3 → SC-005)

1. Criar Depoimento publicado (nome, cargo, texto, foto) → aparece no carrossel após `drush cr` (ou cache esperado) em &lt;5 min.
2. Despublicar → some do carrossel.
3. Ordem: mais recente primeiro (`created` DESC).

---

## F) Idempotência e edge (US4 → SC-006, SC-007, SC-009)

1. Rodar `drush updb -y` de novo → sem nodes/placements duplicados.
2. Despublicar todos → página carrega sem fatal; seção omitida ou vazia segura.
3. Home e `/quem-somos`: amostragem visual inalterada (SC-008).

---

## G) Exclusividade de rota

1. `/para-empresas`: bloco presente.
2. `/`, `/quem-somos`, `/contato`: bloco de depoimentos **ausente**.
