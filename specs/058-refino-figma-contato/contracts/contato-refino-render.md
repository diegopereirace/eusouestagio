# Contract: Renderização Refino `/contato`

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-11-04  
**Consumidor**: tema `default` (Twig + CSS) + visitante anônimo  
**Produtor**: `node` bundle `contato` + alias `/contato` + webform `contato` + `hook_webform_submission_form_alter`  
**Supersede parcial**: [018 contracts/contato-node-render.md](../../018-contato-node/contracts/contato-node-render.md) nos pontos de layout/tipografia/cards abaixo; fields e deploy estrutural da 018 permanecem.

## Objetivo

Contrato de apresentação estável para `/contato` frente ao Figma (bleed + cards + tipografia). Não é API HTTP.

## Estrutura DOM esperada (desktop)

```html
<article class="node node--contato …">
  <div class="layout-contato-node …">  <!-- sem .container que corte o painel -->
    <!-- grid/flex assimétrico -->
    <div class="layout-contato-node__form-col">
      <h2 class="layout-contato-node__title ese-section-title">Envie sua mensagem</h2>
      <div class="layout-contato-node__webform">
        …webform contato…
        <div class="form-actions webform-actions">
          <input|button type="submit" …>Enviar Mensagem</…>
          <div class="layout-contato-node__shortcuts">
            <a class="layout-contato-node__shortcut layout-contato-node__shortcut--email" href="mailto:…">…</a>
            <a class="layout-contato-node__shortcut layout-contato-node__shortcut--whatsapp" href="https://wa.me/…" …>…</a>
          </div>
        </div>
      </div>
    </div>
    <div class="layout-contato-node__media-col">
      <div class="layout-contato-node__panel">
        <img class="layout-contato-node__image" src="…/styles/contato_painel/…" loading="lazy" decoding="async" width="{proporcional}" height="{proporcional}">
        <p class="layout-contato-node__panel-title">…</p>
        <p class="layout-contato-node__panel-text">…</p>
      </div>
    </div>
  </div>
</article>
```

Notas:

- Escopo CSS Contato: `.node--contato` / `.layout-contato-node` (FR-013).
- Shell `#main.container` pode ser liberado em `path-contato` / `:has(.node--contato)` para permitir bleed (sem `100vw`).
- Omitir webform, `<img>`, card ou textos quando field vazio.
- Imagem do painel: Image Style `contato_painel` (scale 640 + WebP); `loading="lazy"`; `decoding="async"`; `width`/`height` HTML proporcionais ao asset após scale (não hardcode 640×720); sem URI crua (FR-010a).
- Deploy destino: `cim` obrigatório para importar `image.style.contato_painel`.
- **Proibido**: segundo formulário / bloco `layout_contato` na mesma resposta.

## Contrato visual

| Propriedade | Regra |
|-------------|--------|
| Library Contato | `default/layout_contato` |
| Painel ≥992px | fundo `#E5EEFF` toca a borda direita da viewport |
| Form ≥992px | margem esquerda ≈ header/container (desvio ≤8px @1440) |
| Colunas ≥992px | altura coerente na mesma linha |
| Título H2 | token [section-title-token.md](section-title-token.md) |
| Submit | `#FD7B1A`, texto branco, sem borda; ~180×56 desktop; 100% width &lt;992px |
| Card E-mail | ~280×70; borda `#C6C6CD`; valor `#45464D`; `mailto:` |
| Card WhatsApp | ~214×70; mesma linguagem; `https://wa.me/{digits}` |
| Ações ≥992px | preferir fileira única; wrap permitido sem overflow-x se a coluna estreitar |
| Inputs linha | altura ~57px; borda fina; radius suave |
| &lt;992px | form acima; painel 100%; ações wrap; sem overflow-x |

## Contrato de campos (inalterado vs 018)

| UI | Field | Comportamento |
|----|-------|---------------|
| Formulário | `webform` | webform `contato` |
| Card E-mail | `field_email` | omit se vazio |
| Card WhatsApp | `field_phone_wpp` | DDI 55 se 10–11 dígitos; omit se vazio |
| Imagem | `field_imagem` | omit se vazio; style `contato_painel` + lazy + decoding async; sem rewrite de arquivo (057) |
| Título / texto painel | `field_text_simple` / `field_text_simple_long` | omit se vazio |

## Aceite

US1–US5, US7 / SC-001–007 → este contrato. US6 / SC-008 (token **não** vaza) → [section-title-token.md](section-title-token.md).
