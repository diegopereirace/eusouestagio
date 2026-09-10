# Especificação de Feature: Topo da home conforme Figma

**Feature Directory**: `specs/002-topo-figma-home`
**Criada em**: 2026-09-10
**Status**: Active
**Input do usuário**: Alinhar header (TopNavBar) e hero da home ao layout Figma (escopo A); manter bloco de busca abaixo; imagens do carrossel continuam vindas do tipo `banners`.

## Escopo

### Inclui

- Header: logo, menu principal, Login/Cadastrar (altura ~87px, botões com radius 16px, tokens de marca).
- Hero da home: layout bipartido (copy fixa à esquerda + mídia/carrossel à direita), CTA “SAIBA MAIS”, citação, contatos, faixas decorativas, setas/dots.

### Fora

- Redesign do bloco de busca hero, footer, cards de vagas, painel.
- Novos campos administráveis no content type `banners` para textos do hero.
- Troca do arquivo de logo do tema.

## Clarifications

### Session 2026-09-10

- Q: Escopo visual? → A: Somente topo (header + hero Figma); busca permanece abaixo.
- Q: Textos do hero? → A: Fixos no Twig (padrão FR-6 da feature 001).
- Q: CTA “SAIBA MAIS”? → A: Âncora `#hero-search`.
- Q: Telefone do hero? → A: Do footer (`field_phone_wpp`) quando existir; senão placeholder do Figma.

## User Scenarios & Testing

### Cenário 1 — Visitante vê TopNavBar no estilo Figma (P1)

1. Visitante acessa a home em desktop (~1280px).
2. Vê header branco sticky com logo à esquerda, links de menu e botões Login (cinza) / Cadastrar (laranja, radius ~16px).
3. Altura visual do header aproxima-se de ~87px (hug do conteúdo).

### Cenário 2 — Visitante vê hero bipartido (P1)

1. Abaixo do header, o hero na região `banner` mostra copy à esquerda e carrossel `#banner-carousel-home-slides` à direita (sem overlay).
2. Na coluna de copy, vê título “ENCONTRE” (verde) + “SUA CARREIRA” (navy), subtítulo, botão pill “SAIBA MAIS”, citação e contatos.
3. Com múltiplos banners, setas e dots navegam só a coluna de mídia; o primeiro slide usa LCP otimizado.

### Cenário 3 — CTA leva à busca (P1)

1. Visitante clica em “SAIBA MAIS”.
2. A página rola até o bloco de busca (`#hero-search`) na região highlighted.

### Cenário 4 — Mobile (P1)

1. Em viewport ≤767.98px, o hero empilha copy acima da mídia (citação/contatos podem ocultar-se por espaço).
2. Menu mobile (offcanvas) continua funcional; botões Login/Cadastrar usam as mesmas cores/radius do desktop.

### Cenário 5 — Editor continua gerenciando imagens (P1)

1. Editor publica/despublica banners com local “Home”.
2. As imagens do lado direito do hero refletem a listagem; textos do hero não mudam via admin.

### Edge Cases

- Zero banners publicados para Home → coluna de copy permanece; coluna de mídia omitida (sem carrossel vazio).
- Um único banner → sem setas/dots (comportamento atual de slide único).
- Footer sem telefone → hero exibe placeholder do Figma (`+00 123 456 789 00`).

## Requirements

- **FR-1**: O tema DEVE expor tokens CSS `--brand-green`, `--brand-navy`, `--brand-orange`, `--header-h` (~87px), `--hero-bg` e usá-los no header e no hero desta feature.
- **FR-2**: O TopNavBar DEVE seguir o layout Figma (fundo branco, botões Login/Cadastrar com radius 16px, Cadastrar laranja).
- **FR-3**: O hero da home DEVE usar **layout bipartido** na região `banner` (copy fixa à esquerda + carrossel `#banner-carousel-home-slides` à direita), **sem** overlay sobre a arte; imagens vindas da View `banners` / `block_home`.
- **FR-4**: O CTA “SAIBA MAIS” DEVE apontar para `#hero-search`; o bloco de busca DEVE ter esse `id`.
- **FR-5**: O carrossel DEVE preservar `<picture>`, intervalo Bootstrap, LCP no primeiro slide e lazy nos demais.
- **FR-6**: Em mobile, o hero DEVE empilhar copy acima da mídia; o menu offcanvas existente DEVE permanecer.
- **FR-7**: Requisitos de filtros/busca da feature `001-banners-busca-home` NÃO DEVEM ser alterados.

## Success Criteria

- **SC-1**: Header e hero da home reconhecíveis frente aos prints Figma em desktop.
- **SC-2**: Busca hero permanece abaixo e acessível via CTA.
- **SC-3**: 0 / 1 / N slides não quebram o layout.
- **SC-4**: Feature 001 (busca/filtros) continua operacional.
