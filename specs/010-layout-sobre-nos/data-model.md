# Data Model: Layout “Sobre nós”

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-14

## Entidades

### `quem_somos` (`node`) — existente; schema de fields inalterado

Página institucional em `/quem-somos` (alias Pathauto/manual já existente). Esta feature **não** cria storages novos nem remove campos.

| Campo | Storage | Obrigatório (config atual) | Uso |
|-------|---------|----------------------------|-----|
| Título da página | `title` | sim | H1 / metatag; **não** é o heading “Sobre nós” da seção |
| Título seção 1 | `field_titulo` | (conforme instance) | Heading da seção “Sobre nós” |
| Corpo seção 1 | `field_text_long_formatted` | sim | WYSIWYG único que envolve a imagem |
| Imagem seção 1 | `field_imagem` | sim | Foto ~proporção 432×269; float à direita em `md+` |
| Título seção 2 | `field_titulo_2` | — | Seção seguinte (fora do redesign) |
| Corpo seção 2 | `field_text_long_formatted_2` | — | Seção seguinte |
| Imagem seção 2 | `field_imagem_2` | — | Seção seguinte |

**Formatos de texto** (`field_text_long_formatted`): `basic_html`, `full_html` (já configurados).

**Imagem** (`field_imagem`):

| Aspecto | Valor |
|---------|--------|
| Extensões | png gif jpg jpeg webp |
| Diretório | `quem-somos/imagem/[Y]-[m]` |
| Alt | campo presente; `alt_field_required: false` (preferir preencher editorialmente) |
| Image style no view | nenhum (URI direto + `img-fluid` / CSS) — alinhado ao Twig atual |

**Conteúdo**: sem seed nesta feature. Hook **não** sobrescreve copy/imagem editoriais.

### Displays `node.quem_somos`

#### Form — `core.entity_form_display.node.quem_somos.default`

| Grupo | Campos |
|-------|--------|
| Primeiro Bloco | `field_titulo`, `field_text_long_formatted`, `field_imagem` |
| Segundo Bloco | `field_titulo_2`, `field_text_long_formatted_2`, `field_imagem_2` |

Garantir (hook / `cim`) que o Primeiro Bloco permanece editável (FR-004).

#### View — `core.entity_view_display.node.quem_somos.default`

| Campo | Formatter (atual) | Região |
|-------|-------------------|--------|
| `field_titulo` | string, label hidden | content |
| `field_text_long_formatted` | text_default, label hidden | content |
| `field_imagem` | image, label hidden, lazy | content |
| `*_2` | idem | content |
| `langcode` | hidden | — |

Modo efetivo da página completa: **`default`** (não há `full`). Twig acessa via `content.*` e/ou `node.field_*` (padrão atual para URI da imagem).

## Relacionamentos

```text
node:quem_somos
├── field_titulo                    → string (seção 1)
├── field_text_long_formatted       → text_long (HTML)  ──wrap──┐
├── field_imagem                    → File/Image                 │ float-md-end
├── field_titulo_2                  → string (seção 2)           │
├── field_text_long_formatted_2     → text_long                  │
└── field_imagem_2                  → File/Image                 │
                                                                 │
entity_view_display node.quem_somos.default                      │
└── alimenta node--quem-somos.html.twig                          │
    ├── .sobre-nos (clearfix) ←──────────────────────────────────┘
    └── .row-custom-2 (seção 2, sem herdar float)
```

## Regras de validação / edge cases (modelo → UI)

| Condição | Comportamento |
|----------|---------------|
| Imagem ausente / arquivo inválido | Omitir `.sobre-nos__media` e decorativos; texto em largura total |
| Texto vazio | Omitir corpo; se houver imagem, exibir título + imagem sem float obrigatório quebrado |
| Texto curto (&lt; altura da imagem) | Float permanece; clearfix impede colisão com seção 2 |
| HTML rico (p, strong, listas) | Preservar via `text_default`; wrap continua válido |
| Reexecução `updb` | Sem duplicar instances; displays intactos |

## State transitions

Não aplicável (sem workflow de estados além de publicado/não publicado do node).

## O que **não** muda

- Bundle / storages / Path `/quem-somos`
- Banner View `block_quem_somos` (009)
- Campos `*_2` e layout em duas colunas da 2ª seção (exceto isolamento via clearfix)
