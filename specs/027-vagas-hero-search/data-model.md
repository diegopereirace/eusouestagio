# Data Model: Hero Search — Página de Vagas

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-04

## Entidades

### 1. Block Plugin `custom_banners_vagas_hero_search` (novo — sem entity content)

Plugin puro (como o hero da home). **Não** cria `block_content`, field storage ou paragraph.

| Aspecto | Valor |
|---------|--------|
| Class | `Drupal\custom_banners\Plugin\Block\VagasHeroSearchBlock` |
| Plugin ID | `custom_banners_vagas_hero_search` |
| Theme hook | `custom_banners_vagas_hero_search` |
| Admin label | Hero: busca de vagas (/vagas) |
| Textos | fixos no Twig (não no banco) |

**Variáveis Twig esperadas:**

| Variável | Tipo | Origem |
|----------|------|--------|
| `action` | string URL | `Url::fromRoute('view.vagas.page_1')` |
| `values` | map `title`/`cidade`/`cursos` | query string atual |
| `pills` | list `{label, url}` | resolução taxonomia `curso` |

### 2. View `vagas` / display `page_1` — alteração estrutural

| Aspecto | Valor |
|---------|--------|
| Path | `vagas` (inalterado) |
| Filtro novo | `title` em `node_field_data.title` |
| Plugin | `string` |
| Operator | `contains` |
| Identifier | `title` |
| Exposed | true, required false |
| Placeholder sugerido | Cargo ou palavra-chave |

**Filtros expostos existentes (não remover):** `nid`, `cursos`, `estado`, `cidade`, `escolaridade`, `regime`.

### 3. Vocabulário `curso` (reuso)

Termos publicados alimentam pills e o campo “Seu curso”. Sem seed de termos nesta feature.

### 4. Placement (config Block) — novo

| Aspecto | Valor |
|---------|--------|
| Config ID | `default_custom_banners_vagas_hero_search` |
| UUID | `a1b2c3d4-e5f6-4789-a012-bcdef0123456` |
| Theme | `default` |
| Region | `highlighted` |
| Weight | `-50` |
| Plugin | `custom_banners_vagas_hero_search` |
| Visibility | `request_path` = `/vagas` (negate false) |
| Label display | `0` |
| Status | `true` |

## Relacionamentos

```text
VagasHeroSearchBlock (plugin)
  ├── form GET → /vagas?title=&cidade=&cursos=
  ├── pills → /vagas?cursos={nome_termo}
  └── library default/vagas_hero_search

View vagas page_1
  ├── filters: title (novo), cidade, cursos, …
  └── path: /vagas

# Placement:
block.block.default_custom_banners_vagas_hero_search
  → plugin custom_banners_vagas_hero_search
  → highlighted / weight -50 / /vagas

# Irmão (não alterar):
block.block.default_custom_banners_hero_search
  → custom_banners_hero_search / highlighted / -50 / <front>

# Legado (permanece desativado):
block.block.default_formularioexpostovagaspage_1
  → status: false
```

## Contrato GET (parâmetros)

| Param | Campo UI | Validação |
|-------|----------|-----------|
| `title` | Cargo ou palavra-chave | string livre; vazio = sem filtro |
| `cidade` | Cidade ou Remoto | string livre (contains em `field_cidade`) |
| `cursos` | Seu curso / pills | nome do termo; widget textfield |

Combinação: AND (grupos de filtro da View). Campos vazios omitidos na query ou enviados vazios — View trata como sem restrição.

## Pills (rótulos canônicos)

| Label | Vocabulário | Param |
|-------|-------------|-------|
| Tecnologia | `curso` | `cursos` |
| Marketing | `curso` | `cursos` |
| Administração | `curso` | `cursos` |
| Engenharia | `curso` | `cursos` |
| Saúde | `curso` | `cursos` |
| Design | `curso` | `cursos` |
| Direito | `curso` | `cursos` |

**Idempotência de resolução**: termo ausente → pill omitida (não quebra página).

## Apresentação (não é entidade Drupal)

| Aspecto | Valor |
|---------|--------|
| Twig | `modules/custom/custom_banners/templates/block--vagas-hero-search.html.twig` |
| Classe escopo | `.vagas-hero-search` |
| Library | `default/vagas_hero_search` → `vagas-hero-search.css` |
| Library home | **não** attach `default/hero_search` neste bloco |

## Regras de validação / fallback

| Estado | Resultado público |
|--------|-------------------|
| Todos os campos vazios no submit | `/vagas` sem filtros obrigatórios; listagem padrão |
| Cidade/curso inexistente | listagem vazia / empty da View; sem erro fatal |
| Pill sem termo | pill omitida |
| Query malformada | View ignora/valida; página utilizável |
| Placement ausente | `/vagas` só com a View |
| Rota ≠ `/vagas` | bloco não aparece |

## Config YAML a versionar (checklist)

**Alterar/exportar:**

- `views.view.vagas.yml` (filtro `title` em `page_1`)
- `block.block.default_custom_banners_vagas_hero_search.yml` (criar)

**Não alterar (regressão proibida):**

- `block.block.default_custom_banners_hero_search.yml`
- Displays `block_1` / `block_2` / `block_3` da View (exceto se `cex` tocá-los sem intenção — revisar diff)
- Placements PE / home / formulário exposto (manter desativado)

## Fora do modelo

- Novos content types / field storages / paragraphs
- Seed de termos `curso`
- Entity `block_content` para o hero
- Alteração estrutural do hero da home
