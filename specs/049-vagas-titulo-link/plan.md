# Implementation Plan: Link no título das vagas em `/vagas`

**Feature**: [spec.md](spec.md) · **Branch**: `049-vagas-titulo-link`

## Summary

Envolver o texto do título do card vertical de `/vagas` com um `<a href="{{ vaga_url }}">` no Twig já existente e ajustar CSS para o link herdar a tipografia do `.vaga-lista__title`. Sem Views, sem hooks de update, sem `cex`.

## Technical Context

| Item | Valor |
|------|--------|
| Stack | Drupal 11, tema `default`, View `vagas` `page_1` |
| Template | `themes/custom/default/templates/views/views-view-field--vagas--page-1--nothing.html.twig` |
| CSS | `themes/custom/default/assets/css/components/vagas-lista-vertical.css` |
| URL | `path('entity.node.canonical', {'node': node.id})` → Pathauto `/para-estudantes/...` |
| Deploy | `git pull` → `drush cr` |

## Constitution Check

- SDD: spec + plan + tasks + contract + quickstart nesta pasta.
- Não toca `core/` / `vendor/`.
- Sem config estrutural → sem `cex`/`updb` (documentado no quickstart).
- Clean URLs: usa canonical + Pathauto existente.

## Architecture

```
/vagas (View page_1)
  └─ field nothing → Twig card
       ├─ h3.vaga-lista__title > a[href=vaga_url]  ← NOVO
       └─ a.vaga-lista__cta[href=vaga_url]         ← já existe
```

## Implementation Approach

1. Twig: link no título com `title_text` e `vaga_url`.
2. CSS: `.vaga-lista__title a` — `color: inherit`, `text-decoration: none`, hover underline ou cor marca, `:focus-visible` outline.
3. `drush cr` local.
4. Validar no browser `/vagas`.

## Risks

| Risco | Mitigação |
|-------|-----------|
| Estilo de link azul default do UA | CSS `color: inherit` + hover explícito |
| Nested interactive elements | Só o título e o CTA são links separados (ok) |

## Out of scope leftovers

- Não atualizar contract 028 in-place; contract desta feature documenta o delta.
