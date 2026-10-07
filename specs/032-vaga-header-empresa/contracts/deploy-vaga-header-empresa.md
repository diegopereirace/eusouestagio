# Deploy: Cabeçalho do Detalhe da Vaga — Perfil Empresa (032)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-10-07

## Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
# versionar tipicamente:
#   node.type.empresa.yml
#   field.field.node.empresa.field_imagem.yml + displays empresa
#   field.storage.node.field_vaga_empresa.yml
#   field.storage.node.field_vaga_carga_horaria.yml
#   field.field.node.vagas.field_vaga_* + form/view displays vagas
#   + tema (Twig header, CSS, preprocess)
#   + custom_configs.install (11049) + assets/vaga-header-empresa/*
#   + PRD.md (§3.1)
```

Não inventar YAML à mão: alterar via admin/API + `cex`.

## Destino (staging / prod / outro local)

Pré-requisito: `settings.php` com `$settings['config_sync_directory'] = 'config/sync'`.

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

Local via Compose: `docker compose exec drupal drush <cmd>`.

## Hook

| Update | Responsabilidade |
|--------|------------------|
| `custom_configs_update_11049` | Ensure CT `empresa` + instance `field_imagem` + displays; ensure `field_vaga_empresa` + `field_vaga_carga_horaria` + displays na vaga; copiar asset logo → `public://`; seed empresa EcoConstrutora (UUID fixo) + vaga Engenheiro Civil (UUID fixo) vinculadas com pills/local. **Não** migrar `field_empresa_u`; **não** remover legado; **não** alterar View listagem / cards laranja. |

Receita: `cim` **antes** de `updb` → `updb` (ensures/seeds) → **2ª** `cim` → `cr`.

## Checklist pós-deploy

| Check | Esperado |
|-------|----------|
| CT `empresa` | existe; form com title + logo |
| Fields vaga | Empresa da vaga + Carga horária no form |
| Seed | 1 empresa + 1 vaga; header completo no detalhe |
| Header visual | card, logo 96, verificado, 4 pills quando preenchidas |
| Legado | `field_empresa_u` ainda presente |
| `drush updb` 2ª vez | sem duplicar empresa/vaga seed |
| `/vagas` + cards | sem regressão |

## Zero manual

Nenhum passo no admin de produção para criar tipos, campos ou o mock seed.
