# Contract: Renderização da seção Missão e Visão (Node Quem Somos)

**Feature**: [spec.md](../spec.md)  
**Consumidor**: tema `default`  
**Produtor**: Node bundle `quem_somos` (fields `field_imagem_desktop`, `field_text_simple_long`, `field_text_simple_long_2`)

## Objetivo

Definir a saída pública estável da faixa Missão | Visão no Node, breakpoints, fallbacks e convivência com o bloco 011.

## Estrutura DOM

```html
<article class="node node--type-quem-somos …">
  <div class="node__content …">
    <div class="container">
      <section class="sobre-nos …">…</section>
    </div>

    <section class="quem-somos-missao-visao" style="background-image: url('…');">
      <!-- ::before = overlay (CSS) -->
      <div class="quem-somos-missao-visao__inner">
        <div class="container px-4 px-lg-5">
          <div class="row">
            <div class="col-md-6 text-center text-white quem-somos-missao-visao__col quem-somos-missao-visao__col--first">
              <h2 class="quem-somos-missao-visao__title">Nossa Missão</h2>
              <p class="quem-somos-missao-visao__text">…</p>
            </div>
            <div class="col-md-6 text-center text-white quem-somos-missao-visao__col">
              <h2 class="quem-somos-missao-visao__title">Nossa Visão</h2>
              <p class="quem-somos-missao-visao__text">…</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</article>
```

Notas:
- Classe raiz obrigatória: `quem-somos-missao-visao` (FR-016).
- `style="background-image: …"` só quando houver URI de mídia.
- Títulos H2 fixos em pt-BR; um nível de heading na seção (sem H1).
- Seção **fora** do `.container` que envolve Sobre nós.
- Preservar `attributes` / `content_attributes` / title hooks do template de node.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente seletores sob `.quem-somos-missao-visao` |
| Full-bleed | breakout CSS para 100% da viewport (escapar `#main.container`) |
| Altura | `min-height: 380px` |
| Fundo | `background-size: cover; background-position: center` |
| Overlay | `::before` absolute full-bleed, `rgba(15, 23, 42, 0.7)` (token local `--qs-mv-overlay`) |
| Tipografia | Poppins; título Bold/SemiBold; corpo Regular; cor branca |
| Divisória | borda sutil no 1º `.col-md-6` em viewport ≥768px; ausente no mobile |

## Contrato responsivo

| Viewport | Layout |
|----------|--------|
| ≥768px (`md`) | duas colunas `col-md-6`; divisória no 1º |
| ≤767.98px | empilhados; sem overflow-x; sem borda lateral de desktop |

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Sem Missão e sem Visão e sem imagem | **omitir** a `<section>` |
| Só Missão ou só Visão | renderizar uma coluna; sem coluna vazia |
| Sem imagem | sem `background-image` inline; cor de fallback escura + overlay |
| Texto longo | wrap natural |

## Contrato de convivência (011)

| Item | Regra |
|------|--------|
| `block.block.default_missaovisao` | `status: false` após deploy |
| Contagem visual em `/quem-somos` | **exatamente uma** faixa Missão/Visão (a do Node) |
| Outras rotas | seção do Node **não** aparece (só no conteúdo desse Node) |

## Contrato de library

- Nome: `default/quem_somos_missao_visao`
- CSS: `themes/custom/default/assets/css/quem-somos-missao-visao.css`
- Attach: no Twig `node--quem-somos.html.twig` (sempre que o template carregar; custo aceitável na página)

## Acessibilidade e segurança

- Contraste texto branco sobre overlay suficiente (SC-001 / SC-008).
- Fundo via CSS background: significado nos textos; sem `|raw` em conteúdo editorial.
- Sem JS obrigatório.

## Fora do contrato

- Banner Quem Somos (região `banner`)
- Layout Sobre nós (`.sobre-nos` / `layout_sobre_nos`)
- Markup do bloco `.block-missao-visao` (011) — não é a fonte pública desta rota
