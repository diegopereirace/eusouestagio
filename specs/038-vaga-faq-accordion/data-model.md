# Data Model: 038-vaga-faq-accordion

## Resumo

**Nenhum storage, bundle ou field instance novo.** A feature verifica estruturas já exportadas (030/031), reutiliza a coleção FAQ seed e, se necessário, preenche o vínculo vazio da vaga Engenheiro Civil.

## Entidades existentes (inalteradas em tipo)

### Coleção FAQ — CT `faq`

| Campo | Tipo | Cardinalidade | Papel |
|-------|------|---------------|-------|
| `title` | string (título do node) | 1 | Nome da coleção (ex.: “FAQ — Exemplo”) |
| `field_faq_itens` | entity_reference_revisions → `faq_item_p` | ilimitada | Itens do Accordion |
| `field_resposta` | text_long (legado) | 1 | Oculto no form; não usado no detalhe |

### Item FAQ — paragraph `faq_item_p`

| Campo | Tipo | Required | Papel |
|-------|------|----------|-------|
| `field_pergunta` | string (texto curto) | sim | Texto do botão do Accordion |
| `field_resposta` | text_long (`basic_html` / `full_html`) | sim | Corpo do painel |

### Vaga — CT `vagas`

| Campo | Tipo | Cardinalidade | Papel nesta feature |
|-------|------|---------------|---------------------|
| `field_vaga_faq` | ER → node `faq` | **1** | Associação vaga → coleção; fonte da seção |

## Relacionamentos

```text
vagas (field_vaga_faq, card. 1)
  └── faq (coleção)
        └── field_faq_itens[] → faq_item_p
              ├── field_pergunta
              └── field_resposta
```

CTA final (`default_ctav1vagas`) e cartão “Sobre a Empresa” (037) **não** entram no modelo de dados desta feature; são âncoras de ordenação na página.

## Validação (produto + render)

| Regra | Onde |
|-------|------|
| Pergunta e resposta obrigatórias no CMS | field instances `required: true` |
| Item inválido (pergunta ou resposta vazia após trim) omitido no Accordion | Twig do include |
| Coleção unpublished / sem itens válidos → seção omitida | Twig |
| Vaga sem `field_vaga_faq` → seção omitida | Twig |
| Máximo uma coleção por vaga | storage `cardinality: 1` |

## Seeds (UUID fixos)

| Entidade | UUID | Conteúdo / condição |
|----------|------|---------------------|
| Coleção FAQ exemplo | `c3d4e5f6-a7b8-4901-b234-56789abcdef0` | Título “FAQ — Exemplo”; itens “Qual a duração do estágio?” e “Existe auxílio home office?” — criar se ausente; acrescentar pergunta faltante sem sobrescrever editorial |
| Vaga Engenheiro Civil | `a7b8c9d0-e1f2-4345-f678-9abcdef01234` | Se `field_vaga_faq` **vazio** e a coleção seed existir → vincular; se já preenchido → no-op |

## Estado / transições

| Estado | Após deploy / reexecução |
|--------|---------------------------|
| Estrutura FAQ ausente em destino “limpo” | Ensures 031 (chamados pelo `11059`) recriam o mínimo; depois cex/cim já versionado cobre o habitual |
| Estrutura já em `config/sync` | Ensures = no-op |
| Coleção seed ausente | Criada com 2 itens |
| Coleção seed sem uma das perguntas | Item faltante acrescentado |
| Engenheiro Civil sem FAQ | Vínculo → coleção seed |
| Engenheiro Civil com FAQ editorial | Vínculo preservado |
| Reexecução completa | 0 nodes/paragraphs/vínculos duplicados |

## O que não muda

- Cardinalidade de `field_vaga_faq` (permanece 1).
- Storages `paragraph.field_pergunta` / `paragraph.field_resposta` / `node.field_faq_itens`.
- Modelo do cartão “Sobre a Empresa” (037) e do CTA `cta_v1`.
- Nenhuma View `faq_vaga`.
