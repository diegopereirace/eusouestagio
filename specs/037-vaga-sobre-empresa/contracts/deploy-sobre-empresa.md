# Contract: deploy 037 — resumo curto da Empresa + cartão na vaga

## Origem

Comandos via `docker compose exec drupal drush <cmd>` (ou equivalente no container ativo).

1. Implementar `custom_configs_update_11057` e o Twig/CSS.
2. `drush updb -y` — cria a instância `node.empresa.field_text_simple` se faltar, garante os displays e preenche o resumo da EcoConstrutora se estiver vazio.
3. `drush cex -y` — exporta, sem edição manual de YAML:
   - `config/sync/field.field.node.empresa.field_text_simple.yml`
   - `config/sync/core.entity_form_display.node.empresa.default.yml`
   - `config/sync/core.entity_view_display.node.empresa.default.yml`
4. `drush cr`.
5. Atualizar o `PRD.md` só na linha do CT `empresa` (§3.1) e no parágrafo do detalhe full que hoje diz que não há bloco “Sobre a Empresa”.

Não há asset novo. O logo da EcoConstrutora já está em `modules/custom/custom_configs/assets/vaga-header-empresa/`.

## Destino

`cim` → `updb` → `cim` → `cr` (receita normativa; runbook em `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`).

- 1º `cim`: importa a instância e os displays.
- `updb`: roda `11057`. Em destino já importado, o ensure de campo/display é no-op e o seed só grava se o resumo estiver vazio.
- 2º `cim`: no-op esperado (nenhum placement novo). Mantido pelo runbook.
- `cr`: Twig e CSS do tema.

Invocação no servidor: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

## Pré-requisito

- `settings.php` ativo alinhado a `sites/default/settings.php.prod`, em especial `$settings['config_sync_directory'] = 'config/sync'`.
- CT `empresa`, `field_vaga_empresa` e a EcoConstrutora já presentes (features 032/034). Se o node seed não existir, o helper de resumo reporta ausência e não cria empresa.

## Idempotência

- Instância já existente → não recria.
- Componente de display já presente → não regrava pesos.
- Resumo da EcoConstrutora não vazio → não sobrescreve, mesmo que o texto seja diferente do Figma.
- Segunda passagem de `updb` → “No pending updates.”
- Segunda passagem de `cim` com `config/sync` igual ao ativo → no-op.

## O que o hook não faz

- Não preenche resumo de outras empresas.
- Não altera `field_text_long_formatted`, logo, cidade ou estado.
- Não torna o resumo obrigatório.
- Não mexe em `field_empresa_u` nem na instância `node.vagas.field_text_simple`.
