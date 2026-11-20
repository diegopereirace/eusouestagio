# Contract: Deploy — Home busca inteligente

**Tipo**: código + assets (sem config estrutural, sem `hook_update_N`).

## Destino

```bash
git pull
# receita completa do projeto é aceitável (no-op para esta feature):
# drush cim -y && drush updb -y && drush cim -y && drush cr
docker compose exec drupal drush cr   # local
# produção: php vendor/bin/drush.php cr
```

## Gates

- Rotas `/api/cursos/autocomplete` e `/api/cidades/autocomplete` 200 com novos params.
- Library `default/hero_search` serve JS/CSS novos.
- Zero cliques no admin.
- Sem YAML novo obrigatório em `config/sync/`.
