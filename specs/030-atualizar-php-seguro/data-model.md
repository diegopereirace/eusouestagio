# Data Model: Atualizar o PHP para a release segura 8.4.27

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-22

Feature de runtime: não cria entidade Drupal. O modelo descreve os dois interpretadores, o portão de versão e o artefato de retorno. Contratos: [runtime-local.md](contracts/runtime-local.md), [runtime-producao.md](contracts/runtime-producao.md).

## Entidades

### 1. Runtime local

| Aspecto | Valor |
|---------|--------|
| Definição | `Dockerfile`, serviço `drupal` do `docker-compose.yml` |
| Base atual | `drupal:11-php8.3-apache` → imagem `eusouestagio-drupal:latest` (2026-09-06), PHP 8.3.29, Debian 13 |
| Base alvo | `drupal:11-php8.4-apache` |
| Digest observado em 22/10/2026 | `sha256:f04b3552ec80ad7f77fcbe45f896a3a347b2ab0fe5264bcc49cb352de9b30925` (não é pin; é a evidência de que a tag já entrega 8.4.27) |
| SO | Debian 13 (trixie), igual à imagem atual |
| SAPI | Apache `mod_php` |
| Código da aplicação | bind mount `./:/var/www/html` — o Drupal da imagem não substitui o do repositório |
| Banco | volume `postgres_data` (PostgreSQL 16). Rebuild do serviço `drupal` não o apaga |
| Ini | `docker/php/custom.ini` copiado para a imagem, sem mudança de valores |

### 2. Runtime de produção

| Aspecto | Valor |
|---------|--------|
| Host | Ubuntu 24.04.3 LTS, Apache/2.4.58, app em `/var/www/html` |
| Atual | PHP 8.3.6, pacote `8.3.6-0ubuntu0.24.04.12`, módulo `php8.3` em `mods-enabled` |
| Alvo | PHP 8.4.27+ da linha 8.4, pacote `libapache2-mod-php8.4` do PPA `ondrej/php` |
| Candidato já publicado | `8.4.27-1+ubuntu24.04.1+deb.sury.org+1` (Noble, amd64) |
| SAPI | continua `mod_php`. Sem `php8.4-fpm` |
| Extensões a repetir | cli, curl, gd, intl, mbstring, opcache, pgsql, readline, xml, zip, common |
| Pacotes 8.3 | permanecem instalados, módulo desabilitado depois da virada |
| Ini | arquivos atuais de `/etc/php/8.3` não são copiados como paliativo; não se define `memory_limit=-1` |

### 3. Portão de versão

| Aspecto | Valor |
|---------|--------|
| Aceite | `PHP_VERSION_ID >= 80427` e `PHP_VERSION_ID < 80500` |
| Onde vale | build da imagem local; CLI de produção; módulo Apache de produção (pacote `libapache2-mod-php8.4` na mesma versão do `php8.4-cli`) |
| Rejeição | qualquer 8.3, qualquer build 8.4 anterior a 8.4.27, qualquer 8.5+ |
| Efeito local | `docker compose build` falha |
| Efeito produção | não executar o `apt install` que habilita o módulo; se a virada já ocorreu e a checagem falha, voltar ao módulo 8.3 |

### 4. Backup de produção (portão)

| Aspecto | Valor |
|---------|--------|
| Quando | antes do `apt install` que troca o módulo (PPA e `apt update` ainda não trocam o PHP em execução) |
| Comando | `php vendor/bin/drush.php sql-dump --result-file=../backups/backup_eu_sou_estagio_<timestamp>.sql --gzip` a partir de `/var/www/html` |
| Validação | arquivo existe, tamanho > 0, `gzip -t` ok |
| Papel | restauração só com corrupção comprovada. A volta normal não usa este arquivo |

### 5. Alvo de retorno (produção)

| Aspecto | Valor |
|---------|--------|
| O que volta | SAPI Apache `php8.3` e binário `/usr/bin/php8.3` |
| O que não volta | banco, `sites/default/files`, código Git |
| Pré-condição | pacotes `php8.3-*` e `libapache2-mod-php8.3` continuam instalados |
| Disparo | versão fora do portão, home ou login falhos depois da virada |

### 6. Capacidades que a troca precisa reapresentar

| Capacidade | Local | Produção |
|------------|-------|----------|
| Imagem (GD) | `gd` com AVIF e WebP, como na imagem atual | pacote `php8.4-gd` |
| PostgreSQL | `pdo_pgsql` e `pgsql` | pacote `php8.4-pgsql` |
| HTTPS | certificado local gerado no build; vhosts atuais | Let's Encrypt já terminado no Apache, sem redirect novo |
| URL limpa | `rewrite` habilitado no `Dockerfile` | `rewrite_module` já ativo |
| Cache do site | `drush cr` no container | `php vendor/bin/drush.php cache:rebuild` |

## Estados

```text
local:    8.3.29 --build com portão--> 8.4.27+ (linha 8.4) | build recusado
produção: 8.3.6 --candidato ok + install--> 8.4.27+ (linha 8.4)
                 --candidato ruim---------> permanece 8.3.6
                 --home/versão falha------> retorno ao módulo 8.3 (banco intacto)
```

Não há estado em que os dois CVE estejam “mitigados” por ini. Ou a versão passa no portão, ou o ambiente continua (ou volta) para o PHP anterior.
