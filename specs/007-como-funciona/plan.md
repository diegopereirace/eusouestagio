# Implementation Plan: Bloco Como funciona

**Branch**: `dev`  
**Date**: 2026-09-13  
**Spec**: [spec.md](spec.md)

## Summary

Entregar o bloco gerenciável **Como funciona** (`como_funciona_bt`) com título, subtítulo e até 7 etapas textuais (`como_funciona_item_p`). Chevrons, números e setas via CSS; cores por posição. Estrutura em `config/sync`; seed idempotente `11009` em `custom_configs`; tema `default`.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Block Content, Paragraphs, Bootstrap Barrio 5 / Bootstrap 5.3  
**Storage**: PostgreSQL; config em `config/sync`; conteúdo via seed  
**Deploy**: `drush cim -y` → `drush updb -y` → `drush cr`

## Design Decisions

1. Reutilizar `field_text_simple` / `field_text_simple_long` no bloco e `field_text_simple` no paragraph.
2. Novo ERR storage `field_como_funciona_itens` com cardinality **7**.
3. Números/chevrons/setas: CSS (`data-step`, clip-path/pseudo-elementos); sem assets no CMS.
4. Cores: CSS `:nth-child` + variáveis `--cf-step-N`.
5. Placement weight `-1` abaixo de O que fazemos (`-2`).
6. UUID: `c8d4e0f2-3a5b-4c6d-8e9f-0a1b2c3d4e5f`.

## Project Structure

```text
specs/007-como-funciona/
config/sync/          # bundle, fields, displays, placement, role
themes/custom/default/
  templates/block/block--block-como-funciona-bt.html.twig
  templates/paragraph/paragraph--como-funciona-item-p.html.twig
  assets/css/style.css
modules/custom/custom_configs/custom_configs.install
PRD.md
```

## Phases

1. Spec/plan/data-model/contract/tasks/quickstart
2. Config YAML + permissions
3. Twig + CSS
4. Seed 11009
5. PRD §3.6 + validação cim/updb/cr
