# Contract: Esteira de Deploy — Runbook Normativo

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-23  
**Consumidor**: responsável pelo release (operador humano via SSH)  
**Produtor**: servidor de produção (`/var/www/html`) — Git, Composer, Drush

## Objetivo

Contrato operacional estável para deploys deste codebase: mesma sequência, mesmos gates, mesmas saídas esperadas. Não é API HTTP nem contrato visual. Validado em produção em 2026-09-23.

## Pré-condições (inputs obrigatórios)

| # | Item | Verificação |
|---|------|-------------|
| 1 | Acesso SSH | `ssh -i C:\Users\diego\.ssh\id_rsa root@177.153.59.190` |
| 2 | Workdir | `/var/www/html` |
| 3 | `settings.php` alinhado a `settings.php.prod` | `config_sync_directory = 'config/sync'` (senão: corrigir com `.bak` antes de prosseguir) |
| 4 | Working tree do servidor limpo | `git status` sem alterações pendentes (preservar arquivos de ambiente) |
| 5 | `../backups/` existe | criar se inexistente |
| 6 | Drush invocável | `php vendor/bin/drush.php status` |

## Contrato da esteira (sequência e saídas esperadas)

Ordem obrigatória; cada passo exige **exit code 0** e output sem erro antes do próximo (FR-010). Qualquer falha ⇒ **parar** e avaliar rollback — nunca prosseguir, nunca reexecutar às cegas.

| # | Passo | Comando | Saída esperada |
|---|-------|---------|----------------|
| 0 | **Backup (portão)** | `php vendor/bin/drush.php sql-dump --result-file=../backups/backup_eu_sou_estagio_$(date +%Y%m%d_%H%M%S).sql --gzip` | arquivo criado; `ls -la` tamanho > 0; `gzip -t` OK. **Falha ⇒ abort (nada alterado)** |
| 1 | Registrar rollback | `git rev-parse HEAD` | hash anotado (alvo de reversão) |
| 2 | Código | `git fetch origin && git checkout main && git merge origin/dev && git pull origin main` | sem conflitos; tree = `main` mais recente. **Conflito ⇒ abortar merge, resolver na origem** |
| 3 | Dependências | `composer install --no-dev --optimize-autoloader` | sem pacotes dev; autoloader otimizado. **Falha ⇒ não prosseguir (banco intacto)** |
| 4 | Config (1ª passada) | `php vendor/bin/drush.php config:import -y` | Views/fields/bundles criados; legados removidos; sem erro de dependência |
| 5 | Banco/conteúdo | `php vendor/bin/drush.php updatedb -y` | hooks `11001`–`11019` (+ `custom_banners`) executados; seeds criados sem duplicar |
| 6 | Config (2ª passada) | `php vendor/bin/drush.php config:import -y` | placements/displays dependentes de UUIDs seedados importados; `config:status` limpo |
| 7 | Caches | `php vendor/bin/drush.php cache:rebuild` | caches reconstruídos; site responde |
| 8 | Verificação | ver "Contrato de verificação" | rotas OK ⇒ deploy concluído |

### Exceção conhecida: migração de banners legado

Aplicável **somente** enquanto houver banners no tipo legado a migrar (resolvido em 2026-09-23; mantido como referência). Antes do passo 4: import cirúrgico via Entity API de `node.type.banners` + storages/instances → `_custom_banners_migrate_legacy()` → `custom_banners_update_11002/11003/11004` → seguir para o passo 4. Remover órfãs (ex.: `field.field.block_content.banner.field_link_2`) via Entity API antes do `cim` completo.

## Tratamento de falha por etapa

| Etapa | Falha típica | Ação |
|-------|--------------|------|
| 0 Backup | dump falhou / vazio / gzip inválido | **Abort** — nada foi alterado; investigar disco/permissões |
| 1 Hash | `git rev-parse` falhou | **Abort** — sem alvo de rollback não há esteira |
| 2 Código | conflito de merge | **Abortar merge** (`git merge --abort`); resolver na origem; não forçar |
| 3 Composer | rede/memória/plataforma | **Parar** — banco intacto; não iniciar `cim`/`updb` |
| 4–6 `cim`/`updb` | exit ≠ 0, dependência, hook parcial | **Parar**; se irrecuperável → rollback (contrato abaixo); nunca `updb` em loop |
| 7 `cr` | falha de rebuild | Reavaliar; se site quebrado → rollback |
| 8 Verificação | 500 persistente / seletores ausentes / log com erros novos | Avaliar correção pontual; se irrecuperável → rollback |

## Contrato de verificação pós-deploy

Como anônimo (sem sessão), após o passo 7:

| Rota | Status | Seletores/conteúdo obrigatório |
|------|--------|-------------------------------|
| `<front>` (`/`) | HTTP 200 ≤ 3 s | `.container`; carrossel de banners full-width; busca hero; Nossos Diferenciais; Nossa Metodologia; O Que Fazemos; Como Funciona; Vagas em Destaque; Depoimentos; novo Rodapé |
| `/quem-somos` | HTTP 200 ≤ 3 s | banner bipartido; Sobre Nós; Missão/Visão; Diferenciais; Impact in Numbers; `.cta-v1` / `.block-cta-v1` — nesta ordem visual |

| Gate extra | Critério |
|------------|----------|
| Erros | zero 500/WSOD; log do site sem erros novos de configuração (`php vendor/bin/drush.php watchdog:show --severity=3 --count=10`) |
| Layout | full-width aplicado onde previsto; `.container` nas demais seções |
| Mobile | sem scroll horizontal nas duas rotas |
| Idempotência | reexecução de `updatedb`/`cim` = no-op (sem duplicação, sem sobrescrever editorial) |

## Contrato de rollback

Acionado quando:

- falha irrecuperável após o início das alterações de banco/config (passos 4–7);
- verificação pós-deploy reprovada (rotas/seletores/log);
- erro 500/WSOD persistente após `cr`.

**O rollback não preserva** conteúdo editorial criado na janela do deploy (RPO = início do deploy — aceito pela spec).

| # | Passo | Comando |
|---|-------|---------|
| 1 | Reverter código | `git reset --hard <hash-registrado>` + `composer install --no-dev --optimize-autoloader` |
| 2 | Restaurar banco | `gunzip -c ../backups/backup_eu_sou_estagio_<timestamp>.sql.gz \| php vendor/bin/drush.php sql-cli` |
| 3 | Caches | `php vendor/bin/drush.php cache:rebuild` |
| 4 | Verificar | `<front>` + `/quem-somos` HTTP 200 com layout anterior; log sem erros novos |
| 5 | Reportar | causa, etapa atingida, ações — antes de nova tentativa |

**SLA**: RTO ≤ 15 minutos; RPO = estado do banco no início do deploy.

## Proibições do contrato

- Passo manual no painel administrativo (FR-009)
- Edição de `core/`, `vendor/`, `.env` ou `settings.php` pela esteira
- SQL direto ou escrita em `config.storage` sem Entity API
- Forçar merge com conflito (`--strategy-option`, `--force`)
- Reexecutar `updatedb` em loop após falha (avaliar erro; rollback se irrecuperável)
- Deploy sem backup verificado ou sem hash de rollback registrado

## Não garantido por este contrato

- Conteúdo editorial criado durante a janela do deploy (perdido em rollback — RPO aceito)
- Disponibilidade de terceiros (DNS, provedor da VPS, Let's Encrypt)
- Paridade de ambiente se `settings.php` divergir de `settings.php.prod` após o pré-voo
