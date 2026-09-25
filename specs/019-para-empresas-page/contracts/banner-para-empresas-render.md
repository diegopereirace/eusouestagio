# Contract: Renderização do Banner Para Empresas

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-25  
**Consumidor**: tema `default` (região `banner` em `/para-empresas`)  
**Produtor**: View `banners` display `block_para_empresas` + Twig (copy fixa) + 2 nodes mídia

## Objetivo

DOM estável do hero B2B: duas colunas (copy/CTA | carrossel de imagens), container ≤1200px, alinhado ao Figma. Não é API HTTP.

## Estrutura DOM esperada

```html
<div class="banner-para-empresas-wrapper">
  <div class="banner-para-empresas__inner"><!-- max-width: 1200px; padding-inline ~24px -->
    <div class="row align-items-center banner-para-empresas g-lg-5"><!-- gap ~48px -->
      <div class="col-12 col-lg-6 banner-para-empresas__copy">
        <span class="banner-para-empresas__tag">SOLUÇÕES CORPORATIVAS</span>
        <h1 class="banner-para-empresas__title">
          Encontre os melhores talentos para sua empresa.
        </h1>
        <div class="banner-para-empresas__ctas d-flex gap-3 flex-wrap">
          <a class="banner-para-empresas__btn banner-para-empresas__btn--primary"
             href="/cadastro/empresa">Cadastrar Empresa</a>
          <a class="banner-para-empresas__btn banner-para-empresas__btn--secondary"
             href="/painel/empresa/vagas/nova">Contrate o Estágio Certo</a>
        </div>
      </div>
      <div class="col-12 col-lg-6 banner-para-empresas__media">
        <div id="banner-carousel-para-empresas" class="carousel slide …">
          <div class="carousel-inner">
            <div class="carousel-item active">…<img>…</div>
            <div class="carousel-item">…<img>…</div>
          </div>
          <!-- controles/indicators só se multi -->
        </div>
      </div>
    </div>
  </div>
</div>
```

Notas:
- Zero banners publicados → **omitir** o wrapper (sem placeholder 1800×600).
- Um banner → classe single; sem controles quebrados.
- Slide sem imagem → omitir item.
- Mobile (&lt;992px): copy empilha **acima** da mídia; sem scroll horizontal causado pelo hero.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente sob `.banner-para-empresas-wrapper` |
| Library | `default/banner_para_empresas` → `assets/css/banner-para-empresas.css` |
| Container | `max-width: 1200px` no inner |
| CTA primário | laranja `#FD7B1A` (token local; não mudar global) |
| Tipografia | Poppins / tokens do tema |

**Proibido**: alterar estilos de home, Quem Somos, Contato ou rodapé via seletores globais desta feature.

## Contrato de CTAs

| Rótulo | `href` |
|--------|--------|
| Cadastrar Empresa | `/cadastro/empresa` |
| Contrate o Estágio Certo | `/painel/empresa/vagas/nova` |

## Convivência

- `banners-block_1` **não** lista `/para-empresas`.
- Display `block_home` / `block_quem_somos` inalterados.
