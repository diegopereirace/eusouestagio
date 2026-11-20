# Quickstart: WebPerf fontes + LCP home (062)

## Desenvolvimento (origem)

```bash
cd themes/custom/default && npm ci && npm run build
# Após alterar image styles / metatag no admin ou via API:
docker compose exec -T drupal vendor/bin/drush cex -y
docker compose exec -T drupal vendor/bin/drush cr
```

## Deploy (destino)

```text
git pull → drush cim -y → drush updb -y → drush cim -y → drush cr
```

Hook esperado: `custom_configs_update_11091` (WebP nos estilos do carrossel + flush).

## Smoke

1. Home anônima: sem `fonts.googleapis.com` no Network; ícones FA no topo/rodapé OK.
2. Contato: tipografia Inter (H2 token) OK.
3. View Source home: meta description; `rel=preload` do hero; `<img>` com width/height no 1º slide.
4. Reteste PSI mobile: https://pagespeed.web.dev/ (URL produção ou staging).
