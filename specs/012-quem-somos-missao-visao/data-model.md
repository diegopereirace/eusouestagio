# Data Model: Quem Somos — Seção Missão e Visão (no Node)

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-20

## Entidades

### Node `quem_somos` — atualizado

Mantém **Sobre nós** (`group_primeiro_bloco`):

| Campo | Storage | Tipo | Cardinalidade | Notas |
|-------|---------|------|---------------|-------|
| Título | `field_titulo` | string | 1 | inalterado |
| Corpo | `field_text_long_formatted` | text_long | 1 | inalterado |
| Imagem | `field_imagem` | image | 1 | exclusivo Sobre nós |

Nova **Seção Missão e Visão** (`group_missao_visao`):

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Imagem de fundo | `field_imagem_desktop` | image | 1 | não | storage **reutilizado** (`banners`) |
| Texto Missão | `field_text_simple_long` | string_long | 1 | não | storage **reutilizado** (`para_empresas`) |
| Texto Visão | `field_text_simple_long_2` | string_long | 1 | não | storage **reutilizado** (`para_empresas`) |

Títulos públicos “Nossa Missão” / “Nossa Visão” **não** são fields — fixos no Twig.

### Field Group `group_missao_visao` — novo (form display)

| Aspecto | Valor |
|---------|--------|
| Label | Seção Missão e Visão |
| format_type | fieldset (padrão do projeto) |
| children | `field_imagem_desktop`, `field_text_simple_long`, `field_text_simple_long_2` |
| weight | após `group_primeiro_bloco` (ex.: 2) |

### Legado removido

| Item | Ação |
|------|------|
| `group_segundo_bloco` | remover do form display |
| `field_titulo_2` | remover instance `quem_somos` (+ storage se órfão) |
| `field_text_long_formatted_2` | remover instance (+ storage se órfão) |
| `field_imagem_2` | remover instance (+ storage se órfão) |
| Markup Twig 2ª seção | remover |

### Bloco `missao_visao` (feature 011) — convivência

| Aspecto | Valor |
|---------|--------|
| Placement | `block.block.default_missaovisao` |
| Mudança | `status: false` (não renderiza em `/quem-somos`) |
| Tipos / conteúdo seed | permanecem no sistema (fora do delete) |

## Relacionamentos

```text
node:quem_somos
├── group_primeiro_bloco (Sobre nós) — inalterado
│     ├── field_titulo
│     ├── field_text_long_formatted
│     └── field_imagem
└── group_missao_visao (novo)
      ├── field_imagem_desktop → File (fundo CSS)
      ├── field_text_simple_long (corpo Missão)
      └── field_text_simple_long_2 (corpo Visão)

block.block.default_missaovisao
└── status: false  (não compete com o Node na rota)
```

## Seed / migração (hook `11015`)

| Atributo | Valor |
|----------|-------|
| Missão (se vazio) | `Desenvolver estagiários a partir do autoconhecimento, conectando suas habilidades e competências às oportunidades certas, para gerar performance, realização profissional e resultados consistentes para as empresas.` |
| Visão (se vazio) | `Ser referência na formação e gestão de estagiários no Brasil, reconhecida por transformar potencial em performance e por construir conexões assertivas entre talentos e organizações.` |
| Imagem (se vazia) | asset `modules/custom/custom_configs/assets/missao-visao/fundo-missao-visao.jpg` → `public://missao-visao/` (ou reuso do File do bloco UUID `b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e`) |
| Migração opcional legado | se `field_text_long_formatted_2` ainda existir e Missão vazia → strip tags → `field_text_simple_long` (uma vez) |

**Idempotência**: não duplica instances/grupos; não recria legado; não sobrescreve textos/imagem editoriais; reexecução `updb` = no-op seguro.

## Displays

| Display | Conteúdo |
|---------|----------|
| Form `node.quem_somos.default` | `group_primeiro_bloco` + `group_missao_visao`; sem `*_2` |
| View `node.quem_somos.default` | campos novos disponíveis ao Twig (ou hidden + acesso via `node.field_*`); sem `*_2` |

## Regras de validação / fallback

| Estado | Resultado público |
|--------|-------------------|
| Ambos textos vazios + sem imagem | omitir seção inteira |
| Só Missão ou só Visão | uma coluna; sem forçar coluna vazia |
| Sem imagem | `background-color` escuro + overlay; textos legíveis |
| Texto longo | wrap natural; sem truncar palavras |
| Reexecução `updb` | no-op |
| Editor alterou seed | hook preserva |

## Configuração a versionar (`drush cex`)

| Arquivo | Ação |
|---------|------|
| `field.field.node.quem_somos.field_imagem_desktop.yml` | criar |
| `field.field.node.quem_somos.field_text_simple_long.yml` | criar |
| `field.field.node.quem_somos.field_text_simple_long_2.yml` | criar |
| `core.entity_form_display.node.quem_somos.default.yml` | atualizar |
| `core.entity_view_display.node.quem_somos.default.yml` | atualizar |
| `block.block.default_missaovisao.yml` | `status: false` |
| `field.field.node.quem_somos.field_*_2.yml` (legado) | remover |
| `field.storage.node.field_*_2.yml` (órfãos) | remover se sem instances |

**Reutilizar (não recriar storage):** `field.storage.node.field_imagem_desktop`, `field.storage.node.field_text_simple_long`, `field.storage.node.field_text_simple_long_2`.

## Fora do modelo

- Paragraphs / ERR para Missão/Visão
- Storages `field_bg_imagem`, `field_image_desktop` (nome EN), fields de título editáveis
- Campos “Valores” do Anexo 2
- Alteração dos fields do Sobre nós / banner
