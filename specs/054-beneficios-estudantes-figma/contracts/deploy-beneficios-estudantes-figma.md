# Contract: Deploy — Benefícios Estudantes (Figma)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-11-03  
**Módulo**: `custom_configs` | **Hook**: `custom_configs_update_11081` (`11078`–`11080` históricos)

## Objetivo

Garantir que destino receba instances/displays novos, seed canônico (badge, título, subtítulo, 6 itens, CTA, colagem), assets e estilos **sem** passos manuais no Block Layout.

## Pré-condições

1. `settings.php` ativo com `$settings['config_sync_directory'] = 'config/sync'`.
2. Código com YAMLs das instances/displays, Twig/CSS, hook `11081` e assets em `modules/custom/custom_configs/assets/beneficios-estudantes/` (`icon-1`…`icon-6`, `collage`).
3. Bundle `beneficios_estudantes` / paragraph `card_icon_text_p` já existentes (features `022`+).
4. Invocação Drush: `docker compose exec drupal drush <cmd>` (local) ou `php vendor/bin/drush.php <cmd>` (VPS, `/var/www/html`).

## Receita destino (padrão)

```text
git pull
drush cim -y
drush updb -y
drush cim -y
drush cr
```

Ordem: **`cim` antes de `updb`**; **2ª `cim`** após `updb`.

**Não** é o caso especial “updb antes do cim” (`11028`).

## Garantias do hook `11081` (idempotente)

| Passo | Ação | Critério de sucesso |
|-------|------|---------------------|
| 1 | Ensure instances `field_text_simple_2`, `field_link`, `field_image` + displays no bundle | fields presentes no form/view |
| 2 | Seed/migração do bloco UUID `a8b9…` (badge, copy, 6 itens se vazio/legado 4 cards, CTA, colagem, ícones) | superfície canônica ou editorial preservado |
| 3 | Ensure placement `default_beneficiosestudantes` ativo / `content_full` / `/para-estudantes` | bloco público na rota |

Reexecução: no-op seguro; **não** sobrescrever editorial divergente; **não** duplicar bloco/placement.

## Configs exportáveis (origem → `drush cex`)

- `field.field.block_content.beneficios_estudantes.field_text_simple_2.yml`
- `field.field.block_content.beneficios_estudantes.field_link.yml`
- `field.field.block_content.beneficios_estudantes.field_image.yml`
- `core.entity_form_display.block_content.beneficios_estudantes.default.yml`
- `core.entity_view_display.block_content.beneficios_estudantes.default.yml`
- Diffs de roles só se permissões mudarem (improvável)

**Não** alterar (nesta feature): storages canônicos; placement de Hero/perfil/jornada/CTA final; Views; PE/QS/home.

## Gates pós-deploy

1. `drush updatedb:status` sem pendência `custom_configs` `11081`.
2. `/para-estudantes` HTTP 200; seção com badge, título Figma, 6 itens, CTA e colagem no desktop.
3. Grade pública antiga de 4 cards centrados **ausente**.
4. Banner, Hero, jornada, `block_3`, CTA final sem regressão atribuível.
5. `/`, `/quem-somos`, `/para-empresas`: sem impacto visual da library desta seção.
6. Segunda `drush updb -y`: zero duplicatas; editorial divergente intacto.
7. Zero criação manual de fields/placement no destino.
