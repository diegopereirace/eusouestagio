# Implementation Plan: Contato como Node

**Branch**: `feature-contato` (scaffolding Spec Kit — `.specify/scripts` ausente neste repo; setup executado manualmente)  
**Date**: 2026-09-24  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/018-contato-node/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Refatorar `/contato` para ser servida **somente** por um Node do Content Type `contato` (webform + imagem + e-mail + WhatsApp + textos da coluna direita), com Twig Bootstrap 5 alinhado ao Figma, CSS sob `.node--contato` / `.layout-contato-node`, seed/alias idempotentes via `custom_configs_update_11023`, remoção do bloco ID 16 e desativação do placement `default_layoutcontato` (017), `drush cex` → `config/sync`, PRD cirúrgico. Sem banner interno, sem Layout Builder, sem alterar o webform `contato`.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Node, Field, Image, Path alias, Block); contrib **Webform** + storage `node.webform` (`webform_node`); tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3). **Nenhuma dependência Composer nova.**  
**Storage**: PostgreSQL; estrutura em `config/sync`; seed via `hook_update_N` + Entity API  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile ≤575.98px / lg+ ≥992px — breakpoints Bootstrap)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS encapsulado (library dedicada ou evolução de `layout_contato`); omit empty no Twig; sem JS novo  
**Constraints**: sem `core/`/`vendor/`; reutilizar storages canônicos em `node` (`webform`, `field_imagem`, `field_text_simple`, `field_text_simple_long`); criar só `field_email` + `field_phone_wpp` em `node` (ausentes); deploy `cim` → `updb` → (2ª `cim`) → `cr`; pt-BR; não redesenhar header/footer; não migrar submissions  
**Scale/Scope**: 1 content type, 2 storages novos + 6 instances, form/view displays, 1 Twig node, CSS/library, 1 hook `11023` (limpeza legado + seed + alias), PRD cirúrgico; webform `contato` e asset `img-contato.png` reaproveitados

**Estado atual verificado (2026-09-24):**

- Último hook: `custom_configs_update_11022` → próximo livre **`11023`**.
- Feature 017 ativa em `/contato`: página `page` UUID `f3a4b5c6-d7e8-4f90-a123-c4d5e6f7a8b9` + bloco `layout_contato` UUID `d1e2f3a4-b5c6-4d7e-8f90-a1b2c3d4e5f6` + placement `default_layoutcontato` em `content_full` só `/contato`.
- Bundle `contato` (node) **não existe**.
- Storages em `node`: `webform` (existe, enforced `webform_node`), `field_imagem`, `field_text_simple`, `field_text_simple_long` — **reutilizar**.
- `field_email` / `field_phone_wpp` existem só em `block_content` (footer) — **criar** equivalentes em `node`.
- Asset `modules/custom/custom_configs/assets/contato/img-contato.png` já versionado.
- Twig/CSS atuais: `block--block-layout-contato.html.twig` + `default/layout_contato` — aposentar na rota (podem permanecer no repo sem placement).
- `default_page_title` já oculta título Drupal em `/contato`.
- Padrão WhatsApp no rodapé: strip de máscara no Twig; seed Contato: `(61) 99999-9999` → `wa.me/5561999999999` (DDI 55).

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storages | PASS | `webform`, `field_imagem`, `field_text_simple`, `field_text_simple_long`; só `field_email`/`field_phone_wpp` novos em `node` (R3) |
| Deploy `cim` → `updb` → `cr` (+ 2ª `cim`) + hook idempotente | PASS | `11023` + `drush cex` estrutural |
| Clean URLs | PASS | alias `/contato` → Node Contato; `mailto:` / `wa.me` |
| Performance / CSS isolado | PASS | seletores sob `.node--contato` / `.layout-contato-node` |
| Contrib first / custom mínimo | PASS | Node + Webform; sem módulo novo |
| Sem dump / Entity API | PASS | seed via Entity API + asset → `public://` |
| Convivência home / Quem Somos / rodapé | PASS | escopo CSS; placement legado desativado |

**Post-design**: gates mantidos; storages novos justificados em research R3. Sem violação injustificada.

## Design Decisions

