# Tasks: Como funciona

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

## Phase 1 — Spec / config

- [x] T001 Criar artefatos SDD em `specs/007-como-funciona/` e apontar feature ativa
- [x] T002 Criar `block_content.type.como_funciona_bt` e `paragraphs.paragraphs_type.como_funciona_item_p`
- [x] T003 Criar storage `field_como_funciona_itens` (card. 7)
- [x] T004 Criar field instances + form/view displays
- [x] T005 Criar placement `block.block.default_comofunciona` e permissões em `user.role.moderador`

## Phase 2 — Tema

- [x] T006 Twig do bloco `block--block-como-funciona-bt.html.twig`
- [x] T007 Twig do paragraph `paragraph--como-funciona-item-p.html.twig` (fallback)
- [x] T008 CSS escopado `.block-como-funciona-bt` (chevrons, números, setas, responsivo)

## Phase 3 — Seed / PRD / validação

- [x] T009 `custom_configs_update_11009` + helper de seed idempotente
- [x] T010 Atualizar PRD §3.6
- [x] T011 `cim` → `updb` → `cr` e validar home
