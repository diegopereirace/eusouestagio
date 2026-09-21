# Data Model: Números / Estatísticas Quem Somos

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-21

## Entidades

### 1. `numero_destaque_p` (paragraph) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Destaque | `field_text_simple` | string (255) | 1 | não* | storage **reutilizado** paragraph; ex.: `20k+` |
| Subtexto | `field_text_simple_long` | string_long | 1 | não* | storage **reutilizado**; ex.: `ESTUDANTES ATIVOS` |

- Label admin: “Número Destaque”.
- Machine name: `numero_destaque_p` (sufixo `_p` obrigatório).
- \*Formulário pode marcar required editorialmente; publicamente, campos vazios são omitidos no Twig.

### 2. `quem_somos` (node) — extensão

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Números | `field_numeros_lista` | entity_reference_revisions → paragraph | **4** | não | storage **novo**; handler → só `numero_destaque_p` |

Campos existentes (Sobre nós / Missão-Visão) **permanecem intocados**:
`field_titulo`, `field_text_long_formatted`, `field_imagem`, `field_imagem_desktop`, `field_text_simple_long`, `field_text_simple_long_2`.

### 3. Storage novo `field_numeros_lista`

| Aspecto | Valor |
|---------|--------|
| Config | `field.storage.node.field_numeros_lista` |
| Entity type | `node` |
| Type | `entity_reference_revisions` |
| Target | `paragraph` |
| Cardinality | `4` |
| Motivo | `field_itens_p` tem card 3 e instance em `para_empresas` — não reutilizar |

## Relacionamentos

```text
node:quem_somos
  ├── (existentes) Sobre nós / Missão-Visão …
  └── field_numeros_lista[0..4] ──► paragraph:numero_destaque_p
                                       ├── field_text_simple      (destaque)
                                       └── field_text_simple_long (subtexto)

# Isolado (não alterar):
node:para_empresas → field_itens_p (card 3)
block_content:diferenciais_quem_somos → field_itens_lista → diferencial_simples_p
```

## Seed (conteúdo)

| # | Destaque (`field_text_simple`) | Subtexto (`field_text_simple_long`) |
|---|-------------------------------|-------------------------------------|
| 1 | `20k+` | `ESTUDANTES ATIVOS` |
| 2 | `1.2k+` | `EMPRESAS PARCEIRAS` |
| 3 | `8k+` | `ESTÁGIOS INICIADOS` |
| 4 | `95%` | `SATISFAÇÃO GLOBAL` |

**Idempotência**: se `field_numeros_lista` no Node `quem_somos` já tiver ≥1 item → não duplicar e não sobrescrever; popular **somente** se lista vazia/ausente.

## Displays

| Entidade | Form | View |
|----------|------|------|
| `numero_destaque_p` | destaque + subtexto | ambos visíveis para Twig |
| `quem_somos` | `field_numeros_lista` (widget paragraphs; default type `numero_destaque_p`; opcional Field Group “Números / Impact in Numbers”) | lista com `entity_reference_revisions_entity_view` |

## Regras de validação / fallback

| Estado | Resultado público |
|--------|-------------------|
| Lista vazia (0 itens) | omitir seção `.section-impact-numbers` inteira |
| 1–3 itens | renderizar apenas existentes; grid `.col-6.col-md-3` |
| 4 itens | grid completo (2×2 mobile / 1×4 desktop) |
| Destaque vazio, subtexto ok | só subtexto |
| Subtexto vazio, destaque ok | só destaque |
| Ambos vazios | não renderizar o item |
| Tentativa de 5º item no form | UI impede (cardinality 4) |

## Config YAML a versionar (checklist)

**Reutilizar (não recriar storage):**

- `field.storage.paragraph.field_text_simple.yml`
- `field.storage.paragraph.field_text_simple_long.yml`

**Criar/exportar:**

- `paragraphs.paragraphs_type.numero_destaque_p.yml`
- `field.storage.node.field_numeros_lista.yml`
- `field.field.paragraph.numero_destaque_p.field_text_simple.yml`
- `field.field.paragraph.numero_destaque_p.field_text_simple_long.yml`
- `field.field.node.quem_somos.field_numeros_lista.yml`
- form/view displays (paragraph + diffs no node `quem_somos`)

## Fora do modelo

- `field_itens_p` / `field_itens_lista`
- Storages paralelos `field_text_simple_small` / `field_text_simple_small_2`
- Título/subtítulo editoriais da seção (além dos 4 números)
- Layout Builder / Field Group obrigatório (group é opcional de UX)
- Alteração de bundles/fields das features 009–013
