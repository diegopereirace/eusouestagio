# Contract: Render do carrossel de Depoimentos

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-26  
**Consumidor**: visitante anônimo em `/para-empresas`  
**Produtor**: View `depoimentos_carousel` + Twig/CSS/JS do tema `default`

## Objetivo

Definir o contrato de markup, comportamento e acessibilidade do carrossel center mode. Complementa [composition-para-empresas.md](composition-para-empresas.md).

## Pré-condições

- ≥1 node `depoimento` publicado **ou** zero (neste caso o Twig **não** renderiza a seção).
- Library `default/depoimentos_carousel` anexada pelo Twig da View.

## Markup (contrato estável)

```text
section.depoimentos-empresas
  [opcional] heading acessível (h2 visualmente oculto ou título de seção se Figma exigir)
  .depoimentos-empresas__viewport
    .depoimentos-empresas__track
      article.depoimento-card[.depoimento-card--active | .depoimento-card--side]
        .depoimento-card__header
          [img.depoimento-card__avatar | omitido se sem foto]
          .depoimento-card__meta
            .depoimento-card__nome   ← title
            .depoimento-card__cargo  ← field_text_simple (omit se vazio)
        .depoimento-card__texto      ← field_text_simple_long (omit se vazio)
  .depoimentos-empresas__dots       ← omitido se ≤1 item
    button[aria-label][aria-current]
```

Classes `--active` / `--side` são responsabilidade do JS; no SSR o primeiro card pode nascer como ativo. Com `data-loop="1"` (N≥2) o motor clona o primeiro/último slide para wrap seamless.

## Comportamento

| Ação | Resultado |
|------|-----------|
| Load com N≥2 | Card central ativo; laterais parcialmente visíveis; opacidade laterais &lt; ativo; **loop infinito** (clones seamless) |
| Clique em dot `i` | Slide `i` torna-se ativo; `aria-current` atualizado |
| Swipe / drag (touch) | Avança/retrocede um índice (wrap infinito se N≥2); sem scroll horizontal da **página** |
| N=1 | Um card centrado; sem dots; sem loop |
| N=0 | Sem `<section.depoimentos-empresas>` |
| Autoplay | **Desligado** |

Motor JS: `themes/custom/default/assets/js/carousel-center.js` (library `default/carousel_center` / `default/depoimentos_carousel`). Banners full-bleed permanecem no **Bootstrap Carousel** (já no tema) — contratos de uso distintos (1 slide vs center multi-item).

## Visual (desktop ≥992px)

| Token | Valor alvo |
|-------|------------|
| Card background | `#FFFFFF` |
| Border radius | `16px` |
| Padding | `32px` |
| Largura card | ~`404px` |
| Min-height | ~`188px` |
| Avatar | ~`48×48`, circular |
| Cor do texto | ~`#45464D` ou token do tema |

## Acessibilidade

- Dots como `<button>` com `aria-label` (“Depoimento 1 de N”).
- Card ativo refletido em `aria-current="true"` no dot correspondente.
- Imagem com `alt` (= nome do autor ou vazio decorativo se o nome já está no texto adjacente — preferir alt = nome).
- Foco visível nos dots; teclado: setas opcional na v1; mínimo = Tab até dots + Enter/Space.

## Isolamento

- Estilos/scripts **somente** sob `.depoimentos-empresas`.
- Não alterar `.block-cta-v1`, banner PE, home, Quem Somos.

## Fora do contrato

- Painel de moderação de depoimentos enviados por visitantes.
- Depoimentos em outras rotas.
