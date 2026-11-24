# Quickstart: 066-refino-cadastro-empresa

## Origem

1. Confirmar placement `default_views_block__banners_block_3` (`status: true`, pages `/cadastro/empresa`).
2. Confirmar filtro de título no display `block_3` = `Banner - Cadastro Empresa`.
3. `drush cex` + `drush cr`.

## Destino

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

Com `updb`: `custom_configs_update_11096` grava as artes desktop (1800×500) e mobile (828×460) no node “Banner - Cadastro Empresa” (campos `field_imagem_desktop` / `field_imagem_mobile`). Reexecução = no-op; depois o editor pode trocar as imagens em `/node/39/edit`.

Receita completa: `git pull` → `cim -y` → `updb -y` → `cr`.

## Smoke

| Rota | Esperado |
|------|----------|
| `/cadastro/empresa` | Banner do conteúdo “Banner - Cadastro Empresa” + cartões + CTA `#FD7B1A` |
| `/node/39/edit` | Editor troca imagem → refletido na rota após `cr` |
| `/cadastro/candidato` | Inalterado |
