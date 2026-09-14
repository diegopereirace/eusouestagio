# Contract: Renderização do Banner Quem Somos

**Feature**: [spec.md](../spec.md)  
**Consumidor**: tema `default` (região `banner` em `/quem-somos`)  
**Produtor**: View `banners` display `block_quem_somos` + Twig com copy fixa

## Objetivo

Definir DOM estável, contratos de CTA, responsividade, overflow da imagem, fallbacks e isolation CSS.

## Estrutura DOM

```html
<div class="banner-quem-somos-wrapper">
  <div class="container">
    <div class="row align-items-center banner-quem-somos">
      <div class="col-12 col-md-7 banner-quem-somos__copy">
        <span class="banner-quem-somos__tag">QUEM SOMOS</span>
        <h1 class="banner-quem-somos__title">
          <span class="banner-quem-somos__title-dark">Mais que conectar</span>
          <span class="banner-quem-somos__title-orange"> desenvolvemos futuros.</span>
        </h1>
        <p class="banner-quem-somos__lead">Somos especialistas em unir empresas e estudantes…</p>
        <div class="banner-quem-somos__ctas d-flex gap-3 flex-wrap">
          <a class="banner-quem-somos__btn banner-quem-somos__btn--primary"
             href="/para-estudantes">Conheça nossas vagas</a>
          <a class="banner-quem-somos__btn banner-quem-somos__btn--secondary"
             href="/cadastro/candidato">Cadastre-se</a>
        </div>
      </div>
      <div class="col-12 col-md-5 banner-quem-somos__media">
        <picture>
          <source media="(max-width: 767.98px)" srcset="…mobile ou desktop…">
          <img class="banner-quem-somos__img" src="…desktop…" alt="…"
               loading="eager" fetchpriority="high">
        </picture>
      </div>
    </div>
  </div>
</div>
```

- Wrapper **somente** quando houver pelo menos um resultado na View.
- Heading: `h1` aceitável no topo da página institucional; se a página já emitir H1 no content, a implementação pode usar `h2` — validar no aceite para um único H1 por página.

## Contrato de links

| CTA | `href` | Estilo |
|-----|--------|--------|
| Conheça nossas vagas | `/para-estudantes` | primário: fundo `#FD7B1A`, texto branco |
| Cadastre-se | `/cadastro/candidato` | secundário: fundo branco, texto escuro, borda |

URLs limpas; sem query obrigatória.

## Contrato responsivo

| Viewport | Comportamento |
|----------|---------------|
| ≥768px (`md+`) | grid `col-md-7` \| `col-md-5`; overflow controlado da imagem à direita |
| &lt;768px | empilha copy/CTAs acima da imagem; sem scroll horizontal causado pelo banner; overflow reduzido/desligado |

## Contrato visual

| Elemento | Regra |
|----------|-------|
| Caixa | fundo branco; cantos/sombra conforme Figma (implementação) |
| Tag | uppercase; borda/fundo sutis na paleta laranja local |
| Título | parte 1 `#0F172A`; parte 2 `#FD7B1A` |
| Tipografia | Poppins (já no tema) |
| CSS | **apenas** seletores sob `.banner-quem-somos-wrapper` |
| Tokens locais | `--bqs-orange`, `--bqs-title` (não mutar `--brand-orange` global) |
| Imagem | preservar alpha; não pintar fundo preto na coluna |

## Contrato de fallback

Ver tabela em [data-model.md](../data-model.md).

## Contrato de placement / deploy

| Item | Valor |
|------|-------|
| Bloco novo | `views_block:banners-block_quem_somos` → região `banner` → `/quem-somos` |
| Legado `block_1` | pages **sem** `/quem-somos` |
| Home `block_home` | inalterado |
| Seed | `custom_configs_update_11012` |
| Deploy | `drush cim -y` → `drush updb -y` → `drush cr` |
| Config | `drush cex` versiona storage, View e placements |

## Acessibilidade

- CTAs como `<a>` com texto visível (não só ícone).
- `alt` descritivo na imagem (do field ou seed).
- Contraste dos botões alinhado à paleta de marca.
- Preservar `attributes` / wrappers Views quando aplicável.
