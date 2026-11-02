# Quickstart: Breakpoints do tema `default`

**Feature**: [spec.md](spec.md) | **Data**: 2026-11-02

```bash
docker compose exec -T drupal vendor/bin/drush cr
```

## Esperado

- Módulo `breakpoint` ativo (`core.extension.yml`)
- Fonte de verdade: `themes/custom/default/default.breakpoints.yml`
  - Art direction / DOM: `default.mobile` (`max-width: 767.98px`), `default.desktop` (`min-width: 768px`)
  - Grid BS5: `xs` (base), `sm` ≥576, `md` ≥768, `lg` ≥992, `xl` ≥1200, `xxl` ≥1400
- `drupalSettings.blockBreakpoint.breakpoints` inclui todos os IDs do grupo após cache rebuild
- CSS permanece com `@media` literais alinhados aos mesmos px (sem CSS vars)

## Aceite

1. Viewport ≤767.98px: topo/CTO mobile; Como funciona usa arte mobile no `<picture>`
2. Viewport ≥768px: variantes desktop
3. Discovery: `php vendor/bin/drush.php php:eval "print_r(array_keys(\\Drupal::service('breakpoint.manager')->getBreakpointsByGroup('default')));"` lista mobile, desktop, xs–xxl
