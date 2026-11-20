# Contract: Deploy Build Assets / WebPerf

**Feature**: [../spec.md](../spec.md) | **Data**: 2026-11-04

## Pré-requisito destino

`settings.php` ativo alinhado a `settings.php.prod` com:

```php
$settings['config_sync_directory'] = 'config/sync';
```

Invocação Drush no servidor: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

**Node.js no servidor: não exigido** — `dist/` vem no Git.

## Origem (após implementar)

```bash
cd themes/custom/default
npm install
npm run build
cd ../../..

docker compose exec drupal drush updb -y
docker compose exec drupal drush cex -y
docker compose exec drupal drush cr

git add themes/custom/default modules/custom/custom_configs \
  config/sync/system.performance.yml specs/060-build-assets-webperf
```

Esperado em `config/sync`:

- `system.performance.yml` — `css.preprocess: true`, `js.preprocess: true`

Esperado no tema:

- `dist/**` atualizado e commitado
- `default.libraries.yml` apontando para `dist/`
- `package.json` + lockfile + `vite.config.*`
- `node_modules/` **não** commitado

## Destino (receita normativa)

Há **config exportável** + **hook** → receita completa:

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

Hook esperado: `custom_configs_update_11090`.

## Gates pós-deploy

| Gate | Critério |
|------|----------|
| Preprocess | Config ativa com CSS/JS preprocess TRUE |
| Agregação | HTML da home anônima com assets agregados (elegíveis) |
| Libraries | Network/HTML carrega CSS/JS do tema a partir de `dist/` (ou agregado derivado) |
| LCP | 1º slide hero com eager + fetchpriority high (se banner ativo) |
| Smoke | Home + 1 página com library migrada sem regressão óbvia |
| Idempotência | Segundo `updb` sem erro; preprocess permanece TRUE |

## Proibido

- Ligar agregação só no admin de produção.
- Exigir `npm install` no servidor de produção como passo de deploy.
- Alterar `core/` ou `vendor/`.
- Commitar `node_modules/`.
