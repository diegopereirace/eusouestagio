# Quickstart: Nossos Diferenciais — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-11

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com YAMLs + Twig/CSS + (se houver) update hook commitados.
3. **Nenhum** dump necessário para subir a estrutura a outro ambiente.

---

## A) Deploy da estrutura (obrigatório — facilita multi-ambiente)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # deve listar só config/sync + tema + custom_configs
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

1. `drush config:status` limpo (ou apenas diffs esperados de ambiente).
2. UI: `Estrutura → Tipos de bloco` lista **Nossos Diferenciais**.
3. UI: `Estrutura → Tipos de parágrafo` lista **Item de Diferencial**.
4. **Não** foi necessário criar field manualmente no destino.
5. **Não** houve import de dump.

---

## B) Cenário visitante (P1 → SC-002, SC-003, SC-006)

1. Abrir a página onde o bloco está posicionado (home ou região definida no placement).
2. Conferir título, subtítulo, imagem (ou placeholder), lista com ícone+título+descrição.
3. Desktop ≥992px: duas colunas. Mobile: empilhado, sem scroll horizontal.
4. Cores dos títulos: 1º verde, 2º azul, 3º laranja.
5. Geometria decorativa visível atrás/ao redor da imagem (CSS).

---

## C) Cenário editor (P1 → SC-001, SC-005)

1. Criar/editar bloco **Nossos Diferenciais**.
2. Preencher textos, imagem única, ≥3 itens; salvar.
3. Cronometrar &lt; 10 min até ver no front (SC-001).
4. Alterar um título de item → front atualiza após publish/cache (SC-005).

---

## D) Fallbacks (P2 → SC-004)

1. Remover imagem → placeholder; página sem erro.
2. Remover todos os itens → header + mídia ok.
3. Item sem ícone → textos ok.

---

## E) Checklist “deploy fácil” (aceite de processo)

| # | Critério | OK? |
|---|----------|-----|
| 1 | Estrutura só via `config/sync` + `cim` | |
| 2 | Seed (se existir) só via `updb`, idempotente | |
| 3 | Mesma receita em todos os ambientes | |
| 4 | Zero dump / zero SQL de schema | |
| 5 | Editor no destino consegue criar/editar o bundle (roles exportadas) | |

---

## Próximo passo

Se a validação local passar: `/speckit-tasks` → implementar na ordem do plan.
