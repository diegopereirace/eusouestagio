# Contract: deploy 034

## Origem

1. `drush updb -y` (`custom_configs_update_11052`)
2. `drush cex -y`
3. `drush cr`

## Destino

`cim` → `updb` → `cim` → `cr`

## Pré-requisito

`settings.php` com `config_sync_directory` = `config/sync` (ou path relativo equivalente).
