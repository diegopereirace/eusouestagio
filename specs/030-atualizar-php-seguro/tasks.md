# Tasks: Atualizar o PHP para a release segura 8.4.27

**Input**: Artefatos de design em `specs/030-atualizar-php-seguro/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/runtime-local.md`, `contracts/runtime-producao.md`, `quickstart.md`  
**Branch**: `main` (feature `030-atualizar-php-seguro`)  
**Testes automatizados**: não solicitados — validação operacional via `quickstart.md` (portão de versão + fumaça). Sem suíte nova.  
**Nota de setup**: `.specify/scripts/powershell/setup-tasks.ps1` e `.specify/templates/tasks-template.md` ausentes neste repo; `FEATURE_DIR` = `specs/030-atualizar-php-seguro` (via `.specify/feature.json`); template alinhado a `specs/016-deploy-producao-layout/tasks.md`  
**Natureza**: atualização de runtime. Código de aplicação, `composer.json`, `docker-compose.yml`, `docker/php/custom.ini`, `core/` e `vendor/` não mudam. A única edição de código é `Dockerfile`.

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar artefatos SDD e o baseline dos dois ambientes antes de qualquer troca de interpretador.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/runtime-local.md`, `contracts/runtime-producao.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/030-atualizar-php-seguro/` e feature ativa em `.specify/feature.json`
- [X] T002 [P] Registrar o baseline local: `Dockerfile` ainda em `FROM drupal:11-php8.3-apache` e a imagem em uso é PHP 8.3.29, conforme `specs/030-atualizar-php-seguro/research.md` (R2) e `specs/030-atualizar-php-seguro/data-model.md` (Runtime local)
- [X] T003 [P] Confirmar em `docker-compose.yml` que o serviço `drupal` monta `./:/var/www/html` e que o Postgres usa o volume nomeado `postgres_data` — nenhum dos dois será editado
- [X] T004 [P] Confirmar em `docker/php/custom.ini` os limites atuais (`memory_limit=512M`, `upload_max_filesize=64M`, `post_max_size=64M`) e que o arquivo permanece intocado (FR-012)

**Checkpoint**: baseline inventariado; nenhum rebuild e nenhuma mudança na VPS nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar o que todas as histórias compartilham — portão `PHP_VERSION_ID >= 80427 && PHP_VERSION_ID < 80500`, capacidades que o `Dockerfile` já instala, pacotes e retorno de produção, ausência de trava Composer. **Nenhuma user story começa antes desta fase.**

- [X] T005 Revisar o `Dockerfile` atual e marcar como intocáveis o `RUN` de GD (AVIF/WebP), `pdo_pgsql`, `pgsql`, certificado local, `a2enmod ssl headers rewrite` e os `COPY` de `docker/php/custom.ini`, `docker/apache/000-default.conf` e `docker/apache/default-ssl.conf`, alinhado a `specs/030-atualizar-php-seguro/contracts/runtime-local.md`
- [X] T006 [P] Conferir que a lista de pacotes, a ordem backup → PPA → portão do candidato → `apt-get install -d` → install → `systemctl restart apache2` e o retorno sem restore de banco em `specs/030-atualizar-php-seguro/contracts/runtime-producao.md` batem com `specs/030-atualizar-php-seguro/research.md` (R5, R6, R7); corrigir o contrato se houver divergência
- [X] T007 [P] Confirmar em `composer.json` que `require` não tem a chave `php` e que `config.platform` / `config.platform.php` estão ausentes; não acrescentar trava (FR-007)

**Checkpoint**: o que pode mudar é só a base e o portão no `Dockerfile`; Composer, ini, Compose, core e vendor ficam fora.

---

## Phase 3: User Story 1 — Site deixa de estar exposto aos RCE de outubro de 2026 (Priority: P1) 🎯 MVP

**Goal**: o ambiente local executa PHP 8.4.27 ou patch posterior da linha 8.4 (`< 8.5.0`), cobrindo CVE-2026-109618 e CVE-2026-109619 (FR-001, FR-002, FR-003, FR-012).  
**Independent Test Criteria**: depois do rebuild, `PHP_VERSION_ID` está em `[80427, 80500)` e uma página pública abre com o conteúdo anterior (spec US1; `quickstart.md` seção A).

- [X] T008 [US1] Trocar em `Dockerfile` a base de `drupal:11-php8.3-apache` para `drupal:11-php8.4-apache`, sem cravar digest e sem mudar para Alpine ou FPM, conforme `specs/030-atualizar-php-seguro/plan.md` (decisão 1) e `specs/030-atualizar-php-seguro/contracts/runtime-local.md`
- [X] T009 [US1] Inserir em `Dockerfile`, imediatamente após o `FROM`, um `RUN` que falha o build salvo `PHP_VERSION_ID >= 80427 && PHP_VERSION_ID < 80500` (exemplo: `php -r "exit(PHP_VERSION_ID >= 80427 && PHP_VERSION_ID < 80500 ? 0 : 1);"`), conforme `specs/030-atualizar-php-seguro/research.md` (R3) e `specs/030-atualizar-php-seguro/data-model.md` (Portão de versão)
- [X] T010 [US1] Reconstruir só o serviço da aplicação, sem apagar volumes: `docker compose build drupal` e `docker compose up -d drupal` a partir da raiz do repositório; não usar `docker compose down -v` (`specs/030-atualizar-php-seguro/quickstart.md` seção A)
- [X] T011 [US1] Ler a versão no container com o portão de `specs/030-atualizar-php-seguro/quickstart.md` seção A (`docker compose exec -T drupal php -r "echo PHP_VERSION, PHP_EOL; exit(PHP_VERSION_ID >= 80427 && PHP_VERSION_ID < 80500 ? 0 : 1);"`); exit diferente de 0 rejeita a entrega local
- [X] T012 [US1] Abrir a home em HTTPS na porta do `.env` e confirmar HTTP 200 com o conteúdo anterior, conforme o teste independente da US1 em `specs/030-atualizar-php-seguro/spec.md` e a saída “Home” de `specs/030-atualizar-php-seguro/contracts/runtime-local.md`

**Checkpoint**: imagem local na faixa 8.4.27+ / < 8.5 e home no ar. Este é o MVP local; a proteção dos visitantes de produção só fecha na US4.

---

## Phase 4: User Story 2 — Visitante continua usando o site depois da troca (Priority: P1)

**Goal**: home, página interna e área autenticada respondem com o conteúdo anterior; imagens, banco e arquivos publicados permanecem (FR-004, FR-005, FR-006, SC-002).  
**Independent Test Criteria**: com o runtime da US1 no ar, home, uma página interna pública e uma tela autenticada abrem; banco e arquivos não foram apagados nem recriados vazios (`quickstart.md` seção B).

- [X] T013 [P] [US2] No container `drupal`, confirmar `gd` (AVIF e WebP), `pdo_pgsql`, `pgsql` e `openssl` via `php -m` e `gd_info()`, conforme `specs/030-atualizar-php-seguro/contracts/runtime-local.md` e `specs/030-atualizar-php-seguro/quickstart.md` seção A
- [X] T014 [P] [US2] Confirmar que o volume `postgres_data` de `docker-compose.yml` não foi recriado e que o conteúdo do banco continua consultável depois do rebuild da US1 (`specs/030-atualizar-php-seguro/data-model.md`, Runtime local)
- [X] T015 [US2] Limpar os caches com `docker compose exec -T drupal vendor/bin/drush cr` e exigir exit 0, conforme `specs/030-atualizar-php-seguro/quickstart.md` seção B
- [X] T016 [US2] No navegador, em HTTPS na porta do `.env`, abrir a home, uma página interna pública e o login de uma conta já existente; imagens carregam, a sessão autentica e os dados da conta aparecem (`specs/030-atualizar-php-seguro/quickstart.md` seção B e `specs/030-atualizar-php-seguro/spec.md` US2)

**Checkpoint**: o visitante local usa o site como antes da troca. Sem `cim`, `updb` ou `composer update`.

---

## Phase 5: User Story 3 — Operador local confirma a versão e reconstrói o ambiente (Priority: P2)

**Goal**: a definição versionada sozinha reconstrói o serviço, a versão lida passa no portão, `drush cr` devolve a home e `composer.json` continua sem trava de PHP (FR-007, FR-008, SC-004).  
**Independent Test Criteria**: reconstruir somente o serviço `drupal`, abri-lo e ler o identificador de versão na faixa 8.4.27+ / < 8.5 (`spec.md` US3; `quickstart.md` seções A e B).

- [X] T017 [US3] A partir do `Dockerfile` já versionado, repetir `docker compose build drupal` e `docker compose up -d drupal` (sem `docker compose down -v`) e reler o portão de versão de `specs/030-atualizar-php-seguro/quickstart.md` seção A
- [X] T018 [P] [US3] Executar `docker compose exec -T drupal vendor/bin/drush cr` e confirmar que a home em HTTPS volta a responder, conforme `specs/030-atualizar-php-seguro/quickstart.md` seção B
- [X] T019 [P] [US3] Revisar `composer.json` depois do rebuild e confirmar que `require.php` e `config.platform` continuam ausentes; não rodar `composer config` nem `composer update` (`specs/030-atualizar-php-seguro/quickstart.md` seção B, FR-007)

**Checkpoint**: um operador reproduz a linha 8.4 só com o `Dockerfile` versionado e o quickstart, sem painel administrativo.

---

## Phase 6: User Story 4 — Operador aplica a mesma linha na VPS com janela curta (Priority: P2)

**Goal**: produção (Ubuntu 24.04, Apache `mod_php`, app em `/var/www/html`) passa a PHP 8.4.27+ da linha 8.4 via PPA `ondrej/php`, com backup conferido antes, indisponibilidade observável < 2 min depois dos `.deb` em cache, e retorno ao módulo 8.3 sem restaurar o banco (FR-005, FR-009, FR-010, FR-011, SC-003, SC-005).  
**Independent Test Criteria**: seguir `contracts/runtime-producao.md` na janela combinada e, ao final, ler a versão na faixa e abrir a home em HTTPS (`quickstart.md` seção C). Cada passo exige sucesso antes do próximo. Candidato fora do portão ⇒ não instalar e deixar o site em 8.3.6.

- [X] T020 [US4] No SSH da VPS (`specs/030-atualizar-php-seguro/contracts/runtime-producao.md`, pré-condições), confirmar workdir `/var/www/html`, `php -v` na linha 8.3, `mods-enabled/php8.3.load` presente, `libapache2-mod-php8.3` e `php8.3-cli` instalados, e `php vendor/bin/drush.php status` com exit 0; não instalar PHP 8.4 neste passo
- [X] T021 [US4] Gerar o backup com `php vendor/bin/drush.php sql-dump --result-file=../backups/backup_eu_sou_estagio_<timestamp>.sql --gzip` a partir de `/var/www/html` e só avançar se o arquivo existir, tamanho > 0 e `gzip -t` ok; falha ⇒ abortar com o Apache intocado (`specs/030-atualizar-php-seguro/contracts/runtime-producao.md` passo 0 e `specs/030-atualizar-php-seguro/data-model.md` Backup de produção)
- [X] T022 [US4] Habilitar `ppa:ondrej/php` e rodar `apt update`, deixando o site em PHP 8.3, conforme `specs/030-atualizar-php-seguro/contracts/runtime-producao.md` passo 1 e `specs/030-atualizar-php-seguro/research.md` (R5)
- [X] T023 [US4] Ler o candidato de `libapache2-mod-php8.4` e de `php8.4-cli`; só continuar se a versão upstream for ≥ 8.4.27 e < 8.5.0; caso contrário abortar sem `apt install` e manter 8.3.6 (`specs/030-atualizar-php-seguro/contracts/runtime-producao.md` passo 2, SC-005)
- [X] T024 [US4] Baixar sem instalar, com `apt-get install -d`, os pacotes `php8.4-cli`, `php8.4-common`, `libapache2-mod-php8.4`, `php8.4-curl`, `php8.4-gd`, `php8.4-intl`, `php8.4-mbstring`, `php8.4-opcache`, `php8.4-pgsql`, `php8.4-readline`, `php8.4-xml` e `php8.4-zip`; Apache continua em 8.3 (`specs/030-atualizar-php-seguro/contracts/runtime-producao.md` passo 3)
- [ ] T025 [US4] Instalar somente o conjunto do passo 3 de `specs/030-atualizar-php-seguro/contracts/runtime-producao.md`; não instalar `php8.4-fpm` e não purgar `php8.3-*`; a partir deste `apt-get install` a indisponibilidade observável deve ficar abaixo de 2 minutos (FR-010)
- [ ] T026 [US4] Recarregar o processo com `systemctl restart apache2` (não só reload), conforme `specs/030-atualizar-php-seguro/plan.md` (decisão 7) e `specs/030-atualizar-php-seguro/contracts/runtime-producao.md` passo 5
- [ ] T027 [US4] Validar o portão em produção: CLI na faixa 8.4.27+ / < 8.5, `/etc/apache2/mods-enabled/php8.4.load` presente, `php8.3.load` ausente, e `dpkg` de `libapache2-mod-php8.4` na mesma versão upstream do CLI (`specs/030-atualizar-php-seguro/quickstart.md` seção C e contrato passo 6)
- [ ] T028 [US4] Rodar `php vendor/bin/drush.php cache:rebuild` com exit 0 e abrir a home em HTTPS e o login; conteúdo e imagens anteriores, sessão autentica, banco e arquivos intactos, dump do T021 ainda no disco (`specs/030-atualizar-php-seguro/contracts/runtime-producao.md` passos 7–8 e contrato de verificação)
- [ ] T029 [US4] Se T027 ou T028 falharem, executar o retorno de `specs/030-atualizar-php-seguro/contracts/runtime-producao.md` e `specs/030-atualizar-php-seguro/quickstart.md` seção D: `a2dismod php8.4`, `a2enmod php8.3`, `update-alternatives --set php /usr/bin/php8.3`, `systemctl restart apache2`, sem restaurar o dump; se T027 e T028 passarem, confirmar que os pacotes `php8.3-*` permanecem instalados e que o dump não foi usado (FR-011)

**Checkpoint**: produção na linha 8.4.27+ com janela curta, ou site ainda (ou de volta) em 8.3 sem perda de banco. Não há `git pull`, `cim` nem `updb` nesta história.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Objetivo**: fechar o diff mínimo, a regra do agente e a consistência dos artefatos com o que foi executado.

- [ ] T030 [P] Atualizar o bloco SPECKIT em `.cursor/rules/specify-rules.mdc` para apontar `specs/030-atualizar-php-seguro/tasks.md` como gerado
- [ ] T031 [P] Revisar consistência cruzada spec ↔ plan ↔ research ↔ data-model ↔ contratos ↔ quickstart (faixa 8.4.27+ / < 8.5, sem paliativo de ini, sem trava Composer, sem FPM) sob `specs/030-atualizar-php-seguro/`
- [ ] T032 Confirmar que o diff de código desta feature é somente `Dockerfile`: `composer.json`, `docker-compose.yml`, `docker/php/custom.ini`, `core/` e `vendor/` sem alteração (FR-007, FR-012 e `specs/030-atualizar-php-seguro/plan.md`)
- [ ] T033 Atualizar a nota de próximo passo em `specs/030-atualizar-php-seguro/checklists/requirements.md` para `/speckit-implement`, agora que `tasks.md` existe

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → Polish (Phase 7)
- US1 bloqueia US2 e US3: as duas leem o runtime produzido pelo `Dockerfile` novo
- US3 pode ser reexecutada sozinha depois que T008 e T009 estiverem no `Dockerfile` (teste independente = rebuild)
- US4 só começa depois da prova local (US1 + US2). T021 bloqueia T022–T028. T023 fora do portão encerra a história sem T024–T028
- T029 só restaura o módulo 8.3 se T027 ou T028 falharem; não é passo da virada bem-sucedida
- Polish depois das histórias locais; T032 por último no diff, T030 pode seguir em paralelo com T031

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) 🎯 MVP (Dockerfile + portão + home local)
                    ├→ US2 (P1) fumaça do visitante
                    ├→ US3 (P2) rebuild reproduzível
                    └→ US4 (P2) VPS, depois de US1+US2
                         └→ T029 retorno só se a validação falhar
                              └→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002 ∥ T003 ∥ T004 após T001
- **Foundational**: T005 ∥ T006 ∥ T007 (arquivos distintos)
- **US1**: T008 → T009 (mesmo `Dockerfile`) → T010 → T011 → T012; sem paralelo interno
- **US2**: T013 ∥ T014 após o container da US1; depois T015 → T016
- **US3**: T018 ∥ T019 após T017
- **US4**: sequência estrita T020 → T029; nenhum `[P]`
- **Polish**: T030 ∥ T031; depois T032 → T033

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 — imagem local na faixa 8.4.27+ / < 8.5 e home no ar. Sem isso a produção não vira.
2. **Continuidade**: US2 — imagens, Postgres, página interna e login.
3. **Reprodução**: US3 — segundo rebuild só do serviço `drupal` e `composer.json` ainda sem trava.
4. **Visitante real**: US4 — backup, portão do candidato, install com pacotes já em cache, restart, `drush cr`, home HTTPS. Falha ⇒ módulo 8.3, banco intacto.
5. **Fechamento**: Polish — regra do agente, diff mínimo, checklist.

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T033`), caminho de arquivo e label `[USn]` só nas fases de história
- Marcador `[P]` apenas onde arquivos ou checagens distintas permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; plano pede fumaça do `quickstart.md`)
- Ordem de histórias: US1 (P1) → US2 (P1) → US3 (P2) → US4 (P2)
