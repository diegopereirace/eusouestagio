# Data Model: Cards Laranja — Vagas em Para Estudantes

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-30

## Entidades

Esta feature **não cria** content types, field storages, paragraphs nem block types. Consome o modelo já existente e altera só a apresentação do display `page_1`.

### 1. Node `vagas` (existente — somente leitura nesta feature)

| Atributo no card | Campo | Notas |
|------------------|-------|-------|
| Título | `title` | truncar ~42 chars no Twig |
| Salário | `field_text_simple` | truncar ~28; omitir visual se vazio (nbsp como Home) |
| Regime | `field_regime_t` → taxonomy `regime` | badge; empty → placeholder invisível |
| Cursos / ícone | `field_cursos_t` → taxonomy `curso`.`field_icone_fa` | 1º termo; fallback `briefcase` |
| Empresa | `field_empresa_u` → user.`field_nome_fantasia` | **não** usar `field_text_simple_2` |
| Local | `field_cidade` + `field_estados` | meta `empresa • local` |
| URL canônica | `entity.node.canonical` | “Ver Mais” / “Inscreva-se” |

**Storages novos**: nenhum. **Instances novas**: nenhuma.

### 2. View `vagas` / display `page_1` (alvo)

| Aspecto | Estado atual | Alvo desta feature |
|---------|--------------|--------------------|
| Plugin | `page` | inalterado |
| Path | `para-estudantes` | inalterado |
| Row plugin | `fields` | inalterado |
| Campo visível | Custom Text `nothing` | inalterado (Twig reescrito) |
| `css_class` | `css-vagas-page container` | manter |
| Header | “Vagas Disponíveis” | **“Vagas de Destaque”** (sem “Ver todas”) |
| Footer | vazio | inalterado |
| Style `row_class` | herda default `col-md-4 col-12` | **`col-12 col-md-6 col-lg-4`** (override no display) |
| Pager | full, 12/página | inalterado |
| Filtros expostos | nid, cursos, estado, cidade, escolaridade, regime | inalterados |
| Empty | “Nenhum resultado encontrado” | inalterado |
| `use_ajax` | true | inalterado |

### 3. View `vagas` / display `block_1` (referência — não redesenhar)

| Aspecto | Valor (manter) |
|---------|----------------|
| Card Twig | `.item-vaga--destaque` |
| CSS wrapper | `.css-vagas-home` |
| Header | “Vagas de Destaque” + “Ver todas as vagas” → `/para-estudantes` |
| Footer mobile | mesmo link |
| `row_class` | `col-md-4 col-12` (default) |
| Botões no card | “Inscreva-se” + “Buscar mais vagas” |

### 4. Taxonomy `curso` (existente)

| Campo | Uso |
|-------|-----|
| `field_icone_fa` | classe Font Awesome do ícone do card (seed 003) |

---

## Relacionamentos

```text
View vagas
  ├── display block_1 (Home) ──► Twig block-1--nothing ──► .item-vaga--destaque
  │                              CSS: .css-vagas-home .item-vaga--destaque
  │                              header: título + “Ver todas” → /para-estudantes
  │
  └── display page_1 (/para-estudantes) ──► Twig page-1--nothing (reescrever)
       CSS: .css-vagas-page .item-vaga--destaque  (mesmo visual)
       header: “Vagas de Destaque” (sem link)
       row: col-12 col-md-6 col-lg-4
       filtros + pager preservados

node:vagas
  ├── field_empresa_u ──► user.field_nome_fantasia
  ├── field_cursos_t ──► taxonomy_term:curso.field_icone_fa
  ├── field_regime_t ──► taxonomy_term:regime
  ├── field_text_simple (salário)
  ├── field_cidade / field_estados
  └── title
```

---

## Validação / regras

| Regra | Origem |
|-------|--------|
| Card laranja paridade Home (≥90% elementos) | SC-001 / FR-001 |
| Grid 1 / 2 / 3 por breakpoint | FR-003 / SC-002 |
| Sem “Ver todas” em `page_1`; Home mantém | FR-004 / FR-011 / SC-005 |
| Sem “Buscar mais vagas” obrigatório em `page_1` | FR-007 |
| Empresa = nome fantasia | FR-008 |
| CTAs → canonical da vaga | FR-006 / SC-003 |
| Hook idempotente + cex | FR-010 / SC-004 / SC-006 |
| Edge: regime/empresa/salário/ícone/empty/truncamento | Edge Cases da spec |

---

## Estado / transições

Não há máquina de estados de conteúdo. Transição relevante é só de **config**:

```text
page_1 (card legado) --[11040 + cex + tema]--> page_1 (card laranja)
Reexecução 11040 --> no-op se já alinhado
```
