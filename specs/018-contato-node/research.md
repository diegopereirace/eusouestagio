# Research: Contato como Node

**Data**: 2026-09-24 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **deploy repetível** (`cim` → `updb` → `cim` → `cr`), **reuso de storages em `node`**, **Clean URL `/contato`** sem formulário duplicado, e **isolamento CSS** sob `.node--contato` / `.layout-contato-node`.

---

## R1 — Substituir envelope `layout_contato` por Content Type `contato`

**Decision**: criar node type `contato` como única fonte de renderização de `/contato`; desativar placement `default_layoutcontato` e remover o conteúdo legado (ID 16 + seed UUID `d1e2f3a4-…` quando presente). Manter o *tipo* de bloco `layout_contato` no config se já exportado (Fora da spec).

**Rationale**: FR-001–003, FR-015; US1/US6; Assumptions (“substitui a renderização baseada em `layout_contato`”).

**Alternatives considered**:
- Manter bloco + fields novos no bloco — rejeitado (pedido explícito Node).
- Só desabilitar placement sem Node — rejeitado (perde canal gerenciável).
- Remover block type `layout_contato` — fora do escopo.

---

## R2 — Mapeamento canônico de fields (pedidos verbais → storages `node`)

**Decision**:

| Pedido verbal | Storage canônico em `node` | Ação |
|---------------|----------------------------|------|
| `field_formulario_contato` | `webform` | instance no bundle `contato` |
| `field_image` | `field_imagem` | instance |
| `field_text_simple_small` | `field_text_simple` | instance (título coluna direita) |
| `field_text_simple_long` | `field_text_simple_long` | instance (descrição) |
| `field_email` | `field_email` | **criar** storage + instance |
| `field_phone_wpp` | `field_phone_wpp` | **criar** storage + instance |

**Rationale**: FR-004–007; regra de reuso do projeto; Assumptions da spec.

**Alternatives considered**: criar `field_formulario_contato` / `field_image` em `node` — storage paralelo proibido; reusar storages de `block_content` — entity type incompatível.

---

## R3 — Storages novos `field_email` e `field_phone_wpp` em `node`

**Decision**: espelhar os tipos do footer (`email` card 1; `string` 255 card 1) sob `field.storage.node.field_email` e `field.storage.node.field_phone_wpp`.

**Rationale**: FR-006; equivalentes só existem em `block_content`.

**Alternatives considered**:
- Hardcode no Twig (017) — rejeitado (US3/US5 exigem gerenciável).
- Campo telephone contrib — YAGNI; footer já usa string.

---

## R4 — Reuso de `node.webform` apesar de `enforced: webform_node`

**Decision**: anexar FieldConfig `node.contato.webform` ao storage existente `field.storage.node.webform`. No view display, formatter que renderiza o formulário (padrão `webform_entity_reference_entity_view` / equivalente já usado no projeto), **não** só link.

**Rationale**: FR-004; evita storage paralelo; `webform_node` ownership do storage não impede instances em outros bundles.

**Alternatives considered**:
- Bundle `webform` na rota `/contato` — já produziu formulário duplicado com o bloco (fix 11022).
- Storage `field_formulario_contato` em `node` — viola reuso.

---

## R5 — Alias `/contato` e destino do shell `page` da 017

**Decision**: Node Contato seed UUID `a4b5c6d7-e8f9-4012-b345-d6e7f8a9b0c1`; título admin “Contato”; alias canônico `/contato`. Se o alias apontar para a página `page` `f3a4b5c6-…` (ou outro node), remover/reassociar como em `_custom_configs_ensure_contato_page()` / 11022 — **sem** apagar o node legado. Título Drupal permanece oculto via `default_page_title`.

**Rationale**: FR-015–016; Edge Cases; limpeza sem perda de histórico.

**Alternatives considered**: apagar a página `page` seed — desnecessário e mais arriscado; path no webform `/contato` — duplicaria form.

---

## R6 — Limpeza do bloco ID 16 e placement

