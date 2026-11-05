# Feature Specification: WebPerf fontes self-host + LCP/CLS home

**Feature Directory**: `specs/062-webperf-fonts-lcp`  
**Created**: 2026-11-05  
**Status**: Approved  
**Input do usuário**: PageSpeed mobile ~42 após ajustes SEO; corrigir performance (fontes, LCP, CLS, FA JS) e description da front.

## Problema

PSI mobile da home (~42) aponta Font display ~3s, render-blocking ~2,3s, LCP ~6,9s e CLS ruim. A causa não é a feature 061 (SEO/segurança): é a dívida deixada pela 060 — Google Fonts via `@import`, Font Awesome JS global (~1,5 MB), hero sem `aspect-ratio`/`width`/`height`/preload e image styles do carrossel sem WebP. SEO 61 também sofre com ausência de `description` na metatag da front.

## Solução (visão de produto)

Visitante anônimo na home mobile vê texto e hero mais cedo (sem cadeia Google Fonts), menos JS não usado, menos layout shift no carrossel, imagens LCP em WebP com preload, e meta description na página inicial — sem redesign visual.

## Escopo

### Inclui

- Self-host WOFF2 Poppins (global) + Inter (só Contato); remover `@import` Google Fonts
- Remover `all.min.js` do Font Awesome (manter CSS)
- `aspect-ratio` + `width`/`height` + preload do 1º slide na home
- WebP nos image styles `banner_carousel` / `banner_carousel_mobile` + `hook_update_N` + cex
- Metatag `description` na front + cex

### Fora

- `cache.page.max_age`, AdvAgg, critical CSS, subset SVG do FA CSS
- Alterar banners PE/estudantes (URI crua / alpha PNG)
- Redesign visual / troca editorial das artes do hero
- Remover `noindex` temporário (decisão de lançamento, fora desta feature)

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Fontes locais sem FOIT de rede (Priority: P1)

Visitante carrega a home sem requests a `fonts.googleapis.com` / `fonts.gstatic.com`; tipografia Poppins com `font-display: swap`.

**Acceptance Scenarios**:

1. **Given** home anônima, **When** inspeciona Network, **Then** não há request a Google Fonts.
2. **Given** página Contato, **When** carrega, **Then** Inter está disponível (library anexada).

### User Story 2 — Hero LCP/CLS estável (Priority: P1)

O carrossel da home reserva espaço e prioriza o 1º slide em WebP.

**Acceptance Scenarios**:

1. **Given** home, **When** View Source, **Then** 1º `<img>` tem `width`/`height`, `fetchpriority="high"`, `loading="eager"` e há `link rel=preload` da imagem LCP.
2. **Given** CSS do carrossel, **When** aplica layout, **Then** `.carousel-inner` tem `aspect-ratio` (desktop 1200/433, mobile 16/10).

### User Story 3 — Menos JS morto (Priority: P2)

Páginas não carregam `fontawesome/js/all.min.js`.

**Acceptance Scenarios**:

1. **Given** home, **When** Network/HTML, **Then** não referencia `all.min.js`; ícones CSS do footer/topo continuam visíveis.

### User Story 4 — Meta description na front (Priority: P2)

A home emite meta description para SEO.

**Acceptance Scenarios**:

1. **Given** front com Metatag, **When** View Source, **Then** existe `<meta name="description" …>` não vazio.

## Requirements *(mandatory)*

- **FR-001**: Remover `@import` Google Fonts de `style.css`; servir Poppins via library local com `font-display: swap`.
- **FR-002**: Inter MUST carregar só onde necessário (library Contato), não na home.
- **FR-003**: Remover JS Font Awesome da library global; manter CSS.
- **FR-004**: Hero home MUST ter aspect-ratio, dimensões no `<img>` e preload do 1º slide na `<front>`.
- **FR-005**: Image styles `banner_carousel` e `banner_carousel_mobile` MUST converter para WebP; deploy via config + `hook_update_N` idempotente.
- **FR-006**: Metatag defaults da front MUST incluir `description`.
- **FR-007**: Não editar `core/` / `vendor/`; rebuild Vite versiona `dist/`.

## Success Criteria *(mandatory)*

- **SC-001**: Insight PSI “Font display” / cadeia Google Fonts eliminado ou drasticamente reduzido.
- **SC-002**: LCP mobile da home bem abaixo de ~6,9s em reteste lab (meta prática).
- **SC-003**: Ícones FA e tipografia Contato intactos visualmente.
- **SC-004**: Deploy destino: `cim` → `updb` → `cim` → `cr` sem passo manual de admin para WebP/metatag.
