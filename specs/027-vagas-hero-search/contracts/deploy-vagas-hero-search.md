# Deploy: Hero Search — Página de Vagas

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-10-04

## Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
# versionar:
#   config/sync/views.view.vagas.yml  (+ filtro title em page_1)
#   config/sync/block.block.default_custom_banners_vagas_hero_search.yml
#   + custom_banners (plugin/Twig/module) + tema (CSS/library)
#   + custom_configs.install + PRD.md
```

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
| `custom_configs_update_11044` | Ensure filtro exposto `title` em `vagas` `page_1` (se ausente); ensure placement `default_custom_banners_vagas_hero_search` (`highlighted`, weight `-50`, pages `/vagas`, status true). Idempotente. |

## Checklist pós-deploy

| Check | Esperado |
|-------|----------|
| `/vagas` | 200; hero com título/subtítulo/form/pills **antes** da listagem |
| Query form | submit → `/vagas?title=&cidade=&cursos=` mapeados |
| Pill (ex. Engenharia) | `/vagas?cursos=…` aplica filtro |
| Sem “Ver todas” | ausente no markup das pills |
| Filtro View | identifier `title` presente em `page_1` |
| `<front>` | hero home inalterado; este bloco ausente |
| Outras rotas | placement ausente |
| `default_formularioexpostovagaspage_1` | permanece desativado |
| `drush updb` 2ª vez | sem duplicar placement; filtro title não duplica |

## Zero manual

Nenhum passo no admin de produção para ativar a feature.
