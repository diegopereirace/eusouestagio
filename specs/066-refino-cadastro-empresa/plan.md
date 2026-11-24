# Implementation Plan: Refino visual Cadastro de Empresa

**Feature**: `066-refino-cadastro-empresa`  
**Branch/context**: espelhar `059-cadastro-candidato-legal` (UI only)

## Approach

1. Spec Kit artifacts + feature ativa em `.specify/feature.json` e `.cursor/rules/specify-rules.mdc`.
2. Gerar hero 1280×356 webp+jpg em `themes/custom/default/assets/images/cadastro-empresa/`.
3. Twig page + CSS library + body class (paridade com candidato).
4. Refatorar markup de `EmpresaRegistrationForm` (helper `sectionHeading`, cartões, submit).
5. Desativar `block.block.default_views_block__banners_block_3` via Drush + `drush cex`.
6. `drush cr` + smoke visual.

## Key files

- `themes/custom/default/templates/layout/page--cadastro--empresa.html.twig`
- `themes/custom/default/src/css/components/cadastro-empresa.css` → `dist/`
- `themes/custom/default/default.libraries.yml`, `default.theme`
- `modules/custom/.../Form/EmpresaRegistrationForm.php`
- `config/sync/block.block.default_views_block__banners_block_3.yml`
