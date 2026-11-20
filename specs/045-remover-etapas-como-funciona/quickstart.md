# Quickstart: Remover etapas do Como funciona

**Feature**: [spec.md](spec.md) | **Data**: 2026-11-02

## Destino (produção / staging)

`updb` **antes** do `cim` que remove o paragraph type (entidades órfãs):

```bash
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

Local (Docker):

```bash
docker compose exec -T drupal vendor/bin/drush updb -y
docker compose exec -T drupal vendor/bin/drush cim -y
docker compose exec -T drupal vendor/bin/drush cr
```

## Esperado

- Hooks `11065`/`11066` limpam referências, apagam paragraphs `como_funciona_item_p`, removem instance/storage `field_como_funciona_itens` e o paragraph type
- Form do bloco sem “Etapas” / “Entradas”
- Home: bloco só com título, subtítulo e imagens (044)

## Aceite

1. Editar bloco Como funciona → sem campo de etapas
2. Re-executar `updb` → mensagem de já ausente / no-op
3. Front da home inalterado visualmente
