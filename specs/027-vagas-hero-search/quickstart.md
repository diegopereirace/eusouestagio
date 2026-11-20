# Quickstart: Hero Search (/vagas) — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-04

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com plugin/Twig/CSS/library + `custom_configs_update_11044` + YAMLs (View + placement) commitados.
3. View `vagas` `page_1` já em path `/vagas` (hooks `11041`/`11042`).
4. **Nenhum** dump necessário.

Modelo: [data-model.md](data-model.md). Render: [contracts/vagas-hero-search-render.md](contracts/vagas-hero-search-render.md). Deploy: [contracts/deploy-vagas-hero-search.md](contracts/deploy-vagas-hero-search.md).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # views.view.vagas + block placement + custom_banners + tema + custom_configs + PRD
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

1. Placement `default_custom_banners_vagas_hero_search` em `highlighted` weight `-50`, só `/vagas`.
2. Filtro exposto `title` presente em `page_1`.
3. Zero criação manual no admin; zero dump.

---

## B) Visitante desktop (US1 → SC-001, SC-006)

1. Abrir `/vagas` em viewport ≥768px.
2. Conferir hero **acima** da listagem: título, subtítulo, 3 campos, botão “Buscar Vagas”, pills.
3. Tokens: padding ≈48/32/40; wrapper ~1280px; botão `#58A83C`; barra em pílula.
4. Confirmar ausência de “Ver todas”.

---

## C) Busca por formulário (US2 → SC-003, SC-004)

1. Preencher “Cargo ou palavra-chave” com termo conhecido → URL com `title=` e listagem filtrada.
2. Preencher “Cidade ou Remoto” → URL com `cidade=`.
3. Preencher “Seu curso” → URL com `cursos=`.
4. Combinar os três → filtros AND.
5. Submit vazio → `/vagas` sem erro.

---

## D) Pills (US3 → SC-005)

1. Clicar em cada pill visível → `/vagas?cursos=…` e filtro aplicado.
2. Se algum rótulo não tiver termo no vocabulário, a pill não deve aparecer.

---

## E) Mobile (US4 → SC-002)

1. Viewport ≤575.98px: campos empilhados; pills com wrap; sem scroll horizontal do hero.
2. Desktop: uma linha com divisórias entre inputs.

---

## F) Isolamento e deploy (US5 → SC-006–SC-009)

1. `<front>`: hero home intacto; sem `.vagas-hero-search`.
2. `/para-estudantes` e outras: este bloco ausente.
3. `drush updb -y` de novo → sem duplicar placement/filtro.
4. Textos do hero: zero linhas no banco (só código/Twig).

---

## Checklist “deploy fácil”

| # | Critério | OK? |
|---|----------|-----|
| 1 | Estrutura via `config/sync` + `cim` | |
| 2 | Filtro `title` + placement via `updb` (`11044`), idempotente | |
| 3 | 2ª `cim` após `updb` | |
| 4 | Zero dump / zero SQL de schema / zero admin prod | |
| 5 | Hero primeiro em `/vagas` (highlighted `-50`) | |
| 6 | Hero home e cards/filtros laterais intactos | |

---

## Próximo passo

Se a validação do plano estiver OK: `/speckit-tasks` → implementar na ordem do plan.
