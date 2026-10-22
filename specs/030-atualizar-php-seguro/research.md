# Research: Atualizar o PHP para a release segura 8.4.27

**Data**: 2026-10-22 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

A spec não tem `[NEEDS CLARIFICATION]`. As decisões abaixo fecham as Assumptions com o que está no repositório, na imagem oficial e na VPS em 22/10/2026.

---

## R1 — Release alvo e o que ela corrige

**Decision**: PHP **8.4.27** ou qualquer patch posterior da linha 8.4 (`PHP_VERSION_ID` de 80427 inclusive até 80500 exclusive). Versão anterior a 8.4.27, linha 8.3 (inclusive 8.3.36) e linha 8.5 (inclusive 8.5.12) não concluem a entrega.

**Rationale**: o anúncio de 21/10/2026 (https://www.php.net/archive/2026.php#2026-10-21-5) publica 8.5.12, 8.4.27, 8.3.36 e 8.2.35 para CVE-2026-109618 e CVE-2026-109619 (CVSS 4.0 9.2, RCE sem autenticação). O texto oficial diz que não há mitigação completa. O pedido e a spec fixam a linha 8.4. O risco citado é maior em `mod_php`, que é o SAPI local e o da VPS.

**Alternatives considered**: ficar no patch 8.3.36 — corrige os CVE e foi rejeitado pela spec; adotar 8.5.12 — também corrige, e a spec o exclui; paliativos (`memory_limit=-1`, desligar progresso de upload, reduzir POST) — o aviso diz que não fecham o problema.

---

## R2 — Imagem local

**Decision**: `FROM drupal:11-php8.4-apache`, mantendo o restante do `Dockerfile` (GD com AVIF/WebP, `pdo_pgsql`, `pgsql`, certificado local, `ssl`/`headers`/`rewrite`, `custom.ini` e vhosts).

**Rationale**: em 22/10/2026 a tag foi puxada e o CLI reportou `PHP 8.4.27 (cli) (built: Oct 21 2026 15:53:29)`, digest `sha256:f04b3552ec80ad7f77fcbe45f896a3a347b2ab0fe5264bcc49cb352de9b30925`, Debian 13.7 (trixie). A imagem local atual já é trixie (Debian 13.2) com PHP 8.3.29 — a suíte não muda, o interpretador muda. O Compose monta `./` em `/var/www/html`, então o Drupal empacotado na imagem (a linha 11.4.10 da tag) não substitui o core 11.4.7 do repositório. A imagem oficial já inclui `gd` e `pdo_pgsql`; o `Dockerfile` atual recompila o GD e adiciona `pgsql`, e a imagem `eusouestagio-drupal` prova que essa sequência gera AVIF, WebP e `pgsql`.

**Alternatives considered**: `11-php8.4-apache-bookworm` — desce de suíte à toa, a imagem atual já é trixie; `11-php8.4-fpm` ou Alpine — outro modelo de processo, fora da spec; pin do digest — um 8.4.28 posterior deixaria de construir mesmo sendo aceite pela spec.

---

## R3 — Portão de versão que não aceita 8.5

**Decision**: o build falha salvo `PHP_VERSION_ID >= 80427 && PHP_VERSION_ID < 80500`. A mesma faixa vale para o CLI e para o módulo Apache na VPS.

**Rationale**: `version_compare(PHP_VERSION, '8.4.27', '>=')` sozinho aceita 8.5.12. A spec trata 8.5 como fora de escopo e trata qualquer build anterior a 8.4.27 como entrega incompleta, mesmo que a tag Docker continue se chamando `11-php8.4-apache`.

**Alternatives considered**: confiar só no nome da tag — um espelho atrasado ou um retag futuro para 8.5 passaria; gravar `8.4.27` exato — rejeitaria o patch seguinte, que a spec permite.

---

## R4 — Drupal 11.4.7 em PHP 8.4

**Decision**: não atualizar o core nem os módulos. O `core/composer.json` exige `php: >=8.3.0`, sem teto que exclua 8.4. A compatibilidade é conferida por fumaça (home, página interna, login, `drush cr`), não por `composer update`.

**Rationale**: a spec deixa Drupal, módulos contribuídos e o banco de fora. O core instalado é 11.4.7 e já declara suporte a partir de 8.3, o que inclui 8.4. Deprecations de PHP 8.4 que não derrubem a página não bloqueiam a entrega; fatal ou página sem o conteúdo anterior bloqueia.

**Alternatives considered**: subir o core para o 11.4.10 que a imagem oficial carrega — mistura duas mudanças e a spec proíbe; adicionar `config.platform.php` — a spec exige que a trava continue ausente, para a versão lida ser a do ambiente real.

---

## R5 — Produção: Ubuntu 24.04 não tem PHP 8.4

**Decision**: habilitar o PPA `ppa:ondrej/php` e instalar a linha 8.4 **somente** se o candidato for 8.4.27 ou patch 8.4 posterior. Pacotes: os mesmos da 8.3 já instalados (`cli`, `libapache2-mod-php8.4`, `common`, `curl`, `gd`, `intl`, `mbstring`, `opcache`, `pgsql`, `readline`, `xml`, `zip`). Não instalar `php8.4-fpm`. Não purgar `php8.3-*`.

**Rationale**: a VPS é Ubuntu 24.04.3 LTS, Apache/2.4.58, `php_module` via `/etc/apache2/mods-enabled/php8.3.load`. Pacotes conferidos: `8.3.6-0ubuntu0.24.04.12` (cli, módulo Apache, curl, gd, intl, mbstring, opcache, pgsql, readline, xml, zip). `apt-cache policy` não lista `php8.4` no arquivo Ubuntu. O PPA Ondřej Surý publicou, para Noble/amd64, `php8.4 8.4.27-1+ubuntu24.04.1+deb.sury.org+1`, inclusive `libapache2-mod-php8.4` (build Launchpad concluído em 21/10/2026). O aviso do PHP sobre repositório voluntário sem patch no dia do release refere-se ao `rpms.remirepo.net`, não a este PPA, que já está no 8.4.27.

**Alternatives considered**: esperar um backport Ubuntu em cima de 8.3.6 — continua na linha 8.3, fora da spec; compilar 8.4.27 do fonte — retorno e janela piores; trocar o Apache do sistema pela imagem Docker — a spec manda seguir o Apache já documentado e não recriar o banco.

---

## R6 — Ordem da virada e o relógio de 2 minutos

**Decision**: backup conferido **antes** de qualquer `apt install` que troque o módulo. Em seguida: adicionar o PPA e `apt update` (ainda no 8.3); abortar se o candidato não for 8.4.27+ na linha 8.4; `apt-get install -d` para encher o cache; só então `apt-get install` dos pacotes listados; `systemctl restart apache2`; ler versão; `php vendor/bin/drush.php cache:rebuild`.

**Rationale**: o postinst de `libapache2-mod-php8.4` desabilita o outro `mod_php` e passa a atender o site. Por isso a versão tem de ser aceita **antes** do install, e os `.deb` têm de estar no disco para a troca caber em menos de 2 minutos. Restart, e não só reload, porque o módulo é carregado no processo do Apache. Não há `cim` nem `updb`: o banco e a config não mudam de forma.

**Alternatives considered**: instalar e só depois olhar `php -v` — se o candidato viesse errado, o site já teria virado; `apachectl graceful` — pode deixar worker no módulo antigo.

---

## R7 — Retorno

**Decision**: alvo de retorno = módulo `php8.3` + alternativa `/usr/bin/php8.3`, não um restore do banco. Comandos de volta: `a2dismod php8.4`, `a2enmod php8.3`, `update-alternatives --set php /usr/bin/php8.3`, `systemctl restart apache2`. O dump em `../backups/` só é restaurado se houver corrupção comprovada.

**Rationale**: a troca não reescreve tabelas nem arquivos publicados. FR-011 pede voltar o interpretador sem restaurar o banco. Manter os pacotes 8.3 instalados torna o retorno uma troca de módulo, não uma reinstalação.

**Alternatives considered**: purgar o 8.3 na mesma janela — alonga o retorno e exige rede para reinstalar; rollback via Git — o `Dockerfile` não é o PHP que o Apache da VPS executa.

---

## R8 — O que não muda no ini nem no Composer

**Decision**: `docker/php/custom.ini` permanece (`memory_limit=512M`, `upload_max_filesize=64M`, `post_max_size=64M`). O `php.ini` de produção não é editado para “mitigar” o aviso. `composer.json` não ganha `require.php` nem `config.platform.php`.

**Rationale**: FR-007 e FR-012. A versão que importa é a do processo que atende o HTTP, lida com `php -v` / `PHP_VERSION` dentro do Apache, não uma trava que o Composer resolveria num PHP diferente.
