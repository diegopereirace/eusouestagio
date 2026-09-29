# Contract: Deploy — Banner Para Estudantes

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-29  
**Módulo**: `custom_configs` | **Hook**: `custom_configs_update_11033`

## Objetivo

Garantir que destino (staging/prod/outro local) receba valor de local, display, seed, placement e desativação do legado **sem** passos manuais no admin.

## Pré-condições

1. `settings.php` ativo com `$settings['config_sync_directory'] = 'config/sync'`.
2. Código com YAMLs em `config/sync`, Twig/CSS, hook `11033` e asset em `modules/custom/custom_configs/assets/banner-para-estudantes/`.
3. Invocação Drush: `docker compose exec drupal drush <cmd>` (local) ou `php vendor/bin/drush.php <cmd>` (VPS).

## Receita destino (padrão)

```text
git pull
drush cim -y
drush updb -y
drush cim -y
drush cr
```

Ordem: **`cim` antes de `updb`**; **2ª `cim`** após `updb` para placements/displays que dependam de UUIDs seedados.

**Não** é o caso especial “updb antes do cim” (esse é só remoção de bundle com conteúdo legado, ex. `11028`).

## Garantias do hook `11033` (idempotente)

| Passo | Ação | Critério de sucesso |
|-------|------|---------------------|
| 1 | Ensure `para_estudantes` em `field_local_exibicao` | valor presente no storage |
| 2 | Ensure display `block_para_estudantes` (defensivo) | display existe e filtra local |
| 3 | Seed nó UUID `c3d4e5f6-a7b8-4901-c234-567890abcdef` + asset | 1 published; sem duplicata |
| 4 | Ensure placement região `banner` / pages `/para-estudantes` | bloco ativo só nessa rota |
| 5 | `default_views_block__banners_block_1.status = false` | legado ausente na página |

Reexecução: no-op; **não** sobrescrever editorial divergente; **não** reativar `block_1` se o editor o tiver reativado de propósito após divergência documentada — preferência: ensure status false apenas se placement ainda for o legado “só para-estudantes” **ou** sempre forçar false nesta feature (produto: legado morto). **Decisão**: force `status = false` no ensure (legado desta rota é substituído pelo novo display).

## Configs exportáveis (origem → `drush cex`)

- `field.storage.node.field_local_exibicao.yml`
- `views.view.banners.yml` (display `block_para_estudantes`)
- `block.block.default_views_block__banners_block_para_estudantes.yml`
- `block.block.default_views_block__banners_block_1.yml` (`status: false`)

## Gates pós-deploy

1. `drush updatedb:status` sem pendência `custom_configs` `11033`.
2. `/para-estudantes` HTTP 200; hero duas colunas; **sem** banner legado de internas.
3. `/`, `/quem-somos`, `/para-empresas`, `/contato`: sem o novo hero; amostragem visual intacta.
4. Segunda `drush updb -y`: zero nós/placements duplicados.
