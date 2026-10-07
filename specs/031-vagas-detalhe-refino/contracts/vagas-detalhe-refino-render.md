# Contract: Render — Detalhe da Vaga (Refino)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-10-07

## Superfície

| Item | Valor |
|------|--------|
| Entity | Node `vagas`, view mode `full` |
| Template | `node--vagas--full.html.twig` |
| Root CSS | `.vaga-detalhe` |
| Library | `default/vagas_detalhe` (+ `default/script-painel` para ações) |
| CTA final | Bloco `default_ctav1vagas` (não markup no Twig do node) |

## Layout

| Viewport | Estrutura |
|----------|-----------|
| Desktop ≥992px | `.row` com coluna principal ~8/12 e sidebar ~4/12 |
| Mobile &lt;992px | Colunas empilhadas (sidebar abaixo); sem overflow-x |

## Coluna principal (ordem)

| # | Seção | Fonte | Omitir se |
|---|-------|-------|-----------|
| 1 | Header da Vaga | title, empresa, local, regime, horários, bolsa, postado há | — (mínimo title) |
| 2 | Sobre a Vaga | `field_text_long_formatted` | vazio |
| 3 | Requisitos | `field_vaga_requisitos` (HTML) + checks CSS | vazio |
| 4 | Benefícios | `field_vaga_beneficios` → grid 2/4 | vazio |
| 5 | Sobre a Empresa | user empresa (logo, nome, sobre truncado) | sem empresa |
| 6 | Dúvidas Frequentes | `field_vaga_faq.entity.field_faq_itens` accordion BS5 | sem FAQ / sem itens |

**Proibido no DOM do detalhe:** “Seu Match com a vaga”, “Processo de Contratação”, “Por que combina com você?”, “Seu Perfil”, bloco `.vaga-detalhe__cta` inline.

**“Ver Empresa”:** omitido (sem rota pública).

## Sidebar

| Bloco | Conteúdo |
|-------|----------|
| Ações | Candidatar-se (largo) + Salvar + Compartilhar — classes/fluxos existentes |
| Resumo da Vaga | Período / Bolsa Auxílio / Modelo / Vagas (“Não informado” se sem campo) |

## FAQ accordion

| Regra | Valor |
|-------|--------|
| IDs | únicos por item (`vaga-faq-{nid}-{delta|pid}`) |
| Pergunta | `field_pergunta` do paragraph |
| Resposta | `field_resposta` do paragraph |
| Comportamento | Accordion BS5 do tema (teclado + mouse) |

## CTA final (bloco)

| Regra | Valor |
|-------|--------|
| Visível | somente rotas de node `vagas` |
| Posição | região `content_full` após `page.content` |
| Visual | fundo `#023C62`; library `cta_v1_vagas` |
| Isolamento | não alterar CTAs claros QS/PE nem CTA escuro PE estudantes |
| Copy seed | “Pronto para o próximo passo?” + corpo + “Candidatar-se Agora” |

## Header global

| Regra | Valor |
|-------|--------|
| Escopo | `default_top` + menu `main` — sem redesign nesta feature |
| Aceite | utilizável desktop + mobile em home e detalhe de vaga |

## Isolamento

Não alterar Twigs/CSS de listagem `/vagas`, cards laranja home/landing, Hero Search 027.
