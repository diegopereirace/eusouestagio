# Data Model: Bloco Como funciona

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-13

## Entidade

### `como_funciona_bt` (`block_content`)

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Título | `field_text_simple` | string | 1 | não | storage reutilizado |
| Subtítulo | `field_text_simple_long` | string_long | 1 | não | storage reutilizado |
| Etapas | `field_como_funciona_itens` | entity_reference_revisions → paragraph | **7** | não | storage novo; bundle `como_funciona_item_p` |

### `como_funciona_item_p` (`paragraph`)

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Texto | `field_text_simple` | string | 1 | não | storage paragraph reutilizado |

## Relacionamentos

```text
block_content:como_funciona_bt
├── field_text_simple
├── field_text_simple_long
└── field_como_funciona_itens[0..7] ──► paragraph:como_funciona_item_p
       └── field_text_simple

block placement (config)
└── plugin block_content:<UUID fixo> ──► block_content:como_funciona_bt
```

## Regras de validação e fallback

| Estado | Resultado público |
|--------|-------------------|
| Título vazio | omitir H2 |
| Subtítulo vazio | omitir parágrafo |
| Sem etapas | omitir fluxo sem erro |
| Etapa sem texto | omitir o chevron da etapa |

## Seed lógico

- Bundle: `como_funciona_bt`
- UUID: `c8d4e0f2-3a5b-4c6d-8e9f-0a1b2c3d4e5f`
- Info/título: `Como funciona`
- Subtítulo: texto da arte de referência
- Etapas: 7 textos da arte (Entendimento… → Desenvolvimento contínuo)
- Placement: `default`, `content_full`, weight `-1`, somente `<front>`

## Configuração a versionar

**Reutilizar:** storages de texto existentes em block_content e paragraph (`field_text_simple`, `field_text_simple_long`).

**Criar/exportar:** bundle, paragraph type, `field_como_funciona_itens`, instances, displays, `block.block.default_comofunciona.yml`, `user.role.moderador.yml`.

## Fora do modelo

- Campos de cor
- Campos de número
- Imagens / assets de chevron
- Nested paragraphs
