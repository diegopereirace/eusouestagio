# Quickstart: O que fazemos — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-13

```bash
docker compose exec -T drupal vendor/bin/drush cim -y
docker compose exec -T drupal vendor/bin/drush updb -y
docker compose exec -T drupal vendor/bin/drush cr
```

## Esperado

- Tipo de bloco “O que fazemos” (`o_que_fazemos_bt`) disponível
- Paragraph “Item O que fazemos” com título + itens
- Seed com 3 cards de referência (UUID `a7c3e9f1-2b4d-4e8a-9c6f-1d2e3f4a5b6c`)
- Placement `default_oquefazemos` em `content_full`, weight `-2`, `<front>`
- Home: seção abaixo de Nossa Metodologia; card do meio em duas colunas

Em ambiente limpo, se o placement falhar no 1º `cim` por UUID inexistente, repetir `cim` após o seed.

## Aceite visual

1. Título “O que fazemos” + descrição da arte
2. Cards navy / green / orange
3. Mobile: cards empilhados
4. Re-executar `updb` → seed no-op
