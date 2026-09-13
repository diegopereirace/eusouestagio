# Implementation Plan: Bloco O que fazemos

**Branch**: `dev`  
**Date**: 2026-09-13  
**Spec**: [spec.md](spec.md)

## Summary

Entregar o bloco gerenciável **O que fazemos** (`o_que_fazemos_bt`) com título, descrição e até 3 cards (`o_que_fazemos_item_p`: título + itens ilimitados). Cores por posição via CSS; card do meio com duas colunas Bootstrap. Estrutura em `config/sync`; seed idempotente `11008` em `custom_configs`; tema `default`.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Block Content, Paragraphs, Bootstrap Barrio 5 / Bootstrap 5.3  
**Storage**: PostgreSQL; config em `config/sync`; conteúdo via seed  
**Deploy**: `drush cim -y` → `drush updb -y` → `drush cr`

## Design Decisions

1. Reutilizar `field_text_simple` / `field_text_simple_long` no bloco e `field_text_simple` no paragraph.
2. Novo ERR storage `field_o_que_fazemos_itens` com cardinality **3** (espelho de `field_icon_title_text_p`).
3. Novo storage paragraph `field_text_simple_multiple` (string, `-1`) para itens.
4. Cores: CSS `:nth-child` + `--brand-navy` / `--brand-green` / `--brand-orange`.
5. Placement weight `-2` abaixo de Nossa Metodologia (`-3`).
6. UUID: `a7c3e9f1-2b4d-4e8a-9c6f-1d2e3f4a5b6c`.

## Project Structure

```text
specs/006-o-que-fazemos/
config/sync/          # bundle, fields, displays, placement, role
themes/custom/default/
  templates/block/block--block-o-que-fazemos-bt.html.twig
  templates/paragraph/paragraph--o-que-fazemos-item-p.html.twig
  assets/css/style.css
modules/custom/custom_configs/custom_configs.install
PRD.md
```

## Phases

1. Spec/plan/data-model/contract/tasks/quickstart
2. Config YAML + permissions
3. Twig + CSS
4. Seed 11008
5. PRD §3.6 + validação cim/updb/cr
