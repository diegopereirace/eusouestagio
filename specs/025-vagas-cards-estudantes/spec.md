# Feature Specification: Cards Laranja + Landing PE shell + Listagem `/vagas`

**Feature Directory**: `specs/025-vagas-cards-estudantes`  
**Created**: 2026-09-30  
**Updated**: 2026-09-30  
**Status**: Draft  
**Input do usuário**: Cards laranja na listagem; landing `/para-estudantes` vira shell Node + blocos (destaque 3 vagas); listagem completa em `/vagas` (`page_1`, 12 + pager).

## Escopo

### Inclui

- Visual de card laranja (`.item-vaga--destaque`) em `page_1` (`/vagas`) e no destaque da landing (`block_3`).
- **Shell `/para-estudantes`**: Node `page` + alias (padrão PE); composição por blocos path-scoped.
- **Novo display `block_3`**: 3 itens, pager `some`, header “Vagas de Destaque” + “Ver todas as vagas” → `/vagas`; placement `content_full` weight 3 em `/para-estudantes`.
- **`page_1`**: path `vagas`; 12 itens + pager `full`; sem link “Ver todas” no header (já é a listagem).
- Home `block_1`: “Ver todas as vagas” → `/vagas` (sem regressão visual).
- Automação: `custom_configs_update_11040` (cards/grid) + `11041` (shell, path, block_3, placements) + `drush cex`.

### Fora

- Redesign visual do `block_1` / `block_2`.
- Pathauto de nós de vaga.
- View Mode / field storages novos.
- Alterações em `core/` ou `vendor/`.

## User Stories (resumo)

1. **Landing PE**: hero → benefícios → jornada → perfil → 3 cards + “Ver todas as vagas”.
2. **Listagem `/vagas`**: até 12 cards/página + pager; filtros/empty intactos.
3. **Home**: “Ver todas as vagas” aponta para `/vagas`.
4. **Deploy**: `cim` → `updb` (`11041`) → `cim` → `cr` sem passo manual.

## Requirements

- **FR-001**: `page_1` em `/vagas` renderiza cards laranja; 12/página + pager full.
- **FR-002**: Landing `/para-estudantes` é Node shell; não usa path da View.
- **FR-003**: `block_3` na landing lista 3 itens sem paginação + link “Ver todas as vagas” → `/vagas`.
- **FR-004**: Ordem `content_full`: benefícios (0) → jornada (1) → perfil (2) → `block_3` (3).
- **FR-005**: Home `block_1` link “Ver todas” → `/vagas`.
- **FR-006**: Título Drupal oculto em `/para-estudantes`.
- **FR-007**: `hook_update_11041` idempotente + config em `config/sync`.

## Success Criteria

- `/para-estudantes`: 3 cards, link para `/vagas`, ordem de blocos correta.
- `/vagas`: HTTP 200, pager, cards laranja.
- Home: link → `/vagas`.
