# Contract: Token tipográfico — título Contato

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-11-04  
**Consumidor**: tema `default` — somente `/contato`  
**Fonte de verdade**: anexo Figma Contato — “Envie sua mensagem”

## Decisão de produto (override)

O token tipográfico do Figma Contato aplica-se **somente** ao H2 “Envie sua mensagem” em `/contato`.  
Headers de seção de outros blocos/páginas **não** herdam este token (mantêm tipografia própria).

## Token canônico

| Propriedade | Valor |
|-------------|--------|
| `font-family` | `Inter, "Segoe UI", sans-serif` |
| `font-weight` | `600` |
| `font-size` | `30px` |
| `line-height` | `38px` |
| `letter-spacing` | `-0.3px` |
| `color` | `#000000` (default) |

**Classe canônica**: `.ese-section-title`  
**Arquivo**: `themes/custom/default/assets/css/ese-section-title.css`  
**Library**: `default/layout_contato` (não em `global-styling`)

## Inclui

- `/contato` — `.layout-contato-node__title` (+ classe `.ese-section-title`)
- Placement legado — `.block-layout-contato .layout-contato__title`

## Exclui

- Headers de bloco público (ex.: `.nd-header__title`, `.nm-header__title`, `.oqf-header__title`, `.cf-header__title`, `.be-header__title`, `.oe-header__title`, `.je-header__title`, `.dqs-header__title`)
- Heroes / banners / `vagas-hero-search__title`
- Microtítulos e títulos de cards
- CTAs, nav, footer, labels de formulário
- Reset massivo de `h1–h6` em `style.css`

## Aceite

- SC-007: Contato 100% no token.
- SC-008: **N/A** (aliases globais retirados por decisão de produto; tipografia dos demais blocos permanece local).
