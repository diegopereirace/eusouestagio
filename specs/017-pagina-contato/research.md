# Research: Página de Contato

**Data**: 2026-09-23 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **deploy repetível** (`cim` → `updb` → `cim` → `cr`), **reuso de storages**, **Clean URL `/contato`** e **isolamento CSS** sob `.block-layout-contato`.

---

## R1 — Atualizar webform `contato` in-place

**Decision**: manter `id: contato` e UUID `38532b7b-3193-4b6a-b666-e1e68693dd94`; substituir elementos legados (`tipo`, `nome`, `e_mail`, `whatsapp`, `mensagem`) pelos novos machine names do Figma; preservar confirmação (“Mensagem enviada!” / “Sua mensagem foi enviada com sucesso!”) e access `create` para anonymous + authenticated.

**Rationale**: FR-001–007; Assumptions; evita webform paralelo e URLs/config órfãs.

**Alternatives considered**:
- Criar webform `contato_v2` — rejeitado (duplicação; submissions e links externos quebram).
- Migrar submissions antigas para novos keys — fora do escopo (Fora / Edge Cases).

---

## R2 — Layout interno do formulário via `webform_flexbox`

**Decision**: envolver pares Nome|E-mail e Telefone|Categoria em elementos `#type: webform_flexbox` (contrib Webform); Assunto e Mensagem em largura total; submit via `webform_actions` com `#submit__label: Enviar Mensagem`. Placeholders exatamente do Figma; `categoria` select com empty option “Estudante” e opções Estudante / Empresa / Outro. Telefone mantém classe `mask-phone` (library `default/masks` já anexada em `custom_configs_webform_submission_form_alter`).

**Rationale**: FR-002–005; Webform já documenta flexbox nativo; sem CSS global de grid no form.

**Alternatives considered**:
- CSS Grid só no tema sobre classes genéricas do webform — mais frágil e risco de regressão.
- Paragraphs/field groups custom — overkill (YAGNI).

---

## R3 — Storage novo `field_formulario_contato`

**Decision**: criar `field.storage.block_content.field_formulario_contato` (`type: webform`, `target_type: webform`, cardinality **1**) + instance no bundle `layout_contato`.

**Rationale**: FR-009. O único storage webform versionado hoje é `field.storage.node.webform` (entity type `node`, enforced `webform_node`) — não reutilizável em `block_content`.

**Alternatives considered**:
- Hardcode `contato` no Twig — rejeitado (FR-009 / US5 editor).
- Campo texto com ID do webform — rejeitado (sem widget/validação nativos).
- Depender de `webform_node` numa página — rejeitado (spec exige Custom Block Type envelope).

---

## R4 — Imagem: reusar `field_image`

**Decision**: instance `field_image` no bundle `layout_contato` (pedido verbal `field_imagem_destaque` → canônico). No form display, limitar UX a 1 imagem (mesmo com storage cardinality `-1`). Seed copia `assets/contato/img-contato.png` → `public://` e anexa se campo vazio.

**Rationale**: FR-010; regra de reuso do projeto; padrão footer/CTO/missão.

**Alternatives considered**: criar `field_imagem_destaque` — proibido pela spec (Fora).

---

## R5 — Rota limpa `/contato` via Node `page` + alias (não via webform page path)

**Decision**: ensure Node bundle `page` com path alias `/contato` (UUID fixo); body vazio/mínimo. Manter webform `settings.page: true` **sem** `page_submit_path=/contato` (rota dedicada permanece `/webform/contato` como canal secundário/admin, ou desabilitar page na implementação se produto quiser um único entrypoint — default: **não** mapear webform para `/contato`). Placement do bloco em `content_full`, visibility `request_path` = `/contato`. Título Drupal permanece oculto (`default_page_title` já negate em `/contato`).

**Rationale**: FR-019; evita **formulário duplicado** (conteúdo da página webform + bloco). Página `page` casa com a regra de visibility do page title (entity_bundle page).

**Alternatives considered**:
- `page_submit_path: /contato` no webform — rejeitado (duplo render do form).
- Só path alias para `/webform/contato` — rejeitado (mesmo problema de layout/envelope).
- Controller custom — rejeitado (contrib/core first).

---

## R6 — Templates, naming e markup Bootstrap

**Decision**:
- Twig preferencial: `themes/custom/default/templates/block/block--block-content--layout-contato.html.twig` (suggestion por bundle); se o tema já padronizar `block--block-{bundle}.html.twig` (ex. CTA), aceitar `block--block-layout-contato.html.twig` desde que a suggestion resolva.
- Classes de escopo: `.block-layout-contato` (raiz) e `.layout-contato` (inner).
- Estrutura: `.container.py-5` > `.row` > `.col-12.col-lg-7` | `.col-12.col-lg-5`.
- Faixa atalhos: `.d-flex.gap-4.mt-4` (wrap no mobile).
- Painel direito: fundo `#E5EEFF`, imagem `.img-fluid`; se imagem vazia → painel sem `<img>` quebrada (ou omitir painel visual — preferir painel sem img).
- Webform ausente → omitir área do form sem fatal.

**Rationale**: FR-012–018; alinhamento a 015/013.

**Alternatives considered**: Layout Builder — fora do escopo.

---

## R7 — Tokens CSS e library

**Decision**: library `default/layout_contato` → `assets/css/layout-contato.css`. Tokens sob `.block-layout-contato`:

| Token | Valor |
|-------|--------|
| Submit / CTA | `#FD7B1A` |
| Painel direito | `#E5EEFF` |
| Inputs | bordas arredondadas ≈8px; aparência form-control/form-select |
| Título H2 | Poppins, negrito |

**Rationale**: FR-005, FR-014, FR-017; isolamento SC-009.

**Alternatives considered**: reusar `.ui-btn--primary` global (`#e55a24`) — cor diverge do Figma / risco de regressão.

---

## R8 — Atalhos E-mail / WhatsApp no Twig

**Decision**: hardcode no Twig nesta fase: `mailto:contato@eusouestagio.com` e `https://wa.me/5561999999999` com rótulos/ícones do Figma. Não criar fields.

**Rationale**: Assumptions; FR-015–016; YAGNI.

**Alternatives considered**: `field_email` / `field_phone_wpp` do footer — possível evolução futura, fora desta feature.

---

## R9 — Hook `11021` e ordem de deploy

**Decision**: `custom_configs_update_11021` orquestra helpers ensure (webform, estrutura bloco, asset, seed, placement, página/alias). Idempotência: UUID fixo; popular só ausente/vazio; nunca sobrescrever editorial divergente; nunca duplicar bloco/página.

Deploy destino (alinhado a `drupal-deploy-configs.mdc` / 016):

`git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr`

**Rationale**: FR-019–022; placement pode depender de UUID seedado.

**Alternatives considered**: só config sem hook — não cobre seed de conteúdo/arquivo/alias.

---

## R10 — Asset e copy do painel

**Decision**: versionar `modules/custom/custom_configs/assets/contato/img-contato.png`. Se a arte incluir o copy “Time de especialistas” / “24 horas úteis”, o Twig não duplica o texto; se a arte for só ilustração, Twig pode complementar abaixo da imagem **sem** inventar copy conflitante — escolher conforme asset final na implementação.

**Rationale**: Assumptions; FR-017.

**Alternatives considered**: fields de texto para o painel — fora do escopo desta fase.

---

## Resolução de clarificações

Nenhum `NEEDS CLARIFICATION` no Technical Context após esta pesquisa. Spec checklist já estava 100% marcada.
