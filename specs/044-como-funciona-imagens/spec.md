# Feature Specification: Como funciona — imagens gerenciáveis

**Feature Directory**: `specs/044-como-funciona-imagens`  
**Created**: 2026-11-02  
**Status**: Implemented  
**Input do usuário**: Substituir o desenho CSS dos chevrons por imagens gerenciáveis (desktop + mobile), sem distorção, responsivas, com carregamento condicional por viewport e deploy automatizado.

## Escopo

### Inclui

- Campos no bloco `como_funciona_bt`: `field_image` (desktop) e `field_imagem_mobile` (mobile).
- Renderização pública via `<picture>` (mobile `max-width: 767.98px`; desktop no `img` fallback).
- Assets WebP versionados em `modules/custom/custom_configs/assets/como-funciona/`.
- Seed idempotente `custom_configs_update_11062` (copia assets e anexa se vazios).
- Remoção do fluxo CSS de chevrons/setas/números no tema.
- Título e subtítulo permanecem editoriais; paragraphs de etapas ficam no CMS sem render visual.

### Fora

- Remoção do paragraph type `como_funciona_item_p` (feita na feature **045**).
- Image styles Drupal na 1ª entrega.
- Alterações em `core/` ou `vendor/`.

## Requirements

- **FR-001**: Editor altera imagens desktop/mobile no bloco sem deploy de código.
- **FR-002**: Media queries do `<picture>` vêm do **Breakpoints API** (`breakpoint.manager` + `default.breakpoints.yml`: `default.mobile` / `default.desktop`), não hardcode solto no Twig.
- **FR-003**: Seleção da arte é **client-side** (`<picture>` / media); HTML idêntico para todos os clientes — compatível com Page Cache e Dynamic Page Cache **sem** cache context de User-Agent (zero inconsistência).
- **FR-004**: Em viewport que casa com `default.mobile` o browser solicita só a arte mobile; caso contrário, a desktop.
- **FR-005**: Imagens com `width`/`height` e `height:auto` / `width:100%` — sem distorção.
- **FR-006**: Assets seed em WebP; `loading="lazy"` e `decoding="async"`.
- **FR-007**: Deploy: `cim` → `updb` (`11062`, `11063`, `11067`) → `cim` → `cr`; seed não sobrescreve editorial; form/view display das imagens garantidos pelo hook (não só pelo cim).
- **FR-008**: `.cf-flow-media` sem margens; artes redesenhadas em alta resolução (desktop ≥ 2x da largura exibida, mobile em fluxo vertical adaptado) — hook `11063` migra a arte seed antiga.

## Success Criteria

- Home mostra o fluxo como imagem alinhada à arte, sem chevrons CSS.
- Troca de viewport troca a fonte da `<picture>` sem baixar as duas.
- Reexecução de `updb` é no-op seguro quando imagens já estão preenchidas.
