# Implementation Plan: Atualizar o PHP para a release segura 8.4.27

**Branch**: `main` (feature `030-atualizar-php-seguro` em `specs/030-atualizar-php-seguro/`; `.specify/scripts/powershell/setup-plan.ps1` ausente neste repo — setup executado manualmente a partir de `.specify/feature.json`)  
**Date**: 2026-10-22  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/030-atualizar-php-seguro/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Trocar o interpretador que serve o Eu Sou Estágio para **PHP 8.4.27** (ou patch posterior da linha 8.4), cobrindo CVE-2026-109618 e CVE-2026-109619, sem mudar Drupal, banco, arquivos publicados nem o modelo Apache. No local, a base do `Dockerfile` passa de `drupal:11-php8.3-apache` para `drupal:11-php8.4-apache`, com o build recusando qualquer versão fora de `>= 8.4.27` e `< 8.5.0`. Na VPS (Ubuntu 24.04, Apache 2.4, `mod_php`), o arquivo do Ubuntu não tem PHP 8.4; a virada usa o PPA `ondrej/php`, que já publicou `8.4.27` para Noble, instala o módulo Apache equivalente e mantém o PHP 8.3 instalado para retorno. `composer.json` continua sem trava de plataforma PHP.

## Technical Context

**Language/Version**: PHP **8.4.27** (local confirmado na imagem oficial em 2026-10-22; produção hoje **8.3.6**). App Drupal **11.4.7** (`composer.lock`), requisito do core `php: >=8.3.0` — 8.4 cabe, 8.5 fica fora desta entrega.  
**Primary Dependencies**: imagem `drupal:11-php8.4-apache` (Debian 13 / Apache / `mod_php`); no servidor, pacotes `php8.4` + `libapache2-mod-php8.4` e as mesmas extensões já instaladas na linha 8.3. Nenhuma dependência Composer nova.  
**Storage**: PostgreSQL 16 inalterado (volume Docker `postgres_data`; em produção, dump gzipado em `../backups/` antes da virada). Arquivos publicados intocados.  
**Testing**: portão de versão + fumaça da home, de uma página interna e de uma tela autenticada — ver [quickstart.md](quickstart.md). Sem suíte automatizada nova.  
**Target Platform**: local — Docker Compose, serviço `drupal`, HTTPS na porta do `.env`. Produção — Ubuntu 24.04.3 LTS, Apache/2.4.58, `mod_php`, app em `/var/www/html`, SSH `ssh -i C:\Users\diego\.ssh\id_rsa root@177.153.59.190`.  
**Project Type**: atualização de runtime (imagem local + pacotes do sistema). Sem módulo, config ou tema novo.  
**Performance Goals**: indisponibilidade observável da produção **< 2 min** depois que o pacote 8.4.27 já estiver baixado (SC-003). Home, página interna e área logada respondem com o conteúdo anterior em até 2 min após o serviço voltar (SC-002).  
**Constraints**: sem paliativo de `memory_limit` / upload / POST (FR-012); sem trava `platform.php` (FR-007); sem `core/` nem `vendor/` à mão; sem `updb`/`cim`; sem trocar Apache por FPM/Alpine; sem PHP 8.3.36, 8.5 ou 8.6; banco só é restaurado se houver corrupção comprovada (FR-011).  
**Scale/Scope**: 1 `Dockerfile`, 0 mudanças de aplicação, 1 runbook de produção, 2 ambientes.

**Estado verificado em 2026-10-22:**

