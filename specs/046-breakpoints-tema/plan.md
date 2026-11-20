# Plan: Alinhar Breakpoints do tema `default`

**Feature**: [spec.md](spec.md) | **Data**: 2026-11-02

## Contexto técnico

- YAML do Breakpoints API **não** injeta `@media` no CSS; serve PHP/JS/Responsive Image.
- Subtema `default` não herda `bootstrap_barrio.breakpoints.yml`; o grupo ativo é `default`.
- PHP em `default.theme` já mapeia o grupo inteiro para `drupalSettings` — expandir YAML basta.

## Abordagem

1. Expandir `default.breakpoints.yml` (manter mobile/desktop; adicionar xs–xxl limpos BS5).
2. Corrigir outliers CSS pontuais.
3. `drush cr` + smoke visual / discovery.

## Arquivos

| Arquivo | Ação |
|---------|------|
| `themes/custom/default/default.breakpoints.yml` | Expandir |
| `themes/custom/default/assets/css/style.css` | `767px` → `767.98px` |
| `themes/custom/default/assets/css/components/vagas-detalhe.css` | `576px` → `575.98px` |
| `specs/046-breakpoints-tema/*` | Spec / plan / tasks / quickstart |
| `.specify/feature.json` | Apontar para 046 |

## Deploy

Somente tema + cache: `drush cr`. Sem `cim`/`updb` (sem config estrutural nova).
