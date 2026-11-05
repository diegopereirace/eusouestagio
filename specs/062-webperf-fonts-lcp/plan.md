# Implementation Plan: WebPerf fontes self-host + LCP/CLS home

**Branch**: `main` (workflow do projeto)  
**Date**: 2026-11-05  
**Spec**: [spec.md](spec.md)  
**Predecessor**: `060-build-assets-webperf`  
**Hook**: `custom_configs_update_11091`

## Summary

1. Self-host Poppins/Inter WOFF2; library `default/fonts` (+ Inter só em Contato); remover `@import` Google.
2. Remover `all.min.js` do Font Awesome.
3. CLS/LCP hero: aspect-ratio, width/height, preload na front.
4. WebP em `banner_carousel` / `banner_carousel_mobile` + update 11091 + cex.
5. Metatag description na front + cex.

## Technical Context

| Item | Valor |
|------|--------|
| Language/Version | PHP 8.3+ / Drupal 11 / Twig 3; Node LTS (Vite build) |
| Primary Dependencies | Tema `default`; `custom_configs`; image styles core |
| Storage | Config image styles + metatag; arquivos estáticos `fonts/` |
| Testing | Manual — [quickstart.md](quickstart.md); reteste PSI mobile |
| Constraints | Sem `core/`/`vendor/`; sem AdvAgg/page cache; PE/estudantes intactos |

## Constitution Check

| Gate | Status |
|------|--------|
| SDD | PASS |
| Sem core/vendor | PASS |
| Deploy cim→updb→cim→cr + hook | PASS (`11091`) |
| Seeds 057 | N/A (sem File seed novo) |
| Performance | PASS (objetivo) |

## Architecture

```text
themes/custom/default/
  fonts/poppins/*.woff2
  fonts/inter/*.woff2
  src/css/fonts-poppins.css → dist/css/fonts-poppins.css
  src/css/fonts-inter.css → dist/css/fonts-inter.css
  default.libraries.yml  # fonts_poppins, fonts_inter; FA sem JS
  default.info.yml       # + default/fonts_poppins
  default.theme          # dimensions + page_attachments preload
  templates/views/...block-home.html.twig
modules/custom/custom_configs/custom_configs.install  # 11091
config/sync/image.style.banner_carousel*.yml
config/sync/metatag.metatag_defaults.front.yml
```

## Implementation approach

- Fonts: WOFF2 latin subset; `font-display: swap`; urls relativos `../../fonts/...` a partir de `dist/css/`.
- Inter: dependency de `layout_contato` apenas.
- Preload: `html_head_link` na front com URL do 1º slide (mesmo estilo desktop; media para mobile se viável).
- WebP: efeito `image_convert_avif` extension webp (padrão do projeto em `wide`); hook garante efeito + flush derivatives.
