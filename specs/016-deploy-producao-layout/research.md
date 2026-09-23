# Research: Deploy em Produção — Consolidação do Novo Layout

**Data**: 2026-09-23 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Spec sem `[NEEDS CLARIFICATION]`; as decisões abaixo resolvem as Assumptions e consolidam as lições do deploy executado em produção nesta data. Todas priorizam **repetibilidade** (mesma receita em todo deploy), **segurança** (backup como portão) e **zero passo manual no admin**.

---

## R1 — Ordem da esteira: `cim` → `updb` → `cim` → `cr`

**Decision**: sequência normativa `git` → `composer install --no-dev --optimize-autoloader` → `drush config:import -y` → `drush updatedb -y` → `drush config:import -y` → `drush cache:rebuild`, cada passo condicionado ao sucesso (exit code 0) do anterior.

**Rationale**: os `hook_update_N` do `custom_configs` (seeds de blocos/paragraphs) dependem dos bundles/fields já importados pelo 1º `cim`; a 2ª passada do `cim` importa placements/displays que referenciam UUIDs seedados no `updb`. Validado em produção em 2026-09-23. Alinha com `estagio-fluxo-dev.mdc` e `drupal-deploy-configs.mdc` (`cim` **antes** de `updb`).

**Alternatives considered**: ordem do pedido original (`updatedb` antes de `config:import`) — rejeitada: hooks no-opam e `11012` falha sem os bundles.

---

## R2 — `config_sync_directory`: alinhar `settings.php` a `settings.php.prod` (pré-requisito)

**Decision**: antes da esteira, garantir que o `settings.php` ativo de produção aponta `config_sync_directory` para `config/sync` (mesma linha do `settings.php.prod` versionado). Divergência ⇒ corrigir com backup do arquivo (`settings.php.bak_<data>`) antes de prosseguir.

**Rationale**: no deploy de 2026-09-23 o ativo apontava para o diretório hash legado (`sites/default/files/config_F1OK.../sync`); como `settings.php` não é versionado, o release não o corrigia sozinho — o `cim` importaria do lugar errado.

**Alternatives considered**: versionar `settings.php` de produção — rejeitado (contém credenciais; regra de segurança do projeto).

---

## R3 — Conflito de ordenação `custom_banners` × `custom_configs`

**Decision**: quando houver migração de conteúdo legado pendente (caso `custom_banners`), executar **antes** do `cim` completo: import cirúrgico via Entity API de `node.type.banners` + storages/instances (`field_imagem_desktop`, `field_imagem_mobile`, `field_local_exibicao`, `field_peso`) → migração (`_custom_banners_migrate_legacy()`) → hooks `custom_banners_update_11002/11003/11004` → `cim` completo.

**Rationale**: `custom_banners` exige `updb` antes do `cim` (migra e remove o legado), enquanto `custom_configs` exige o inverso. O import cirúrgico cria as tabelas de campo via Entity API e destrava a sequência.

**Alternatives considered**: escrever config direto em `config.storage` sem `save()` via Entity API — **proibido** (tabelas de campo não são criadas); pular a migração — perde os 7 banners legados.

---

## R4 — Config órfã bloqueando deleção de bundle legado

**Decision**: instâncias de campo existentes **somente** no ativo de produção (ex.: `field.field.block_content.banner.field_link_2`) que abortam a deleção de um tipo legado devem ser removidas via Entity API antes do `cim`.

**Rationale**: o `cim` aborta a deleção do `block_content.type.banner` enquanto houver instância órfã referenciando o bundle.

**Alternatives considered**: deleção manual no admin — viola FR-009 (zero passo manual); SQL direto — proibido (risco de corrupção de tabelas de campo).

---

## R5 — Hooks que no-oparam: reset pontual de schema + reexecução

**Decision**: hooks registrados como executados mas que no-oparam (11004–11011 na 1ª tentativa) são reexecutados com reset pontual (`keyvalue system.schema.custom_configs = 11003`) seguido de `updatedb`.

**Rationale**: idempotência dos hooks garante reexecução segura — seeds verificam existência (UUID fixo) e não duplicam nada. Confirmado em produção.

