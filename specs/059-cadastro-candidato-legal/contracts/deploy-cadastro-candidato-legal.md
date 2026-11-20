# Contract: Deploy Cadastro Candidato + Páginas Legais

**Feature**: [../spec.md](../spec.md) | **Data**: 2026-11-04

## Pré-requisito destino

`settings.php` ativo alinhado a `settings.php.prod` com:

```php
$settings['config_sync_directory'] = 'config/sync';
```

Invocação Drush no servidor: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

## Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cr
git add config/sync themes/custom/default modules/custom/custom_configs \
  modules/custom/custom_configs/modules/custom_configs_users PRD.md \
  specs/059-cadastro-candidato-legal
```

Esperado em `config/sync` (mínimo):

- `field.field.user.user.field_termo.yml` — description com URLs reais
- `block.block.default_views_block__banners_block_2.yml` — `status: false` (ou equivalência sem render na rota)

## Destino (receita normativa)

Há **config exportável** + **seed** → receita completa:

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

Hook esperado: `custom_configs_update_11089`.

## Gates pós-deploy

| Gate | Critério |
|------|----------|
| Legais | `/termos-de-uso` e `/politica-de-privacidade` ≠ 404 |
| Rodapé | Links Institucionais abrem as mesmas URLs |
| Cadastro UI | Banner + card em `/cadastro/candidato`; sem View banner duplicado |
| Aceite | Links do checkbox abrem em nova aba |
| Submit | Fluxo login automático → `/painel/estudante/perfil` |
| Idempotência | Segundo `updb` não duplica Nodes nem apaga body editorial |

## Proibido

- Criar as páginas só no admin de produção.
- Editar YAML de sync “na mão” inventando field novo (só exportar o que o admin/API alterou).
- `Replace` silencioso de assets File (057) — esta feature usa imagens estáticas do tema.
