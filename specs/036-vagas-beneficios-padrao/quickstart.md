# Quickstart: Benefícios Padrão em Todas as Vagas

**Feature**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md) | **Deploy**: [contracts/deploy-beneficios-padrao.md](contracts/deploy-beneficios-padrao.md)

Comandos Drush na **origem** via `docker compose exec drupal drush <cmd>` (ou equivalente no container ativo). No **destino** via `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

## Pré-requisito

- Os 4 arquivos de ícone commitados em `modules/custom/custom_configs/assets/beneficios/beneficios-01.png` … `beneficios-04.png` (Plano de Carreira, Vale Transporte, Vale Refeição, Horário Flexível).
- `settings.php` ativo alinhado a `sites/default/settings.php.prod` (`config_sync_directory = 'config/sync'`).

## Deploy na origem

```bash
docker compose exec drupal drush updb -y     # roda custom_configs_update_11056 (backfill + flip required)
docker compose exec drupal drush cex -y      # consolida field.field.node.vagas.field_vaga_beneficios.yml (required: true)
docker compose exec drupal drush cr
```

Commitar: `modules/custom/custom_configs/custom_configs.install` + `modules/custom/custom_configs/assets/beneficios/beneficios-0N.png` + `themes/custom/default/assets/css/components/vagas-detalhe.css` + `themes/custom/default/templates/content/node--vagas--full.html.twig` + `themes/custom/default/templates/paragraph/paragraph--beneficio-vaga-p.html.twig` + `config/sync/field.field.node.vagas.field_vaga_beneficios.yml`.

## Deploy no destino

```bash
git pull
php vendor/bin/drush.php cim -y      # importa required: true do YAML
php vendor/bin/drush.php updb -y     # roda 11056 (backfill + flip via Entity API)
php vendor/bin/drush.php cim -y      # 2ª passada (no-op aqui, mantida pelo runbook)
php vendor/bin/drush.php cr
```

## Conferência

1. Abrir 3 vagas que antes não tinham benefícios → confirmar seção "Benefícios" com 4 cards (ícone + título) na ordem: Plano de Carreira, Vale Transporte, Vale Refeição, Horário Flexível.
2. Abrir 1 vaga que já tinha benefícios customizados → confirmar conteúdo editorial preservado (sem substituição).
3. Mobile (≤991px): grade 2×2; desktop (≥992px): 4 colunas; ícone ~48px sem distorção; título semibold em azul escuro.
4. No admin, editar uma vaga e remover todos os benefícios → tentar salvar → confirmar bloqueio de validação (campo obrigatório).
5. Reexecutar `drush updb -y` → mensagem de no-op (zero vagas atualizadas, zero duplicações).
6. `cat config/sync/field.field.node.vagas.field_vaga_beneficios.yml | grep required` → `required: true`.

## Falha esperada (Edge Case)

Se algum dos 4 PNGs não estiver presente em `modules/custom/custom_configs/assets/beneficios/` no momento do `updb`, o hook aborta com mensagem `Backfill benefícios: asset ausente ...` e **não** flipa `required: TRUE`. Resolver o asset faltante (commit) e reexecutar `drush updb -y`.

## Resultado da validação (origem — 2026-10-27)

1. `drush updb -y` → `Backfill benefícios: 2 vagas atualizadas, 13 já preenchidas, 15 total.`
2. Vagas backfilladas (ex.: Engenheiro Civil) exibem 4 cards na ordem Figma com `width/height="48"` e ícones em `/sites/default/files/beneficios/beneficios-0N.png`.
3. Vaga editorial (ex.: Estágio em UI/UX Design) preservou benefícios customizados (sem substituição pelo padrão).
4. Validação Entity API com coleção vazia → `field_vaga_beneficios: Este valor não pode ser nulo.` (`required: true`).
5. Reexecução `drush updb -y` → `No pending updates.`; `drush cex -y` → config ativa idêntica a `config/sync`.
6. `config/sync/field.field.node.vagas.field_vaga_beneficios.yml` → `required: true`.
7. Edge Case asset ausente: tratado no hook antes de qualquer escrita (abort + sem flip) — código em `_custom_configs_backfill_vaga_beneficios_padrao()`.
