# Quickstart: PHP 8.4.27 — validação local e produção

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-22

Guia para provar a troca de ponta a ponta. Contratos: [runtime local](contracts/runtime-local.md), [runtime de produção](contracts/runtime-producao.md). Modelo: [data-model.md](data-model.md).

## Pré-requisitos

1. Docker no ambiente local; Compose deste repositório; volume `postgres_data` já populado.
2. Produção, só na janela combinada: SSH `ssh -i C:\Users\diego\.ssh\id_rsa root@177.153.59.190`, workdir `/var/www/html`.
3. Nenhuma destas checagens edita `core/`, `vendor/`, `custom.ini` ou o `composer.json`.

O portão, nos dois ambientes, é: versão **8.4.27 ou patch 8.4 posterior**, e **não** 8.5. Em PHP isso é `PHP_VERSION_ID >= 80427 && PHP_VERSION_ID < 80500`.

---

## A) Local — rebuild e versão (US1, US3)

A partir da raiz do repositório, depois que o `Dockerfile` apontar para `drupal:11-php8.4-apache` e o build recusar versão fora da faixa:

```bash
docker compose build drupal
docker compose up -d drupal
docker compose exec -T drupal php -r "echo PHP_VERSION, PHP_EOL; exit(PHP_VERSION_ID >= 80427 && PHP_VERSION_ID < 80500 ? 0 : 1);"
docker compose exec -T drupal php -m
```

**Esperado**

- Exit 0 e impressão `8.4.27` ou `8.4.<n>` com n ≥ 27.
- Lista de módulos contém `gd`, `pdo_pgsql`, `pgsql`.
- Build com base ainda em 8.3, ou tag 8.4 atrasada, ou imagem 8.5: exit diferente de 0. Nesse caso a entrega local não é aceita.

Não usar `docker compose down -v`.

---

## B) Local — site e caches (US2, US3)

```bash
docker compose exec -T drupal vendor/bin/drush cr
```

No navegador, em HTTPS na porta do `.env`:

| Passo | Esperado |
|-------|----------|
| Home | abre com o conteúdo e as imagens de antes |
| Uma página interna pública | o mesmo |
| Login de uma conta já existente | sessão autentica e os dados da conta aparecem |
| `composer config --unset` **não** é executado; revisar o `composer.json` | continua sem `require.php` e sem `config.platform` |

---

## C) Produção — só com o pacote certo (US4)

Seguir [runtime-producao.md](contracts/runtime-producao.md) nesta ordem. Resumo do que tem de ser verdade ao final:

```bash
cd /var/www/html
php -r 'echo PHP_VERSION, PHP_EOL; exit(PHP_VERSION_ID >= 80427 && PHP_VERSION_ID < 80500 ? 0 : 1);'
test -e /etc/apache2/mods-enabled/php8.4.load
test ! -e /etc/apache2/mods-enabled/php8.3.load
php vendor/bin/drush.php cache:rebuild
```

Se o postinst deixar o Apache no módulo 8.3, habilitar `php8.4` e desabilitar `php8.3` antes do restart. Abrir a home em HTTPS. A janela em que o site deixou de responder, contada a partir do `apt install` com os `.deb` já baixados, fica abaixo de 2 minutos.

**Abortar antes da instalação** se o candidato do apt não estiver na faixa 8.4.27+ / < 8.5. O site permanece em 8.3.6.

---

## D) Retorno controlado (SC-005, FR-011)

Não exercitar isto contra a produção saudável. O critério, se a versão ou a home falhar depois da virada:

1. Reabilitar `php8.3`, desabilitar `php8.4`, devolver a alternativa `php` para `/usr/bin/php8.3`, reiniciar o Apache — detalhes no contrato de produção.
2. `php -v` volta à linha 8.3 e a home abre.
3. O banco não é restaurado.

O dump do passo 0 do contrato fica em `../backups/` como rede de segurança, não como passo da volta normal.
