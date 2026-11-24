# Quickstart: 066-refino-cadastro-empresa

## Origem

1. Confirmar assets em `themes/custom/default/assets/images/cadastro-empresa/`.
2. Desativar bloco `default_views_block__banners_block_3` e `drush cex`.
3. `drush cr`.

## Destino

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

Sem `updb` (sem seed File).

## Smoke

| Rota | Esperado |
|------|----------|
| `/cadastro/empresa` | Hero estático + cartões + CTA `#FD7B1A` |
| `/cadastro/candidato` | Inalterado |
