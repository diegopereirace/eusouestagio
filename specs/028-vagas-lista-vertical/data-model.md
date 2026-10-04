# Data Model: Listagem Vertical de Vagas (`/vagas`)

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-04

## Entidades

### 1. Field `field_vaga_destaque` (novo)

| Aspecto | Valor |
|---------|--------|
| Entity type | `node` |
| Field name | `field_vaga_destaque` |
| Type | `boolean` |
| Cardinality | 1 |
| Default | `0` / `False` (desmarcado) |
| Label (instance) | Destaque |
| Bundle | `vagas` |
| Form widget | `boolean_checkbox` (on/off) no display `default` |
| View display node | omitido ou hidden (card lê entity na View) |
| Translatable | conforme padrão do projeto para booleans novos (preferir `FALSE` se os peers user forem não-traduzíveis; alinhar no `cex`) |

**Regras:**

- Vagas existentes após deploy → valor default (não destaque) até marcação editorial.
- Reexecução do ensure → no-op se storage/instance/component já existem.
- Valores possíveis: `1` (destaque) / `0` (padrão).

### 2. Node `vagas` — campos reutilizados no card

| Campo | Tipo | Uso no card |
|-------|------|-------------|
| `title` | string | Título |
| `field_empresa_u` | ER → `user` | Nome (`field_nome_fantasia`) + logo (`user_picture`) |
| `field_cidade` | string | Local |
| `field_estados` | list_string | UF no local |
| `field_regime_t` | ER → `regime` | Pill de regime |
| `field_text_simple` | string | Bolsa auxílio / salário |
| `field_horarios` | list_string | Carga horária |
| `field_text_simple_multiple_2` | string multi | Tags primárias (Benefícios) |
| `field_cursos_t` | ER multi → `curso` | Fallback de tags |
| `created` | timestamp | “Publicada há …” |
| `field_vaga_destaque` | boolean | Badge, borda, CTA, sort |

**Não criar** nesta feature: taxonomia de tecnologias, field de logo na empresa, campos de bookmark/favorito.

### 3. View `vagas` / display `page_1` — alterações

| Aspecto | Antes | Depois |
|---------|-------|--------|
| Path | `vagas` | inalterado |
| `items_per_page` | 12 | **5** |
| `use_ajax` | true | **true** (garantir) |
| Pager | `full` | `full` (AJAX) |
| Sorts | herdado `created` DESC | **`field_vaga_destaque` DESC**, `created` DESC |
| `row_class` | `col-12 col-md-6 col-lg-4` | `col-12` (lista) |
| `css_class` | `css-vagas-page container` | manter (lista estilizada sob `.css-vagas-page`) |
| Row plugin | `fields` + `nothing` | inalterado |
| Filtros expostos | `title`, `nid`, `cursos`, `estado`, `cidade`, `escolaridade`, `regime` | **não remover** (Hero 027 depende de `title`/`cidade`/`cursos`) |

### 4. User (empresa) — reuso

| Campo | Uso |
|-------|-----|
| `field_nome_fantasia` | Nome da empresa no card |
| `user_picture` | Logo ~64×64; omitir se vazio |

### 5. Placements fora de escopo (limpeza)

Não há entidades novas. Alvos **condicionais** se existirem:

- Block configs cujo label/admin_label ou conteúdo referencie “Recomendado para você” / “Melhore seu currículo” **e** visibility `/vagas`.

Ação: `status: false` ou delete idempotente. Inventário atual: **zero** matches.

## Relacionamentos

```text
Node vagas
  ├── field_vaga_destaque (boolean)          # NOVO — sort + UI
  ├── field_empresa_u → User
  │     ├── field_nome_fantasia
  │     └── user_picture                     # logo
  ├── field_regime_t → Term (regime)
  ├── field_cursos_t → Term[] (curso)        # tags fallback
  ├── field_text_simple_multiple_2[]         # tags primárias
  ├── field_text_simple / field_horarios
  └── created

View vagas.page_1
  ├── path /vagas
  ├── 5/page + AJAX + full pager
  ├── sorts: destaque DESC, created DESC
  └── row → Custom Text Twig (.item-vaga--lista)

# Acima (inalterado):
block.block.default_custom_banners_vagas_hero_search
  → highlighted / -50 / /vagas

# Irmãos (NÃO alterar):
View displays block_1 (home), block_2 (similares), block_3 (PE)
```

## Validação / estados

| Estado | Resultado |
|--------|-----------|
| Destaque = 1 | Badge + borda verde; CTA “Candidatura Rápida”; prioridade no sort |
| Destaque = 0 / NULL | Sem badge/borda; CTA “Ver Detalhes”; após destaques no sort |
| Sem logo | Card sem imagem (ou placeholder); layout ok |
| Sem bolsa / horário / tags | Seções omitidas; card utilizável |
| Sem empresa / cidade | Meta parcial |
| 0 resultados | Empty da View; página intacta |
| ≥6 vagas | Página 1 mostra ≤5; pager AJAX carrega mais |
| Hook 2ª vez | Sem duplicar storage; View não corrompe |
| Blocos exclusos ausentes | Cleanup no-op |

## Config YAML a versionar (checklist)

**Criar/exportar:**

- `field.storage.node.field_vaga_destaque.yml`
- `field.field.node.vagas.field_vaga_destaque.yml`
- `core.entity_form_display.node.vagas.default.yml` (componente)
- `views.view.vagas.yml` (`page_1`: pager, sorts, row_class, ajax)

**Não alterar (regressão proibida):**

- Displays `block_1` / `block_2` / `block_3` (exceto ruído involuntário de `cex` — revisar diff)
- `block.block.default_custom_banners_vagas_hero_search.yml`
- `block.block.default_custom_banners_hero_search.yml`
- Twigs `views-view-field--vagas--block-{1,3}--nothing.html.twig`

## Fora do modelo

- View modes novos
- Composer / `views_infinite_scroll`
- Sidebar de filtros Figma
- Blocos “Recomendado…” / “Melhore…” (implementação)
- Contador / “Ordenar por” / favoritos
