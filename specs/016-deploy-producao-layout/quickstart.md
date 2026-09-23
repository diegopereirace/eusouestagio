# Quickstart: Deploy em Produção — validação e execução

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-23

Guia de validação ponta a ponta do release (Home + Quem Somos). Contrato normativo da esteira: [contracts/deploy-runbook.md](contracts/deploy-runbook.md). Modelo de artefatos: [data-model.md](data-model.md).

## Pré-requisitos

1. Acesso SSH: `ssh -i C:\Users\diego\.ssh\id_rsa root@177.153.59.190`; workdir `/var/www/html`.
2. `settings.php` de produção alinhado a `settings.php.prod` (`config_sync_directory = 'config/sync'`).
3. `main` contendo todo `origin/dev` (merge realizado na esteira ou previamente).
4. Drush invocável: `php vendor/bin/drush.php status`.

---

## A) Pré-voo (portões — US1, FR-001/002/003)

```bash
cd /var/www/html
mkdir -p ../backups
php vendor/bin/drush.php sql-dump --result-file=../backups/backup_eu_sou_estagio_$(date +%Y%m%d_%H%M%S).sql --gzip
ls -la ../backups/                 # arquivo recente, tamanho > 0
gzip -t ../backups/backup_eu_sou_estagio_<timestamp>.sql.gz   # íntegro
git rev-parse HEAD                 # anotar: alvo de rollback
git status                         # tree limpo
```

**Gate**: dump falhou/vazio ou tree sujo ⇒ **abortar** antes de qualquer alteração.

---

## B) Esteira (US2, FR-004–FR-010)

```bash
git fetch origin && git checkout main && git merge origin/dev && git pull origin main
composer install --no-dev --optimize-autoloader
php vendor/bin/drush.php config:import -y     # 1ª passada: bundles/fields
php vendor/bin/drush.php updatedb -y          # hooks 11001–11019 (+ custom_banners)
php vendor/bin/drush.php config:import -y     # 2ª passada: placements/displays
php vendor/bin/drush.php cache:rebuild
```

**Gate**: cada comando com exit code 0 e output sem erro; falha ⇒ parar e avaliar rollback (seção E).

---

## C) Verificação pós-deploy (US3, FR-011–FR-014, SC-003–SC-005)

Como anônimo, a partir de qualquer máquina:

```bash
curl -s -o /dev/null -w "%{http_code} %{time_total}s\n" https://eusouestagio.com.br/
curl -s -o /dev/null -w "%{http_code} %{time_total}s\n" https://eusouestagio.com.br/quem-somos
curl -s https://eusouestagio.com.br/ | grep -o 'class="[^"]*container[^"]*"' | head -3
curl -s https://eusouestagio.com.br/quem-somos | grep -oE 'block-cta-v1|cta-v1' | head -3
# no servidor (locale pt-BR):
# php vendor/bin/drush.php watchdog:show --severity=3 --count=10
```

Esperado:

1. Ambas as rotas **HTTP 200** em ≤ 3 s.
2. `<front>`: `.container`, carrossel full-width, busca hero e blocos 004–008 presentes no HTML.
3. `/quem-somos`: banner bipartido → Sobre Nós → Missão/Visão → Diferenciais → Impact in Numbers → `.cta-v1`/`.block-cta-v1`, nesta ordem visual.
4. Log do site sem erros novos: `php vendor/bin/drush.php watchdog:show --severity=3 --count=10` (locale pt-BR: severidade `Erro` = `3`; evita `--severity=Error`).
5. Mobile (viewport ≤575.98px): sem scroll horizontal nas duas rotas.

---

## D) Idempotência (SC-008)

```bash
php vendor/bin/drush.php updatedb -y     # esperado: "no updates" / no-op
php vendor/bin/drush.php config:import -y  # esperado: sem diffs
```

Sem duplicação de blocos/seeds; conteúdo editorial de produção preservado.

---

## E) Rollback (US4, FR-015 — executar só em falha irrecuperável)

```bash
git reset --hard <hash-registrado>
composer install --no-dev --optimize-autoloader
gunzip -c ../backups/backup_eu_sou_estagio_<timestamp>.sql.gz | php vendor/bin/drush.php sql-cli
php vendor/bin/drush.php cache:rebuild
```

Verificar `<front>` e `/quem-somos` HTTP 200 com o layout anterior. **Alvo**: concluir em ≤ 15 minutos. Reportar causa/etapa/ações antes de nova tentativa.

---

## Checklist "deploy fácil"

| # | Critério | OK? |
|---|----------|-----|
| 1 | Backup verificado (existe, > 0, gzip íntegro, fora de `web/`) antes de tudo | [x] |
| 2 | Hash de rollback registrado | [x] |
| 3 | `settings.php` alinhado a `settings.php.prod` | [x] |
| 4 | Ordem `cim` → `updb` → `cim` → `cr` respeitada | [x] |
| 5 | Zero passo manual no admin | [x] |
| 6 | Rotas `<front>` + `/quem-somos` HTTP 200 ≤ 3 s, seletores OK, log limpo | [x] |
| 7 | Reexecução de `updb`/`cim` = no-op | [x] |
| 8 | Rollback testável: dump + hash disponíveis | [x] |

> Checklist preenchida na consolidação pós-deploy de 2026-09-23 (`/speckit-implement`). Revalidar itens 1–2 e 6–8 em todo novo release.

---

## Estado deste release

Deploy **executado em produção em 2026-09-23** com os desvios documentados na spec (§"Desvios executados"). Runbook normativo em [contracts/deploy-runbook.md](contracts/deploy-runbook.md). Consolidação: merge dos artefatos desta feature (`dev` → `main`) via `/speckit-implement`.

## Próximo passo

Usar [contracts/deploy-runbook.md](contracts/deploy-runbook.md) + este quickstart como referência dos **próximos** deploys (mesma receita, mesmos gates).
