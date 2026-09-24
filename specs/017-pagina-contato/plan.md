# Implementation Plan: Página de Contato

**Branch**: `feature-contato` (scaffolding Spec Kit — `.specify/scripts` ausente neste repo; setup executado manualmente)  
**Date**: 2026-09-23  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/017-pagina-contato/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar a **Página de Contato** em `/contato`: atualizar o webform `contato` (campos, placeholders, flexbox lado a lado, submit “Enviar Mensagem”), criar o Custom Block Type `layout_contato` (referência ao webform + `field_image`), Twig duas colunas Bootstrap 5 (form + atalhos | painel `#E5EEFF` + ilustração), CSS sob `.block-layout-contato`, seed/placement idempotente via `custom_configs_update_11021`, asset `img-contato.png`, rota limpa `/contato` (página `page` + alias), `drush cex` → `config/sync`, PRD cirúrgico. Sem banner interno, sem Layout Builder, sem storage paralelo de imagem.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Block Content, Field, Image, Path alias, Node `page`); contrib **Webform** (já no projeto); tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3). **Nenhuma dependência Composer nova.**  
**Storage**: PostgreSQL; estrutura em `config/sync`; seed via `hook_update_N` + Entity API  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile ≤575.98px / lg+ ≥992px — breakpoints Bootstrap)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS encapsulado em library dedicada; JS só máscara já existente (`default/masks` no webform `contato`); omit empty no Twig  
**Constraints**: sem `core/`/`vendor/`; reutilizar `field_image`; criar storage `field_formulario_contato` (tipo webform em `block_content`); deploy `cim` → `updb` → (2ª `cim` se placements/UUIDs) → `cr`; pt-BR; não redesenhar header/footer; não migrar submissions legadas  
**Scale/Scope**: 1 webform atualizado, 1 block type, 1 field storage novo + 2 instances, displays, 1 Twig, 1 CSS/library, 1 placement, 1 página seed `/contato`, 1 asset, 1 hook `11021`, PRD cirúrgico

**Estado atual verificado (2026-09-23):**

- Último hook: `custom_configs_update_11020` → próximo livre **`11021`**.
- Webform `contato` existe (UUID `38532b7b-3193-4b6a-b666-e1e68693dd94`); elementos legados: `tipo`, `nome`, `e_mail`, `whatsapp`, `mensagem`; confirmação “Mensagem enviada!” / “Sua mensagem foi enviada com sucesso!”; acesso create anonymous+authenticated; `settings.page: true` com `page_submit_path` vazio (rota dedicada padrão `/webform/contato`).
- Storage `field_image` em `block_content` **existe** (cardinality `-1`); reutilizar instance no bundle.
- Storage de referência webform em `block_content` **ausente** (só `field.storage.node.webform` via `webform_node`) → criar `field_formulario_contato`.
- Bundle `layout_contato` **não existe**.
- Asset `img-contato.png` **ainda não** versionado em `assets/` → criar em `modules/custom/custom_configs/assets/contato/`.
- `default_page_title` já oculta título Drupal em `/contato` (negate).
- `11020` removeu `/contato` de `banners-block_1`.
- Máscara telefone: `custom_configs_webform_submission_form_alter` anexa `default/masks` ao webform `contato` — **preservar** (classe `mask-phone` no elemento `telefone`).
- Poppins já no tema; reforço tipográfico no escopo do novo bloco.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md`, asset sob `assets/` |
| Reuso de field storages | PASS | `field_image` reutilizado; só `field_formulario_contato` novo (justificado R3) |
| Deploy `cim` → `updb` → `cr` (+ 2ª `cim`) + hook idempotente | PASS | `11021` + `drush cex` estrutural |
| Clean URLs | PASS | alias `/contato`; atalhos `mailto:` / `wa.me` |
| Performance / CSS isolado | PASS | library + seletores sob `.block-layout-contato` |
| Contrib first / custom mínimo | PASS | Webform existente; Block Content; sem módulo novo |
| Sem dump / Entity API | PASS | seed via Entity API + assets → `public://` |
| Convivência home / Quem Somos / rodapé | PASS | escopo CSS + visibility só `/contato` |

**Post-design**: gates mantidos; storage novo `field_formulario_contato` justificado em research R3. Sem violação injustificada.

## Design Decisions

