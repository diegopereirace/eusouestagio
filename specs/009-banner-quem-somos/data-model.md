# Data Model: Banner da página Quem Somos

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-14

## Entidades

### `banners` (`node`) — existente; schema de fields inalterado

| Campo | Storage | Uso nesta feature |
|-------|---------|-------------------|
| Título | `title` | interno/admin; **não** exibido no banner público (copy no Twig) |
| Local | `field_local_exibicao` | valor **`quem_somos`** (novo allowed value) |
| Imagem desktop | `field_imagem_desktop` | coluna direita; PNG com alpha preferencial |
| Imagem mobile | `field_imagem_mobile` | opcional; fallback = desktop |
| Peso | `field_peso` | ordenação ASC (menor primeiro) |
| Status | `status` | só publicados entram na View |

**Allowed values de `field_local_exibicao` (após a feature):**

| Valor | Rótulo |
|-------|--------|
| `home` | Home |
| `internas` | Internas |
| `quem_somos` | Quem Somos |

**Seed (conteúdo):**

| Atributo | Valor |
|----------|-------|
| UUID fixo | `e9f0a1b2-c3d4-4e5f-8690-1234567890ab` |
| Bundle | `banners` |
| `field_local_exibicao` | `quem_somos` |
| `field_peso` | `0` |
| `status` | publicado |
| Imagem | asset `modules/custom/custom_configs/assets/banner-quem-somos/quem-somos-img.png` → `public://banners/quem-somos/quem-somos-img.png` |
| Alt sugerido | `Mulher com prancheta — Eu Sou Estágio` |

Idempotência: se já existir node com esse UUID **ou** qualquer `banners` com `local=quem_somos`, o hook **não** cria duplicata nem sobrescreve mídia editorial.

### View `banners` / display `block_quem_somos` — novo

| Aspecto | Regra |
|---------|--------|
| Plugin | `views_block:banners-block_quem_somos` |
| Filtros | `status=1`, `type=banners`, `field_local_exibicao=quem_somos` |
| Sort | herda default: `field_peso ASC`, `created DESC` |
| Itens | efetivamente 1 (sem carrossel) |
| Empty | sem área vazia → zero markup público |

### Bloco `default_views_block__banners_block_quem_somos` — novo

| Aspecto | Valor |
|---------|--------|
| Plugin | `views_block:banners-block_quem_somos` |
| Tema | `default` |
| Região | `banner` |
| Weight | `0` |
| Visibilidade | `request_path` = `/quem-somos` |

### Bloco legado `default_views_block__banners_block_1` — ajuste

| Antes | Depois |
|-------|--------|
| `/para-estudantes`, `/para-empresas`, `/contato`, `/quem-somos` | `/para-estudantes`, `/para-empresas`, `/contato` |

### Página `/quem-somos` (fora do modelo desta feature)

Node/tipo institucional `quem_somos` e seção “Sobre nós” **não** mudam; apenas o bloco de topo na região `banner`.

## Relacionamentos

```text
field.storage.node.field_local_exibicao
└── allowed: home | internas | quem_somos

node:banners (UUID seed ou editorial)
├── field_local_exibicao = quem_somos
├── field_imagem_desktop → File (PNG)
└── field_imagem_mobile  → File (opcional)

View banners: block_quem_somos
└── filtra local=quem_somos

block.block.default_views_block__banners_block_quem_somos
└── plugin views_block:banners-block_quem_somos → região banner → /quem-somos

Twig (fonte de verdade da copy/CTAs)
└── tag, título, parágrafo, botões hardcoded
```

## Copy fixa (não é entidade CMS)

| Elemento | Conteúdo |
|----------|----------|
| Tag | `QUEM SOMOS` |
| Título parte 1 | `Mais que conectar` (`#0F172A`) |
| Título parte 2 | `desenvolvemos futuros.` (`#FD7B1A`) |
| Parágrafo | `Somos especialistas em unir empresas e estudantes de forma estratégica, promovendo experiências de estágio que geram aprendizado, crescimento e resultados para todos.` |
| CTA primário | `Conheça nossas vagas` → `/para-estudantes` |
| CTA secundário | `Cadastre-se` → `/cadastro/candidato` |

## Regras de validação / fallback

| Estado | Resultado público |
|--------|-------------------|
| Zero banners publicados `quem_somos` | omitir wrapper inteiro |
| Sem `field_imagem_mobile` | usar desktop |
| Múltiplos publicados | 1º por sort canônico; sem carrossel |
| PNG com alpha | preservar transparência |
| Reexecução `updb` | no-op (sem duplicar node/display) |
| Editor trocou imagem do seed | hook não sobrescreve |

## Configuração a versionar (`drush cex`)

| Arquivo | Ação |
|---------|------|
| `field.storage.node.field_local_exibicao.yml` | + `quem_somos` |
| `views.view.banners.yml` | + display `block_quem_somos` |
| `block.block.default_views_block__banners_block_quem_somos.yml` | criar |
| `block.block.default_views_block__banners_block_1.yml` | remover `/quem-somos` |

## Fora do modelo

- Novos field storages / fields de texto no tipo `banners`
- Alteração do content type `quem_somos` (página)
- Redesign da seção “Sobre nós”
- Mudanças em `block_home` / `block_2` / `block_3` (exceto convivência path do `block_1`)
