# Contract: LCP do hero da home

**Feature**: [../spec.md](../spec.md) | **Data**: 2026-11-04  
**Regra de negócio**: `.cursor/rules/estagio-banner-home.mdc`

## Candidato LCP

| Item | Valor |
|------|--------|
| Superfície | Hero carrossel da home |
| Markup | View `banners` display `block_home` |
| Template canônico | `themes/custom/default/templates/views/views-view--banners--block-home.html.twig` |
| Seletor | Primeiro `.carousel-item.active img.home-hero__img` (primeiro slide) |

## Atributos obrigatórios (1º slide / LCP)

| Atributo | Valor |
|----------|--------|
| `loading` | `eager` |
| `fetchpriority` | `high` |

## Slides seguintes

Podem usar `loading="lazy"` (não competir com LCP).

## Fora deste contrato

- Bloco `custom_banners_hero_search` (busca abaixo do hero — **sem** imagem LCP).
- Lazy/eager global de todas as imagens do site.
- `rel=preload` / image styles WebP (features futuras).

## Aceite

1. Inspecionar HTML da home com banner ativo → 1º slide conforme tabela.
2. Se banner hero desligado no ambiente → SC de LCP **N/A**; build/agregação seguem válidos.
3. Baseline 2026-11-04 já conforme; implementação = **auditar** e corrigir só se houver lacuna.
