# Data Model: Bloco Missão e Visão

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-16

## Entidades

### `missao_visao` (`block_content`) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Imagem de fundo | `field_image` | image | 1 | não | storage **reutilizado** `block_content.field_image` |
| Itens | `field_itens_lista` | entity_reference_revisions → paragraph | **2** | não | storage **novo**; target `missao_visao_item_p` |

Info/label admin do bloco: “Missão e Visão” (não exibido publicamente — `label_display: '0'`).

### `missao_visao_item_p` (`paragraph`) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Título | `field_text_simple` | string | 1 | não* | storage **reutilizado** paragraph |
| Descrição | `field_text_simple_long` | string_long | 1 | não* | storage **reutilizado** paragraph |

\*Formulário pode marcar required editorialmente; publicamente, título/descrição vazios são omitidos no Twig.

### Bloco placement `default_missaovisao` — novo

| Aspecto | Valor |
|---------|--------|
| Config ID | `default_missaovisao` |
| Plugin | `block_content:b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e` |
| Tema | `default` |
| Região | `content_full` |
| Weight | `0` |
| Label display | oculto (`'0'`) |
| Visibilidade | `request_path` = `/quem-somos` |

## Relacionamentos

```text
block_content:missao_visao (UUID b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e)
├── field_image → File (fundo)
└── field_itens_lista[0..2] ──► paragraph:missao_visao_item_p
       ├── field_text_simple
       └── field_text_simple_long

block.block.default_missaovisao
└── plugin block_content:<mesmo UUID> → content_full → /quem-somos
```

## Seed (conteúdo)

| Atributo | Valor |
|----------|-------|
| UUID fixo | `b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e` |
| Bundle | `missao_visao` |
| Status | publicado |
| Item 1 título | `Nossa Missão` |
| Item 1 texto | `Conectar empresas e estudantes por meio de experiências de estágio que geram aprendizado real, crescimento profissional e resultados sustentáveis para todos os envolvidos.` |
| Item 2 título | `Nossa Visão` |
| Item 2 texto | `Ser referência nacional em estágio, reconhecida pela qualidade da seleção, pela gestão responsável e pelo desenvolvimento contínuo de talentos.` |
| Imagem | asset `modules/custom/custom_configs/assets/missao-visao/fundo-missao-visao.jpg` → `public://missao-visao/fundo-missao-visao.jpg` |
| Alt sugerido | `Fundo institucional — Missão e Visão` |

**Idempotência**: se já existir bloco com esse UUID, o hook **não** duplica; preenche paragraphs/imagem **somente** se lista vazia / imagem ausente; **nunca** sobrescreve texto ou mídia editorial divergente.

## Displays

| Entidade | Form | View |
|----------|------|------|
| `missao_visao` | `field_image`, `field_itens_lista` (widgets padrão paragraphs) | campos visíveis para o Twig (ou hidden + acesso via entity no Twig) |
| `missao_visao_item_p` | título + descrição | título + descrição |

## Regras de validação / fallback

| Estado | Resultado público |
|--------|-------------------|
| Zero itens | omitir grade (e, se sem imagem, preferir omitir seção útil / manter só fallback escuro sem texto — sem erro Twig) |
| 1 item | uma coluna `col-md-6` (ou full width da coluna); sem `border-end` forçado em item único se não houver “segunda” |
| 2 itens | `col-md-6` + `col-md-6`; `border-end` no 1º em `md+` |
| Sem imagem | `background-color` escuro + overlay; textos legíveis |
| Texto longo | wrap natural; sem `text-overflow` que corte palavras |
| Reexecução `updb` | no-op (sem duplicar tipos/bloco/paragraphs/placement) |
| Editor alterou seed | hook preserva |

## Configuração a versionar (`drush cex`)

| Arquivo | Ação |
|---------|------|
| `block_content.type.missao_visao.yml` | criar |
| `paragraphs.paragraphs_type.missao_visao_item_p.yml` | criar |
| `field.storage.block_content.field_itens_lista.yml` | criar |
| `field.field.block_content.missao_visao.field_image.yml` | criar (reusa storage) |
| `field.field.block_content.missao_visao.field_itens_lista.yml` | criar |
| `field.field.paragraph.missao_visao_item_p.field_text_simple.yml` | criar |
| `field.field.paragraph.missao_visao_item_p.field_text_simple_long.yml` | criar |
| `core.entity_*_display.block_content.missao_visao.default.yml` | criar |
| `core.entity_*_display.paragraph.missao_visao_item_p.default.yml` | criar |
| `block.block.default_missaovisao.yml` | criar |
| `user.role.*` | só se cex mostrar diff de permissões |

**Reutilizar (não recriar storage):** `field.storage.block_content.field_image`, `field.storage.paragraph.field_text_simple`, `field.storage.paragraph.field_text_simple_long`.

## Fora do modelo

- Storages `field_text_simple_small` / paralelos de título
- Fields no node `quem_somos` para Missão/Visão
- Cardinalidade &gt; 2
- Nested paragraphs
- Exibição em home ou outras rotas
