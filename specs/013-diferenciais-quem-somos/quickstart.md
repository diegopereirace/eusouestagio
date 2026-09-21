# Quickstart: Diferenciais Quem Somos — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-20

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com YAMLs + Twig/CSS/library + `custom_configs_update_11016` + assets commitados.
3. Regra Cursor `.cursor/rules/drupal-deploy-configs.mdc` presente (já no repo).
4. **Nenhum** dump necessário para subir a estrutura a outro ambiente.

Modelo de dados: [data-model.md](data-model.md). Contrato visual: [contracts/diferenciais-quem-somos-render.md](contracts/diferenciais-quem-somos-render.md).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # config/sync + tema + custom_configs (+ assets)
# commit / push
```

### Destino (staging / prod / outro local)

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cr
```

**Gates pós-deploy:**

1. `drush config:status` limpo (ou só diffs esperados de ambiente).
2. UI: `Estrutura → Tipos de bloco` lista **Diferenciais Quem Somos**.
3. UI: `Estrutura → Tipos de parágrafo` lista **Item Diferencial Simples**.
4. `field_itens_lista` storage com cardinality ilimitada.
5. Zero criação manual de fields no destino; zero dump.

---

## B) Visitante desktop (US1 → SC-001, SC-004)

1. Abrir `/quem-somos` em viewport ≥992px.
2. Conferir título centralizado, descrição contida, grid com até 4 colunas.
3. Cada item: ícone (≤64px) acima do rótulo Poppins SemiBold.
4. Abrir home: bloco `diferenciais_quem_somos` **ausente**; `nossos_diferenciais` intacto se publicado.
5. Abrir outra interna: bloco 013 **ausente**.

---

## C) Visitante mobile / md (US2 → SC-002)

1. Viewport ≤575.98px: 2 itens/linha; sem scroll horizontal causado pelo bloco.
2. Viewport md: até 3 itens/linha (`col-md-4`).

---

## D) Editor (US3 → SC-003)

1. Editar bloco **Diferenciais Quem Somos** (título, descrição, itens, ordem, ícones).
2. Salvar e recarregar `/quem-somos` (após cache esperado).
3. Cronometrar &lt; 5 min (SC-003).
4. Item só com rótulo (sem ícone) → rótulo legível.

---

## E) Idempotência e fallbacks (US5 → SC-005–SC-007)

1. Rodar `drush updb -y` de novo → sem duplicar tipos/blocos/paragraphs/placements.
2. Esvaziar título/descrição → elementos omitidos; página sem erro.
3. Zero itens → só cabeçalho (se houver).

---

## F) Isolamento visual (SC-008)

Amostrar home (`.block-nossos-diferenciais`), banner Quem Somos, Sobre nós, Missão/Visão Node — sem regressão causada pelo CSS do 013.

---

## Checklist “deploy fácil”

| # | Critério | OK? |
|---|----------|-----|
| 1 | Estrutura só via `config/sync` + `cim` | |
| 2 | Seed/placement via `updb` (`11016`), idempotente | |
| 3 | Mesma receita em todos os ambientes | |
| 4 | Zero dump / zero SQL de schema | |
| 5 | Editor no destino edita o bundle (roles exportadas) | |
| 6 | Home `nossos_diferenciais` não alterada | |

---

## Próximo passo

Se a validação do plano estiver OK: `/speckit-tasks` → implementar na ordem do plan.
