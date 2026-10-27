# Contract: deploy 036 — Benefícios Padrão em Todas as Vagas

## Origem

1. Colocar os 4 ícones em `modules/custom/custom_configs/assets/beneficios/beneficios-01.png` … `beneficios-04.png` (commit).
2. `drush updb -y` — roda `custom_configs_update_11056` (backfill de vagas vazias + flip `required: TRUE` via Entity API).
3. `drush cex -y` — consolida `field.field.node.vagas.field_vaga_beneficios.yml` com `required: true` em `config/sync/`.
4. `drush cr` — limpa cache de Twig/CSS/registry.

## Destino

`cim` → `updb` → `cim` → `cr` (receita normativa do projeto).

- 1º `cim`: importa `required: true` do YAML (config estrutural).
- `updb`: roda `11056` — backfill das vagas vazias com os 4 benefícios padrão (ícones copiados de `modules/custom/custom_configs/assets/beneficios/` para `public://beneficios/`).
- 2º `cim`: no-op (placements/displays dependentes de UUIDs — nenhum aqui, mas mantido pelo runbook).
- `cr`: reconstrói caches.

## Pré-requisito

- `settings.php` ativo alinhado a `sites/default/settings.php.prod` — em especial `$settings['config_sync_directory'] = 'config/sync'` (ver `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`).
- Os 4 arquivos `beneficios-0N.png` presentes em `modules/custom/custom_configs/assets/beneficios/` (versionados no Git, chegam via `git pull`).
- Invocação Drush no servidor: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

## Idempotência

- Reexecução de `updb` em ambiente já atualizado: `isEmpty()` falso para todas as vagas → 0 atualizadas; `required: TRUE` já é true → no-op; managed files reusados por URI → não recriados.
- Reexecução de `cim`: `config/sync` idêntico ao ativo → no-op.

## Falha conhecida (Edge Case)

- Se algum dos 4 PNGs não for legível em `modules/custom/custom_configs/assets/beneficios/` no momento do `updb`, o hook aborta com mensagem clara (`t('Backfill benefícios: asset ausente ...')`) e **não** flipa `required: TRUE`. Estado permanece consistente (campo opcional, vagas vazias). Resolver o asset faltante e reexecutar `updb`.

## Verificação pós-deploy

1. Abrir 3 vagas que antes não tinham benefícios → confirmar seção "Benefícios" com 4 cards (ícone + título) na ordem do Figma.
2. Abrir 1 vaga que já tinha benefícios customizados → confirmar conteúdo editorial preservado.
3. Tentar salvar uma vaga nova sem benefícios no admin → confirmar bloqueio de validação (campo obrigatório).
4. Inspecionar `config/sync/field.field.node.vagas.field_vaga_beneficios.yml` → `required: true`.
5. Mobile: grade 2×2; desktop: 4 colunas; ícone ~48px sem distorção; título semibold em azul escuro.