1. **Content Type `contato`**: label “Contato”; fields — `webform` (reuso storage), `field_imagem`, `field_email` (novo storage node), `field_phone_wpp` (novo storage node), `field_text_simple` (título coluna direita), `field_text_simple_long` (descrição).
2. **Não** criar `field_formulario_contato` / `field_image` / `field_text_simple_small` em `node` — mapear pedidos verbais aos canônicos (research R2–R3).
3. **Limpeza 017**: `11023` exclui `block_content` ID **16** se existir; exclui/desativa conteúdo seed `layout_contato` UUID `d1e2f3a4-…` se ainda presente; desabilita ou remove placement `default_layoutcontato` (status false / delete config) para `/contato` não renderizar o bloco.
4. **Alias `/contato`**: reassociar ao Node Contato seed (UUID fixo novo); remover alias da página `page` `f3a4b5c6-…` (node pode permanecer sem alias, como 11022 fez com webform legado).
5. **Twig**: `themes/custom/default/templates/node/node--contato--full.html.twig` (ou `node--contato.html.twig` se view mode default = full); classes `.node--contato` + `.layout-contato-node`; markup Bootstrap 7/12 | 5/12; H2 hardcoded “Envie sua mensagem”; atalhos a partir dos fields (omit empty).
6. **WhatsApp**: padrão do footer (strip máscara) + prepend `55` se o número tiver 10–11 dígitos (BR sem DDI).
7. **CSS/library**: evoluir `layout-contato.css` / `default/layout_contato` para escopo `.node--contato` / `.layout-contato-node` (manter tokens `#E5EEFF`, submit `#FD7B1A`); attach no Twig do node.
8. **UUIDs**: Node Contato seed `a4b5c6d7-e8f9-4012-b345-d6e7f8a9b0c1` (fixo).
9. **Hook `11023`**: (1) delete ID 16 + limpeza layout_contato/placement; (2) ensure content type + storages novos + instances + displays; (3) seed Node se ausente/campos vazios; (4) alias `/contato` → Node Contato; **nunca** sobrescrever editorial divergente; **nunca** duplicar.
10. **PRD**: §3.1/§3.6 + rota §10 — Content Type `contato`, aposentadoria do bloco na página, hook `11023`.
11. **Fora**: remover o *tipo* `layout_contato` do sistema; alterar elementos do webform; banner; Layout Builder; redesign header/footer.

## Project Structure

### Documentation (this feature)

```text
specs/018-contato-node/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/contato-node-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  node.type.contato.yml
  field.storage.node.field_email.yml                    # criar
  field.storage.node.field_phone_wpp.yml                # criar
  field.field.node.contato.webform.yml
  field.field.node.contato.field_imagem.yml
  field.field.node.contato.field_email.yml
  field.field.node.contato.field_phone_wpp.yml
  field.field.node.contato.field_text_simple.yml
  field.field.node.contato.field_text_simple_long.yml
  core.entity_form_display.node.contato.default.yml
  core.entity_view_display.node.contato.default.yml     # e/ou .full
  block.block.default_layoutcontato.yml                 # status false ou remoção via cex
  user.role.*.yml                                       # permissões do bundle (se necessário)

themes/custom/default/
  templates/node/node--contato--full.html.twig          # ou node--contato.html.twig
  assets/css/layout-contato.css                         # escopo → .node--contato
  default.libraries.yml                                 # library layout_contato (reuse/rename)

modules/custom/custom_configs/
  custom_configs.install                                # custom_configs_update_11023 + helpers
  assets/contato/img-contato.png                        # reutilizar

PRD.md                                                  # §3.x + §10
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Config: content type, storages novos, instances, displays → `drush cex`
3. Twig node + CSS/library (tokens Figma, fields editáveis, omit empty)
4. Hook `11023` (limpeza ID 16 + placement; seed Node; alias)
5. PRD + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Novos storages `field_email` / `field_phone_wpp` em `node` | Spec FR-006; equivalentes só existem em `block_content` (entity type incompatível) | Reusar storages do footer — impossível cross-entity; hardcode Twig — regrediria US3/US5 |
| Reuso de `node.webform` (enforced `webform_node`) | Storage canônico de referência webform em `node`; evita `field_formulario_contato` paralelo | Bundle `webform` em `/contato` — já causou duplicação (11022); storage paralelo — viola reuso |
| Manter tipo `layout_contato` no config | Spec Fora: não exige remoção do tipo; só parar de renderizar em `/contato` | Deletar block type + configs 017 — escopo maior, risco de cim órfão |
