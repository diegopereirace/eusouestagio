# Contract: Renderização do bloco Nossa Metodologia

**Feature**: [spec.md](../spec.md)  
**Consumidor**: tema `default`  
**Produtor**: `block_content` bundle `nossa_metodologia`  
**Atualizado**: 2026-09-11 — itens editáveis (etapas + passos)

## Objetivo

Definir a saída pública estável da seção, seus breakpoints, fallbacks e restrição de placement. Este contrato não expõe API HTTP.

## Estrutura DOM

```html
<section class="block-nossa-metodologia">
  <div class="container">
    <header class="nm-header text-center text-md-end">
      <h2 class="nm-header__title">…</h2>
      <p class="nm-header__subtitle">…</p>
    </header>
    <div class="nm-etapas">
      <img class="nm-etapas__item img-fluid" src="…" alt="…" loading="lazy">
      <!-- … -->
    </div>
    <div class="nm-passos">
      <div class="nm-passo paragraph--type--metodologia-passo-p">
        <div class="nm-passo__icon"><img …></div>
        <p class="nm-passo__title">…</p>
      </div>
      <!-- setas entre passos: CSS decorativo -->
    </div>
  </div>
</section>
```

Header, `.nm-etapas` e `.nm-passos` só existem quando houver dados correspondentes.

## Contrato responsivo

| Viewport | Texto | Etapas | Passos |
|----------|-------|--------|--------|
| `<768px` | centralizado | coluna / wrap | coluna (setas ocultas ou rotacionadas) |
| `≥768px` | alinhado à direita | fila horizontal | fila horizontal com setas CSS entre itens |

Imagens devem respeitar a largura do container, manter proporção e não gerar overflow horizontal.

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Sem etapas | omitir `.nm-etapas` |
| Sem passos | omitir `.nm-passos` |
| Passo sem ícone | só título |
| Passo sem título | só ícone |
| Alt etapa vazio | `alt=""` |
| Alt ícone vazio | usar título do passo ou `alt=""` |

## Contrato editorial

- Título e subtítulo vêm dos fields de texto do bloco.
- Etapas superiores vêm de `field_image` (multi-valor).
- Passos inferiores vêm de `field_metodologia_passos` → `metodologia_passo_p` (ícone + título).
- Setas entre passos **não** são conteúdo CMS.
- Ausência de campos não produz headings, parágrafos ou imagens quebradas.
- Alterações salvas pelo editor aparecem após a invalidação normal de cache.

## Contrato de placement

- Tema: `default`
- Região: `content_full`
- Caminho: somente `<front>`
- Ordem: weight `-3`, imediatamente após `default_nossosdiferenciais` (weight `-4`)
- Rótulo administrativo não é exibido no front

## Acessibilidade e segurança

- Manter um único H2 para o título da seção.
- Sempre emitir `alt` nas imagens.
- Não inserir conteúdo editorial com `|raw`.
- O template deve preservar `title_prefix`, `title_suffix`, `attributes` e `content_attributes` do Drupal.

## Não garantido

- Transferência das imagens editoriais entre ambientes pelo `cim`.
- Crop ou otimização via image style na primeira versão.
- Exibição fora da home.
