# Data Model: Bloco de Depoimentos — Para Empresas

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-26

## Entidades

### 1. Content type `depoimento`

| Aspecto | Valor |
|---------|--------|
| Machine name | `depoimento` |
| Label | Depoimento |
| Description | Depoimentos de clientes/empresas para prova social B2B |
| Preview mode | `teaser` (recomendado) |
| New revision | conforme padrão do projeto (ex.: `page`) |
| Menu / pathauto | sem path público obrigatório; listagem só via View |

#### Campos

| Campo | Storage | Tipo storage | Label instance | Obrigatório | Notas |
|-------|---------|--------------|----------------|-------------|-------|
| Nome do autor | `title` | nativo | Título | sim | Valor = nome (ex.: “Mariana Silva”) |
| Cargo / empresa | `field_text_simple` | `string` (255) | Cargo / Empresa | não* | Ex.: “Head de Talentos - TechCorp” |
| Texto do depoimento | `field_text_simple_long` | `string_long` | Texto do depoimento | não* | Corpo do card |
| Foto do autor | `field_imagem` | `image` | Foto do autor | não | Alt recomendado = nome; directory `depoimentos/[date:custom:Y]-[date:custom:m]` |

\*Formulário editorial deve incentivar preenchimento; runtime omite elementos vazios (edge cases da spec).

**Storages novos**: nenhum.

#### Displays

- **Form `default`**: title, `field_imagem`, `field_text_simple`, `field_text_simple_long` (+ meta Drupal padrão).
- **View `default`**: campos completos (admin/preview).
- **View `teaser`**: title + cargo + texto + imagem (`thumbnail`) — consumido pela View se row style = Content/Teaser.

---

### 2. View `depoimentos_carousel`

| Aspecto | Valor |
|---------|--------|
| Machine name | `depoimentos_carousel` |
| Display | Block `block_depoimentos_empresas` |
| Base | Content |
| Filtros | `status = 1`, `type = depoimento` |
| Sort | `created` DESC |
| Pager | none |
| Empty | sem área (Twig omite seção se zero rows) |
| css_class | `depoimentos-empresas-view` (ou equivalente) |

---

### 3. Nodes seed (≥4)

UUIDs fixos (não reutilizar UUIDs de outras features):

| # | UUID | Título | `field_text_simple` | Asset avatar |
|---|------|--------|---------------------|--------------|
| 1 | `a0b1c2d3-e4f5-4601-a001-depoimentos0001` | Mariana Silva | Head de Talentos - TechCorp | `avatar-1.png` |
| 2 | `a0b1c2d3-e4f5-4601-a002-depoimentos0002` | Ricardo Gomes | CEO - Inovatech | `avatar-2.png` |
| 3 | `a0b1c2d3-e4f5-4601-a003-depoimentos0003` | Ana Paula Costa | Gerente de RH - Horizonte Soft | `avatar-3.png` |
| 4 | `a0b1c2d3-e4f5-4601-a004-depoimentos0004` | Lucas Ferreira | Diretor de Operações - Nexo Digital | `avatar-4.png` |

- `status`: publicado.
- `field_text_simple_long`: copy curta pt-BR (1–3 frases) alinhada ao tom Figma / B2B.
- Assets: `modules/custom/custom_configs/assets/depoimentos/avatar-{1..4}.png` → `public://depoimentos/…` via hook.
- Idempotência: load by UUID; se existe e editorial divergiu, **não** sobrescrever; se falta imagem e asset existe, anexar só se campo vazio.

---

### 4. Placement do bloco da View

| Aspecto | Valor |
|---------|--------|
| Config ID | `default_views_block__depoimentos_carousel_block_depoimentos_empresas` |
| Plugin | `views_block:depoimentos_carousel-block_depoimentos_empresas` |
| Tema | `default` |
| Região | `content_full` |
| Weight | **4** |
| Pages | `/para-empresas` |
| `label_display` | `'0'` |

---

### 5. Ajuste de weight — CTA PE

| Config ID | Campo | Antes | Depois |
|-----------|-------|-------|--------|
| `default_ctav1paraempresas` | `weight` | 4 | **5** |

Demais placements PE (0–3) **inalterados**.

---

### 6. Hook `custom_configs_update_11032`

Responsabilidades (idempotentes):

1. Ensure defensivo: bundle `depoimento` + field instances + displays (se ainda ausentes após `cim`).
2. Seed dos 4 nodes + cópia de assets.
3. Ensure placement do bloco da View (UUID/config id estável) e pages/weight.
4. Ensure `default_ctav1paraempresas.weight = 5`.
5. Mensagem de retorno Drush com contagem criada/skipped.

**Não faz**: sobrescrever copy/imagem editorial; criar storages novos; alterar placements de home/Quem Somos.

---

## Relacionamentos

```text
node.depoimento ──(listado por)──> View depoimentos_carousel
                                     └── Block placement ──> região content_full (/para-empresas, w4)
CTA default_ctav1paraempresas ──> content_full w5 (âncora inferior)
Benefícios default_beneficiosparaempresas ──> content_full w3 (âncora superior)
```

## Validation rules (runtime)

- Zero publicados → seção omitida.
- Sem `field_imagem` → card sem avatar.
- Texto/cargo vazios → omitir nós DOM correspondentes.
- Reexecução hook → no-op seguro.
