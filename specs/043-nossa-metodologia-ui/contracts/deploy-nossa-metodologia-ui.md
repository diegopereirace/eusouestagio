# Contract: Deploy — Nossa Metodologia UI

**Tipo**: código de tema (sem config estrutural, sem `hook_update_N`).

## Destino

```bash
git pull
# receita completa do projeto é aceitável (no-op estrutural para esta feature):
# drush cim -y && drush updb -y && drush cim -y && drush cr
docker compose exec drupal drush cr   # local
# produção: php vendor/bin/drush.php cr
```

## Artefatos que sobem no Git

| Artefato | Caminho |
|----------|---------|
| Twig bloco (± paragraph) | `themes/custom/default/templates/…` |
| CSS + library | `assets/css/nossa-metodologia.css`, `default.libraries.yml` |
| Limpeza legado | remoção do bloco NM em `style.css` |

## Gates

- Home e `/para-empresas` exibem a nova apresentação após `drush cr`.
- Library `default/nossa_metodologia` presente no HTML da página (CSS carregado).
- Zero cliques no admin; placements e conteúdo editorial intactos.
- Sem YAML novo obrigatório em `config/sync/`.
