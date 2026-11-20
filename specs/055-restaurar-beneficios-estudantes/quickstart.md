# Quickstart: Restaurar Benefícios Estudantes — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-03

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir do container `/var/www/html`). Em produção: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

Modelo: [data-model.md](data-model.md). Contratos: [contracts/beneficios-estudantes-render.md](contracts/beneficios-estudantes-render.md), [contracts/deploy-beneficios-estudantes.md](contracts/deploy-beneficios-estudantes.md).

## Pré-requisitos

1. Stack local no ar; `$settings['config_sync_directory'] = 'config/sync'`.
2. Branch com tema + `custom_configs_update_11081` + assets + (se houver) diffs de display commitados.
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

**Esperado no `git status`:** tema (Twig/CSS), `custom_configs.install`, assets (ícones/órbita), PRD, specs; `config/sync` só se displays mudarem.

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
2. `/para-estudantes` mostra o layout Figma restaurado sem edição manual no Block Layout.
3. Segunda `drush updb -y` → no-op (sem itens/blocos duplicados).

---

## B) Desktop — layout Figma (US1 → SC-001, SC-002)

1. Abrir `/para-estudantes` em viewport ≥992px; rolar até Benefícios.
2. **Esperado**: fundo `#eef5ff`; badge/label; título bicolor `Muito mais do que` / `encontrar um estágio.`; subtítulo; **6** cards brancos na ordem canônica; CTA laranja “Cadastre-se gratuitamente”; colagem circular (centro + órbita) à direita.
3. Confirmar ausência da grade pública de 4 cards (“Vagas alinhadas” etc.).

---

## C) Tablet / mobile (US2 → SC-003)

1. Viewports ~768px e ~375px.
2. **Esperado (~768)**: conteúdo full; itens legíveis; colagem abaixo redimensionada sem overflow.
3. **Esperado (~375)**: 1 coluna; CTA acionável; colagem omitida ou sem overflow; sem scroll horizontal da seção.
4. Clique no CTA → `/cadastro/candidato` (SC-004).

---

## D) Editor (US3)

1. Editar o bloco no painel: alterar título de um item e o rótulo/URI do CTA; salvar.
2. Recarregar `/para-estudantes` — mudanças refletidas.
3. Confirmar que não há UI para editar cada foto da órbita individualmente.

---

## E) Idempotência / recreate (US4 → SC-005, SC-006, SC-008)

1. Em ambiente com as 4 cards seed (`Vagas alinhadas`…), rodar `drush updb -y`.
2. **Esperado**: 6 itens canônicos (ou preservação se editorial divergente); sem segundo bloco.
3. (Opcional controlado) Remover a instância UUID `a8b9…` ou desabilitar placement → `updb` → seção volta na ordem correta.
4. Reexecutar `updb` — sem duplicar paragraphs/placement.

---

## F) Regressão rápida

1. Banner, Hero Search, jornada, destaque `block_3`, CTA final inalterados em `/para-estudantes`.
2. `/`, `/quem-somos`, `/para-empresas` sem mudança visual atribuível a esta library.
