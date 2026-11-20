# Feature Specification: Alinhar Breakpoints do tema `default`

**Feature Directory**: `specs/046-breakpoints-tema`  
**Created**: 2026-11-02  
**Status**: Implemented  
**Input do usuário**: Analisar breakpoints; habilitar módulo Breakpoints e usar YAML no tema com melhores práticas.

## Escopo

### Inclui

- Confirmar módulo core `breakpoint` ativo em `config/sync/core.extension.yml`.
- Expandir `themes/custom/default/default.breakpoints.yml` com escala Bootstrap 5 (`xs`–`xxl`), preservando `default.mobile` / `default.desktop`.
- Normalizar outliers de media query no CSS custom (`767px` → `767.98px`, `576px` → `575.98px`).
- Documentar no quickstart a verificação pós-`drush cr`.

### Fora

- Habilitar ou configurar Responsive Image (rejeitado na feature 001; art direction via `<picture>`).
- Reescrever em massa os `@media` do CSS ou introduzir CSS custom properties de breakpoint.
- Migrar o tema para SCSS/PostCSS.
- Alterar IDs `default.mobile` / `default.desktop` (contratos 044, topo, CTO).
- Alterações em `core/` ou `vendor/`.

## Requirements

- **FR-001**: O módulo `breakpoint` permanece habilitado na config sincronizada.
- **FR-002**: O grupo `default` expõe `mobile`, `desktop`, `xs`, `sm`, `md`, `lg`, `xl`, `xxl` via `breakpoint.manager`.
- **FR-003**: `default.mobile` = `(max-width: 767.98px)` e `default.desktop` = `(min-width: 768px)` — inalterados semanticamente.
- **FR-004**: Breakpoints de grid usam mobile-first `min-width` alinhados ao Bootstrap 5 (576 / 768 / 992 / 1200 / 1400) e `multipliers: [1x, 2x]`.
- **FR-005**: Consumidores existentes (Twig `data-breakpoint`, preprocess Como funciona, `drupalSettings.blockBreakpoint`) continuam funcionando sem mudança de IDs.
- **FR-006**: CSS custom não contém outliers `max-width: 767px` ou `max-width: 576px` (usar `.98`).

## Success Criteria

- Discovery do grupo `default` lista os 8 breakpoints.
- Home: topo, CTO e Como funciona inalterados visualmente em ≤767.98px e ≥768px.
- `drush cr` após alteração do YAML sem erros.
