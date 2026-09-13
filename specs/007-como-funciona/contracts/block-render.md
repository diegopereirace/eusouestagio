# Contract: Renderização do bloco Como funciona

**Feature**: [spec.md](../spec.md)  
**Consumidor**: tema `default`  
**Produtor**: `block_content` bundle `como_funciona_bt`

## Objetivo

Definir a saída pública estável da seção, breakpoints, fallbacks e placement.

## Estrutura DOM

```html
<section class="block-como-funciona-bt">
  <div class="container">
    <header class="cf-header">
      <h2 class="cf-header__title">Como funciona</h2>
      <p class="cf-header__subtitle">…</p>
    </header>
    <ol class="cf-flow">
      <li class="cf-step" data-step="1">
        <span class="cf-step__label">Entendimento da empresa</span>
      </li>
      <!-- … até data-step="7" -->
    </ol>
  </div>
</section>
```

## Contrato visual (CSS, não CMS)

| Elemento | Origem |
|----------|--------|
| Número 1–7 | `data-step` / pseudo-elemento |
| Forma chevron | CSS clip-path / camadas |
| Seta superior | pseudo-elemento |
| Cor da borda | `:nth-child` / `--cf-step-N` |

Tokens Figma de referência: flow max-width 1150px / altura ~136px; chevron ~178×129; stroke 4px; overlap ~16px.

## Contrato responsivo

| Viewport | Comportamento |
|----------|---------------|
| largo | fluxo horizontal com overlap |
| estreito | wrap em linhas ou escala reduzida; sem overflow quebrado |

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Sem título | omitir H2 |
| Sem subtítulo | omitir subtítulo |
| Sem etapas | omitir `.cf-flow` |
| Etapa sem texto | omitir o `<li>` |

## Contrato de placement

- Tema: `default`
- Região: `content_full`
- Caminho: somente `<front>`
- Weight: `-1` (após `default_oquefazemos` weight `-2`)
- UUID: `c8d4e0f2-3a5b-4c6d-8e9f-0a1b2c3d4e5f`

## Acessibilidade e segurança

- Um único H2 para o título da seção.
- Lista ordenada (`ol`) para as etapas.
- Não usar `|raw` em conteúdo editorial.
- Preservar `title_prefix`, `title_suffix`, `attributes`, `content_attributes`.