1. **Webform `contato`**: atualizar in-place (mesmo `id`/UUID); substituir elementos legados pelos novos machine names; flexbox nativo (`webform_flexbox`) nas linhas 1–2; `actions` com `#submit__label: Enviar Mensagem`; preservar confirmação e access create; **não** apontar `page_submit_path` para `/contato` (evita formulário duplicado com o bloco).
2. **Rota `/contato`**: garantir Node bundle `page` + path alias `/contato` (UUID fixo); body vazio/mínimo; título Drupal permanece oculto via `default_page_title`.
3. **Block type** `layout_contato`: `field_formulario_contato` (webform, card 1) + `field_image` (imagem destaque; instance card 1 recomendada no form display).
4. **Twig**: `block--block-content--layout-contato.html.twig` (ou sugestão Barrio equivalente `block--block-layout-contato.html.twig` se o tema já padronizar `block--block-*` — ver research R5); classes `.block-layout-contato` / `.layout-contato`.
5. **Markup**: `.container.py-5` → `.row` → `.col-12.col-lg-7` (H2 + webform + faixa atalhos) | `.col-12.col-lg-5` (painel `#E5EEFF` + img).
6. **Atalhos**: hardcoded no Twig — e-mail `contato@eusouestagio.com`, WhatsApp `(61) 99999-9999` → `https://wa.me/5561999999999`.
7. **CSS/library** `default/layout_contato` → `assets/css/layout-contato.css`; attach no Twig; botão submit `#FD7B1A` sob escopo do bloco.
8. **UUIDs**: bloco seed `d1e2f3a4-b5c6-4d7e-8f90-a1b2c3d4e5f6`; placement `default_layoutcontato` UUID `e2f3a4b5-c6d7-4e8f-9012-b3c4d5e6f7a8`; página `/contato` UUID `f3a4b5c6-d7e8-4f90-a123-c4d5e6f7a8b9`.
9. **Hook `11021`**: ensure webform elements/settings; ensure block type + storage/instances/displays; ensure asset → `public://`; seed bloco se ausente/campos vazios; ensure placement `content_full` só `/contato`; ensure página+alias; **nunca** sobrescrever editorial divergente; **nunca** duplicar.
10. **PRD**: §3.6 (+ rotas §10 se listar públicas) — `layout_contato`, webform `contato`, placement, `/contato`, hook `11021`.
11. **Fora**: banner interno; Layout Builder; `field_imagem_destaque` paralelo; migração de submissions; redesign header/footer.

## Project Structure

### Documentation (this feature)

```text
specs/017-pagina-contato/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/layout-contato-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  webform.webform.contato.yml                                      # atualizar elements/actions
  block_content.type.layout_contato.yml                            # criar
  field.storage.block_content.field_formulario_contato.yml         # criar (webform)
  field.field.block_content.layout_contato.field_formulario_contato.yml
  field.field.block_content.layout_contato.field_image.yml
  core.entity_form_display.block_content.layout_contato.default.yml
  core.entity_view_display.block_content.layout_contato.default.yml
  block.block.default_layoutcontato.yml                            # content_full /contato
  user.role.*.yml                                                  # permissões do bundle (se necessário)

themes/custom/default/
  templates/block/block--block-content--layout-contato.html.twig   # ou block--block-layout-contato.html.twig
  assets/css/layout-contato.css
  default.libraries.yml                                            # + layout_contato

modules/custom/custom_configs/
  custom_configs.install                                           # custom_configs_update_11021 + helpers
  assets/contato/img-contato.png                                   # arte Figma versionada
  custom_configs.module                                            # manter masks no webform contato

PRD.md                                                             # §3.6 (+ §10 se aplicável)
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Config: webform atualizado, block type, storage/instances, displays, página/alias, placement → `drush cex`
3. Twig + library/CSS (tokens Figma, Bootstrap 2 colunas, atalhos, omit empty)
4. Asset + hook `11021` (ensure + seed + placement + rota)
5. PRD + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Novo storage `field_formulario_contato` | Spec exige referência a webform no bloco; storage existente é só em `node` (`webform_node`) | Reusar `node.webform` — entity type incompatível; embutir webform ID no Twig — perde FR-009/US5 |
| Página `page` + bloco vs webform `page_submit_path=/contato` | Bloco envelopa form+imagem; path no webform duplicaria o form na região de conteúdo | Só rota do webform — layout Figma (painel/imagem/atalhos) ficaria fora do envelope gerenciável |
| Atalhos hardcoded no Twig | Spec/Assumptions desta fase; evita fields extras | Reusar `field_email`/`field_phone_wpp` — escopo maior sem pedido |
