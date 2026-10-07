# Deploy: Detalhe da Vaga — Refino (031)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-10-07

## Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
# versionar tipicamente:
#   paragraphs.paragraphs_type.faq_item_p.yml + field_pergunta/field_resposta + displays
#   field.storage.node.field_faq_itens.yml + field.field.node.faq.field_faq_itens.yml
#   field.storage.node.field_vaga_requisitos.yml (text_long) + field_vaga_faq (card. 1)
#   displays faq / vagas
#   block.block.default_ctav1vagas.yml
#   + tema (Twig full, CTA suggestion, CSS/libraries)
#   + custom_configs.install (11048)
#   + PRD.md (§3.1 / §3.6)
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
| `custom_configs_update_11048` | Ensure `faq_item_p` + storages/displays; ensure `field_faq_itens` + displays FAQ; migrar FAQ 1:1→itens; seed 1 coleção / 2 itens (UUID fixo); ajustar `field_vaga_faq` card. 1 + consolidar refs; recreate `field_vaga_requisitos` text_long + migrar strings→HTML; seed CTA `cta_v1` vagas + ensure placement `default_ctav1vagas` (`content_full`, `entity_bundle:node` = `vagas`). **Não** alterar View listagem, hero 027, cards laranja, CTAs QS/PE/estudantes. |

Receita: `cim` **antes** de `updb` → `updb` (ensures/seeds/migrações) → **2ª** `cim` (placement pós-seed) → `cr`.

## Checklist pós-deploy

| Check | Esperado |
|-------|----------|
| FAQ coleção | 1 seed com 2 itens; form edita paragraphs |
| `field_vaga_faq` | card. 1 no form da vaga |
| Requisitos | Text long formatted; checks no tema |
| Página full | grid 8/4; sem Processo / Perfil / Match / CTA inline |
| FAQ accordion | itens da coleção; IDs únicos |
| CTA bloco | aparece só em vagas; escuro `#023C62`; landings intactas |
| Ações sidebar | candidatar/salvar/share intactos |
| `/vagas` + cards laranja | sem regressão |
| Header | smoke desktop/mobile OK |
| `drush updb` 2ª vez | sem duplicar coleção/CTA/placement |

## Zero manual

Nenhum passo no admin de produção para ativar a feature.
