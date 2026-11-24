# Implementation Plan: Refino visual Cadastro de Empresa

**Feature**: `066-refino-cadastro-empresa`  
**Branch/context**: espelhar UI de `059` + banner CMS (reversão do hero estático)

## Approach

1. Spec Kit artifacts + feature ativa em `.specify/feature.json` e `.cursor/rules/specify-rules.mdc`.
2. Twig page renderiza `page.banner` (não assets estáticos do tema).
3. CSS library + body class (paridade visual do formulário com candidato).
4. Refatorar markup de `EmpresaRegistrationForm` (helper `sectionHeading`, cartões, submit).
5. Ativar `block.block.default_views_block__banners_block_3` + filtro de título no display `block_3` + `drush cex`.
6. `drush cr` + smoke visual.

## Key files

- `themes/custom/default/templates/layout/page--cadastro--empresa.html.twig`
- `themes/custom/default/templates/views/views-view-field--banners--block-3--nothing.html.twig`
- `themes/custom/default/src/css/components/cadastro-empresa.css` → `dist/`
- `themes/custom/default/default.libraries.yml`, `default.theme`
- `modules/custom/.../Form/EmpresaRegistrationForm.php`
- `config/sync/block.block.default_views_block__banners_block_3.yml`
- `config/sync/views.view.banners.yml`
