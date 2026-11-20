# Deploy: Listagem Vertical de Vagas (`/vagas`)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-10-04

## Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
# versionar tipicamente:
#   config/sync/field.storage.node.field_vaga_destaque.yml
#   config/sync/field.field.node.vagas.field_vaga_destaque.yml
#   config/sync/core.entity_form_display.node.vagas.default.yml
#   config/sync/views.view.vagas.yml   (page_1: 5, sorts, row_class, ajax)
#   + tema (Twigs page_1, CSS, library, preprocess)
#   + custom_configs.install (11045)
#   + PRD.md (§3.1 / §3.6)
```

Revisar o diff de `views.view.vagas.yml`: **não** aceitar mudanças acidentais em `block_1` / `block_2` / `block_3`.

## Destino (staging / prod / outro local)

Pré-requisito: `settings.php` com `$settings['config_sync_directory'] = 'config/sync'`.

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

Local via Compose: `docker compose exec drupal drush <cmd>`.

## Hook

| Update | Responsabilidade |
|--------|------------------|
| `custom_configs_update_11045` | Ensure `field_vaga_destaque` (storage + instance + form display); ensure View `page_1` (5 itens, AJAX, sorts destaque+created, row_class lista); cleanup idempotente de placements “Recomendado para você” / “Melhore seu currículo” em `/vagas` se existirem. **Não** alterar hero 027, `block_1`/`block_2`/`block_3`, formulário exposto desativado. |

## Checklist pós-deploy

| Check | Esperado |
|-------|----------|
| `/vagas` | 200; Hero Search acima; lista vertical de cards brancos |
| Field | `field_vaga_destaque` no bundle `vagas`; checkbox no form |
| View | `items_per_page` 5; `use_ajax` true; sorts destaque DESC + created DESC |
| Destaque | badge + borda + prioridade na ordem |
| Pager | AJAX; ≤5 cards na 1ª carga com ≥6 vagas |
| Home / PE / similares | cards laranja intactos |
| Blocos exclusos | ausentes/desabilitados em `/vagas` |
| `drush updb` 2ª vez | sem duplicar field storage; View íntegra |

## Zero manual

Nenhum passo no admin de produção para ativar a feature.
