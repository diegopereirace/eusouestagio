# Data Model: Bloco O que fazemos

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-13

## Entidade

### `o_que_fazemos_bt` (`block_content`)

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Título | `field_text_simple` | string | 1 | não | storage reutilizado |
| Descrição | `field_text_simple_long` | string_long | 1 | não | storage reutilizado |
| Cards | `field_o_que_fazemos_itens` | entity_reference_revisions → paragraph | **3** | não | storage novo; bundle `o_que_fazemos_item_p` |

### `o_que_fazemos_item_p` (`paragraph`)

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Título | `field_text_simple` | string | 1 | não | storage paragraph reutilizado |
| Itens | `field_text_simple_multiple` | string | **-1** | não | storage novo em paragraph |

## Relacionamentos

```text
block_content:o_que_fazemos_bt
├── field_text_simple
├── field_text_simple_long
└── field_o_que_fazemos_itens[0..3] ──► paragraph:o_que_fazemos_item_p
       ├── field_text_simple
       └── field_text_simple_multiple[]

block placement (config)
└── plugin block_content:<UUID fixo> ──► block_content:o_que_fazemos_bt
```

## Regras de validação e fallback

| Estado | Resultado público |
|--------|-------------------|
| Título vazio | omitir H2 |
| Descrição vazia | omitir parágrafo |
| Sem cards | omitir grade sem erro |
| Card sem título | omitir header do card (se houver itens, ainda renderiza body) |
| Card sem itens | só header (se houver título) |
| Card índice 1 | itens em duas colunas (`col-6`) |

## Seed lógico

- Bundle: `o_que_fazemos_bt`
- UUID: `a7c3e9f1-2b4d-4e8a-9c6f-1d2e3f4a5b6c`
- Info/título: `O que fazemos`
- Descrição: texto da arte de referência
- Cards: Recrutamento Estratégico / Gestão / Desenvolvimento com listas da arte
- Placement: `default`, `content_full`, weight `-2`, somente `<front>`

## Configuração a versionar

**Reutilizar:** storages de texto existentes em block_content e paragraph (`field_text_simple`, `field_text_simple_long`).

**Criar/exportar:** bundle, paragraph type, `field_o_que_fazemos_itens`, `field.storage.paragraph.field_text_simple_multiple`, instances, displays, `block.block.default_oquefazemos.yml`, `user.role.moderador.yml`.

## Fora do modelo

- Campos de cor
- Imagens
- Nested paragraphs
