# Data Model: Detalhe da Vaga — Refino (031)

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-07  
**Predecessor model**: [030 data-model](../030-vagas-detalhe-layout/data-model.md)

## Entidades

### 1. Node type `faq` (coleção — evolução)

| Aspecto | Valor |
|---------|--------|
| Machine name | `faq` |
| Label | FAQ |
| Papel | Coleção de perguntas/respostas referenciável por vagas |
| `title` | Nome da coleção (ex.: “FAQ — Exemplo”), **não** a pergunta |
| Menus | nenhum |

**Campos:**

| Campo | Tipo | Cardinality | Notas |
|-------|------|-------------|--------|
| `title` (nativo) | string | 1 | Nome da coleção |
| `field_faq_itens` | ERR → `faq_item_p` (storage novo `node.field_faq_itens`) | -1 | Itens da coleção |
| `field_resposta` (legado 030) | `text_long` | 1 | Migrado para paragraphs; ocultar no form pós-migração; storage permanece |

**Seed (UUID fixo):**

| UUID | Título | Itens |
|------|--------|-------|
| `c3d4e5f6-a7b8-4901-b234-56789abcdef0` | FAQ — Exemplo | 2× `faq_item_p` (abaixo) |

| # | Pergunta | Resposta (placeholder) |
|---|----------|------------------------|
| 1 | Qual a duração do estágio? | A duração varia conforme o edital da vaga e o acordo com a instituição de ensino. Consulte a descrição da vaga para o período informado. |
| 2 | Existe auxílio home office? | Depende da vaga e da empresa. Quando disponível, o benefício aparece na seção Benefícios ou na descrição. |

Regras: criar coleção só se UUID ausente; não sobrescrever itens/título se editorial divergir; não duplicar itens com a mesma pergunta na reexecução.

Seeds 030 (`b1c2d3e4-…abc01` / `…abc02`): fundo na coleção seed quando ainda forem nodes 1:1; não recriar como FAQs separados.

### 2. Paragraph `faq_item_p` (novo)

| Aspecto | Valor |
|---------|--------|
| Machine name | `faq_item_p` |
| Label | Item FAQ |
| Fields | `field_pergunta` (string, storage novo `paragraph.field_pergunta`), `field_resposta` (`text_long`, storage novo `paragraph.field_resposta`) |

### 3. Paragraph `beneficio_vaga_p` (existente — sem mudança de schema)

| Aspecto | Valor |
|---------|--------|
| Machine name | `beneficio_vaga_p` |
| Fields | `field_image` (ícone), `field_text_simple` (texto; pedido verbal `field_text_simple_small` → canônico) |

### 4. Fields no bundle `vagas` (evolução)

| Field | Storage | Type | Card. | Target / notes |
|-------|---------|------|-------|----------------|
| `field_vaga_faq` | existente (ajustar card.) | `entity_reference` | **1** (era -1) | `node` → `faq` (coleção) |
| `field_vaga_requisitos` | **recreate** | `text_long` | **1** (era string -1) | HTML/lista; checks via CSS no tema |
| `field_vaga_beneficios` | existente | ERR | -1 | `beneficio_vaga_p` |
| `field_vaga_etapas_processo` | existente | string | -1 | **fora da UI full**; schema mantido |

**Displays `node.vagas`:** form `default` — requisitos (textarea formatado), benefícios (paragraphs), FAQ (autocomplete card. 1). View display: campos podem ficar hidden se Twig full ler a entity.

**Displays `node.faq`:** form — title + `field_faq_itens` (paragraphs); `field_resposta` legado hidden.

### 5. Bloco `cta_v1` — instância vagas (nova)

| Aspecto | Valor |
|---------|--------|
| Tipo | `cta_v1` (existente) |
| UUID `block_content` | `d4e5f6a7-b8c9-4012-c345-6789abcdef01` |
| Placement id | `default_ctav1vagas` |
| UUID placement | `e5f6a7b8-c9d0-4123-d456-789abcdef012` |
| Região | `content_full` |
| Weight | `10` (após conteúdo do node) |
| Visibility | `entity_bundle:node` → `vagas` |
| Campos seed | `field_text_simple` título; `field_text_simple_long` corpo; `field_link` primário “Candidatar-se Agora” → `internal:/cadastro/candidato`; `field_link_2` vazio ou omitido |
| Visual | Twig suggestion + library `cta_v1_vagas` (escuro `#023C62`) |

### 6. Campos reutilizados no detalhe (sem alteração estrutural)

| Campo | Uso UI |
|-------|--------|
| `title` | H1 header |
| `field_text_long_formatted` | Sobre a Vaga |
| `field_empresa_u` → user | Logo, nome fantasia, sobre (sem “Ver Empresa”) |
| `field_cidade` + `field_estados` | Localização |
| `field_regime_t` | Badge + resumo Modelo |
| `field_horarios` | Badge carga + resumo Período |
| `field_text_simple` | Badge/resumo Bolsa |
| `created` | “Postado há…” |

### 7. Dependências de fluxo (sem schema novo)

| Recurso | Uso |
|---------|-----|
| Tabela `vagas_salvas` | estado Salvar |
| Node `candidatura` | estado Candidatar-se |
| JS `script-painel` | handlers sidebar |
| Header `default_top` + menu `main` | FR-020 smoke |

## Relacionamentos

```text
Node vagas
  ├── field_vaga_faq (1) ──────────► Node faq (coleção)
  │                                    ├── title (nome da coleção)
  │                                    └── field_faq_itens[] ──► Paragraph faq_item_p
  │                                                               ├── field_pergunta
  │                                                               └── field_resposta
  ├── field_vaga_requisitos (text_long)
  ├── field_vaga_beneficios[] ─────► Paragraph beneficio_vaga_p
  │                                    ├── field_image
  │                                    └── field_text_simple
  ├── field_text_long_formatted
  ├── field_empresa_u ─────────────► User (empresa)
  └── (field_vaga_etapas_processo — schema only)

Block content cta_v1 (instância vagas)
  └── Placement default_ctav1vagas @ content_full
        visibility: entity_bundle:node = vagas
```

## Validações / regras

- Seções omitidas se vazias: requisitos, benefícios, FAQ, empresa.
- FAQ unpublished → não exibir a anônimos (acesso Drupal).
- Reexecução `11048`: sem duplicar coleção seed, itens, CTA nem placement.
- Editorial divergente do seed: não sobrescrever.
- Cardinalidade FAQ da vaga: no máximo 1 coleção.

## Migrações (hook `11048`)

| De | Para | Condição |
|----|------|----------|
| FAQ node title+`field_resposta` | 1× `faq_item_p` em `field_faq_itens` | coleção sem itens / legado 1:1 |
| Seeds 030 abc01/abc02 | Itens da coleção seed `c3d4…` | sem duplicar pergunta |
| `field_vaga_faq` N refs | 1 coleção consolidada | antes de card. 1 |
| `field_vaga_requisitos` string[] | HTML `<ul><li>` em text_long | destino vazio |
| — | Seed CTA + placement | ausente / campos vazios |
