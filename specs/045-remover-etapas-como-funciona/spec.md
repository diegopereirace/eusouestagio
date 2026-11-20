# Feature Specification: Remover etapas/Entradas do Como funciona

**Feature Directory**: `specs/045-remover-etapas-como-funciona`  
**Created**: 2026-11-02  
**Status**: Implemented  
**Input do usuário**: Remover o campo “Entradas”/Etapas do bloco Como funciona (não usado no front) e facilitar o deploy.

## Escopo

### Inclui

- Remoção do field instance `field_como_funciona_itens` no bundle `como_funciona_bt`.
- Remoção do storage exclusivo `block_content.field_como_funciona_itens`.
- Remoção do paragraph type `como_funciona_item_p` (instance `field_text_simple`, form/view displays) e entidades órfãs.
- Hooks idempotentes `custom_configs_update_11065` / `11066`.
- Export `config/sync` sem essas configs; displays do bloco sem o componente.
- Atualização do seed histórico `_custom_configs_seed_como_funciona` para não depender do paragraph.
- Remoção do Twig legado `paragraph--como-funciona-item-p.html.twig`.

### Fora

- Alteração das imagens desktop/mobile (feature 044).
- Remoção de título/subtítulo do bloco.
- Alterações em `core/` ou `vendor/`.

## Requirements

- **FR-001**: Editor do bloco `como_funciona_bt` não vê mais o campo Etapas/Entradas (`field_como_funciona_itens`).
- **FR-002**: Front permanece com título, subtítulo e `<picture>` (inalterado).
- **FR-003**: Deploy automatizado: `updb` (`11065`/`11066`) → `cim` → `cr` (updb antes do cim que remove o paragraph type, para limpar entidades).
- **FR-004**: Reexecução do hook = no-op seguro.
- **FR-005**: Storage `paragraph.field_text_simple` **não** é removido (reutilizado por outros bundles).

## Success Criteria

- Form `/admin/content/block/{id}` do Como funciona sem seção Etapas/Entradas.
- `config/sync` sem YAMLs de `field_como_funciona_itens` / `como_funciona_item_p`.
- Home continua renderizando o bloco só com imagens.
