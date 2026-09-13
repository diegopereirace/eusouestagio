# Tasks: O que fazemos

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

## Phase 1 — Spec / config

- [x] T001 Criar artefatos SDD em `specs/006-o-que-fazemos/` e apontar feature ativa
- [x] T002 Criar `block_content.type.o_que_fazemos_bt` e `paragraphs.paragraphs_type.o_que_fazemos_item_p`
- [x] T003 Criar storages `field_o_que_fazemos_itens` (card. 3) e `paragraph.field_text_simple_multiple` (card. -1)
- [x] T004 Criar field instances + form/view displays
- [x] T005 Criar placement `block.block.default_oquefazemos` e permissões em `user.role.moderador`

## Phase 2 — Tema

- [x] T006 Twig do bloco `block--block-o-que-fazemos-bt.html.twig`
- [x] T007 Twig do paragraph `paragraph--o-que-fazemos-item-p.html.twig` (ou lógica no bloco)
- [x] T008 CSS escopado `.block-o-que-fazemos-bt` (cores, radius, bordas, responsivo)

## Phase 3 — Seed / PRD / validação

- [x] T009 `custom_configs_update_11008` + helper de seed idempotente
- [x] T010 Atualizar PRD §3.6
- [x] T011 `cim` → `updb` → `cr` e validar home
