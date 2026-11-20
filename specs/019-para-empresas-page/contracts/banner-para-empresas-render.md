# Contract: Renderização do Banner Para Empresas

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-25  
**Consumidor**: tema `default` (região `banner` em `/para-empresas`)  
**Produtor**: View `banners` display `block_para_empresas` + Twig (2 CTAs) + nodes mídia (imagem completa por slide)

## Objetivo

DOM estável do hero B2B: **uma imagem por slide** em carrossel **full-bleed** (100% da largura da região `banner`), **dois links CTA** em overlay, setas laterais quando multi-slide. Não é API HTTP.

## Estrutura DOM esperada

```html
<div class="banner-para-empresas-wrapper">
  <div class="banner-para-empresas__inner"><!-- full-bleed: sem max-width / sem padding lateral -->
    <section class="banner-para-empresas" aria-label="Para empresas">
      <h1 class="visually-hidden">Encontre os melhores talentos para sua empresa.</h1>
      <div id="banner-carousel-para-empresas" class="carousel slide …">
        <div class="carousel-inner">
          <div class="carousel-item active">…<img class="banner-para-empresas__img">…</div>
          <div class="carousel-item">…<img>…</div>
        </div>
        <!-- controles/indicators só se multi -->
      </div>
      <div class="banner-para-empresas__ctas d-flex gap-3 flex-wrap">
        <a class="banner-para-empresas__btn banner-para-empresas__btn--primary"
           href="/cadastro/empresa">Cadastrar Empresa</a>
        <a class="banner-para-empresas__btn banner-para-empresas__btn--secondary"
           href="/painel/empresa/vagas/nova">Contrate o Estágio Certo</a>
      </div>
    </section>
  </div>
</div>
```

Notas:
- Zero banners publicados → **omitir** o wrapper (sem placeholder 1800×600).
- Um banner → classe single; sem controles quebrados.
- Slide sem imagem → omitir item.
- A arte do slide DEVE ser o frame completo do Figma (copy + visual), não só a coluna direita.
- Desktop: CTAs em overlay absoluto sobre o carrossel; mobile (&lt;992px): CTAs empilham abaixo da imagem; sem scroll horizontal causado pelo hero.
- Multi-slide: setas laterais (prev/next) visíveis e utilizáveis nas bordas do carrossel.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente sob `.banner-para-empresas-wrapper` (+ liberação da região `banner` em `body.node--type-para-empresas`) |
| Library | `default/banner_para_empresas` → `assets/css/banner-para-empresas.css` |
| Container | full-bleed (`width: 100%`; sem `max-width` no inner); imagem `aspect-ratio: 1200 / 433` |
| Controles | setas laterais com contraste (fundo circular escuro) quando multi |
| CTA primário | laranja `#FD7B1A` (token local; não mudar global) |
| Tipografia dos botões | Poppins / tokens do tema |

**Proibido**: alterar estilos de home, Quem Somos, Contato ou rodapé via seletores globais desta feature.  
**Proibido**: reconstruir tag/título/descrição em HTML ao lado da imagem (ficam na arte do slide).

## Contrato de CTAs

| Rótulo | `href` |
|--------|--------|
| Cadastrar Empresa | `/cadastro/empresa` |
| Contrate o Estágio Certo | `/painel/empresa/vagas/nova` |

## Convivência

- `banners-block_1` **não** lista `/para-empresas`.
- Display `block_home` / `block_quem_somos` inalterados.
