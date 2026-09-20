# Contract: Renderização do bloco Missão e Visão

**Feature**: [spec.md](../spec.md)  
**Consumidor**: tema `default`  
**Produtor**: `block_content` bundle `missao_visao` (+ paragraphs `missao_visao_item_p`)

## Objetivo

Definir a saída pública estável da faixa Missão | Visão, breakpoints, fallbacks e placement.

## Estrutura DOM

```html
<section class="block block-missao-visao" style="background-image: url('…');">
  <!-- ::before = overlay (CSS) -->
  <div class="block-missao-visao__inner">
    <div class="container px-4 px-lg-5">
      <div class="row">
        <div class="col-md-6 text-center text-white block-missao-visao__col block-missao-visao__col--first">
          <h2 class="block-missao-visao__title">Nossa Missão</h2>
          <p class="block-missao-visao__text">…</p>
        </div>
        <div class="col-md-6 text-center text-white block-missao-visao__col">
          <h2 class="block-missao-visao__title">Nossa Visão</h2>
          <p class="block-missao-visao__text">…</p>
        </div>
      </div>
    </div>
  </div>
</section>
```

Notas:
- Classe raiz obrigatória: `block-missao-visao` (FR-008).
- `style="background-image: …"` só quando houver URI de mídia.
- Títulos usam heading semântico (H2) por item; um único nível na seção (sem H1).
- Preservar `title_prefix`, `title_suffix`, `attributes`, `content_attributes` do tema de bloco.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente seletores sob `.block-missao-visao` |
| Altura | `min-height: 380px` |
| Fundo | `background-size: cover; background-position: center` |
| Overlay | `::before` absolute full-bleed, cor azul/escuro translúcida (`--mv-overlay`) |
| Tipografia | Poppins; título peso maior; texto peso menor; cor branca |
| Divisória | borda sutil no 1º `.col-md-6` em viewport ≥768px; ausente no mobile |

## Contrato responsivo

| Viewport | Layout |
|----------|--------|
| ≥768px (`md`) | duas colunas `col-md-6` lado a lado; divisória no 1º |
| ≤767.98px | empilhados; sem overflow-x; sem borda lateral de desktop |

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Sem itens | omitir `.row` / grade; sem erro Twig |
| 1 item | renderizar uma coluna; sem forçar coluna vazia |
| Sem título no item | omitir H2 daquele item |
| Sem descrição | omitir `<p>` daquele item |
| Sem imagem | sem `background-image` inline; cor de fallback escura + overlay |
| Sem imagem e sem itens | markup mínimo ou seção omitida — página não quebra |

## Contrato de placement

- Tema: `default`
- Região: `content_full`
- Caminho: somente `/quem-somos`
- Weight: `0`
- UUID do conteúdo: `b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e`
- Config: `block.block.default_missaovisao`

## Contrato de library

- Nome: `default/missao_visao`
- CSS: `themes/custom/default/assets/css/block-missao-visao.css`
- Attach: no Twig do bloco (sempre que o template renderizar)

## Acessibilidade e segurança

- Contraste texto branco sobre overlay suficiente (SC-001 / SC-007).
- `alt` da imagem de fundo: se a imagem for só decorativa via CSS background, o significado está nos textos; se houver `<img>` residual, fornecer alt adequado.
- Não usar `|raw` em conteúdo editorial.
- Sem JS obrigatório.

## Fora do contrato

- Banner Quem Somos (região `banner`)
- Layout Sobre nós (`node--quem-somos`)
- Exibição em `<front>` ou outras rotas
