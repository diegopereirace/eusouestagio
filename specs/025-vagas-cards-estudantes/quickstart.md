# Quickstart: Landing PE shell + Listagem `/vagas`

## Deploy (destino)

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

Hooks: `11040` (cards/grid page_1) + `11041` (path `/vagas`, shell PE, `block_3`, placements).

## Validação

1. `/para-estudantes`: hero → benefícios → jornada → perfil → **3** cards + “Ver todas as vagas”; **sem** pager; **sem** título Drupal.
2. Clicar “Ver todas as vagas” → `/vagas`.
3. `/vagas`: até 12 cards laranja + pager full; HTTP 200.
4. Home: “Ver todas as vagas” → `/vagas`.
5. Banner PE full-bleed (`body.path-para-estudantes` via preprocess).

## Origem (dev)

Após alterar View/placements no admin (ou via YAML+ensure):

```bash
docker compose exec -T drupal vendor/bin/drush cex -y
docker compose exec -T drupal vendor/bin/drush cr
```
