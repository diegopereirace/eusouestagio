# Quickstart: Como funciona — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-13

```bash
docker compose exec -T drupal vendor/bin/drush cim -y
docker compose exec -T drupal vendor/bin/drush updb -y
docker compose exec -T drupal vendor/bin/drush cr
```

## Esperado

- Tipo de bloco “Como funciona” (`como_funciona_bt`) disponível
- Paragraph “Item Como funciona” com texto único
- Seed com 7 etapas de referência (UUID `c8d4e0f2-3a5b-4c6d-8e9f-0a1b2c3d4e5f`)
- Placement `default_comofunciona` em `content_full`, weight `-1`, `<front>`
- Home: seção abaixo de O que fazemos
- Visual do fluxo: ver feature `044-como-funciona-imagens` (imagens desktop/mobile via `<picture>`)

Em ambiente limpo, se o placement falhar no 1º `cim` por UUID inexistente, repetir `cim` após o seed.

## Aceite visual

1. Título “Como funciona” (à direita) + subtítulo da arte
2. Fluxo em imagem gerenciável (desktop/mobile) — feature 044
3. Mobile: arte mobile legível sem distorção
4. Re-executar `updb` → seed no-op