**Decision**: no `11023`, nesta ordem lógica:
1. Se existir `block_content` load(16) → delete (Entity API); no-op se ausente.
2. Se existir bloco UUID `d1e2f3a4-b5c6-4d7e-8f90-a1b2c3d4e5f6` e não for o mesmo ID já deletado → delete (cobre ambientes onde o seed não é ID 16).
3. Placement `default_layoutcontato`: `status: false` e/ou delete da config de bloco; garantir que `/contato` não recebe plugin `block_content:d1e2f3a4-…`.

**Rationale**: FR-001–002; ID 16 pode divergir do UUID seed entre ambientes.

**Alternatives considered**: só ID 16 — incompleto se o seed tiver outro nid; só desabilitar placement sem delete — deixa lixo editorial acessível, aceitável parcial mas spec pede exclusão do ID 16.

---

## R7 — Twig, naming e markup Bootstrap

**Decision**:
- Template: `themes/custom/default/templates/node/node--contato--full.html.twig` (suggestion por bundle + view mode); fallback aceitável `node--contato.html.twig` se o display público for `default`.
- Classes: raiz `.node--contato` + inner `.layout-contato-node` (BEM `.layout-contato-node__*`); reaproveitar estrutura visual da 017.
- Grid: `.container.py-5` > `.row` > `.col-12.col-lg-7` | `.col-12.col-lg-5`.
- H2: “Envie sua mensagem” hardcoded (Poppins/negrito via CSS).
- Coluna direita: painel `#E5EEFF`; imagem `field_imagem`; abaixo, `field_text_simple` + `field_text_simple_long` centralizados (omit empty).
- Atalhos: só se field preenchido; omit empty de webform/imagem.

**Rationale**: FR-009–014; US1/US3/US4.

**Alternatives considered**: Layout Builder — fora; reutilizar Twig do bloco com `{% include %}` — acopla legado; preferir Twig novo limpo.

---

## R8 — CSS / library

**Decision**: manter library `default/layout_contato` apontando para `assets/css/layout-contato.css`; atualizar seletores de `.block-layout-contato` para `.node--contato` / `.layout-contato-node` (ou dual-scope temporário se o bloco ainda existir em algum ambiente até o updb). Tokens: painel `#E5EEFF`, submit `#FD7B1A`.

**Rationale**: FR-014; SC-009; YAGNI (não criar segunda library).

**Alternatives considered**: library nova `layout_contato_node` — desnecessário; CSS global — risco de regressão.

---

## R9 — Derivação WhatsApp (`wa.me`)

**Decision**: no Twig, strip de máscara como no footer (`replace` espaços/`()`/`-`/`+`); se o resultado tiver 10 ou 11 dígitos, prepend `55`. Seed: valor editorial `(61) 99999-9999` → link `https://wa.me/5561999999999`. Exibir o valor do field como rótulo/texto da caixa.

**Rationale**: FR-011; US3; Assumptions; alinhado ao footer com ajuste DDI exigido pela spec.

**Alternatives considered**: preprocess PHP — só se Twig ficar ilegível; seed já com `5561…` — menos amigável no admin.

---

## R10 — Hook `11023` e ordem de deploy

**Decision**: `custom_configs_update_11023` orquestra: limpeza legado → ensure structure (type + storages novos + instances + displays) → seed Node (asset → `public://` se imagem vazia) → alias `/contato`. Idempotência: UUID fixo; popular só ausente/vazio; nunca sobrescrever editorial divergente; nunca duplicar Node.

Deploy destino:

`git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr`

**Rationale**: FR-015–018; storages novos precisam existir via `cim` antes do ensure de instances no updb (ou ensure cria storage se ausente — preferir cex + cim first).

**Alternatives considered**: só config sem hook — não cobre delete ID 16, seed, alias, asset.

---

## R11 — PRD e webform

**Decision**: atualização cirúrgica do `PRD.md` (§3.1 content types / §3.6 blocos / §10 rotas). **Não** alterar elementos do webform `contato` (já entregue na 017); apenas referenciá-lo pelo campo `webform` do Node.

**Rationale**: FR-019; Fora da spec.

**Alternatives considered**: reabrir elementos do webform — fora do escopo.

---

## Resolução de clarificações

Nenhum `NEEDS CLARIFICATION` no Technical Context após esta pesquisa. Spec checklist já estava 100% marcada.
