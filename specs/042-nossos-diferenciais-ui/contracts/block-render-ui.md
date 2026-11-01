# Contract: Renderização UI — Nossos Diferenciais (042)

**Feature**: [spec.md](../spec.md) | **Data**: 2026-11-01  
**Consumidor**: visitante (Home, `/para-empresas`)  
**Produtor**: tema `default` (Twig + library `default/nossos_diferenciais`)  
**Base**: estende o contrato 004 [`block-render.md`](../../004-nossos-diferenciais/contracts/block-render.md) — esta versão é a fonte de verdade visual pós-refatoração.

## Objetivo

Contrato de apresentação estável para aceite side-by-side com o Figma. Não é API HTTP.

## Library

- Nome: `default/nossos_diferenciais`
- CSS: `themes/custom/default/assets/css/nossos-diferenciais.css`
- Attach: Twig do bloco (`attach_library`)
- Dependência sugerida: `default/bootstrap` (utilitários de grid/flex)

## Estrutura DOM esperada

```html
<section class="block-nossos-diferenciais" id="nossos-diferenciais" …>
  <div class="content">
    <div class="container">
      <header class="nd-header">
        <h2 class="nd-header__title">…</h2>          <!-- omitir se vazio -->
        <p class="nd-header__subtitle">…</p>         <!-- omitir se vazio; max-width ~663px -->
      </header>
      <div class="row align-items-center nd-grid">
        <div class="col-lg-7">
          <div class="nd-media">
            <span class="nd-media__deco-ring" aria-hidden="true"></span>
            <span class="nd-media__deco-circle" aria-hidden="true"></span>
            <span class="nd-media__deco-dot" aria-hidden="true"></span>
            <img class="nd-media__img" alt="…" width="1090" height="591" loading="lazy">
            <!-- ou .nd-media__placeholder se sem field_image -->
          </div>
        </div>
        <div class="col-lg-5">
          <div class="nd-list">
            <!-- Drupal field wrappers com display:contents -->
            <div class="nd-item d-flex align-items-start gap-3">
              <div class="nd-item__icon">…</div>
              <div class="nd-item__body">
                <h3 class="nd-item__title">…</h3>
                <p class="nd-item__desc">…</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
```

## Decisão de colagem (2026-11-01)

- A imagem do CMS (`field_image`, proporção ~1324×718) **já é a colagem completa** (âncora + 2 fatias embutidas no asset editorial).
- **Não** há `.nd-media__slice` nem PNGs de fatia no tema — fatias separadas no markup foram rejeitadas para não duplicar arte já presente no upload.
- Decos (`.nd-media__deco-circle` anel verde, `.nd-media__deco-dot` ponto navy, `.nd-media__deco-ring` anel cinza) são `span aria-hidden` com `z-index` **atrás** da foto, posicionados em % do wrapper; anel cinza só em ≥992px.
- Colunas desktop: `col-lg-7` (colagem) + `col-lg-5` (lista); texto do item `max-width: 24rem`; ícone 72px (56px mobile).
- Âncora servida via `image_style('wide')` (1090w) + `width`/`height` para CLS; `object-fit: contain` + `aspect-ratio: 1324 / 718`.

## Tokens visuais (aceitação)

| Token | Valor / regra |
|-------|----------------|
| Título seção | Poppins 600, 30px, lh 38px, ls −0.3px, `#000000` |
| Subtítulo | Poppins 400, 14px, lh 20px; max-width ~663px |
| Colagem CMS | proporção ~1324×718 (âncora+fatias no asset); style `wide` |
| Deco círculo | anel verde ~42% do wrapper, atrás da foto |
| Deco ponto | ~49×49 navy, atrás da foto |
| Deco ring | anel cinza; só desktop ≥992px; oculto ≤575px |
| Item títulos 1/4/7… | `var(--brand-green)` + bold via `.field__item:nth-child(3n+1)` |
| Item títulos 2/5/8… | `var(--brand-navy)` + bold via `.field__item:nth-child(3n+2)` |
| Item títulos 3/6/9… | `var(--brand-orange)` + bold via `.field__item:nth-child(3n+3)` |
| Breakpoint colunas | ≥992px (`col-lg-7` / `col-lg-5`); abaixo = empilhado |
| Overflow | `overflow: hidden` na seção — zero scroll horizontal |

## Acessibilidade

- Âncora CMS: `alt` do campo; `loading="lazy"`; `decoding="async"`.
- Decos: `aria-hidden="true"`.
- Contraste dos títulos coloridos sobre fundo claro da Home.

## Não garantido

- Edição editorial das fatias embutidas no PNG (fazem parte do asset CMS; trocar a imagem troca a colagem inteira).
- Paridade pixel-perfect em zoom extremo / fontes do SO (SC cobre overflow, não DPI).
