# Deploy: Landing PE shell + `/vagas`

## Destino

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

## Checklist pós-deploy

| Check | Esperado |
|-------|----------|
| `/para-estudantes` | 200; shell Node; 3 cards; “Ver todas” → `/vagas`; sem pager |
| Ordem content_full | benefícios → jornada → perfil → block_3 |
| `/vagas` | 200; ≤12 cards; pager full |
| Home | “Ver todas as vagas” → `/vagas` |
| Título Drupal em PE | oculto |

## Hooks

- `11040` — cards/grid page_1
- `11041` — path page_1→vagas; shell PE; block_3; placements; page_title negate; filtros→/vagas
