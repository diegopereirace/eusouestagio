# Contract: Renderização Layout de Contato (`/contato`)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-23  
**Consumidor**: tema `default` (Twig) + visitante anônimo  
**Produtor**: `block_content` bundle `layout_contato` + placement `default_layoutcontato` + webform `contato`

## Objetivo

Contrato de apresentação estável para `/contato` frente ao Figma (form + atalhos | painel ilustração). Não é API HTTP.

## Estrutura DOM esperada

```html
<div class="block block-layout-contato ...">
  <div class="layout-contato container py-5">
    <div class="row">
      <div class="layout-contato__form-col col-12 col-lg-7">
        <h2 class="layout-contato__title">Envie sua mensagem</h2>
        <!-- webform contato: flexbox linhas 1–2; submit Enviar Mensagem -->
        <div class="layout-contato__webform">…</div>
        <div class="layout-contato__shortcuts d-flex gap-4 mt-4">
          <a class="layout-contato__shortcut" href="mailto:contato@eusouestagio.com">… E-mail …</a>
          <a class="layout-contato__shortcut" href="https://wa.me/5561999999999">… WhatsApp …</a>
        </div>
      </div>
      <div class="layout-contato__media-col col-12 col-lg-5">
        <div class="layout-contato__panel">
          <img class="img-fluid" src="…" alt="…">
          <!-- copy de apoio só se não estiver embutido na arte -->
        </div>
      </div>
    </div>
  </div>
</div>
```

Notas:
- Escopo CSS obrigatório: `.block-layout-contato` (FR-012).
- Labels de bloco Drupal ocultos (`label_display: 0`).
- Título visual da seção = H2 “Envie sua mensagem” (Poppins, negrito); título de página Drupal oculto.
- Omitir webform ou `<img>` quando o campo correspondente estiver vazio; sem fatal / imagem quebrada.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente seletores sob `.block-layout-contato` |
| Library | `default/layout_contato` → `assets/css/layout-contato.css` |
| Painel direito | fundo `#E5EEFF`; cantos arredondados; padding interno |
| Submit | fundo `#FD7B1A`; texto legível; rótulo **Enviar Mensagem** |
| Inputs | aparência Bootstrap `form-control` / `form-select`; radius ≈8px |
| Título | Poppins, negrito |

**Proibido**: alterar estilos de home, Quem Somos ou rodapé via seletores globais desta feature.

## Contrato do webform (campos)

| Ordem | Campos | Layout desktop |
|-------|--------|----------------|
| Linha 1 | `nome_completo` \| `email` | lado a lado (`webform_flexbox`) |
| Linha 2 | `telefone` \| `categoria` | lado a lado |
| Linha 3 | `assunto` | full width |
| Linha 4 | `mensagem` | full width |
| Ação | Enviar Mensagem | botão primário laranja |

Placeholders: conforme [data-model.md](../data-model.md). Confirmação pós-envio: “Mensagem enviada!” / sucesso atual.

## Contrato responsivo

| Viewport | Layout |
|----------|--------|
| ≥992px (`lg+`) | duas colunas 7/12 \| 5/12 |
| &lt;992px | empilha: formulário acima, painel abaixo; sem overflow-x do bloco |
| Atalhos mobile | usáveis (wrap / empilhados); sem overflow |

## Contrato de navegação (atalhos)

| Item | Destino |
|------|---------|
| E-mail | `mailto:contato@eusouestagio.com` |
| WhatsApp | `https://wa.me/5561999999999` |

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Imagem vazia | painel sem `<img>` quebrada |
| Webform vazio | omitir área do formulário |
| Validação falha | erros Webform; campos válidos preservados |
| Erro Twig | proibido — página estável |

## Contrato de deploy / idempotência

- Hook `custom_configs_update_11021` + configs exportadas.
- Destino: `cim` → `updb` → `cim` → `cr`.
- 2ª `updb` não duplica bloco/página nem sobrescreve editorial divergente.
- Detalhe operacional: [quickstart.md](../quickstart.md).

## Fora deste contrato

- API REST/JSON de submissions.
- Redesign de header/footer.
- Banner interno em `/contato`.
- Migração de submissions com keys legadas.
