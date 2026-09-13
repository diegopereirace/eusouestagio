# Contract: Renderização do bloco O que fazemos

**Feature**: [spec.md](../spec.md)  
**Consumidor**: tema `default`  
**Produtor**: `block_content` bundle `o_que_fazemos_bt`

## Objetivo

Definir a saída pública estável da seção, breakpoints, fallbacks e placement.

## Estrutura DOM

```html
<section class="block-o-que-fazemos-bt">
  <div class="container">
    <header class="oqf-header text-center">
      <h2 class="oqf-header__title">O que fazemos</h2>
      <p class="oqf-header__subtitle">…</p>
    </header>
    <div class="row oqf-cards">
      <div class="col-12 col-md-4 oqf-card">
        <div class="oqf-card__header">RECRUTAMENTO ESTRATÉGICO</div>
        <div class="oqf-card__body">
          <ul class="oqf-card__items">…</ul>
        </div>
      </div>
      <div class="col-12 col-md-4 oqf-card oqf-card--middle">
        <div class="oqf-card__header">GESTÃO</div>
        <div class="oqf-card__body">
          <div class="row">
            <div class="col-6"><ul>…</ul></div>
            <div class="col-6"><ul>…</ul></div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4 oqf-card">…</div>
    </div>
  </div>
</section>
```

## Contrato responsivo

| Viewport | Cards | Itens do meio |
|----------|-------|---------------|
| `<768px` | empilhados (`col-12`) | duas colunas internas (`col-6`) |
| `≥768px` | três colunas (`col-md-4`) | duas colunas internas |

## Contrato de cor (CSS, não CMS)

| Posição | Cor |
|---------|-----|
| 1º card | `--brand-navy` |
| 2º card | `--brand-green` |
| 3º card | `--brand-orange` |

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Sem título | omitir H2 |
| Sem descrição | omitir subtítulo |
| Sem cards | omitir `.oqf-cards` |
| Card sem itens | só header |
| Card sem título | só body (se houver itens) |

## Contrato de placement

- Tema: `default`
- Região: `content_full`
- Caminho: somente `<front>`
- Weight: `-2` (após `default_nossametodologia` weight `-3`)
- UUID: `a7c3e9f1-2b4d-4e8a-9c6f-1d2e3f4a5b6c`

## Acessibilidade e segurança

- Um único H2 para o título da seção.
- Não usar `|raw` em conteúdo editorial.
- Preservar `title_prefix`, `title_suffix`, `attributes`, `content_attributes`.
