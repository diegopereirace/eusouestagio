# Contract: Renderização UI — Nossa Metodologia (043)

**Feature**: [spec.md](../spec.md) | **Data**: 2026-11-01  
**Consumidor**: visitante (Home, `/para-empresas`)  
**Produtor**: tema `default` (Twig + library `default/nossa_metodologia`)  
**Base**: estende o contrato 005 [`block-render.md`](../../005-nossa-metodologia/contracts/block-render.md) — esta versão é a fonte de verdade visual pós-refatoração.

## Objetivo

Contrato de apresentação estável para aceite side-by-side com o Figma. Não é API HTTP.

## Library

- Nome: `default/nossa_metodologia`
- CSS: `themes/custom/default/assets/css/nossa-metodologia.css`
- Attach: Twig do bloco (`attach_library`)
- Dependência sugerida: `default/bootstrap` (utilitários de texto/flex)

## Estrutura DOM esperada

```html
<section class="block-nossa-metodologia" id="nossa-metodologia" …>
  <div class="content">
    <div class="container">
      <header class="nm-header text-end">
        <h2 class="nm-header__title">…</h2>       <!-- omitir se vazio -->
        <p class="nm-header__subtitle">…</p>      <!-- omitir se vazio -->
      </header>

      <div class="nm-etapas" role="list">         <!-- omitir se sem etapas -->
        <div class="nm-etapas__item" role="listitem">
          <img class="nm-etapas__image img-fluid" alt="…" loading="lazy">
        </div>
        <!-- conectores entre etapas: CSS ::after decorativo -->
      </div>

      <div class="nm-passos">                     <!-- omitir se sem passos -->
        <!-- Drupal field wrappers -->
        <div class="nm-passo …">
          <div class="nm-passo__icon">…</div>
          <p class="nm-passo__title">…</p>
        </div>
        <!-- setas entre passos: CSS ::after decorativo -->
      </div>
    </div>
  </div>
</section>
```

## Tokens visuais (aceitação)

| Token | Valor / regra |
|-------|----------------|
| Título seção | Poppins 600, 30px, lh 38px, ls −0.3px, `#000000` (não navy) |
| Cabeçalho desktop | alinhado à direita (`text-md-end`); título e subtítulo mesma margem direita |
| Cabeçalho mobile | centralizado (`text-center`) |
| Wrapper etapas (≥992px) | `max-width: 770px`; centralizado; altura suficiente ~48px para chevrons |
| Wrapper passos (≥992px) | `max-width: 1160px`; centralizado; altura aproximada ~154px |
| Wrappers (&lt;992px) | largura efetiva 100% do container útil |
| Setas verdes | centradas verticalmente em relação aos círculos (não ao rótulo) |
| Breakpoint larguras | `@media (min-width: 992px)` — Mobile First |
| Mobile linhas | preferir empilhamento em coluna; sem overflow horizontal |
| Tablet wrap passos | setas horizontais omitidas quando o wrap quebra o fluxo |
| Overflow | sem scroll horizontal causado pela seção |

## Acessibilidade

- Imagens de etapa/passo: `alt` do campo; `loading="lazy"`.
- Setas e conectores: decorativos (`pointer-events: none`; não anunciados por leitores de tela).
- Contraste do título `#000000` sobre fundo claro.
- Um único H2 para o título da seção.

## Placement (inalterado)

- Home: `block.block.default_nossametodologia` — `<front>`, weight `-3`
- Para empresas: `block.block.default_nossametodologiaparaempresas` — `/para-empresas`, weight `1`
- Mesma entidade UUID; `label_display: '0'`

## Não garantido

- Paridade pixel-perfect em zoom extremo / fontes do SO (SC cobre overflow e checklist ≥90%, não DPI).
- Edição editorial de setas/conectores (permanecem apresentação).
