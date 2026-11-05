# Research: 062 WebPerf fontes + LCP

## R1 — Google Fonts `@import`

Cadeia render-blocking clássica dentro do CSS agregado. Self-host WOFF2 + `font-display: swap` elimina DNS/TLS a Google e o insight Font display (~3s no PSI).

## R2 — Inter só no Contato

`ese-section-title.css` (library `layout_contato`) é o consumidor Inter. Carregar Inter globalmente na home é desperdício.

## R3 — Font Awesome JS

Templates usam `<i class="fa-*">` (CSS webfont). `all.min.js` (~1,5 MB) é SVG/JS kit desnecessário e aparece em unused JS + cache lifetimes.

## R4 — CLS home

PE/estudantes já usam `aspect-ratio: 1200/433` (mobile `16/10`). Replicar no hero home + attrs intrínsecos.

## R5 — AVIF/WebP nos banners

Padrão do projeto: `image_convert_avif` com `extension: webp` (fallback). Se o toolkit suporta AVIF, o derivative sai `.avif` (melhor que WebP no PSI). Aplicar só aos styles do carrossel home (não PE/estudantes raw PNG).

## R7 — Fonts CSS sem preprocess

Agregação Drupal stripava `url()` relativos dos WOFF2 no CSS agregado. Libraries `fonts_poppins` / `fonts_inter` usam `preprocess: false`.

## R6 — SEO description

`metatag.metatag_defaults.front` só title/canonical → PSI SEO ~61. Description barata, zero custo de render. `noindex` temporário no tema permanece fora de escopo.
