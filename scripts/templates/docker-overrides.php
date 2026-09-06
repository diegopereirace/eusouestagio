<?php

/**
 * @file
 * Overrides locais para Docker (anexados ao settings.php pelo script de setup).
 */

$settings['hash_salt'] = 'local-docker-eusouestagio-dev-only-not-for-prod';

$settings['trusted_host_patterns'] = [
  '^localhost$',
  '^127\.0\.0\.1$',
];

$databases['default']['default'] = [
  'database' => getenv('DRUPAL_DB_NAME') ?: 'drupal',
  'username' => getenv('DRUPAL_DB_USER') ?: 'drupal',
  'password' => getenv('DRUPAL_DB_PASSWORD') ?: 'drupal',
  'prefix' => 'drupal_',
  'host' => getenv('DRUPAL_DB_HOST') ?: 'postgres',
  'port' => getenv('DRUPAL_DB_PORT') ?: '5432',
  'driver' => 'pgsql',
  'namespace' => 'Drupal\\pgsql\\Driver\\Database\\pgsql',
  'autoload' => 'core/modules/pgsql/src/Driver/Database/pgsql/',
];

// Diretório de sync alinhado ao dump local em _db/dump_vps.sql.
$settings['config_sync_directory'] = 'sites/default/files/config_F1OKrujSfA2iXJVmaGOU_MvHmtaUInSi9VS1tlKbkzUEYEfl4GyWxIZXQAPHmM9y3GTE4HeIVA/sync';

$config['system.logging']['error_level'] = 'verbose';

// Ambiente local: sem CAPTCHA/reCAPTCHA nos formularios de cadastro.
$settings['disable_captcha'] = TRUE;
