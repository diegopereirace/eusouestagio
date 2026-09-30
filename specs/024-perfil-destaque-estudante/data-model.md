# Data Model: Perfil em Destaque (Estudante)

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-30

## Entidades

### 1. `perfil_destaque_estudante` (block_content) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Ilustração | — | — | — | — | **não cadastrável** — mock HTML/CSS no Twig (`.pd-visual` + gradiente + card) |
| Título | `field_text_simple` | string | 1 | não | storage **reutilizado** |
| Itens | `field_itens_lista` | entity_reference_revisions → paragraph | storage `-1`; **max 3 no form/validação** | não | storage **reutilizado**; handler → `item_lista_icone_p` |
| CTA | `field_link` | link | 1 | não | storage **reutilizado**; seed URI `/painel/estudante/perfil` |

- Label admin: “Perfil em Destaque”.
- Publicamente: `label_display: '0'` no placement.

### 2. `item_lista_icone_p` (paragraph) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Ícone | `field_image` | image | 1 | não | storage **reutilizado** `paragraph`; render ~20×20 |
| Título | `field_text_simple` | string | 1 | não* | storage **reutilizado**; *recomendado no form |
| Descrição | `field_text_simple_long` | string_long | 1 | não | storage **reutilizado** |

\*Formulário pode marcar required editorialmente; publicamente, campos vazios são omitidos no Twig.

### 3. Bloco placement `default_perfildestaqueestudante` — novo

| Aspecto | Valor |
|---------|--------|
| Config ID | `default_perfildestaqueestudante` |
| Plugin | `block_content:c0d1e2f3-a4b5-4678-c901-2def01234567` |
| Tema | `default` |
| Região | `content_full` |
| Weight | **`2`** (após `default_jornadaestudante` weight `1`) |
| Label display | oculto (`'0'`) |
| Visibilidade | `request_path` = `/para-estudantes` |
| `status` | `true` |

### 4. Storage compartilhado — sem alteração

| Storage | Estado | Ação nesta feature |
|---------|--------|---------------------|
| `field.storage.block_content.field_itens_lista` | `cardinality: -1` | **nenhuma** (limite 3 só no form/validação do bundle) |
| `field.storage.block_content.field_image` | existente | reutilizar |
| `field.storage.block_content.field_text_simple` | existente | reutilizar |
| `field.storage.block_content.field_link` | existente | reutilizar |
| `field.storage.paragraph.field_image` | existente | reutilizar |
| `field.storage.paragraph.field_text_simple` | existente | reutilizar |
| `field.storage.paragraph.field_text_simple_long` | existente | reutilizar |

**Storages novos**: nenhum.

---

## Relacionamentos

```text
block_content:perfil_destaque_estudante (UUID c0d1e2f3-a4b5-4678-c901-2def01234567)
  ├── field_image                (ilustração coluna esquerda)
  ├── field_text_simple          (título seção)
  ├── field_itens_lista[] ──► paragraph:item_lista_icone_p   (max 3 via form)
  │                              ├── field_image             (ícone 20×20)
  │                              ├── field_text_simple       (título item)
  │                              └── field_text_simple_long  (descrição)
  └── field_link                 (CTA → /painel/estudante/perfil)

block.block.default_perfildestaqueestudante
  └── plugin block_content:<mesmo UUID> → content_full weight 2 → /para-estudantes

# Isolados (não alterar):
block_content:beneficios_estudantes (UUID a8b9…) + placement weight 0
block_content:jornada_estudante (UUID b9c0…) + placement weight 1
View banners block_para_estudantes (região banner)
View vagas page_1 (/para-estudantes)
```

## Seed (conteúdo)

| Atributo | Valor |
|----------|-------|
| UUID fixo | `c0d1e2f3-a4b5-4678-c901-2def01234567` |
| Bundle | `perfil_destaque_estudante` |
| Status | publicado |
| Título | `Seu perfil em destaque` |
| Ilustração | asset versionado (pasta abaixo) |
| Itens | 3 paragraphs (ordem abaixo) |
| CTA | título `Completar meu perfil`; URI `/painel/estudante/perfil` |
| Assets | `modules/custom/custom_configs/assets/perfil-destaque-estudante/` |

