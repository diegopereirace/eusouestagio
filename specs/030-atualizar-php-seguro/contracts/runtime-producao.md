# Contract: Runtime de produção PHP 8.4

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-10-22  
**Consumidor**: operador da VPS, via SSH  
**Produtor**: Apache + pacotes PHP do Ubuntu 24.04 (PPA `ondrej/php`)

## Objetivo

O Apache que já serve `/var/www/html` passa a usar PHP 8.4.27 ou patch posterior da linha 8.4, com backup do banco conferido antes, janela observável abaixo de 2 minutos depois que os pacotes estão em cache, e retorno ao `mod_php` 8.3 sem restaurar o banco.

Não é a esteira de config do [deploy-runbook](../../016-deploy-producao-layout/contracts/deploy-runbook.md): aqui não há `git pull`, `composer install`, `cim` nem `updb`.

## Pré-condições

| # | Item | Verificação |
|---|------|-------------|
| 1 | SSH | variáveis locais `EUSOU_SSH_KEY` e `EUSOU_SSH_HOST` (fora do Git) |
| 2 | Workdir | `/var/www/html` |
| 3 | SAPI atual | `mods-enabled/php8.3.load` presente; `php -v` na linha 8.3 |
| 4 | Pacotes 8.3 instalados | `libapache2-mod-php8.3` e `php8.3-cli` permanecem (alvo de retorno) |
| 5 | Drush | `php vendor/bin/drush.php status` |
| 6 | Disco para o dump e para os `.deb` | `../backups/` criável; cache do apt com espaço |

Estado de partida conferido em 22/10/2026: Ubuntu 24.04.3, Apache/2.4.58, PHP `8.3.6-0ubuntu0.24.04.12`, módulo `php8.3`. O arquivo Ubuntu não tem `php8.4`. O PPA já publicou `8.4.27-1+ubuntu24.04.1+deb.sury.org+1`.

## Contrato da virada

Cada passo exige sucesso antes do próximo. Candidato fora do portão ⇒ **não instalar** e deixar o site no 8.3.

| # | Passo | Resultado exigido |
|---|-------|-------------------|
| 0 | Backup | dump gzipado em `../backups/`, tamanho > 0, `gzip -t` ok. Falha ⇒ abortar, Apache intocado |
| 1 | PPA + índice | `ppa:ondrej/php` habilitado e `apt update` ok. O site continua em 8.3 |
| 2 | Portão do candidato | `libapache2-mod-php8.4` e `php8.4-cli` candidatos na linha 8.4, versão upstream ≥ 8.4.27 e < 8.5.0. Senão ⇒ abortar |
| 3 | Download | `apt-get install -d` dos pacotes abaixo. Apache ainda em 8.3 |
| 4 | Instalação | instalar só: `php8.4-cli`, `php8.4-common`, `libapache2-mod-php8.4`, `php8.4-curl`, `php8.4-gd`, `php8.4-intl`, `php8.4-mbstring`, `php8.4-opcache`, `php8.4-pgsql`, `php8.4-readline`, `php8.4-xml`, `php8.4-zip`. Sem `php8.4-fpm`. Sem purgar 8.3. O pacote `php-common` sobe junto, como dependência. Se o postinst disser `php8.3 module already enabled, not enabling PHP 8.4`, o Apache ainda está no 8.3: aí `a2dismod php8.3`, `a2enmod php8.4` e `update-alternatives --set php /usr/bin/php8.4` |
| 5 | Restart | `systemctl restart apache2` depois do módulo 8.4 estar habilitado. Indisponibilidade observável &lt; 2 min a partir do passo 4 |
| 6 | Versão | CLI `php -v` na faixa 8.4.27+ / &lt; 8.5; `mods-enabled/php8.4.load` presente e `php8.3.load` ausente; `dpkg` de `libapache2-mod-php8.4` na mesma versão upstream do CLI |
| 7 | Cache | `php vendor/bin/drush.php cache:rebuild` com exit 0 |
| 8 | Site | home em HTTPS abre com o conteúdo anterior |

Não alterar `memory_limit`, `post_max_size`, `upload_max_filesize` nem o progresso de upload para “contornar” o aviso.

## Contrato de verificação

| Checagem | Critério |
|----------|----------|
| Versão | 8.4.27 ou patch 8.4 posterior no CLI **e** no módulo Apache |
| Home | HTTPS 200, conteúdo e imagens de antes |
| Login | sessão autentica |
| Banco e arquivos | nenhum drop, nenhum volume recriado; dump do passo 0 continua no disco |
| Log | sem fatal novo que impeça a home |

## Contrato de retorno

Disparar se o passo 6 ou o passo 8 falhar.

| Ação | Efeito |
|------|--------|
| `a2dismod php8.4` e `a2enmod php8.3` | Apache volta ao módulo anterior |
| `update-alternatives --set php /usr/bin/php8.3` | CLI e Drush voltam ao 8.3 |
| `systemctl restart apache2` | site atende de novo na versão anterior |
| Banco | **não** restaurar, salvo corrupção comprovada |

Os pacotes 8.4 podem permanecer instalados e desabilitados. Remover o PPA não faz parte do retorno.
