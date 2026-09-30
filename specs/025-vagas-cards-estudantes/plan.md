# Plan: Landing PE shell + Listagem `/vagas` (025)

**Atualizado**: 2026-09-30

## Architecture

- `/para-estudantes` = Node `page` shell (UUID `d0e1f2a3-…`) + blocs path-scoped
- `/vagas` = View `vagas` `page_1` (12 + pager full, cards laranja)
- Landing destaque = `block_3` (3 itens, header → `/vagas`)

## Ordem `/para-estudantes`

banner hero → content_full: benefícios(0) → jornada(1) → perfil(2) → block_3(3)

## Automação

- `11040`: cards/grid page_1
- `11041`: path `/vagas`, shell, block_3, title hide, placements, filtros → `/vagas`

## Deploy

`cim` → `updb` → `cim` → `cr`
