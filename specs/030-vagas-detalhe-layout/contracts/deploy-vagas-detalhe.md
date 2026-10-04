# Deploy: Detalhe da Vaga — Layout Duas Colunas

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-10-04

## Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
# versionar tipicamente:
#   node.type.faq.yml + field_resposta + displays FAQ
#   paragraphs.paragraphs_type.beneficio_vaga_p.yml + field/displays
#   field.storage.node.field_vaga_*.yml + field_resposta
#   field.field.node.vagas.field_vaga_*.yml
#   core.entity_form_display.node.vagas.default.yml
#   core.entity_view_display.node.vagas.default.yml  (+ full se houver)
#   + tema (node--vagas--full, CSS, library, preprocess)
#   + custom_configs.install (11047)
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
| `custom_configs_update_11047` | Ensure CT `faq` + `field_resposta` + displays; seed 2 FAQ (UUIDs fixos); ensure paragraph `beneficio_vaga_p` + fields/displays; ensure `field_vaga_faq`, `field_vaga_etapas_processo`, `field_vaga_requisitos`, `field_vaga_beneficios` no bundle `vagas` + form/view displays; migração opcional legado→novos se destino vazio. **Não** alterar View `vagas` listagem, hero 027, cards laranja. |

Receita: `cim` **antes** de `updb` (storages no sync) → `updb` (ensures/seeds) → **2ª** `cim` → `cr`.

## Checklist pós-deploy

| Check | Esperado |
|-------|----------|
| CT FAQ | existe; 2 seeds; form pergunta+resposta |
| Paragraph | `beneficio_vaga_p` com ícone + título |
| Fields vagas | 4 novos no form |
| Página full | grid 8/4; seções Figma; sem Match |
| Stepper | último círculo `#FD7B1A` |
| FAQ accordion | abre/fecha; IDs únicos |
| Ações | candidatar/salvar/share intactos |
| `/vagas` + cards laranja | sem regressão |
| `drush updb` 2ª vez | sem duplicar CT/fields/seeds |

## Zero manual

Nenhum passo no admin de produção para ativar a feature.
