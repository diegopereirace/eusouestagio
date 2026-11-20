# Contract: Renderização Listagem Vertical — /vagas

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-10-04  
**Consumidor**: visitante em `/vagas` (abaixo do Hero Search)  
**Produtor**: View `vagas` display `page_1` + Twigs do tema `default` + library `vagas_lista_vertical`

## Objetivo

Contrato de apresentação da listagem principal de vagas em coluna vertical de cards brancos. **Não** se aplica a Home (`block_1`), similares (`block_2`) nem Para Estudantes (`block_3`).

## Estrutura DOM esperada (lista)

```html
<div class="view view-vagas view-id-vagas view-display-id-page_1 css-vagas-page container …">
  <!-- header da View (ex.: “Vagas” / “Resultado - Vagas”) permanece -->
  <div class="view-content vagas-lista">
    <!-- cada row: sem col-md-6 col-lg-4 -->
    <div class="views-row">
      <article class="item-vaga item-vaga--lista item-vaga--lista--destaque"><!-- modifier só se destaque -->
        <div class="vaga-lista__inner">
          <span class="vaga-lista__badge"><!-- só destaque: estrela + “Destaque” --></span>
          <div class="vaga-lista__logo"><!-- img ou omitido --></div>
          <div class="vaga-lista__body">
            <h3 class="vaga-lista__title">…</h3>
            <p class="vaga-lista__meta">Empresa • Cidade, UF</p>
            <span class="vaga-lista__regime">…</span>
            <div class="vaga-lista__facts">
              <span class="vaga-lista__bolsa">…</span>
              <span class="vaga-lista__carga">…</span>
            </div>
            <ul class="vaga-lista__tags">…</ul>
            <p class="vaga-lista__published">Publicada há …</p>
          </div>
          <div class="vaga-lista__actions">
            <a class="vaga-lista__cta vaga-lista__cta--rapida|outline" href="/node/NID">…</a>
          </div>
        </div>
      </article>
    </div>
  </div>
  <!-- pager Drupal full, com AJAX da View -->
  <nav class="pager">…</nav>
</div>
```

Notas:
- Classe de escopo da página: `.css-vagas-page` (já existente).
- Card: `.item-vaga--lista` (**nunca** `.item-vaga--destaque` nesta rota).
- Modifier destaque: `.item-vaga--lista--destaque` (borda verde + badge).
- Container da lista centralizado (`max-width` ~560–720px).
- Máx. **5** cards na primeira resposta HTML.
- **Proibido** nesta feature: sidebar Figma de filtros; blocos “Recomendado para você” / “Melhore seu currículo”; contador “N vagas”; dropdown “Ordenar por”; ícone favoritar.

## Contrato de dados do card

| Região UI | Fonte | Fallback |
|-----------|-------|----------|
| Badge “Destaque” | `field_vaga_destaque == 1` | omitir |
| Logo | `field_empresa_u.entity.user_picture` | omitir / placeholder |
| Título | `title` | — |
| Empresa | `field_nome_fantasia` | omitir trecho |
| Local | `field_cidade` + `field_estados` | parcial / omitir |
| Regime | label `field_regime_t` | omitir pill |
| Bolsa | `field_text_simple` | omitir |
| Carga | label `field_horarios` | omitir |
| Tags | até 3 de `field_text_simple_multiple_2`; senão `field_cursos_t`; `+N` se sobrar | omitir lista |
| Publicação | `created` → “Publicada há …” | omitir se indisponível |
| CTA href | canonical da vaga | obrigatório |
| CTA label | destaque → “Candidatura Rápida”; senão “Ver Detalhes” | — |

## Contrato View / query

| Aspecto | Regra |
|---------|--------|
| Path | `/vagas` (`view.vagas.page_1`) |
| Ordenação padrão | destaque DESC, depois created DESC (sobre o conjunto filtrado) |
| Página | 5 itens |
| AJAX | `use_ajax: true`; pager sem full reload síncrono clássico |
| Filtros Hero | `?title=&cidade=&cursos=` continuam válidos (feature 027) |
| Isolamento | `block_1` / `block_2` / `block_3` mantêm markup/CSS laranja |

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | library `default/vagas_lista_vertical` sob `.css-vagas-page` / `.item-vaga--lista` |
| Card | fundo branco; padding generoso; radius; borda sutil ou sombra leve |
| Destaque | borda `#58A83C`; badge `#58A83C` topo-direita (estrela + texto) |
| CTA destaque | sólido escuro |
| CTA padrão | outline |
| Grid legado | ausente (`col-md-6 col-lg-4` não envolve os cards) |
| Home / PE | seletores `.item-vaga--destaque` sob `.css-vagas-home` / PE **inalterados** em comportamento |

## Anti-contratos (falha)

- Card laranja `.item-vaga--destaque` renderizado em `page_1`.
- Grid de 3 colunas na listagem.
- Badge em vaga sem destaque.
- CTA apontando para rota genérica sem a vaga.
- Alteração visual dos cards da Home / `block_3` / similares.
- Dependência de `views_infinite_scroll` para cumprir o contrato (pager AJAX padrão basta).