**Alternatives considered**: reexecutar `updatedb` às cegas em loop — proibido pela spec (edge case); editar seeds manualmente — viola o padrão de deploy.

---

## R6 — Invocação do Drush no servidor

**Decision**: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html` (ou `chmod +x vendor/bin/drush` uma única vez).

**Rationale**: `vendor/bin/drush` sem bit de execução no servidor; invocação via `php` é estável e independe de permissão de arquivo.

**Alternatives considered**: Drush global — dependência de ambiente desnecessária; Composer 1.x — fora de suporte.

---

## R7 — Backup: comando, destino e verificação

**Decision**: `drush sql-dump --result-file=../backups/backup_eu_sou_estagio_$(date +%Y%m%d_%H%M%S).sql --gzip` (criar `../backups` se inexistente). Verificação obrigatória: arquivo existe, tamanho > 0 coerente com dumps anteriores, integridade gzip (`gzip -t`). Falha ⇒ **deploy abortado**.

**Rationale**: FR-001/FR-002; destino fora da raiz pública `web/` evita exposição do dump via HTTP; gzip reduz janela de I/O em disco da VPS.

**Alternatives considered**: `pg_dump` direto — equivalente aceito pela spec, mas `sql-dump` usa as credenciais do próprio Drupal (menos parâmetros, menos erro); dump dentro de `web/` — risco de vazamento, rejeitado.

---

## R8 — Rollback: reversão de código + restore de banco

**Decision**: registrar `git rev-parse HEAD` do servidor antes da atualização (FR-003). Rollback = `git reset --hard <hash>` → `composer install --no-dev --optimize-autoloader` → `gunzip -c <dump> | drush sql-cli` → `drush cache:rebuild` → verificação das rotas. Alvos: RTO ≤ 15 min; RPO = início do deploy.

**Rationale**: FR-015; o restore do dump desfaz `hook_update_N` e imports de config parciais/totais de uma vez — não existe "desimportar" config seletivamente com segurança.

**Alternatives considered**: rollback só de código sem restore de banco — insuficiente (config ativa e schema já alterados); `git revert` — histórico poluído sem benefício em produção.

---

## R9 — Topologia Git do servidor

**Decision**: fetch/pull no servidor usa o remote configurado em `/var/www/html`; a esteira adapta **apenas** o nome do remote, sem mudar requisitos. Consolidação: `git fetch origin` → `git checkout main` → `git merge origin/dev` → `git pull origin main`.

**Rationale**: Assumptions da spec; verificado na execução de 2026-09-23 sem conflitos.

**Alternatives considered**: deploy por `rsync`/FTP — viola FR-004 (código exclusivamente via Git); forçar merge com `--strategy-option` — proibido (conflito ⇒ abortar e resolver na origem).

---

## R10 — Deploy sem janela de manutenção

**Decision**: executar em horário de baixo tráfego, sem modo de manutenção; `cache:rebuild` ao final como único ponto de inconsistência potencial (usuários podem ver layout antigo até o rebuild).

**Rationale**: SC-006 (indisponibilidade percebida < 5 min); as alterações são aditivas (novos blocos/fields) e o legado convive durante a esteira.

**Alternatives considered**: modo de manutenção (`drush sset system.maintenance_mode 1`) — rejeitado: downtime desnecessário para release aditivo.

---

## R11 — Verificação pós-deploy

**Decision**: verificação por rota como anônimo — `<front>` e `/quem-somos` devem responder HTTP 200 ≤ 3 s, com os seletores-chave do novo layout (`.container`, carrossel full-width, busca hero, blocos 004–008 na home; banner bipartido, Sobre Nós, Missão/Visão, Diferenciais, Impact in Numbers, `.cta-v1`/`.block-cta-v1` em Quem Somos) e log sem erros novos. Detalhes e comandos em [contracts/deploy-runbook.md](contracts/deploy-runbook.md) e [quickstart.md](quickstart.md).

**Rationale**: FR-011–FR-014 / SC-003–SC-005.

**Alternatives considered**: verificação só visual manual — não reproduzível; suite automatizada nova — YAGNI para 2 rotas (curl + grep bastam).
