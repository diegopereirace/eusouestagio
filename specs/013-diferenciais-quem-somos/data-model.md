# Data Model: Diferenciais Quem Somos

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-20

## Entidades

### 1. `diferenciais_quem_somos` (block_content) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Título | `field_text_simple` | string | 1 | não | storage **reutilizado** `block_content` |
| Descrição | `field_text_simple_long` | string_long | 1 | não | storage **reutilizado** |
| Itens | `field_itens_lista` | entity_reference_revisions → paragraph | **ilimitada** (`-1` no storage) | não | storage **reutilizado**; handler → `diferencial_simples_p` |

- Label admin: “Diferenciais Quem Somos”.
- Publicamente: `label_display: '0'` no placement.
- Revision: alinhar ao padrão dos outros block types do projeto.

### 2. `diferencial_simples_p` (paragraph) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Ícone | `field_image` | image | 1 | não | storage **reutilizado** paragraph |
| Rótulo | `field_text_simple` | string | 1 | não* | storage **reutilizado**; *recomendado no form |

\*Formulário pode marcar required editorialmente; publicamente, rótulo/ícone vazios são omitidos no Twig.

### 3. Bloco placement `default_diferenciaisquemsomos` — novo

| Aspecto | Valor |
|---------|--------|
| Config ID | `default_diferenciaisquemsomos` |
| Plugin | `block_content:d5e6f7a8-b9c0-4d1e-8f2a-3b4c5d6e7f80` |
| Tema | `default` |
| Região | `content_full` |
| Weight | `10` |
| Label display | oculto (`'0'`) |
| Visibilidade | `request_path` = `/quem-somos` |

### 4. Ajuste de storage compartilhado

| Storage | Antes | Depois | Impacto |
|---------|-------|--------|---------|
| `field.storage.block_content.field_itens_lista` | `cardinality: 2` | `cardinality: -1` | Desbloqueia lista 013; `missao_visao` (placement off) permanece válido |

## Relacionamentos

```text
block_content:diferenciais_quem_somos (UUID d5e6f7a8-b9c0-4d1e-8f2a-3b4c5d6e7f80)
  ├── field_text_simple
  ├── field_text_simple_long
  └── field_itens_lista[] ──► paragraph:diferencial_simples_p
                                 ├── field_image
                                 └── field_text_simple

block.block.default_diferenciaisquemsomos
  └── plugin block_content:<mesmo UUID> → content_full → /quem-somos

# Isolado (não alterar):
block_content:nossos_diferenciais → field_diferenciais_lista → diferencial_item_p
```

## Seed (conteúdo)

| Atributo | Valor |
|----------|-------|
| UUID fixo | `d5e6f7a8-b9c0-4d1e-8f2a-3b4c5d6e7f80` |
| Bundle | `diferenciais_quem_somos` |
| Status | publicado |
| Título | `Nossos Diferenciais` (placeholder editorial) |
| Descrição | texto institucional curto pt-BR (placeholder; editor substitui) |
| Itens | 8 paragraphs com rótulos placeholder (ex.: “Atendimento personalizado”, “Seleção qualificada”, …) |
| Ícones | assets em `modules/custom/custom_configs/assets/diferenciais-quem-somos/` → `public://diferenciais-quem-somos/` |

**Idempotência**: se UUID já existe → não duplica bloco; preenche lista/ícones **somente** se vazios/ausentes; **nunca** sobrescreve texto ou mídia editorial divergente.

## Displays

| Entidade | Form | View |
|----------|------|------|
| `diferenciais_quem_somos` | título, descrição, lista (widget paragraphs; default type `diferencial_simples_p`) | campos visíveis para Twig (`entity_reference_revisions_entity_view` na lista) |
| `diferencial_simples_p` | ícone + rótulo | ícone + rótulo |

## Regras de validação / fallback

| Estado | Resultado público |
|--------|-------------------|
| Sem título | omitir `h2` |
| Sem descrição | omitir parágrafo |
| Zero itens | só cabeçalho (se houver); sem erro |
| &lt; 8 itens | grid com colunas disponíveis |
| &gt; 8 itens | todos renderizam; sem truncar |
| Item sem ícone | só rótulo |
| Item sem rótulo | só ícone (se houver); sem texto vazio |

## Config YAML a versionar (checklist)

**Reutilizar (não recriar storage):**

- `field.storage.block_content.field_text_simple.yml`
- `field.storage.block_content.field_text_simple_long.yml`
- `field.storage.paragraph.field_image.yml`
- `field.storage.paragraph.field_text_simple.yml`
- `field.storage.block_content.field_itens_lista.yml` (**atualizar** cardinality)

**Criar/exportar:**

- `block_content.type.diferenciais_quem_somos.yml`
- `paragraphs.paragraphs_type.diferencial_simples_p.yml`
- 3× `field.field.block_content.diferenciais_quem_somos.*`
- 2× `field.field.paragraph.diferencial_simples_p.*`
- form/view displays (bloco + paragraph)
- `block.block.default_diferenciaisquemsomos.yml`
- diffs em `user.role.*.yml`

## Fora do modelo

- Campos/bundles da home `nossos_diferenciais` / `diferencial_item_p`
- Storage paralelo `field_text_simple_small`
- Storage novo de lista
- Layout Builder / Field Group neste bloco
