# Quickstart: Benefícios Estudantes Figma — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-03

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir do container `/var/www/html`). Em produção: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

Modelo: [data-model.md](data-model.md). Contratos: [contracts/beneficios-estudantes-figma-render.md](contracts/beneficios-estudantes-figma-render.md), [contracts/deploy-beneficios-estudantes-figma.md](contracts/deploy-beneficios-estudantes-figma.md).

## Pré-requisitos

1. Stack local no ar; `$settings['config_sync_directory'] = 'config/sync'`.
2. Branch com tema + `custom_configs_update_11081` + assets + `config/sync` das instances commitados.
3. `/para-estudantes` responde 200 antes da validação visual final.

---

## A) Deploy (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush updb -y
docker compose exec drupal drush cex -y
docker compose exec drupal drush cr
git status
```

**Esperado no `git status`:** tema (Twig/CSS), `custom_configs.install`, assets, PRD, specs, e em `config/sync` as instances `field_text_simple_2` / `field_link` / `field_image` + displays do bundle.

### Destino

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cim -y
docker compose exec drupal drush cr
```

**Gates pós-deploy:**

1. `drush config:status` limpo (ou só diffs de ambiente).
2. `/para-estudantes` mostra o novo layout sem edição manual no Block Layout.
3. Segunda `drush updb -y` → no-op (sem itens/blocos duplicados).

---

## B) Desktop — layout Figma (US1 → SC-001, SC-002)

1. Abrir `/para-estudantes` em viewport ≥992px; rolar até Benefícios.
2. **Esperado**: badge `BENEFÍCIOS PARA ESTUDANTES`; título `Muito mais do que encontrar um estágio.`; subtítulo institucional; **6** itens na ordem canônica; CTA laranja “Cadastre-se gratuitamente”; colagem à direita.
3. Confirmar ausência da grade antiga de 4 cards centrados (“Vagas alinhadas” etc. como UI pública).

---

## C) Tablet / mobile (US2 → SC-003)

1. Viewports ~768px e ~375px.
2. **Esperado**: itens legíveis (2 colunas no tablet, 1 no mobile estreito); CTA acionável; sem scroll horizontal da seção; colagem não impede leitura (ocultada &lt;992).
3. Clique no CTA → `/cadastro/candidato` (SC-004).

---

## D) Editor (US3)

1. Editar o bloco no painel: alterar título de um item e o rótulo/URI do CTA; salvar.
2. Recarregar `/para-estudantes` — mudanças refletidas.
3. Confirmar que não há UI para editar cada foto da órbita individualmente (arte única).

---

## E) Idempotência / legado (US4 → SC-005, SC-006)

1. Em ambiente com as 4 cards seed antigas, rodar `drush updb -y` (ou reexecutar após reset controlado).
2. **Esperado**: 6 itens canônicos (ou preservação se editorial divergente); sem segundo bloco de benefícios.
3. Reexecutar `updb` — sem duplicar paragraphs/placement.

---

## F) Regressão rápida

- Banner, Hero Search, jornada, destaque `block_3` e CTA final permanecem.
- `/para-empresas`, `/quem-somos`, home sem mudança atribuível a esta library.