- Imagem local em uso (`eusouestagio-drupal:latest`, build 2026-09-06): PHP **8.3.29**, Debian 13 (trixie), com `gd` (AVIF/WebP), `pdo_pgsql` e `pgsql`.
- `docker pull drupal:11-php8.4-apache` → digest `sha256:f04b3552ec80ad7f77fcbe45f896a3a347b2ab0fe5264bcc49cb352de9b30925`, PHP **8.4.27** (build 21/10/2026 15:53 UTC), Debian 13.7 (trixie). A tag flutuante **já cumpre** o portão hoje; o build mesmo assim recusa regressão ou salto para 8.5.
- Produção: PHP **8.3.6-0ubuntu0.24.04.12** (CLI e `libapache2-mod-php8.3`), módulo `php8.3` habilitado. `apt-cache` do arquivo Ubuntu não oferece `php8.4`. PPA `ondrej/php` publica `8.4.27-1+ubuntu24.04.1+deb.sury.org+1` (Noble, amd64), inclusive `libapache2-mod-php8.4`.
- `composer.json` não declara `php` nem `config.platform.php`.
- `docker/php/custom.ini` permanece (`memory_limit=512M`, uploads 64M). Não será afrouxado.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc`, `drupal-deploy-configs.mdc` e a regra de acesso à VPS.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK antes deste plano |
| Sem editar `core/` / `vendor/` | PASS | só `Dockerfile` e docs da spec; volume do projeto cobre `/var/www/html` |
| Sem atualizar Drupal, módulos ou banco | PASS | sem `composer update`, `cim` ou `updb` |
| Backup antes da virada de produção | PASS | mesmo portão do runbook `016` (`sql-dump` + `gzip -t`) antes do `apt install` que troca o módulo |
| `.env` / credenciais fora do Git | PASS | nenhum segredo no plano; imagem e PPA são públicos |
| Clean URLs / HTTPS | PASS | `a2enmod rewrite ssl` e os vhosts atuais permanecem; produção continua no Apache/Let's Encrypt já no ar |
| Configuração exportável / hook idempotente | PASS | não há estrutura Drupal nova; nada a exportar com `drush cex` |
| Contrib first / custom mínimo | PASS | nenhum módulo novo |

**Post-design**: gates mantidos. A única dependência nova de ambiente é o PPA na VPS, justificada em Complexity Tracking porque o arquivo Ubuntu 24.04 não entrega a linha 8.4. Sem violação injustificada.

## Design Decisions

1. **Local = mesma família de imagem, outra linha de PHP.** `FROM drupal:11-php8.4-apache`. Continua Debian + Apache + `mod_php`. A tag `11-php8.4-apache` hoje aponta para trixie, a mesma suíte da imagem local atual. Alpine/FPM ficam de fora.
2. **Portão no build, não digest cravado.** A tag flutuante só passa se `PHP_VERSION_ID` estiver em `[80427, 80500)`. Assim 8.4.27 e patches 8.4 posteriores passam; 8.3, 8.5 e um espelho velho derrubam o build. Cravar o digest de 22/10/2026 impediria um 8.4.28 legítimo.
3. **O Drupal da imagem oficial não entra no site.** O Compose monta o repositório em `/var/www/html`. A imagem `11.4.10` por baixo não substitui o core 11.4.7 do projeto.
4. **Extensões: repetir o `RUN` que já funciona.** A imagem oficial já traz `gd` e `pdo_pgsql`; o `Dockerfile` recompila o GD com AVIF/WebP e acrescenta `pgsql`. Essa sequência já produziu a imagem local atual. `custom.ini` e os vhosts são copiados como estão.
5. **Produção não reconstrói Docker.** O PHP do servidor é pacote Ubuntu. Procedimento: backup → conferir candidato `8.4.27` no PPA → baixar pacotes (`apt-get install -d`) → instalar a linha 8.4 → se o postinst não habilitar o módulo, `a2dismod php8.3` e `a2enmod php8.4` → `systemctl restart apache2` → ler a versão do CLI e do módulo → `drush cr`. Sem `git pull` obrigatório para o interpretador mudar (o `Dockerfile` não roda na VPS).
6. **Pacotes espelhados, sem FPM.** Instalar o conjunto já presente na 8.3: `php8.4-cli`, `libapache2-mod-php8.4`, `php8.4-common`, `curl`, `gd`, `intl`, `mbstring`, `opcache`, `pgsql`, `readline`, `xml`, `zip`. Não instalar `php8.4-fpm`. Não remover `php8.3-*`.
7. **Retorno sem banco.** Se a versão ou a home falhar: `a2dismod php8.4`, `a2enmod php8.3`, alternativa `php` de volta para `/usr/bin/php8.3`, `systemctl restart apache2`. O dump só é restaurado com corrupção comprovada.
8. **Janela curta.** A troca de módulo acontece no `apt install`, não no download. O relógio de 2 minutos conta a partir dos pacotes já em cache.
9. **Sem paliativo e sem trava Composer.** `memory_limit`, `post_max_size` e progresso de upload não mudam. `composer config platform.php` permanece ausente.

## Project Structure

### Documentation (this feature)

```text
specs/030-atualizar-php-seguro/
├── spec.md
├── checklists/requirements.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/runtime-local.md
├── contracts/runtime-producao.md
└── quickstart.md
```

### Source Code (mudança planejada)

```text
Dockerfile          # FROM drupal:11-php8.4-apache + portão PHP_VERSION_ID
# Sem alteração:
#   docker-compose.yml, docker/php/custom.ini, docker/apache/*.conf
#   composer.json, core/, vendor/, config/sync/, modules/, themes/
```

## Phases

1. Artefatos desta feature (este plano, research, modelo, contratos, quickstart).
2. `Dockerfile`: nova base + portão de versão; rebuild só do serviço `drupal` (volume do Postgres intacto).
3. Validação local: versão, extensões, home, página interna, login, `drush cr`.
4. Janela de produção: backup conferido → PPA → download → instalação do módulo 8.4.27 → restart → versão Apache/CLI → `drush cr` → home em HTTPS. Falha ⇒ retorno ao `php8.3` sem restaurar o banco.

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| PPA `ondrej/php` na VPS | Ubuntu 24.04 só publica PHP 8.3.6; o pacote 8.4.27 da linha pedida já está nesse PPA, com `libapache2-mod-php8.4` | Permanecer em 8.3.36 (a spec fixa 8.4); compilar do fonte (janela longa e retorno pior); PHP-FPM (outro modelo de processo) |
| Tag Docker flutuante | A spec aceita `11-php8.4-apache` quando o identificador interno é 8.4.27+; o portão no build barra 8.5 e regressão | Digest fixo — bloquearia o próximo patch 8.4, que a spec também aceita |
| `restart` do Apache, não só reload | Troca de `mod_php` só vale com o processo recarregado por completo; ainda fica muito abaixo de 2 min | Reload gracioso — pode manter workers no módulo antigo |
