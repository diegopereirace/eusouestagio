# Contract: Cross-filter UI (hero Home)

**Consumidor**: `themes/custom/default/assets/js/hero-search.js` + Twig `block--hero-search.html.twig`

## Comportamento

1. Form GET `/vagas` com `cursos`, `cidade`, `estado` — todos opcionais.
2. Autocomplete de cada input envia constraints do outro campo nos query args do endpoint.
3. Cache client: chave `path|q|cidade|estado|curso`.
4. Delay AC: 80ms. Probe enable/disable do oposto: debounce 300ms; no `select`, probe imediato (`q` vazio + constraint).
5. Abort XHR anterior (AC e probe).
6. Se probe retornar `[]` com constraint ativa → `disabled` no oposto, limpa valor (+ `estado` se cidade), mostra `.hero-search__hint`.
7. Se origem esvaziar → remove `disabled`, esconde hint.
8. Sem JS: campos sempre editáveis; submit funciona.

## Copy hints

- Curso: `Sem cursos com vagas nesta cidade.`
- Cidade: `Sem cidades com vagas para este curso.`
