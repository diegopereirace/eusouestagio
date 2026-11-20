# Data Model: 034-empresa-pagina-publica

## CT `empresa` (expandido)

| Campo | Storage | Label | Uso |
|-------|---------|-------|-----|
| `title` | core | Nome | H1 / Pathauto |
| `field_imagem` | existente | Logo | Header |
| `field_text_long_formatted` | existente (instance nova) | Sobre a Empresa | Corpo |
| `field_cidade` | existente (instance nova) | Cidade | Meta local |
| `field_estados` | existente (instance nova) | Estado | Meta local |

## Seeds

| UUID | Título | Campos |
|------|--------|--------|
| `f6a7b8c9-d0e1-4234-e567-89abcdef0123` | EcoConstrutora | sobre + Curitiba + PR se vazios |
| `a7b8c9d0-e1f2-4345-f678-9abcdef01234` | Engenheiro Civil | `field_text_long_formatted` + `field_vaga_requisitos` se vazios; vínculo empresa |

## View `empresa_vagas`

- Base: `node_field_data`
- Filters: published, bundle `vagas`
- Argument: `field_vaga_empresa_target_id` (NID empresa)
- Display: `block_empresa` (embed no Twig full)

## Pathauto

- ID: `empresa`
- Pattern: `/empresa/[node:title]`
- Bundle: `empresa`
