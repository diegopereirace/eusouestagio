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
        <div class="col-lg-6">
          <div class="nd-media">
            <!-- decos: .nd-media__deco-* ou pseudo; aria-hidden -->
            <img class="nd-media__img" alt="…" loading="lazy">  <!-- ou .nd-media__placeholder -->
            <img class="nd-media__slice …" aria-hidden="true" alt="">  <!-- 0..n; ocultas no mobile estreito -->
          </div>
        </div>
        <div class="col-lg-6">
          <div class="nd-list">
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

## Ajuste pós-implementação (2026-11-01)

- A imagem do CMS (`field_image`, 1324×718) **já é a colagem completa** (âncora + 2 fatias). Não existem `.nd-media__slice` nem PNGs de fatia no tema; o DOM acima de fatias é **obsoleto**.
- Decos (`.nd-media__deco-circle` anel verde, `.nd-media__deco-dot` ponto navy, `.nd-media__deco-ring` anel cinza) são `span aria-hidden` atrás/à frente da imagem, posicionados em % do wrapper.
- Colunas desktop: `col-lg-7` (colagem) + `col-lg-5` (lista); texto do item `max-width: 24rem`; ícone 72px (56px mobile).

## Tokens visuais (aceitação)

| Token | Valor / regra |
|-------|----------------|
| Título seção | Poppins 600, 30px, lh 38px, ls −0.3px, `#000000` |
| Subtítulo | Poppins 400, 14px, lh 20px; max-width ~663px |
| Âncora | ~391×350 (proporção), `object-fit` contido |
| Fatias | ~125×358; só desktop / tablet largo |
| Deco círculo | ~278×278 verde (cortado / anel conforme Figma) |
| Deco ponto | ~49×49 navy |
| Item títulos 1/4/7… | `var(--brand-green)` + bold |
| Item títulos 2/5/8… | `var(--brand-navy)` + bold |
| Item títulos 3/6/9… | `var(--brand-orange)` + bold |
| Breakpoint colunas | ≥992px (`col-lg-6`); abaixo = empilhado |
| Overflow mobile | zero scroll horizontal causado pela seção |

## Acessibilidade

- Âncora CMS: `alt` do campo; `loading="lazy"`.
- Fatias e decos: `aria-hidden="true"` (e `alt=""` em `<img>` decorativos).
- Contraste dos títulos coloridos sobre fundo claro da Home.

## Não garantido

- Presença dos PNG de fatia em ambientes sem o deploy de código do tema.
- Edição editorial das fatias (não são CMS).
- Paridade pixel-perfect em zoom extremo / fontes do SO (SC cobre overflow, não DPI).
