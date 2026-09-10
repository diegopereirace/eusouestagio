# Plano de implementação: 003-vagas-destaque-home

## Summary

Reestilizar o bloco Home `views_block:vagas-block_1` no visual anexo1 e expor ícone por curso via `field_icone_fa` (Font Awesome).

## Technical approach

1. Config CM: storage + field instance + form/view display do termo `curso`.
2. Twig: reescrever `views-view-field--vagas--block-1--nothing.html.twig`.
3. CSS: regras sob `.css-vagas-home .item-vaga--destaque`.
4. View YAML: header/footer links e título.
5. Update hook em `custom_configs`: seed de ícones nos termos conhecidos (TI, Design, Administração, Marketing).

## Files

- `config/sync/field.storage.taxonomy_term.field_icone_fa.yml`
- `config/sync/field.field.taxonomy_term.curso.field_icone_fa.yml`
- `config/sync/core.entity_form_display.taxonomy_term.curso.default.yml`
- `config/sync/core.entity_view_display.taxonomy_term.curso.default.yml`
- `config/sync/views.view.vagas.yml`
- `themes/custom/default/templates/views/views-view-field--vagas--block-1--nothing.html.twig`
- `themes/custom/default/assets/css/style.css`
- `modules/custom/custom_configs/custom_configs.install`
