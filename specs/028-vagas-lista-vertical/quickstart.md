# Quickstart: Listagem Vertical de Vagas (`/vagas`) — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-04

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com Twig/CSS/library + `custom_configs_update_11045` + YAMLs (field + form display + View) commitados.
3. Hero Search (`027`) já em `/vagas` (não deve regredir).
4. Ideal: ≥6 vagas publicadas; pelo menos 1 marcada como Destaque após o field existir.
5. **Nenhum** dump necessário.

Modelo: [data-model.md](data-model.md). Render: [contracts/vagas-lista-vertical-render.md](contracts/vagas-lista-vertical-render.md). Deploy: [contracts/deploy-vagas-lista-vertical.md](contracts/deploy-vagas-lista-vertical.md).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # field_vaga_destaque + form display + views.view.vagas + tema + custom_configs + PRD
```

### Destino

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cim -y
docker compose exec drupal drush cr
```

**Gates pós-deploy:**

1. Field `field_vaga_destaque` no bundle `vagas`; checkbox no form de edição.
2. View `page_1`: 5/página, AJAX, sorts destaque+created, lista (sem grid 3 cols).
3. Zero criação manual no admin; zero dump.

---

## B) Lista vertical branca (US1 → SC-001)

1. Abrir `/vagas` (viewport desktop).
2. Abaixo do Hero: cards brancos empilhados (1 por linha), container centralizado.
3. Confirmar ausência do grid laranja de 3 colunas e da classe `.item-vaga--destaque` nos cards da listagem.

---

## C) Destaque (US2 → SC-002)

1. Editar uma vaga → marcar “Destaque” → salvar.
2. `/vagas`: essa vaga aparece antes das não-destaque; badge verde + borda; CTA “Candidatura Rápida”.
3. Desmarcar Destaque → badge/borda somem; ordenação deixa de priorizá-la.

---

## D) Dados do card + CTA (US3 → SC-005 parcial)

1. Comparar card com node no admin (empresa, local, regime, bolsa, horário, tags).
2. Clicar CTA → página canônica da vaga.
3. Vaga sem logo/tags/horário → seções omitidas; layout ok.

---

## E) Paginação AJAX (US4 → SC-003, SC-004)

1. Com ≥6 vagas publicadas, 1ª carga mostra ≤5 cards.
2. Usar pager (próxima / número) → novos resultados via AJAX (sem reload síncrono clássico da página).

---

## F) Form editorial (US5 → SC-005)

1. Nova vaga: checkbox “Destaque” desmarcado por padrão.
2. Marcar/desmarcar + salvar → valor persiste ao reabrir.

---

## G) Isolamento e deploy (US6 → SC-006–SC-009)

1. `<front>` / `/para-estudantes` / página de vaga (similares): cards laranja intactos.
2. Hero Search em `/vagas` intacto.
3. Sem sidebar Figma nova; sem “Recomendado…” / “Melhore…”.
4. `drush updb -y` de novo → sem duplicar field; View íntegra.
5. Filtros `?title=&cidade=&cursos=` ainda filtram a lista.

---

## Checklist “deploy fácil”

| # | Critério | OK? |
|---|----------|-----|
| 1 | Estrutura via `config/sync` + `cim` | |
| 2 | Field + View via `updb` (`11045`), idempotente | |
| 3 | 2ª `cim` após `updb` | |
| 4 | Zero dump / zero SQL de schema / zero admin prod | |
| 5 | Lista vertical branca em `/vagas` (5/página, AJAX, sort) | |
| 6 | Home / PE / similares / Hero 027 intactos | |

---

## Próximo passo

Se a validação do plano estiver OK: `/speckit-tasks` → implementar na ordem do plan.
