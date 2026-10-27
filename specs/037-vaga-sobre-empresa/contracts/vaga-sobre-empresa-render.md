# Contract: cartão "Sobre a Empresa" no detalhe da vaga (037)

## Onde entra

`node--vagas--full.html.twig`, coluna `col-12 col-lg-8`, **depois** de `.vaga-detalhe__beneficios` (se essa seção existir) e **antes** de `.vaga-detalhe__faq` (se existir). Implementação: `{% if show_empresa_card and empresa_node %}{% include '@default/content/includes/vaga-sobre-empresa.html.twig' %}{% endif %}`. A flag `show_empresa_card` é definida em `default_preprocess_node__vagas__full()` (Twig sandbox do Drupal 11 bloqueia `Node::access()` no template).

## Quando renderiza

| Condição | Cartão |
|----------|--------|
| `field_vaga_empresa` vazio | omitido |
| Referência para nó não publicado | omitido (`empresa_node` continua nulo, como no header; flag falsa) |
| Nó publicado sem `access('view')` para o visitante | omitido (`show_empresa_card` falsa via preprocess) |
| Nó publicado e acessível | renderiza, mesmo sem logo e sem resumo |

## Markup

| Elemento | Fonte | Notas |
|----------|-------|-------|
| Section | — | `<section class="vaga-detalhe__empresa">` — herda `margin-bottom: 2rem` já definido |
| Cartão | — | `.vaga-detalhe__empresa-card` |
| Logo | `empresa_node.field_imagem` | Só se houver arquivo. `<img>` 80×80, `alt` = nome da empresa, `loading="lazy"`. Sem arquivo, a coluna do logo não sai |
| Título | `empresa_node.label` | `Sobre a {nome}` via `'Sobre a @nome'|t`. Um heading de seção (`h2`), não compete com o `h1` da vaga |
| Subtítulo | `empresa_node.field_text_simple` | Parágrafo só se o valor, após trim, não for vazio. Sem placeholder |
| Ação | rota `entity.node.canonical` do node empresa | Texto visível “Ver Empresa”. URL limpa Pathauto (`/empresa/...`) |

## Layout

- **≥ 992px**: linha — logo, bloco de textos (`min-width: 0`), botão à direita (`white-space: nowrap`).
- **≤ 991.98px**: coluna, conteúdo centralizado, botão em uma linha só (“Ver Empresa” não parte no meio).
- Título longo e resumo de até 255 caracteres quebram linha (`overflow-wrap: break-word`). Sem `line-clamp`, sem reticências.
- Sem logo: título, subtítulo (se houver) e botão permanecem.

## Visual (substituir o CSS morto do cartão antigo)

| Token | Valor |
|-------|--------|
| Fundo do cartão | `#EFF4FF` |
| Borda | `1px solid #D5E3F7` |
| Radius do cartão e do logo | `12px` |
| Título | `var(--vd-navy)` (`#023C62`), semibold/bold |
| Subtítulo | `#45464D` |
| Logo | 80×80, `object-fit: cover` |
| Botão `.vaga-detalhe__empresa-cta` | contorno navy, fundo transparente, radius 12px; hover preenche navy com texto branco |

O logo do **header** (`.vaga-detalhe__logo`, 112px) não muda.

## Fora deste contrato

- Header, sidebar, FAQ, benefícios e listagens.
- Página `node--empresa--full.html.twig` (não exibe o resumo curto).
- Qualquer leitura de `field_empresa_u`.
