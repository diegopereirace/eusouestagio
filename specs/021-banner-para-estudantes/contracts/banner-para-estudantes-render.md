# Contract: Renderização do Banner Para Estudantes

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-29  
**Consumidor**: tema `default` (região `banner` em `/para-estudantes`)  
**Produtor**: View `banners` display `block_para_estudantes` + Twig (copy/CTAs) + nó mídia (ilustração)

## Objetivo

DOM estável do hero da jornada do estudante: **duas colunas** (copy HTML + CTAs | ilustração), container ≤1280px, paddings Figma, **sem carrossel**. Não é API HTTP.

## Estrutura DOM esperada

```html
<div class="hero-estudantes-wrapper">
  <div class="banner-para-estudantes row align-items-center">
    <div class="col-12 col-lg-6 banner-para-estudantes__copy">
      <span class="banner-para-estudantes__badge">Plataforma exclusiva para estudantes</span>
      <h1 class="banner-para-estudantes__title">Seu futuro profissional começa aqui.</h1>
      <p class="banner-para-estudantes__lead">
        Conecte-se com as melhores empresas, descubra oportunidades alinhadas ao seu perfil
        e dê o primeiro passo para uma carreira de sucesso.
      </p>
      <div class="banner-para-estudantes__ctas d-flex gap-3 flex-wrap">
        <a class="banner-para-estudantes__btn banner-para-estudantes__btn--primary"
           href="/para-estudantes#main-content">Encontrar minha vaga</a>
        <a class="banner-para-estudantes__btn banner-para-estudantes__btn--secondary"
           href="/cadastro/candidato">Criar meu perfil</a>
      </div>
    </div>
    <div class="col-12 col-lg-6 banner-para-estudantes__media text-center text-lg-end">
      <picture>
        <source media="(max-width: 767.98px)" srcset="…">
        <img class="banner-para-estudantes__img img-fluid" src="…" alt="…" loading="eager" fetchpriority="high">
      </picture>
    </div>
  </div>
</div>
```

Notas:
- Zero banners publicados → **omitir** o wrapper (sem faixa vazia).
- Sem imagem → omitir coluna `__media` (ou picture); copy permanece.
- Mobile (&lt;992px): colunas empilham (copy acima); sem scroll horizontal causado pelo hero.
- Um único item (pager 1); sem controles de carrossel.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente sob `.hero-estudantes-wrapper` / classes do display |
| Library | `default/banner_para_estudantes` → `assets/css/banner-para-estudantes.css` |
| Container | `max-width: 1280px`; padding `64px 40px 48px 40px` |
| CTA primário | laranja `#FD7B1A` (token local; não mudar global) |
| CTA secundário | outline (navy / tokens do tema) |
| Tipografia | Poppins / tokens do tema; título navy; lead cinza secundário |

**Proibido**: alterar estilos de home, Quem Somos, Para Empresas, Contato ou rodapé via seletores globais desta feature.  
**Proibido**: carrossel Bootstrap neste display.

## Contrato de CTAs

| Rótulo | `href` |
|--------|--------|
| Encontrar minha vaga | `/para-estudantes#main-content` |
| Criar meu perfil | `/cadastro/candidato` |

## Convivência

- `banners-block_1` **desativado** (`status: false`) — não aparece em `/para-estudantes`.
- Displays `block_home` / `block_quem_somos` / `block_para_empresas` inalterados.
