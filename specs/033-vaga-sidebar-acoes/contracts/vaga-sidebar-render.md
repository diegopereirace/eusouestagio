# Contract: render da sidebar do detalhe da vaga

**Feature**: `033-vaga-sidebar-acoes`  
**Template**: `node--vagas--full.html.twig` inclui os parciais abaixo.  
**Fora**: Match, Perfil, header, coluna principal, listagem.

## Includes

| Parcial | Responsabilidade |
|---------|------------------|
| `includes/vaga-sidebar-acoes.html.twig` | Cartão de ações |
| `includes/vaga-sidebar-resumo.html.twig` | Cartão de resumo |
| `includes/vaga-share-popover.html.twig` | Compartilhar (inalterado no comportamento) |

Inclusão com `only`.

## Cartão de ações

Container: `.vaga-detalhe__acoes` (branco, borda, raio, padding — já no CSS).

### Candidato autenticado, ainda não candidatado

```html
<a href="{{ path('custom_panel.vagas_candidatar', {node: node.id}) }}"
   class="vaga-detalhe__btn-candidatar w-100 js-candidatar-vaga"
   data-node-id="…"
   data-apply-url="{{ path('custom_panel.vagas_candidatar', {node: node.id}) }}"
   data-method="POST">Candidatar-se</a>
```

O listener `.js-candidatar-vaga` cancela a navegação e envia POST para `data-apply-url` com `X-CSRF-Token`. Sucesso: texto “Candidatura Enviada”, `aria-disabled="true"`, remove `href`.

### Candidato já candidatado

Botão desabilitado “Candidatura Enviada”, mesma classe visual, sem `js-candidatar-vaga`.

### Anônimo

Link primário para `user.login` com `destination` = caminho canônico da vaga. Sem classe `js-candidatar-vaga`.

### Outros papéis autenticados

Só Compartilhar.

### Secundários (candidato ou anônimo)

```html
<div class="vaga-detalhe__acoes-secundarias d-flex gap-3 mt-3">
  <button type="button"
    class="ui-btn ui-btn--outline flex-grow-1 js-salvar-vaga"
    data-node-id="…"
    data-nid="…"
    data-toggle-url="{{ path('custom_panel.vagas_salvas_toggle') }}"
    data-method="POST">…</button>
  <!-- include share -->
</div>
```

Salvar só para role `candidato`. O JS lê `data-toggle-url` (fallback: caminho já usado hoje).

## Cartão de resumo

Container: `.vaga-detalhe__resumo` (fundo `#F4F6F9`).

Título: “RESUMO DA VAGA” via CSS (`uppercase`, navy, negrito). O texto traduzível no Twig é “Resumo da Vaga”.

Cada linha: `.vaga-detalhe__resumo-row.d-flex.justify-content-between.mb-2` com `<dt>` (rótulo) e `<dd>` (valor).

| Ordem | Rótulo | Valor |
|-------|--------|-------|
| 1 | Período | `field_vaga_periodo` ou “Não informado” |
| 2 | Bolsa Auxílio | `field_text_simple` ou “Não informado” |
| 3 | Modelo | primeiro termo de `field_regime_t` ou “Não informado” |
| 4 | Vagas | `field_vaga_quantidade` formatado `%02d` ou “Não informado” |

`hasField` antes de ler os campos novos (ambientes ainda sem o hook).

## Visual

| Elemento | Regra |
|----------|--------|
| `.vaga-detalhe__btn-candidatar` | fundo `var(--vd-orange)` (`#FD7B1A`), texto `#fff`, `font-weight: 700`, `min-height: 80px`, largura 100%, sem sublinhado |
| Outline | `.ui-btn--outline` (fundo branco, borda `#d1d5db`, texto `#374151`, ícones Font Awesome bookmark e share) |
| Resumo | `--vd-resumo-bg: #f4f6f9` |

## Proibido

- Ler `field_horarios` no resumo.
- Hardcode de “EcoConstrutora” ou “6 meses +” no Twig.
- GET na rota de candidatura.
- Renderizar Match ou Perfil.
