# Data Model: Bloco CTA v1 — Quem Somos

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-22

## Entidades

### 1. `cta_v1` (block_content) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Título | `field_text_simple` | string (255) | 1 | não* | storage **reutilizado**; ex.: `Seu próximo estágio começa aqui.` |
| Subtítulo | `field_text_simple_long` | string_long | 1 | não* | storage **reutilizado** |
| Botão primário | `field_link` | link | 1 | não* | storage **reutilizado**; URI + title |
| Botão secundário | `field_link_2` | link | 1 | não* | storage **novo**; URI + title |

- Label admin: “CTA v1”.
- Machine name: `cta_v1`.
- \*Formulário pode marcar required editorialmente; publicamente, campos vazios são omitidos no Twig.

### 2. Storage novo `field_link_2`

| Aspecto | Valor |
|---------|--------|
| Config | `field.storage.block_content.field_link_2` |
| Entity type | `block_content` |
| Type | `link` |
| Cardinality | `1` |
| Motivo | segundo botão nomeado; `field_link` já é o primário (card 1) |

### 3. Placement (config Block)

| Aspecto | Valor |
|---------|--------|
| Config ID | `default_ctav1quemsomos` |
| UUID placement | `f7a8b9c0-d1e2-4f3a-a0b5-192a3b4c5d6e` |
| Theme | `default` |
| Region | `content_full` |
| Weight | `12` (após `default_impactnumbersquemsomos` = `11`) |
| Plugin | `block_content:{UUID do conteúdo}` |
| Visibility | `request_path` = `/quem-somos` (negate false) |
| Label display | `0` (oculto) |

### 4. Instância seed (`block_content`)

| Aspecto | Valor |
|---------|--------|
| UUID | `e6f7a8b9-c0d1-4e2f-9a3b-4c5d6e7f8091` |
| Bundle | `cta_v1` |
| Revisão | alinhada aos demais block types do projeto |

## Relacionamentos

```text
block_content:cta_v1
  ├── field_text_simple      (título)
  ├── field_text_simple_long (subtítulo)
  ├── field_link             (botão primário)
  └── field_link_2           (botão secundário)

# Placement:
block.block.default_ctav1quemsomos
  → plugin block_content:e6f7a8b9-c0d1-4e2f-9a3b-4c5d6e7f8091
  → content_full / weight 12 / /quem-somos

# Vizinhos (não alterar):
default_diferenciaisquemsomos   weight 10
default_impactnumbersquemsomos  weight 11
```

## Seed (conteúdo)

| Campo | Valor |
|-------|--------|
| Título | `Seu próximo estágio começa aqui.` |
| Subtítulo | `Junte-se a milhares de estudantes e encontre a oportunidade que vai mudar sua carreira.` |
| Primário | title `Buscar vagas` → uri `internal:/vagas` (ou equivalente limpo `/vagas`) |
| Secundário | title `Cadastrar gratuitamente` → uri `internal:/cadastro/candidato` |

**Idempotência**: se a instância com UUID fixo já existir → não duplicar; popular **somente** campos vazios; **não** sobrescrever editorial divergente do seed.

## Displays

| Entidade | Form | View |
|----------|------|------|
| `cta_v1` | título, subtítulo, `field_link`, `field_link_2` (widget link padrão) | quatro campos visíveis para Twig |

## Regras de validação / fallback

| Estado | Resultado público |
|--------|-------------------|
| Título vazio | omitir título |
| Subtítulo vazio | omitir subtítulo |
| Primário sem URI | omitir botão primário |
| Secundário sem URI | omitir botão secundário |
| Ambos botões ausentes | omitir wrapper de ações |
| Todos os campos vazios | **não** renderizar o card (bloco sem faixa vazia) |
| Página ≠ `/quem-somos` | bloco não aparece (visibility) |

## Config YAML a versionar (checklist)

**Reutilizar (não recriar storage):**

- `field.storage.block_content.field_text_simple.yml`
- `field.storage.block_content.field_text_simple_long.yml`
- `field.storage.block_content.field_link.yml`

**Criar/exportar:**

- `block_content.type.cta_v1.yml`
- `field.storage.block_content.field_link_2.yml`
- `field.field.block_content.cta_v1.field_text_simple.yml`
- `field.field.block_content.cta_v1.field_text_simple_long.yml`
- `field.field.block_content.cta_v1.field_link.yml`
- `field.field.block_content.cta_v1.field_link_2.yml`
- form/view displays `block_content.cta_v1`
- `block.block.default_ctav1quemsomos.yml`
- diffs de `user.role.*.yml` (permissões do bundle), se necessário

## Fora do modelo

- Storages `field_text_simple_small` / `field_link_secundario`
- Paragraphs / Layout Builder
- Alteração de bundles/fields/placements das features 009–014
- Alteração do bloco `cto` ou de seus storages
