# Implementation Plan: Deploy em Produção — Consolidação do Novo Layout (Home + Quem Somos)

**Branch**: `dev` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo, setup executado manualmente)  
**Date**: 2026-09-23  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/016-deploy-producao-layout/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Executar e formalizar o **deploy em produção** do release consolidado (features `001`–`015`: novo layout da Home e de `/quem-somos`) via esteira 100% automatizada — backup gzipado do PostgreSQL 16 **antes** de qualquer alteração (portão), registro do hash de rollback, Git (`main` ← `origin/dev`), `composer install --no-dev --optimize-autoloader`, `drush config:import -y` → `drush updatedb -y` → `drush config:import -y` (ordem validada: hooks do `custom_configs` dependem dos bundles importados) → `drush cache:rebuild` — com verificação pós-deploy de `<front>` e `/quem-somos` (HTTP 200, blocos novos, zero erros) e procedimento de rollback documentado (RTO ≤ 15 min). **Estado**: deploy executado em produção em 2026-09-23; este plano consolida o runbook normativo, os desvios aplicados e as ações permanentes derivadas.

## Technical Context

**Language/Version**: Bash (SSH) + Drush (via `php vendor/bin/drush.php`) + Composer 2 + Git; app Drupal 11.4.7 / PHP 8.3 — **sem código PHP/Twig/CSS novo nesta feature**  
**Primary Dependencies**: Drush, Composer, Git no servidor; `config/sync` versionado; `custom_configs_update_11001`–`11019` + `custom_banners_update_11002`–`11004` já mergeados. **Nenhuma dependência nova.**  
**Storage**: PostgreSQL 16 (dump gzipado em `../backups/`, fora da raiz pública); config ativa sincronizada a partir de `config/sync`  
**Testing**: verificação pós-deploy por rota (HTTP 200 + seletores HTML) e inspeção de log — ver [quickstart.md](quickstart.md); sem suite automatizada dedicada  
**Target Platform**: VPS produção — Apache + HTTPS (Let's Encrypt), app em `/var/www/html`, acesso `ssh -i C:\Users\diego\.ssh\id_rsa root@177.153.59.190`  
**Project Type**: operação de release/deploy (runbook) sobre codebase Drupal 11  
**Performance Goals**: indisponibilidade percebida < 5 min (SC-006); rotas críticas HTTP 200 em ≤ 3 s pós-deploy (SC-003)  
**Constraints**: backup verificado como portão (FR-001/002); zero passo manual no admin (FR-009); cada etapa verificada antes da próxima (FR-010); `.env`/`settings.php` de produção não versionados e não tocados pela esteira; nunca editar `core/`/`vendor/`; pt-BR  
**Scale/Scope**: 1 release (32 commits `origin/main..dev` no momento da spec), 19 hooks `custom_configs` + 3 hooks `custom_banners`, ~234 arquivos, 2 rotas críticas verificadas, 1 runbook normativo + 1 procedimento de rollback

**Estado atual verificado (2026-09-23):**

- `git rev-list --count origin/main..dev` = **1** → código do release **já consolidado em `main`**; resta apenas o commit de docs desta spec (`f0fb6627`).
- Deploy **já executado** em produção nesta data; spec registra ordem validada e 5 desvios/lições (ver "Plano de Release" na spec).
- `settings.php` ativo de produção apontava `config_sync_directory` para hash legado; corrigido no deploy (backup em `settings.php.bak_20260923`). **Ação permanente**: alinhar `settings.php` com `settings.php.prod` é pré-requisito de todo deploy.
- Drush no servidor sem bit de execução → usar `php vendor/bin/drush.php` (ou `chmod +x`, já aplicado).
- Working tree local `dev` limpo e sincronizado com `origin/dev`.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + PRD + regra de acesso VPS.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK antes do plan |
| Sem `core/` / `vendor/` | PASS | esteira só roda `git`/`composer`/`drush`; nenhuma edição em core/vendor |
| Configuration Management (`cim` antes de `updb`) | PASS | runbook validado em produção: `cim` → `updb` → `cim` → `cr` (spec §Plano de Release) |
| Hooks idempotentes / Entity API / sem dump de schema | PASS | `11001`–`11019` idempotentes; reexecução pós-reset de schema não duplicou seeds (desvio 4) |
| Backup antes de qualquer alteração de banco | PASS | FR-001/002 como portão; dump gzipado fora da raiz pública |
| `.env` / credenciais fora do Git | PASS | Fora de escopo; `settings.php` prod não versionado; alteração manual registrada com `.bak` |
| Clean URLs / HTTPS | PASS | nenhuma rota nova; verificação em `<front>` e `/quem-somos` sob HTTPS |
| Zero passo manual no admin | PASS | FR-009; desvios executados via Drush/Entity API, não via UI |
| Contrib first / custom mínimo | PASS | nenhum módulo/dependência nova nesta feature |

**Post-design**: gates mantidos; desvios do deploy documentados como ações permanentes no runbook (research R2–R6). Sem violação injustificada.

## Design Decisions

1. **Ordem normativa da esteira**: `git` → `composer install --no-dev --optimize-autoloader` → `cim -y` → `updb -y` → `cim -y` (2ª passada para placements/displays dependentes de UUIDs seedados) → `cr`. A ordem `updb` antes de `cim` (pedido original) é **rejeitada** — hooks no-opam/falam sem os bundles importados.
2. **Backup como portão bloqueante**: dump via `drush sql-dump --gzip` para `../backups/` com timestamp; verificação explícita (existência + tamanho + integridade gzip) antes de qualquer `git checkout`. Falha ⇒ abort.
3. **Hash de rollback registrado** (`git rev-parse HEAD` do servidor) antes da atualização de código; rollback = `git reset --hard <hash>` + restore do dump + `cr` (RTO ≤ 15 min, RPO = início do deploy).
4. **Pré-requisito permanente**: `settings.php` ativo alinhado a `settings.php.prod` (`config_sync_directory = 'config/sync'`) **antes** da esteira — divergência aborta o `cim` ou importa do diretório errado.
5. **Conflito de ordenação `custom_banners` × `custom_configs`**: resolvido com import cirúrgico via Entity API (`node.type.banners` + storages/instances) → migração legado (`_custom_banners_migrate_legacy()`) → hooks `custom_banners_update_11002/11003/11004` → `cim` completo. Documentado como exceção; próximos deploys sem migração pendente seguem o fluxo padrão.
6. **Higiene de config órfã**: instâncias `field.field.*` existentes só no ativo (ex.: `block_content.banner.field_link_2`) devem ser removidas via Entity API antes do `cim` quando bloquearem deleção de bundle legado.
7. **Hooks que no-oparam** são reexecutados com reset pontual de schema (`keyvalue system.schema.<modulo> = <N anterior>`) + `updatedb` — idempotência garante ausência de duplicação.
8. **Drush no servidor**: invocar como `php vendor/bin/drush.php` a partir de `/var/www/html` (bit de execução ausente no `vendor/bin/drush`).
9. **Deploy sem janela de manutenção**: horário de baixo tráfego; `cr` ao final minimiza respostas inconsistentes; usuários podem ver layout antigo até o rebuild.
10. **Fora**: alterações editoriais em produção, mudanças de infraestrutura (Apache/SSL/PHP/PG/SO), versionamento de `.env`, qualquer código além do consolidado em `dev`.

## Project Structure

### Documentation (this feature)

```text
specs/016-deploy-producao-layout/
├── spec.md
├── checklists/requirements.md
├── plan.md                        # este arquivo
├── research.md
├── data-model.md
├── contracts/deploy-runbook.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
# Nenhum código novo — feature operacional.
# Artefatos de referência já existentes no release:
config/sync/                       # configuração versionada (fonte da verdade do cim)
modules/custom/custom_configs/custom_configs.install   # hooks 11001–11019 (já mergeados)
modules/custom/custom_banners/                         # hooks 11002–11004 + migração legado
settings.php.prod                  # referência versionada p/ alinhar settings.php de prod

# Pendente pós-deploy (docs):
# merge de dev → main com os commits desta spec (f0fb6627)
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Pré-voo: backup verificado + hash registrado + `settings.php` alinhado a `settings.php.prod`
3. Esteira: `git` → `composer` → `cim` → `updb` → `cim` → `cr` (executada em 2026-09-23)
4. Verificação pós-deploy: `<front>` + `/quem-somos` (HTTP 200, seletores, log limpo)
5. Consolidação: merge dos docs da spec em `main`; runbook normativo como referência dos próximos deploys

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| 2ª passada de `cim` após `updb` | placements/displays dependem de UUIDs seedados pelos hooks | import único — deixa placements órfãos na 1ª passada |
| Import cirúrgico via Entity API (desvio 2) | `custom_banners` exige `updb` antes do `cim` completo (migra e remove legado) | escrever config direto em `config.storage` — tabelas de campo não são criadas |
| Reset de schema + reexecução de hooks (desvio 4) | hooks 11004–11011 no-oparam na 1ª tentativa; idempotência comprovada | reexecutar às cegas em loop — proibido pela spec |
| Deploy sem downtime | tráfego baixo + `cr` ao final; indisponibilidade percebida < 5 min | janela de manutenção com site fora — desnecessária para este release |
