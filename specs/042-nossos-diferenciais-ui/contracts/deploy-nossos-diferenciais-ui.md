# Contract: Deploy — Nossos Diferenciais UI

**Tipo**: código + assets de tema (sem config estrutural, sem `hook_update_N`).

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
| CSS + library | `assets/css/nossos-diferenciais.css`, `default.libraries.yml` |
| Limpeza legado | remoção do bloco ND em `style.css` |
| Fatias (se versionadas) | `assets/images/nossos-diferenciais/*` |

## Gates

- Home e `/para-empresas` exibem a nova apresentação após `drush cr`.
- Library `default/nossos_diferenciais` presente no HTML da página (CSS carregado).
- Zero cliques no admin; placements e conteúdo editorial intactos.
- Sem YAML novo obrigatório em `config/sync/`.
