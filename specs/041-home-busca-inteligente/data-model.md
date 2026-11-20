# Data Model: Home — Busca Inteligente

**Feature**: [spec.md](spec.md) | **Data**: 2026-11-01

Sem CT/fields/placements novos. Reusa vagas + taxonomia curso.

## Entidades

| Entidade | Campos usados | Papel |
|----------|---------------|-------|
| Node `vagas` | `status`, `field_cidade`, `field_estados`, `field_cursos_t` | Fonte do índice e contagens |
| Termo `curso` | `name`, `status` | Autocomplete sem constraint; nomes no índice |

## Índice lógico (não persistido)

Triple cacheado:

| Campo | Tipo | Regra |
|-------|------|-------|
| `cidade` | string | Canônico |
| `estado` | string UF | Obrigatório no índice |
| `curso` | string | Label do termo |
| `count` | int ≥ 1 | Vagas publicadas do triple |
| `cidade_norm` / `curso_norm` | string | Match accent/case-insensitive |

## Contratos de UI

| Campo Home | Constraint enviada ao outro endpoint | Efeito se 0 opções |
|------------|--------------------------------------|--------------------|
| `cidade` (+ `estado` hidden) | `cidade`, `estado` → cursos | Desabilita curso + hint |
| `cursos` | `curso` → cidades | Desabilita cidade + limpa estado + hint |

## View `vagas` page_1

Sem mudança: filtros `cursos`, `cidade`, `estado` opcionais.
