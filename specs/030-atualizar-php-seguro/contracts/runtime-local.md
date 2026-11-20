# Contract: Runtime local PHP 8.4

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-10-22  
**Consumidor**: quem reconstrói o ambiente Docker  
**Produtor**: `Dockerfile` + serviço `drupal` do Compose

## Objetivo

O serviço `drupal` sobe em PHP 8.4.27 ou patch posterior da linha 8.4, com as mesmas capacidades de imagem, PostgreSQL, HTTPS local e URL limpa. O contrato é o identificador de versão e o comportamento do site, não uma API HTTP nova.

## Pré-condições

| # | Item | Verificação |
|---|------|-------------|
| 1 | Repositório no estado desta feature | `Dockerfile` com base `drupal:11-php8.4-apache` |
| 2 | `.env` com porta HTTPS, banco e credenciais já usados pelo Compose | serviço `postgres` saudável |
| 3 | Volume `postgres_data` preservado | rebuild **sem** `docker compose down -v` |
| 4 | `composer.json` sem trava de PHP | `platform.php` ausente; `require` sem chave `php` |

## Contrato da imagem

| Item | Valor |
|------|--------|
| Base | `drupal:11-php8.4-apache` |
| Suíte | Debian 13 (trixie), Apache, `mod_php` |
| Portão | build termina com exit 0 só se `PHP_VERSION_ID >= 80427` e `PHP_VERSION_ID < 80500` |
| Extensões obrigatórias no CLI do container | `gd` (AVIF e WebP), `pdo_pgsql`, `pgsql`, `openssl` |
| Ini | `memory_limit=512M`, `upload_max_filesize=64M`, `post_max_size=64M` — iguais a `docker/php/custom.ini` |
| Fora | digest cravado, variante Alpine/FPM, `config.platform.php`, edição de `core/` ou `vendor/` |

O Drupal copiado dentro da imagem oficial não é o código servido: o volume do projeto cobre `/var/www/html`.

## Saídas esperadas

| Checagem | Resultado |
|----------|-----------|
| `php -v` no container | linha `PHP 8.4.27` ou `PHP 8.4.<patch>` com patch ≥ 27, e nunca `8.5` |
| `php -m` | inclui `gd`, `pdo_pgsql`, `pgsql` |
| Home e uma página interna em HTTPS | HTTP 200, conteúdo anterior, imagens carregam |
| Login | sessão autentica |
| `vendor/bin/drush cr` | exit 0; home volta a responder |
| Postgres | volume não recriado; conteúdo consultável depois do rebuild |

## Falha

| Situação | Ação |
|----------|------|
| Tag Docker ainda com PHP < 8.4.27 ou com 8.5 | build falha no portão; não publicar a imagem; site local permanece na imagem anterior |
| Extensão ou certificado ausente | imagem não é aceita; corrigir o `RUN` existente (GD/pgsql/SSL), sem trocar o servidor web |
| Página sem o conteúdo anterior | não concluir a entrega local |
