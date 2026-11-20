# Data Model: 051-vaga-faq-padrao

## Resumo

**Nenhum storage, bundle ou field instance novo.** A feature redesigna semanticamente a coleção seed existente como **FAQ padrão das vagas** e remove esse campo da fonte de verdade da renderização e do formulário editorial da vaga.

## Entidades

### FAQ padrão das vagas — CT `faq` (coleção canônica)

| Atributo | Valor |
|----------|--------|
| UUID fixo | `c3d4e5f6-a7b8-4901-b234-56789abcdef0` |
| Título seed | `FAQ — Exemplo` (não sobrescrever se editorial divergiu) |
| Status | Publicado para a seção aparecer |
| Itens | `field_faq_itens` → paragraphs `faq_item_p` |

| Campo | Tipo | Papel |
|-------|------|--------|
| `title` | string | Nome da coleção no CMS |
| `field_faq_itens` | ERR → `faq_item_p` | Itens do Accordion em **todas** as vagas |
| `field_resposta` | text_long (legado) | Oculto; não usado |

### Item FAQ — paragraph `faq_item_p`

| Campo | Required | Papel |
|-------|----------|--------|
| `field_pergunta` | sim | Texto do botão do Accordion |
| `field_resposta` | sim | Corpo do painel |

**Validação na renderização**: item só entra em `faq_paras` se pergunta **e** resposta não vazias após `trim`. Se zero itens válidos → seção omitida em **todas** as vagas.

### Vaga — CT `vagas`

| Campo | Papel após 051 |
|-------|----------------|
| `field_vaga_faq` | **Legado**. Storage/instance permanecem. Form: `hidden`. Público: **não lido**. Valores históricos ignorados. |

## Relacionamentos

```text
ANTES (038):
  vagas.field_vaga_faq ──ER──► faq ──ERR──► faq_item_p[]

DEPOIS (051):
  FAQ padrão (UUID fixo) ──ERR──► faq_item_p[]
       ▲
       │ load por UUID (preprocess / helper)
  detalhe de qualquer vaga (sem ER por anúncio)
```

## Regras de estado

| Estado do FAQ padrão | Efeito em todas as vagas públicas |
|----------------------|-----------------------------------|
| Ausente | Seção omitida; deploy deve criá-lo |
| Unpublished | Seção omitida |
| Publicado, 0 itens válidos | Seção omitida |
| Publicado, ≥1 item válido | Seção idêntica em todas as vagas |

| Estado do form Vagas | Esperado |
|----------------------|----------|
| Após deploy | `field_vaga_faq` ausente da UI (region hidden) |
| Valores legados no banco | Permitidos; sem impacto no público |

## Seeds / ensure (idempotente)

| Ação | Comportamento |
|------|----------------|
| Ensure CT/fields FAQ | Reuso helpers 030/031 — no-op se presentes |
| Seed coleção UUID | Criar se ausente; acrescentar perguntas seed faltantes; não sobrescrever textos existentes |
| Hide form `field_vaga_faq` | Set region hidden se ainda content — no-op se já hidden |
| Limpar `field_vaga_faq` nas vagas | **Não** (fora do necessário) |

## O que não muda

- Cardinalidade/storage de `field_vaga_faq`, `field_faq_itens`, `field_pergunta`, `field_resposta`.
- Markup/CSS do Accordion (038).
- Ordem das seções no detalhe (após Sobre a Empresa, antes do CTA de região).
