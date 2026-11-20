# Contract: Deploy — Restaurar Benefícios Estudantes (Figma)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-11-03  
**Módulo**: `custom_configs` | **Hook**: `custom_configs_update_11081`

## Objetivo

Garantir que destino receba seed canônico restaurado (badge, título Figma, subtítulo, 6 itens, CTA, colagem/órbita), assets, estilos e placement ativo **sem** passos manuais no Block Layout — inclusive se a instância tiver sido apagada ou o placement desabilitado após `11079`/`11080`.

## Pré-condições

1. `settings.php` ativo com `$settings['config_sync_directory'] = 'config/sync'`.
2. Código com Twig/CSS, hook `11081`, canônico de 6 itens e assets em `modules/custom/custom_configs/assets/beneficios-estudantes/` (`icon-1`…`icon-6`, faces da órbita e/ou `collage.png`).
3. Bundle `beneficios_estudantes` / paragraph `card_icon_text_p` / instances badge·CTA·imagem **já existentes** (features `022`+`054`).
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
| 1 | Ensure structure/displays do bundle (defensivo) | fields badge/CTA/imagem presentes |
| 2 | Seed/migração do bloco UUID `a8b9…` (recreate se ausente; 4 cards legado → 6; fill badge/CTA/colagem) | superfície canônica ou editorial preservado |
| 3 | Copiar assets da órbita → `public://` se ausentes | faces disponíveis ou fallback composto |
| 4 | Ensure placement `default_beneficiosestudantes` ativo / `content_full` / `/para-estudantes` | bloco público na rota após Hero |

Reexecução: no-op seguro; **não** sobrescrever editorial divergente; **não** duplicar bloco/placement.

## Configs exportáveis (origem → `drush cex`)

Esperado **sem** YAML estrutural novo (instances já existem). Exportar apenas se displays/labels mudarem:

- `core.entity_form_display.block_content.beneficios_estudantes.default.yml`
- `core.entity_view_display.block_content.beneficios_estudantes.default.yml`

**Não** alterar (nesta feature): storages canônicos; placement de Hero/perfil/jornada/CTA final; Views; PE/QS/home.

## Gates pós-deploy

1. `drush updatedb:status` sem pendência `custom_configs` `11081`.
2. `/para-estudantes` HTTP 200; seção com badge, título bicolor, 6 cards, CTA e colagem/órbita no desktop; fundo `#eef5ff`.
3. Grade pública de 4 cards centrados (“Vagas alinhadas”…) **ausente**.
4. Com instância deletada antes do update: após `updb`, seção reaparece (SC-008).
5. Banner, Hero, jornada, `block_3`, CTA final sem regressão atribuível.
6. `/`, `/quem-somos`, `/para-empresas`: sem impacto visual da library desta seção.
7. Segunda `drush updb -y`: zero duplicatas; editorial divergente intacto.
8. Zero criação manual de fields/placement no destino.
