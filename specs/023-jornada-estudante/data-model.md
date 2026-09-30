# Data Model: Jornada do Estudante

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-30

## Entidades

### 1. `jornada_estudante` (block_content) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Título | `field_text_simple` | string | 1 | não | storage **reutilizado** `block_content` |
| Passos | `field_itens_lista` | entity_reference_revisions → paragraph | storage `-1`; **max 4 no form/validação** | não | storage **reutilizado**; handler → `passo_jornada_p` |

- Label admin: “Jornada do Estudante”.
- Publicamente: `label_display: '0'` no placement.
- **Não** persiste número da etapa nem cor da badge.

### 2. `passo_jornada_p` (paragraph) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Título | `field_text_simple` | string | 1 | não* | storage **reutilizado**; *recomendado no form |
| Descrição | `field_text_simple_long` | string_long | 1 | não | storage **reutilizado** |

\*Formulário pode marcar required editorialmente; publicamente, campos vazios são omitidos no Twig. Número/cor = apresentação.

### 3. Bloco placement `default_jornadaestudante` — novo

| Aspecto | Valor |
|---------|--------|
| Config ID | `default_jornadaestudante` |
| Plugin | `block_content:b9c0d1e2-f3a4-4567-b890-1cdef0123456` |
| Tema | `default` |
| Região | `content_full` |
| Weight | **`1`** (após `default_beneficiosestudantes` weight `0`) |
| Label display | oculto (`'0'`) |
| Visibilidade | `request_path` = `/para-estudantes` |
| `status` | `true` |

### 4. Storage compartilhado — sem alteração

| Storage | Estado | Ação nesta feature |
|---------|--------|---------------------|
| `field.storage.block_content.field_itens_lista` | `cardinality: -1` | **nenhuma** (limite 4 só no form/validação do bundle) |
| `field.storage.block_content.field_text_simple` | existente | reutilizar |
| `field.storage.block_content.field_text_simple_long` | existente | reutilizar (não usado no bloco; só no paragraph) |
| `field.storage.paragraph.field_text_simple` | existente | reutilizar |
| `field.storage.paragraph.field_text_simple_long` | existente | reutilizar |

**Storages novos**: nenhum.

---

## Relacionamentos

```text
block_content:jornada_estudante (UUID b9c0d1e2-f3a4-4567-b890-1cdef0123456)
  ├── field_text_simple          (título seção)
  └── field_itens_lista[] ──► paragraph:passo_jornada_p   (max 4 via form)
                                 ├── field_text_simple      (título passo)
                                 └── field_text_simple_long (descrição)
                                 # número/cor: Twig loop.index / loop.last

block.block.default_jornadaestudante
  └── plugin block_content:<mesmo UUID> → content_full weight 1 → /para-estudantes

# Isolados (não alterar):
block_content:beneficios_estudantes (UUID a8b9…) + placement weight 0
View banners block_para_estudantes (região banner)
View vagas page_1 (/para-estudantes)
```

## Seed (conteúdo)

| Atributo | Valor |
|----------|-------|
| UUID fixo | `b9c0d1e2-f3a4-4567-b890-1cdef0123456` |
| Bundle | `jornada_estudante` |
| Status | publicado |
| Título | `Sua jornada até o sucesso` |
| Passos | 4 paragraphs (ordem abaixo) |
| Assets | **nenhum** (sem ícones) |

| # | Título do passo | Descrição seed |
|---|-----------------|----------------|
| 1 | Crie seu perfil | Mostre suas habilidades e formação de forma clara e atrativa. |
| 2 | Descubra oportunidades | Receba recomendações inteligentes baseadas no seu perfil. |
| 3 | Candidate-se | Com apenas um clique, envie seu perfil para as melhores vagas. |
| 4 | Comece sua carreira | Acompanhe seus processos e celebre suas conquistas. |

**Idempotência**: se UUID já existe → não duplica bloco; preenche lista **somente** se vazia/ausente; **nunca** sobrescreve texto editorial divergente.

## Displays

| Entidade | Form | View |
|----------|------|------|
| `jornada_estudante` | título + lista (widget paragraphs; default type `passo_jornada_p`; max 4 enforced) | campos visíveis para Twig (`entity_reference_revisions_entity_view` na lista) |
| `passo_jornada_p` | título + descrição | título + descrição |

## Hook `custom_configs_update_11037`

Responsabilidades (idempotentes):

1. Ensure paragraph type `passo_jornada_p` + field instances + form/view displays (defensivo).
2. Ensure block type `jornada_estudante` + field instances (handler lista → `passo_jornada_p`) + form/view displays.
3. Seed `BlockContent` UUID `b9c0…` + 4 paragraphs se campos vazios.
4. Ensure placement `default_jornadaestudante` (região `content_full`, pages `/para-estudantes`, weight `1`).
5. Mensagem Drush created/skipped.

**Não faz**: criar storages; alterar cardinality do storage compartilhado; alterar 021/022/vagas/PE/QS/home; sobrescrever editorial divergente.

## Regras de validação / fallback

| Estado | Resultado público |
|--------|-------------------|
| Sem título da seção | omitir `h2` |
| Zero passos | só cabeçalho (se houver); sem erro |
| &lt; 4 passos | grid com colunas disponíveis; badges 1…N; **último** item laranja |
| Tentativa de &gt; 4 no form | bloqueada (UI + validação) |
| Passo sem título | descrição + badge (se houver); sem heading vazio |
| Passo sem descrição | título + badge; sem parágrafo vazio |
| Reexecução hook | no-op seguro |

## Config YAML a versionar (checklist)

**Reutilizar (não recriar storage):**

- `field.storage.block_content.field_text_simple.yml`
- `field.storage.block_content.field_itens_lista.yml` (inalterado)
- `field.storage.paragraph.field_text_simple.yml`
- `field.storage.paragraph.field_text_simple_long.yml`

**Criar/exportar:**

- `block_content.type.jornada_estudante.yml`
- `paragraphs.paragraphs_type.passo_jornada_p.yml`
- 2× `field.field.block_content.jornada_estudante.*`
- 2× `field.field.paragraph.passo_jornada_p.*`
- form/view displays (bloco + paragraph)
- `block.block.default_jornadaestudante.yml`
- diffs em `user.role.*.yml`

## Fora do modelo

- Bundles `beneficios_estudantes` / `card_icon_text_p` / PE / home
- Campo numérico, cor ou ícone no paragraph
- Storage paralelo `field_text_simple_small` ou lista nova
- Alteração de cardinality do storage `field_itens_lista`
- Alterações em View `banners` / `vagas`
