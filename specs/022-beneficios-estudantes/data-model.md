# Data Model: Benefícios Para Estudantes

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-29

## Entidades

### 1. `beneficios_estudantes` (block_content) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Título | `field_text_simple` | string | 1 | não | storage **reutilizado** `block_content` |
| Subtítulo | `field_text_simple_long` | string_long | 1 | não | storage **reutilizado** |
| Cards | `field_itens_lista` | entity_reference_revisions → paragraph | **ilimitada** (`-1` no storage) | não | storage **reutilizado**; handler → `card_icon_text_p` |

- Label admin: “Benefícios Estudantes”.
- Publicamente: `label_display: '0'` no placement.
- **Não** é o mesmo bundle de “Benefícios para Empresas” (`diferenciais_quem_somos`).

### 2. `card_icon_text_p` (paragraph) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Ícone | `field_image` | image | 1 | não | storage **reutilizado** paragraph |
| Título | `field_text_simple` | string | 1 | não* | storage **reutilizado**; *recomendado no form |
| Texto | `field_text_simple_long` | string_long | 1 | não | storage **reutilizado** |

\*Formulário pode marcar required editorialmente; publicamente, campos vazios são omitidos no Twig.

### 3. Bloco placement `default_beneficiosestudantes` — novo

| Aspecto | Valor |
|---------|--------|
| Config ID | `default_beneficiosestudantes` |
| Plugin | `block_content:a8b9c0d1-e2f3-4456-a789-0bcdef123456` |
| Tema | `default` |
| Região | `content_full` |
| Weight | `0` |
| Label display | oculto (`'0'`) |
| Visibilidade | `request_path` = `/para-estudantes` |
| `status` | `true` |

### 4. Storage compartilhado — sem alteração

| Storage | Estado | Ação nesta feature |
|---------|--------|---------------------|
| `field.storage.block_content.field_itens_lista` | `cardinality: -1` | **nenhuma** (já ilimitado desde 013) |
| `field.storage.block_content.field_text_simple` | existente | reutilizar |
| `field.storage.block_content.field_text_simple_long` | existente | reutilizar |
| `field.storage.paragraph.field_image` | existente | reutilizar |
| `field.storage.paragraph.field_text_simple` | existente | reutilizar |
| `field.storage.paragraph.field_text_simple_long` | existente | reutilizar |

**Storages novos**: nenhum.

---

## Relacionamentos

```text
block_content:beneficios_estudantes (UUID a8b9c0d1-e2f3-4456-a789-0bcdef123456)
  ├── field_text_simple          (título seção)
  ├── field_text_simple_long     (subtítulo)
  └── field_itens_lista[] ──► paragraph:card_icon_text_p
                                 ├── field_image
                                 ├── field_text_simple
                                 └── field_text_simple_long

block.block.default_beneficiosestudantes
  └── plugin block_content:<mesmo UUID> → content_full → /para-estudantes

# Isolados (não alterar):
block_content:diferenciais_quem_somos (QS d5e6… / PE f6a7…)
paragraph:icone_titulo_descricao | diferencial_item_p | diferencial_simples_p
View banners block_para_estudantes (região banner)
View vagas page_1 (/para-estudantes)
```

## Seed (conteúdo)

| Atributo | Valor |
|----------|-------|
| UUID fixo | `a8b9c0d1-e2f3-4456-a789-0bcdef123456` |
| Bundle | `beneficios_estudantes` |
| Status | publicado |
| Título | `Encontre oportunidades que combinam com você.` |
| Subtítulo | `Nossa plataforma utiliza inteligência para conectar você às vagas e empresas que fazem sentido para o seu perfil e momento.` |
| Cards | 4 paragraphs (ordem abaixo) |
| Ícones | `icon-1.png`…`icon-4.png` em `modules/custom/custom_configs/assets/beneficios-estudantes/` → `public://` via hook |

| # | Título do card | Texto seed |
|---|----------------|------------|
| 1 | Vagas alinhadas | Oportunidades filtradas pelo seu curso, interesses e objetivos profissionais. |
| 2 | Modelos de trabalho | Presencial, híbrido ou remoto — escolha o formato que encaixa na sua rotina. |
| 3 | Vários segmentos | Empresas de diferentes áreas e portes, ampliando suas possibilidades de carreira. |
| 4 | Filtros Inteligentes | Refine a busca por localização, escolaridade, regime e outros critérios relevantes. |

**Idempotência**: se UUID já existe → não duplica bloco; preenche lista/ícones **somente** se vazios/ausentes; **nunca** sobrescreve texto ou mídia editorial divergente. Ausência de PNG: criar cards sem ícone (omit no Twig); não falhar o update.

## Displays

| Entidade | Form | View |
|----------|------|------|
| `beneficios_estudantes` | título, subtítulo, lista (widget paragraphs; default type `card_icon_text_p`) | campos visíveis para Twig (`entity_reference_revisions_entity_view` na lista) |
| `card_icon_text_p` | ícone + título + texto | ícone + título + texto |

## Hook `custom_configs_update_11035`

Responsabilidades (idempotentes):

1. Ensure paragraph type `card_icon_text_p` + field instances + form/view displays (defensivo).
2. Ensure block type `beneficios_estudantes` + field instances (handler lista → `card_icon_text_p`) + form/view displays.
3. Seed `BlockContent` UUID `a8b9…` + 4 paragraphs + cópia assets → `public://` se campos vazios.
4. Ensure placement `default_beneficiosestudantes` (região/pages/status/weight).
5. Mensagem Drush created/skipped.

**Não faz**: criar storages; alterar PE/QS/home; alterar hero `021` / View `vagas`; sobrescrever editorial divergente.

## Regras de validação / fallback

| Estado | Resultado público |
|--------|-------------------|
| Sem título | omitir `h2` |
| Sem subtítulo | omitir parágrafo |
| Zero cards | só cabeçalho (se houver); sem erro |
| &lt; 4 cards | grid com colunas disponíveis |
| &gt; 4 cards | todos renderizam; sem truncar |
| Card sem ícone | título + texto legíveis |
| Card sem título | ícone + texto (se houver); sem heading vazio |
| Card sem texto | ícone + título; sem parágrafo vazio |
| Reexecução hook | no-op seguro |

## Config YAML a versionar (checklist)

**Reutilizar (não recriar storage):**

- `field.storage.block_content.field_text_simple.yml`
- `field.storage.block_content.field_text_simple_long.yml`
- `field.storage.block_content.field_itens_lista.yml` (inalterado)
- `field.storage.paragraph.field_image.yml`
- `field.storage.paragraph.field_text_simple.yml`
- `field.storage.paragraph.field_text_simple_long.yml`

**Criar/exportar:**

- `block_content.type.beneficios_estudantes.yml`
- `paragraphs.paragraphs_type.card_icon_text_p.yml`
- 3× `field.field.block_content.beneficios_estudantes.*`
- 3× `field.field.paragraph.card_icon_text_p.*`
- form/view displays (bloco + paragraph)
- `block.block.default_beneficiosestudantes.yml`
- diffs em `user.role.*.yml`

## Fora do modelo

- Bundles `diferenciais_quem_somos` / `nossos_diferenciais` / paragraphs legados
- Storage paralelo `field_text_simple_small` ou `field_cards_lista`
- Storage novo de lista
- Alterações em View `banners` / `vagas`
