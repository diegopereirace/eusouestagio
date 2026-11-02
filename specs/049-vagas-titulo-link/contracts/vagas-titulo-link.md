# Contract: Título linkado na listagem `/vagas`

**Feature**: [spec.md](../spec.md) · **Delta sobre**: [028 vagas-lista-vertical-render](../../028-vagas-lista-vertical/contracts/vagas-lista-vertical-render.md)

## DOM (trecho do card)

```html
<div class="vaga-lista__body">
  <h3 class="vaga-lista__title">
    <a href="/para-estudantes/{slug-ou-node}">Estágio em …</a>
  </h3>
  <!-- meta, facts, tags, footer inalterados -->
  <div class="vaga-lista__actions">
    <a class="vaga-lista__cta …" href="/para-estudantes/{mesmo-slug}">…</a>
  </div>
</div>
```

## Regras

| Aspecto | Regra |
|---------|--------|
| `href` do título | Idêntico ao CTA: `entity.node.canonical` |
| Escopo | Somente `page_1` / Twig `views-view-field--vagas--page-1--nothing` |
| Acessibilidade | Link focável; `:focus-visible` perceptível |
| CSS | Escopo `.css-vagas-page`; sem sublinhado em estado default |

## Não alterar

- Markup/CSS dos displays `block_1`, `block_2`, `block_3`.
- Pathauto pattern `para_estudants`.
- Permissões de view do node.
