# Data Model: Deploy em Produção — Consolidação do Novo Layout

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-23

Feature operacional: não cria entidades Drupal novas. O modelo abaixo descreve os artefatos do release e seus estados — é a base do contrato da esteira ([contracts/deploy-runbook.md](contracts/deploy-runbook.md)).

## Entidades

### 1. Release

| Aspecto | Valor |
|---------|--------|
| Conteúdo | 32 commits `origin/main..dev` (features `001`–`015` + ajustes) no momento da spec |
| Destino | branch `main` (produção) |
| Origem | `origin/dev` (já sincronizado localmente) |
| Delta de código | +21.998 / −1.015 linhas em 234 arquivos |
| Core | 11.4.7 (commit `a30c8b68`) — **inalterado** por este release |
| Estado em 2026-09-23 | **consolidado em `main`**; resta 1 commit de docs (`f0fb6627`, esta spec) |

### 2. Backup de segurança (portão)

| Aspecto | Valor |
|---------|--------|
| Comando | `drush sql-dump --result-file=../backups/backup_eu_sou_estagio_$(date +%Y%m%d_%H%M%S).sql --gzip` |
| Destino | `../backups/` relativo a `/var/www/html` — **fora** da raiz pública `web/` |
| Formato | dump PostgreSQL 16 gzipado, timestamp no nome |
| Validação | existência + tamanho > 0 + integridade gzip (`gzip -t`) |
| Papel | ponto de restauração (RPO = início do deploy) |
| Regra | nenhuma alteração de código/banco antes deste artefato verificado (FR-001/002) |

### 3. Alvo de rollback (código)

| Aspecto | Valor |
|---------|--------|
| Registro | `git rev-parse HEAD` do servidor, anotado antes da atualização (FR-003) |
| Reversão | `git reset --hard <hash>` + `composer install --no-dev --optimize-autoloader` |
| Garantia | working tree volta exatamente ao commit pré-deploy |

### 4. Configuração versionada

| Aspecto | Valor |
|---------|--------|
| Fonte | `config/sync` (YAMLs) — passa a ser a config ativa após `cim` |
| Diretório ativo | `config_sync_directory` do `settings.php` de produção **deve** apontar para `config/sync` (pré-requisito; referência: `settings.php.prod`) |
| Conteúdo do release | novas Views/displays, fields, paragraphs (`diferencial_simples_p`, `numero_destaque_p`), block types (`cta_v1`, `diferenciais_quem_somos`), placements |
| Remoções | legados `banner` (block type), `field_titulo_2`, `field_imagem_2`, `field_text_long_formatted_2` |
| Órfãs conhecidas | `field.field.block_content.banner.field_link_2` (só no ativo) — remover via Entity API antes do `cim` |

### 5. Atualizações de banco (conteúdo/seeds)

| Módulo | Hooks | Natureza |
|--------|-------|----------|
| `custom_configs` | `11001`–`11019` | block types, field storages/instances, displays, paragraphs, seeds via Entity API, placements — **idempotentes** |
| `custom_banners` | `11002`–`11004` | migração de banners legados (`_custom_banners_migrate_legacy()`, 7 nodes) + remoção do legado |

- Executados uma única vez por ambiente; reexecução = no-op seguro (SC-008).
- Dependem dos bundles/fields importados pelo `cim` (ordem normativa: `cim` → `updb` → `cim`).
- Nunca sobrescrevem conteúdo editorial divergente; preenchem apenas ausente/vazio.

### 6. Ambiente de produção

| Aspecto | Valor |
|---------|--------|
| VPS | Apache + HTTPS (Let's Encrypt) |
| Acesso | `ssh -i C:\Users\diego\.ssh\id_rsa root@177.153.59.190` |
| App | `/var/www/html` (raiz pública `web/`) |
| Banco | PostgreSQL 16 |
| Drush | `php vendor/bin/drush.php` a partir de `/var/www/html` |
| Arquivos não versionados | `.env`, `settings.php` (nunca tocados pela esteira) |

## Relacionamentos

```text
Release (main ← origin/dev)
  ├── exige Backup verificado ............ portão (FR-001/002)
  ├── registra Alvo de rollback .......... hash pré-deploy (FR-003)
  ├── aplica Configuração versionada ..... cim (1ª passada)
  ├── executa Atualizações de banco ...... updb (11001–11019, custom_banners)
  ├── reaplica Configuração versionada ... cim (2ª passada: placements/displays)
  └── reconstrói caches .................. cr → verificação das rotas

Rollback (se falha irrecuperável)
  ├── código: git reset --hard <hash registrado>
  ├── banco:  restore do Backup (desfaz hooks + imports)
  └── caches: cr → verificação das rotas
```

## Máquina de estados do deploy

| Estado | Transição | Condição |
|--------|-----------|----------|
| `pré-voo` | → `backup OK` | dump existe, tamanho > 0, gzip íntegro |
| `pré-voo` | → `abortado` | falha no dump (nada foi alterado) |
| `backup OK` | → `código atualizado` | fetch/checkout/merge/pull sem conflito + composer OK |
| `código atualizado` | → `config importada` | 1º `cim` exit 0 |
| `config importada` | → `banco atualizado` | `updb` exit 0 (11001–11019) |
| `banco atualizado` | → `consolidado` | 2º `cim` + `cr` exit 0 |
| `consolidado` | → `verificado` | `<front>` + `/quem-somos` HTTP 200, seletores OK, log limpo |
| qualquer estado após `backup OK` | → `rollback` | falha irrecuperável (exit ≠ 0 sem correção, erro 500 persistente, import corrompido) |
| `rollback` | → `restaurado` | hash revertido + dump restaurado + rotas 200 com layout anterior (≤ 15 min) |

## Regras de validação

| Regra | Enforcement |
|-------|-------------|
| Nenhuma etapa sem sucesso da anterior | exit code + inspeção de output (FR-010) |
| Nenhum passo manual no admin | runbook só usa git/composer/drush (FR-009) |
| Config órfã removida via Entity API | nunca SQL direto, nunca `config.storage` sem `save()` |
| Hooks nunca sobrescrevem editorial | idempotência + preenchimento só de vazio/ausente |
| `.env` / `settings.php` fora da esteira | não versionados; alteração manual registrada com `.bak` |

## Fora do modelo

- Conteúdo editorial criado em produção durante a janela (perdido em rollback — RPO aceito)
- Mudanças de infraestrutura (Apache, SSL/TLS, PHP, PostgreSQL, SO)
- Novas entidades Drupal (nenhuma nesta feature)
