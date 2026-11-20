# Contract: Deploy — Nossos Diferenciais UI

**Tipo**: código de tema + assets de seed no módulo (sem config estrutural nova).

## Destino (obrigatório)

```bash
git pull
# receita completa do projeto (cim no-op estrutural para UI pura; updb aplica 11064 se pendente):
drush cim -y && drush updb -y && drush cim -y && drush cr
# local:
docker compose exec drupal vendor/bin/drush.php updb -y
docker compose exec drupal vendor/bin/drush.php cr
# produção:
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cr
```

**Gate crítico:** `drush cr` é **obrigatório**. A library `default/nossos_diferenciais` saiu do CSS global; sem rebuild de cache Twig/CSS a Home pode renderizar markup novo **sem** estilos.

## Artefatos que sobem no Git

| Artefato | Caminho |
|----------|---------|
| Twig bloco (± paragraph) | `themes/custom/default/templates/…` |
| CSS + library | `assets/css/nossos-diferenciais.css`, `default.libraries.yml` |
| Limpeza legado | remoção do bloco ND em `style.css` |
| Image styles | reutiliza `wide` (colagem) e `thumbnail` (ícones) — já em `config/sync` |
| Seed mídia (042 follow-up) | `modules/custom/custom_configs/assets/nossos-diferenciais/` + `custom_configs_update_11064` |

## Gates

- Home e `/para-empresas` exibem a nova apresentação após `updb` + `drush cr`.
- Library `default/nossos_diferenciais` presente no HTML da página (CSS carregado).
- Se `field_image` / ícones estavam vazios, colagem seed (~1324×718) e ícones aparecem; editorial já preenchido **não** é sobrescrito.
- Zero cliques no admin; placements intactos.
- Sem YAML novo obrigatório em `config/sync/`.
