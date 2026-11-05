# Quickstart: Ocultar identificação pública do Drupal

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-05

Comandos Drush: `docker compose exec -T drupal vendor/bin/drush cr` (workdir `/var/www/html`). Em produção: `php vendor/bin/drush.php cr` a partir de `/var/www/html`.

## Deploy

```bash
git pull
# local: custom.ini (expose_php=Off) é montado via docker-compose; recreate se necessário
docker compose up -d --force-recreate drupal
docker compose exec -T drupal vendor/bin/drush cr
```

Produção (VPS Apache/`mod_php`): além do `.htaccess` (scaffold), garantir `expose_php=Off` no `php.ini` ativo (ou conf.d) e `systemctl restart apache2` — o header `X-Powered-By` do PHP não some só com o EventSubscriber.

Sem `cim` / `updb` (sem config nem hook_update).

## Verificação

### A) Meta Generator ausente (SC-001)

```bash
curl -sS http://localhost/ | findstr /I "Generator"
# esperado: sem linha name="Generator" com Drupal
```

Ou View Source no browser: sem `<meta name="Generator" content="Drupal …">`.

### B) Headers ausentes (SC-002)

```bash
curl -sSI http://localhost/ | findstr /I "X-Generator X-Powered-By"
# esperado: sem output
```

### C) SEO intacto (SC-003)

Confirmar no `<head>` presença de title/description/canonical do Metatag (quando configurados).
