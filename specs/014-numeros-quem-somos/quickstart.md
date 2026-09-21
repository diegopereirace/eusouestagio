# Quickstart: Impact in Numbers (Quem Somos) — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-21

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com YAMLs + Twig/CSS/library + `custom_configs_update_11017` commitados.
3. **Nenhum** dump necessário para subir a estrutura a outro ambiente.

Modelo de dados: [data-model.md](data-model.md). Contrato visual: [contracts/impact-numbers-render.md](contracts/impact-numbers-render.md).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # config/sync + tema + custom_configs + PRD
# commit / push
```

Esse passo integra o **deploy do layout v2** de `/quem-somos` (PRD §3.1.0: hooks `11013` + `11015` + `11016` + **`11017`**).

### Destino (staging / prod / outro local)

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cr
```

**Gates pós-deploy:**

1. `drush config:status` limpo (ou só diffs esperados de ambiente).
2. UI: `Estrutura → Tipos de parágrafo` lista **Número Destaque**.
3. UI: Node Quem Somos exibe lista `field_numeros_lista` (máx. 4).
4. Zero criação manual de fields no destino; zero dump.

---

## B) Visitante desktop (US1 → SC-001, SC-004)

1. Abrir `/quem-somos` em viewport ≥768px.
2. Conferir faixa escura (`#0F172A`) com 4 números laranja em uma linha.
3. Subtextos em caixa alta, branco com opacidade reduzida, gap ~8px abaixo do número.
4. Ordem: 20k+ → 1.2k+ → 8k+ → 95% (ou editorial já salvo).
5. Conferir que Sobre nós, Missão/Visão e Diferenciais permanecem visualmente intactos.

---

## C) Visitante mobile (US2 → SC-002)

1. Viewport ≤575.98px: 2 itens por linha; sem scroll horizontal causado pela seção.
2. Gap destaque↔subtexto ≈ 8px.

---

## D) Editor (US3 → SC-003)

1. Editar o Node Quem Somos: alterar um destaque/subtexto; tentar 5º item (deve ser bloqueado).
2. Salvar e recarregar `/quem-somos` (após cache esperado).
3. Cronometrar &lt; 3 min (SC-003).

---

## E) Idempotência e fallbacks (US4 → SC-005–SC-007)

1. Rodar `drush updb -y` de novo → sem duplicar paragraphs; editorial divergente preservado.
2. Esvaziar a lista (ou todos os campos) em ambiente de teste → seção omitida; página sem erro.
3. Deixar 2 itens → grid com 2 colunas preenchidas.

---

## F) Isolamento visual (SC-008)

Amostrar Sobre nós, Missão/Visão, Diferenciais Quem Somos, home — sem regressão causada pelo CSS de `.section-impact-numbers`.

---

## Checklist “deploy fácil”

| # | Critério | OK? |
|---|----------|-----|
| 1 | Estrutura só via `config/sync` + `cim` | |
| 2 | Seed via `updb` (`11017`), idempotente | |
| 3 | Mesma receita em todos os ambientes | |
| 4 | Zero dump / zero SQL de schema | |
| 5 | Cardinality 4 impede 5º item no form | |
| 6 | Features 009–013 não alteradas | |

---

## Próximo passo

Se a validação do plano estiver OK: `/speckit-tasks` → implementar na ordem do plan.
