# Deploy: CTA Final — Para Estudantes

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-30

## Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
# versionar: config/sync/block.block.default_ctav1paraestudantes.yml
# + tema (Twig/CSS/library) + custom_configs.install + PRD.md
```

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
| `custom_configs_update_11043` | Seed instância `cta_v1` UUID `e1f2a3b4-…` (só se ausente/campos vazios); ensure placement `default_ctav1paraestudantes` (content_full, weight 4, pages `/para-estudantes`) |

## Checklist pós-deploy

| Check | Esperado |
|-------|----------|
| `/para-estudantes` | 200; CTA final escuro como **último** bloco de `content_full` (após 3 cards) |
| Copy seed (ou editorial) | título “Pronto para dar o próximo passo?” + 2 botões |
| Primário / secundário | `/cadastro/candidato` · `/vagas` |
| Ordem content_full | benefícios → jornada → perfil → block_3 → **CTA** |
| `/quem-somos` | CTA claro **sem** gradiente escuro / outline branco |
| `/para-empresas` | CTA anterior **sem** herdar estilos desta feature |
| Home / outras | placement ausente |
| `drush updb` 2ª vez | sem duplicar bloco; editorial divergente preservado |

## Zero manual

Nenhum passo no admin de produção para ativar a feature.
