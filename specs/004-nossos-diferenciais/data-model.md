# Data Model: Bloco Nossos Diferenciais

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-11

## Entidades

### 1. `nossos_diferenciais` (block_content)

| Campo | Storage | Tipo | Obrigatório | Notas |
|-------|---------|------|-------------|-------|
| Título da seção | `field_text_simple` | string | não | Reuso; label no instance |
| Subtítulo | `field_text_simple_long` | string_long | não | Reuso |
| Imagem de destaque | `field_image` | image | não | 1 arquivo; placeholder Twig se vazio |
| Lista de diferenciais | `field_diferenciais_lista` | entity_reference_revisions | não | **Storage novo**; target `paragraph` / bundle `diferencial_item_p` |

- **Revision**: alinhar ao padrão dos outros block types do projeto (`cto` = `revision: false` ok).
- **View display**: fields emitidos para o Twig do bloco (lista como `entity_reference_revisions_entity_view`).

### 2. `diferencial_item_p` (paragraph)

| Campo | Storage | Tipo | Obrigatório | Notas |
|-------|---------|------|-------------|-------|
| Ícone | `field_image` | image | não* | Reuso storage paragraph; *recomendado no form |
| Título | `field_text_simple` | string | sim (form) | Reuso |
| Descrição | `field_text_simple_long` | string_long | não | Reuso |

## Relacionamentos

```text
block_content:nossos_diferenciais
  └── field_diferenciais_lista[] ──► paragraph:diferencial_item_p
                                        ├── field_image
                                        ├── field_text_simple
                                        └── field_text_simple_long
```

## Regras de validação / fallback

| Condição | Comportamento |
|----------|----------------|
| Sem título / subtítulo | Omitir elemento no Twig |
| Sem imagem | Placeholder Twig na coluna esquerda |
| Sem itens | Render header + coluna imagem; lista vazia |
| Item sem ícone | Texto do item permanece |
| N itens | Todos renderizam; cor do título = `(index % 3)` |

## Config YAML a versionar (checklist de deploy)

**Reutilizar (não recriar storage):**

- `field.storage.block_content.field_text_simple.yml`
- `field.storage.block_content.field_text_simple_long.yml`
- `field.storage.block_content.field_image.yml`
- `field.storage.paragraph.field_text_simple.yml`
- `field.storage.paragraph.field_text_simple_long.yml`
- `field.storage.paragraph.field_image.yml`

**Criar/exportar:**

- `block_content.type.nossos_diferenciais.yml`
- `paragraphs.paragraphs_type.diferencial_item_p.yml`
- `field.storage.block_content.field_diferenciais_lista.yml`
- 4× `field.field.block_content.nossos_diferenciais.*`
- 3× `field.field.paragraph.diferencial_item_p.*`
- form/view displays (bloco + paragraph)
- role permissions (diff em `user.role.*.yml`)
- (opcional) `block.block.default_nossosdiferenciais.yml` se seed UUID fixo

## Seed (update hook) — modelo lógico

Entidade `block_content` com UUID fixo documentado no update, por exemplo:

- Título: `Nossos Diferenciais`
- Subtítulo: texto institucional da spec
- 3 paragraphs: Encontramos / Gerenciamos / Desenvolvemos (+ descrições da spec)
- Imagem: vazia (placeholder)

Idempotência: se UUID já existe → no-op com mensagem.

## Fora do modelo

- Campo de cor por item
- Múltiplas imagens fatiadas
- Reuso do bundle `icone_titulo_descricao` no lugar de `diferencial_item_p`
