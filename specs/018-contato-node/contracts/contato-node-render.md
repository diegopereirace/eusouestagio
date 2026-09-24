# Contract: Renderização Contato Node (`/contato`)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-24  
**Consumidor**: tema `default` (Twig) + visitante anônimo  
**Produtor**: `node` bundle `contato` + alias `/contato` + webform `contato`

## Objetivo

Contrato de apresentação estável para `/contato` frente ao Figma (form + atalhos gerenciáveis | painel ilustração + textos). Não é API HTTP. Substitui o contrato da 017 baseado em `layout_contato`.

## Estrutura DOM esperada

```html
<article class="node node--contato …">
  <div class="layout-contato-node container py-5">
    <div class="row">
      <div class="layout-contato-node__form-col col-12 col-lg-7">
        <h2 class="layout-contato-node__title">Envie sua mensagem</h2>
        <div class="layout-contato-node__webform">…webform contato…</div>
        <div class="layout-contato-node__shortcuts d-flex gap-4 mt-4 flex-wrap">
          <a class="layout-contato-node__shortcut" href="mailto:…">… E-mail …</a>
          <a class="layout-contato-node__shortcut" href="https://wa.me/…" target="_blank" rel="noopener noreferrer">… WhatsApp …</a>
        </div>
      </div>
      <div class="layout-contato-node__media-col col-12 col-lg-5">
        <div class="layout-contato-node__panel">
          <img class="img-fluid layout-contato-node__image" src="…" alt="…">
          <p class="layout-contato-node__panel-title text-center">Time de especialistas</p>
          <p class="layout-contato-node__panel-text text-center">Nossa equipe responderá…</p>
        </div>
      </div>
    </div>
  </div>
</article>
```

Notas:
- Escopo CSS obrigatório: `.node--contato` e/ou `.layout-contato-node` (FR-014).
- Título visual da seção = H2 “Envie sua mensagem”; título de página Drupal oculto.
- Omitir webform, `<img>`, atalho ou textos quando o field correspondente estiver vazio; sem fatal / imagem quebrada.
- **Proibido** na mesma resposta de `/contato`: segundo formulário ou markup proveniente de `layout_contato` / bloco ID 16.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente seletores sob `.node--contato` / `.layout-contato-node` |
| Library | `default/layout_contato` → `assets/css/layout-contato.css` |
| Painel direito | fundo `#E5EEFF`; cantos/padding alinhados ao design; textos `.text-center` |
| Submit | fundo `#FD7B1A`; rótulo **Enviar Mensagem** (webform 017) |
| Título H2 | Poppins, negrito |

**Proibido**: alterar estilos de home, Quem Somos ou rodapé via seletores globais desta feature.

## Contrato de campos (origem → UI)

| UI | Field | Comportamento |
|----|-------|---------------|
| Formulário | `webform` | render do webform `contato` |
| Caixa E-mail | `field_email` | `mailto:{valor}`; omit se vazio |
| Caixa WhatsApp | `field_phone_wpp` | `https://wa.me/{dígitos}` (+ DDI 55 se 10–11 dígitos); omit se vazio |
| Imagem | `field_imagem` | `.img-fluid`; omit `<img>` se vazio |
| Título painel | `field_text_simple` | omit se vazio |
| Texto painel | `field_text_simple_long` | omit se vazio |

## Contrato responsivo

| Viewport | Layout |
|----------|--------|
| ≥992px (`lg+`) | duas colunas 7/12 \| 5/12 |
| &lt;992px | empilha: formulário acima, painel abaixo; sem overflow-x do layout |
| Atalhos mobile | usáveis (wrap); sem overflow |

## Contrato de deploy / limpeza

| Gate | Esperado |
|------|----------|
| Após `cim` → `updb` → `cim` → `cr` | `/contato` = Node Contato; um único formulário |
| Bloco ID 16 | inexistente |
| Placement `default_layoutcontato` | inativo ou ausente em `/contato` |
| Reexecução `updb` | sem Node Contato duplicado; sem sobrescrita editorial |

## Critérios de aceite (ligação)

- US1–US4 / SC-001–004, SC-008–009 → este contrato DOM/CSS/responsivo.
- US5 / SC-005 → fields editáveis no Node.
- US6 / SC-006–007 → limpeza + seed idempotente.