| # | Título do item | Descrição seed |
|---|----------------|----------------|
| 1 | Mostre suas habilidades | Vá além do currículo padrão e destaque competências, formação e diferenciais do seu perfil. |
| 2 | Seja encontrado pelas empresas | Recrutadores buscam ativamente candidatos com perfil completo e atualizado. |
| 3 | Receba oportunidades relevantes | Notificações personalizadas quando surgirem vagas alinhadas ao seu perfil. |

**Idempotência**: se UUID já existe → não duplica bloco; preenche campos/lista **somente** se vazios/ausentes; **nunca** sobrescreve texto/arte editorial divergente.

## Displays

| Entidade | Form | View |
|----------|------|------|
| `perfil_destaque_estudante` | ilustração + título + lista (widget paragraphs; default type `item_lista_icone_p`; max 3 enforced) + link CTA | campos visíveis para Twig (`entity_reference_revisions_entity_view` na lista; image + link) |
| `item_lista_icone_p` | ícone + título + descrição | ícone + título + descrição |

## Hook `custom_configs_update_11038`

Responsabilidades (idempotentes):

1. Ensure paragraph type `item_lista_icone_p` + field instances + form/view displays (defensivo).
2. Ensure block type `perfil_destaque_estudante` + field instances (handler lista → `item_lista_icone_p`) + form/view displays.
3. Copiar assets seed para `public://` se ausentes.
4. Seed `BlockContent` UUID `c0d1…` + ilustração + 3 paragraphs + CTA se campos vazios.
5. Ensure placement `default_perfildestaqueestudante` (região `content_full`, pages `/para-estudantes`, weight `2`).
6. Mensagem Drush created/skipped.

**Não faz**: criar storages; alterar cardinality do storage compartilhado; alterar 021/022/023/vagas/PE/QS/home; sobrescrever editorial divergente.

## Regras de validação / fallback

| Estado | Resultado público |
|--------|-------------------|
| Sem título da seção | omitir `h2` |
| Ilustração ausente | coluna esquerda vazia/omitida; coluna direita permanece |
| Zero itens | cabeçalho/ilustração/CTA (quando preenchidos); sem erro |
| &lt; 3 itens | lista com os disponíveis |
| Tentativa de &gt; 3 no form | bloqueada (UI + validação) |
| Item sem ícone | título + descrição; layout flex ok |
| Item sem título | ícone + descrição; sem heading vazio |
| Item sem descrição | ícone + título; sem parágrafo vazio |
| CTA vazio | omitir botão |
| Reexecução hook | no-op seguro |

## Config YAML a versionar (checklist)

**Reutilizar (não recriar storage):**

- `field.storage.block_content.field_image.yml`
- `field.storage.block_content.field_text_simple.yml`
- `field.storage.block_content.field_itens_lista.yml` (inalterado)
- `field.storage.block_content.field_link.yml`
- `field.storage.paragraph.field_image.yml`
- `field.storage.paragraph.field_text_simple.yml`
- `field.storage.paragraph.field_text_simple_long.yml`

**Criar/exportar:**

- `block_content.type.perfil_destaque_estudante.yml`
- `paragraphs.paragraphs_type.item_lista_icone_p.yml`
- 4× `field.field.block_content.perfil_destaque_estudante.*`
- 3× `field.field.paragraph.item_lista_icone_p.*`
- form/view displays (bloco + paragraph)
- `block.block.default_perfildestaqueestudante.yml`
- diffs em `user.role.*.yml`

## Fora do modelo

- Bundles `beneficios_estudantes` / `card_icon_text_p` / `jornada_estudante` / `passo_jornada_p` / PE / home
- Storage paralelo `field_text_simple_small` ou `field_imagem`
- Storage de lista novo ou alteração de cardinality do `field_itens_lista`
- Alterações em View `banners` / `vagas`
- Fluxo de autenticação do painel do estudante
