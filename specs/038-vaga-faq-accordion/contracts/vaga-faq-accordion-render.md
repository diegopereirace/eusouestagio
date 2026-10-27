# Contract: Accordion "Dúvidas Frequentes" no detalhe da vaga (038)

## Onde entra

`node--vagas--full.html.twig`, coluna `col-12 col-lg-8`, **depois** do include “Sobre a Empresa” (037) e **antes** do fechamento da coluna. O CTA “Pronto para o próximo passo?” permanece como bloco de região **após** o node (`default_ctav1vagas`).

Implementação: montar `faq_paras` (ou equivalente) no pai **ou** no include; chamar:

```twig
{% include '@default/content/includes/vaga-faq-accordion.html.twig' with {
  node: node,
  faq_paras: faq_paras
} only %}
```

(Omitir o include se a lista filtrada estiver vazia.)

## Quando a seção renderiza

| Condição | Seção |
|----------|-------|
| `field_vaga_faq` vazio | omitida |
| Coleção não publicada | omitida |
| `field_faq_itens` vazio | omitida |
| Após filtro, zero itens com pergunta **e** resposta não vazias (trim) | omitida |
| ≥1 item válido em coleção publicada | renderiza |

Sem empresa / sem cartão 037: a seção permanece na coluna principal após a última seção de conteúdo presente (Benefícios / Requisitos / Sobre a Vaga).

## Markup

| Elemento | Classe / atributo | Notas |
|----------|-------------------|-------|
| Section | `.vaga-detalhe__faq` | Herda `margin-bottom: 2rem` já definido |
| Título | `h2.vaga-detalhe__section-title` | Texto “Dúvidas Frequentes” via `|t`; cor navy já no CSS da classe |
| Container | `.accordion` + `id="vaga-faq-{{ node.id() }}"` | Sem `accordion-flush`; usar `id()` (não `.id`) para evitar FieldItemList na concatenação |
| Item | `.accordion-item` | Um por item válido |
| Cabeçalho | `h3.accordion-header` | |
| Botão | `.accordion-button` (+ `.collapsed` se fechado) | `data-bs-toggle="collapse"`; **sem** `data-bs-parent` (painéis independentes) |
| Painel | `.accordion-collapse.collapse` (+ `.show` no 1º se desejado) | `id` / `aria-*` alinhados ao botão |
| Corpo | `.accordion-body` | Resposta com formato HTML permitido (`|raw` apenas no valor do campo text_long já filtrado pelo CMS) |

IDs únicos: `vaga-faq-{vaga_nid}-{paragraph_id|delta}`.

## Visual (CSS escopado a `.vaga-detalhe__faq`)

| Token | Valor / regra |
|-------|----------------|
| Cartão (`.accordion-item`) | fundo `#fff`; borda `1px solid` fina; radius ~10px; `margin-bottom: 1rem`; overflow hidden se necessário para o radius |
| Botão | fundo branco no estado padrão **e** ativo; texto navy/`#1a2b3c`, `font-weight: 600`; sem fundo azul BS5 |
| Seta | escura (override ícone/filtro BS5) à direita |
| Título seção | reusa `.vaga-detalhe__section-title` (`#023C62`) |

Mobile: cartões na largura da coluna; botão com área de toque confortável; pergunta longa quebra linha sem empurrar a seta para fora.

## Fora deste contrato

- View / bloco Views / placement de região para o FAQ.
- Header, sidebar, benefícios, cartão empresa, CTA final (copy/placement).
- Alterar cardinalidade ou storages FAQ.
