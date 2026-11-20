# Contract: render FAQ padrão no detalhe da vaga (051)

Estende [038 `vaga-faq-accordion-render.md`](../../038-vaga-faq-accordion/contracts/vaga-faq-accordion-render.md): **mesma** posição, markup e CSS; muda **somente** a fonte dos itens.

## Fonte de dados

| Antes (038) | Depois (051) |
|-------------|--------------|
| `node.field_vaga_faq.entity` | Coleção UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0` via `custom_configs_load_faq_padrao()` |

Valores em `field_vaga_faq` **MUST NOT** influenciar o HTML público.

## Pipeline

1. `default_preprocess_node__vagas__full`:
   - Carrega FAQ padrão (publicado + `access('view')` conforme visitante).
   - Monta `faq_paras`: paragraphs com pergunta e resposta não vazias (trim).
   - Adiciona cache tags do node FAQ (e list/tag necessários) ao render do node.
2. `node--vagas--full.html.twig`:
   - Se `faq_paras` não vazio → include `@default/content/includes/vaga-faq-accordion.html.twig` com `node` + `faq_paras`.
   - **Não** inspecionar `node.field_vaga_faq`.

## Quando a seção renderiza

| Condição | Seção |
|----------|-------|
| FAQ padrão ausente / unpublished / sem view access | omitida em **todas** as vagas |
| Após filtro, zero itens válidos | omitida em **todas** as vagas |
| ≥1 item válido | **idêntica** em toda vaga publicada |

## Markup / visual

Inalterado em relação ao contrato 038 (`.vaga-detalhe__faq`, Accordion sem `data-bs-parent`, título “Dúvidas Frequentes”).

## Editorial

| Superfície | Esperado |
|------------|----------|
| Form Vagas (criar/editar) | Sem widget “FAQ da vaga” |
| Form/edição do node FAQ padrão | Continua editável; mudanças refletem em todas as vagas após cache invalidate |

## Fora deste contrato

- Redesign CSS/JS do Accordion.
- FAQ em páginas institucionais.
- Remoção física do storage `field_vaga_faq`.
