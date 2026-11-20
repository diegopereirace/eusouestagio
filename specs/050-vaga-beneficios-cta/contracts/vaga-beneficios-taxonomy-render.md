# Contract: render da seção Benefícios (taxonomia)

**Feature**: `050-vaga-beneficios-cta`  
**Template**: `themes/custom/default/templates/content/node--vagas--full.html.twig`  
**CSS**: reutilizar `.vaga-detalhe__beneficios` / `__beneficio-card` / `__beneficio-icon` / `__beneficio-title` em `vagas-detalhe.css`  
**Fora**: Requisitos, FAQ, sidebar, header, listagem `/vagas`, benefícios de landings

## Fonte de dados

| Antes (036) | Depois (050) |
|-------------|----------------|
| `node.field_vaga_beneficios` → paragraph `beneficio_vaga_p` (`field_image` + `field_text_simple`) | `node.field_vaga_beneficios_t` → `taxonomy_term` (`beneficios`) (`field_image` + `name`) |

## Markup esperado (por item)

Grade inalterada: `col-6 col-lg-3` (2 cols mobile / 4 desktop).

```html
<div class="col-6 col-lg-3">
  <div class="vaga-detalhe__beneficio-card">
    <div class="vaga-detalhe__beneficio-icon">
      <img src="…" alt="…" width="48" height="48" loading="lazy">
      <!-- ou fallback FA se termo sem ícone (não esperado pós-seed) -->
    </div>
    <div class="vaga-detalhe__beneficio-title">{{ term.label }}</div>
  </div>
</div>
```

## Regras

1. Seção renderiza **somente** se `field_vaga_beneficios_t` tiver ao menos um termo.
2. Ordem: ordem dos valores no campo (ordem de seleção / delta).
3. Ícone: URI do `field_image` do termo; `alt` = nome do benefício quando vazio.
4. **Não** iterar `field_vaga_beneficios` (paragraphs) no detalhe público após esta feature.
5. Paragraph template `paragraph--beneficio-vaga-p.html.twig` pode permanecer no tema (legado), mas deixa de ser caminho canônico do full.

## Form (contrato editorial)

- Widget: checkboxes dos termos do vocab `beneficios`.
- Campo paragraphs `field_vaga_beneficios`: hidden.
- Validação: required ≥ 1 termo.
