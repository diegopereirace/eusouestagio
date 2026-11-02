# Data Model: 050-vaga-beneficios-cta

## Resumo

| Mudança | Tipo |
|---------|------|
| Vocabulário `beneficios` | **Novo** |
| `field.storage.taxonomy_term.field_image` + instance no vocab | **Novo** |
| `field_vaga_beneficios_t` no bundle `vagas` | **Novo** (ER → termos `beneficios`, required, unlimited) |
| `field_vaga_beneficios` (paragraphs) | Inalterado no storage; **oculto** form/view |
| `field_empresa_u` | Inalterado no storage; **oculto** no form |
| `field_vaga_empresa` | Inalterado; permanece editável |
| Bloco CTA `default_ctav1vagas` | Visibility + comportamento Twig (sem novo storage) |

## Vocabulário `beneficios`

| Propriedade | Valor |
|-------------|--------|
| Machine name (`vid`) | `beneficios` |
| Label | Benefícios |
| Descrição | Catálogo de benefícios selecionáveis nas vagas |

### Campo no termo

| Campo | Storage | Tipo | Notas |
|-------|---------|------|-------|
| `name` | core | string | Rótulo público |
| `field_image` | `taxonomy_term.field_image` (**novo**) | image | WebP; `alt` recomendado = nome do benefício; directory sugerido `taxonomy/beneficios/[date:custom:Y]-[date:custom:m]` |

### Termos seed (UUID fixos — a fixar na implementação)

| # | Nome | Asset versionado | Destino público |
|---|------|------------------|-----------------|
| 1 | Plano de Carreira | `assets/beneficios/beneficios-01.webp` | `public://beneficios/beneficios-01.webp` |
| 2 | Vale Transporte | `assets/beneficios/beneficios-02.webp` | `public://beneficios/beneficios-02.webp` |
| 3 | Vale Refeição | `assets/beneficios/beneficios-03.webp` | `public://beneficios/beneficios-03.webp` |
| 4 | Horário Flexível | `assets/beneficios/beneficios-04.webp` | `public://beneficios/beneficios-04.webp` |
| 5 | Assistência Médica | `assets/beneficios/beneficios-05.webp` | `public://beneficios/beneficios-05.webp` |
| 6 | Auxílio Academia / Gympass | `assets/beneficios/beneficios-06.webp` | `public://beneficios/beneficios-06.webp` |
| 7 | Day Off no Aniversário | `assets/beneficios/beneficios-07.webp` | `public://beneficios/beneficios-07.webp` |

Idempotência: lookup por UUID (preferencial) ou nome normalizado; não sobrescrever nome/ícone se já preenchidos e divergentes; asset ausente → falha explícita naquele termo (não criar sem ícone).

## Bundle `vagas` — campos

| Campo | Antes | Depois (UI) | Storage |
|-------|-------|-------------|---------|
| `field_empresa_u` | Visível no form | **Hidden** no form | Mantém (painel) |
| `field_vaga_empresa` | Visível | Visível (canônico detalhe) | Mantém |
| `field_vaga_beneficios` | Paragraphs, required, visível | **Hidden** form + view | Mantém (rollback) |
| `field_vaga_beneficios_t` | — | Checkboxes, **required**, visível form; fonte do detalhe | **Novo** ER → `beneficios` |

### Validação

- `field_vaga_beneficios_t`: `required: true` → ao menos 1 termo ao salvar (FR-009 / SC-004).
- Widget: `options_buttons` (checkboxes).
- Handler: `default:taxonomy_term`, target bundles `beneficios` only.

## Migração de dados (estado)

```
Vaga com paragraphs (títulos)
  → match normalizado aos 7 nomes
  → field_vaga_beneficios_t preenchido (se vazio)
  → paragraphs permanecem no ERR legado (ocultos na UI)

Vaga sem match / sem paragraphs
  → field_vaga_beneficios_t vazio até correção editorial
  → novas publicações bloqueadas sem seleção (required)
```

Normalização sugerida: `mb_strtolower(trim())`, colapsar whitespace interno; comparar com os 7 nomes canônicos (incluindo a barra em “Auxílio Academia / Gympass”).

## CTA (sem entity nova)

| Item | Valor |
|------|--------|
| Block content UUID | `d4e5f6a7-b8c9-4012-c345-6789abcdef01` |
| Placement id / UUID | `default_ctav1vagas` / `e5f6a7b8-c9d0-4123-d456-789abcdef012` |
| Visibility | `entity_bundle:node` = `vagas` **e** `user_role` = `candidato` |
| Ação botão | POST `custom_panel.vagas_candidatar` via `.js-candidatar-vaga` |
| Pós-candidatura | Bloco visível; botão disabled “Candidatura Enviada” |

## Relacionamentos

```
node.vagas
  ├─ field_vaga_empresa → node.empresa (1)
  ├─ field_empresa_u → user (1, legado oculto)
  ├─ field_vaga_beneficios_t → taxonomy_term.beneficios (N)  [canônico UI]
  └─ field_vaga_beneficios → paragraph.beneficio_vaga_p (N, legado oculto)

taxonomy_term.beneficios
  └─ field_image → file (WebP)
```
