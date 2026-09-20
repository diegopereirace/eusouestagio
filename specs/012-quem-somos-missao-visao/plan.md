# Implementation Plan: Quem Somos — Seção Missão e Visão (no Node)

**Branch**: `dev` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo, setup executado manualmente)  
**Date**: 2026-09-20  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/012-quem-somos-missao-visao/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Substituir o layout legado Anexo 2 (2ª seção `*_2` + Twig em duas colunas) e a entrega pública da feature `011` (Custom Block `missao_visao` em `content_full`) pela seção Missão | Visão **no próprio Node** `quem_somos`: Field Group `group_missao_visao`, reuso de `field_imagem_desktop` / `field_text_simple_long` / `field_text_simple_long_2`, Twig + CSS full-width com overlay, `hook_update_N` `11015` idempotente, `drush cex` → `config/sync`, PRD §3.1.0 / §3.6 cirúrgico. Sem Paragraphs.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Node, Image, Field, Text); Field Group (já instalado); tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3). **Nenhuma dependência Composer nova. Sem Paragraphs nesta feature.**  
**Storage**: PostgreSQL; estrutura em `config/sync`; seed/migração mínima via `hook_update_N`  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (desktop ≥768px / mobile &lt;768px — breakpoint Bootstrap `md`)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS encapsulado em library dedicada; imagem de fundo única; sem JS novo  
**Constraints**: sem alteração em `core/`/`vendor/`; sem storages paralelos; sem Paragraphs; deploy `cim` → `updb` → `cr`; pt-BR; banner 009 e Sobre nós 010 intactos; uma única faixa Missão/Visão em `/quem-somos`  
**Scale/Scope**: 3 field instances + 1 Field Group no bundle; remoção de 3 instances + 1 grupo legado; 1 Twig Node; 1 CSS/library; 1 hook `11015`; disable do placement 011; PRD cirúrgico

**Estado atual verificado (2026-09-20):**

- Último hook: `custom_configs_update_11014` → próximo livre **`11015`**.
- Bundle `quem_somos` tem instances: `field_titulo`, `field_text_long_formatted`, `field_imagem`, `field_titulo_2`, `field_text_long_formatted_2`, `field_imagem_2` + groups `group_primeiro_bloco` / `group_segundo_bloco`.
- **Ainda não** existem instances em `quem_somos` para `field_imagem_desktop`, `field_text_simple_long`, `field_text_simple_long_2`.
- Storages node reutilizáveis **existem**: `field_imagem_desktop` (usado em `banners`), `field_text_simple_long` e `field_text_simple_long_2` (usados em `para_empresas`).
- Storages legado `field_titulo_2`, `field_imagem_2`, `field_text_long_formatted_2`: instances **somente** em `quem_somos` → após remover instances, storages podem ser excluídos (FR-004).
- Twig `node--quem-somos.html.twig`: 1ª seção Sobre nós + 2ª seção legado (row `*_2`); library `layout_sobre_nos`.
- Bloco `default_missaovisao` (`status: true`, região `content_full`, pages `/quem-somos`) — **deve deixar de renderizar** nessa rota.
- CSS visual de referência: `block-missao-visao.css` (reutilizar padrões de overlay/grid sob novo escopo de classe).

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storages | PASS | três storages node existentes; zero storage novo |
| Sem Paragraphs (escopo 012) | PASS | campos plain no Node |
| Deploy `cim` → `updb` → `cr` + hook idempotente | PASS | `11015` + `drush cex` estrutural |
| Clean URLs | PASS | rota `/quem-somos` inalterada |
| Performance / CSS isolado | PASS | library + seletores sob `.quem-somos-missao-visao` |
| Contrib first / custom mínimo | PASS | sem módulo novo; Field Group já no projeto |
| Sem duplicidade 011 + Node | PASS | disable placement `default_missaovisao` |

**Post-design**: gates mantidos; sem violação injustificada. Full-bleed via CSS breakout justificado (node vive dentro de `#main.container`; ver research R4).

## Design Decisions

1. **Campos no Node** (sem Paragraphs): anexar `field_imagem_desktop` (fundo), `field_text_simple_long` (Missão), `field_text_simple_long_2` (Visão) ao bundle `quem_somos`.
2. **Field Group** `group_missao_visao` (rótulo “Seção Missão e Visão”) no `entity_form_display`; remover `group_segundo_bloco` e instances `*_2`.
3. **Títulos públicos fixos** no Twig: “Nossa Missão” / “Nossa Visão” (não editáveis).
4. **Twig**: remover markup da 2ª seção legado; restringir `.container` à seção Sobre nós; renderizar `<section class="quem-somos-missao-visao">` como irmão fora do container interno do node.
5. **Full-width**: CSS breakout (`100vw` / margens negativas) sob `.quem-somos-missao-visao` para escapar `#main.container` (substitui o benefício da região `content_full` da 011).
6. **Fundo + overlay**: `style="background-image: url(...)"` no wrapper; CSS `cover`/`center`/`min-height: 380px`; overlay `::before` com `--qs-mv-overlay: rgba(15, 23, 42, 0.7)`.
7. **Grid**: `.container` + `.row` + `.col-md-6` + `.text-center` + `.text-white`; divisória no 1º col em `md+`.
8. **Library**: `default/quem_somos_missao_visao` → `assets/css/quem-somos-missao-visao.css`; attach no Twig do node (junto ou além de `layout_sobre_nos`).
9. **Bloco 011**: exportar `block.block.default_missaovisao` com `status: false` (conteúdo/tipos podem permanecer); hook garante disable se config ainda ativa.
10. **Hook `11015`**: idempotente — remove legado; anexa campos/grupo/displays; seed textos Assumptions + imagem (asset 011 ou cópia do bloco) **somente se vazios**; não sobrescreve editorial; disable placement.
11. **Storages legado**: após remover instances, excluir storages `field_titulo_2` / `field_imagem_2` / `field_text_long_formatted_2` se sem outras instances (estado atual: só `quem_somos`).
12. **PRD**: §3.1.0 (campos + remoção `*_2`) e §3.6 (bloco `missao_visao` aposentado na rota; seção no Node + `11015`).
13. **Fora**: banner 009, Sobre nós 010 (`group_primeiro_bloco`), Valores do Anexo 2, criação de storages paralelos.

## Project Structure

### Documentation (this feature)

```text
specs/012-quem-somos-missao-visao/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/quem-somos-missao-visao-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  field.field.node.quem_somos.field_imagem_desktop.yml          # criar
  field.field.node.quem_somos.field_text_simple_long.yml        # criar
  field.field.node.quem_somos.field_text_simple_long_2.yml      # criar
  core.entity_form_display.node.quem_somos.default.yml          # grupo novo; sem *_2
  core.entity_view_display.node.quem_somos.default.yml          # campos novos; sem *_2
  block.block.default_missaovisao.yml                           # status: false
  # remover (cex após admin/hook):
  #   field.field.node.quem_somos.field_titulo_2.yml
  #   field.field.node.quem_somos.field_text_long_formatted_2.yml
  #   field.field.node.quem_somos.field_imagem_2.yml
  #   field.storage.node.field_titulo_2.yml (se órfão)
  #   field.storage.node.field_text_long_formatted_2.yml (se órfão)
  #   field.storage.node.field_imagem_2.yml (se órfão)

themes/custom/default/
  templates/content/node--quem-somos.html.twig                  # legado out; seção nova
  assets/css/quem-somos-missao-visao.css                        # criar
  default.libraries.yml                                         # + quem_somos_missao_visao

modules/custom/custom_configs/
  custom_configs.install                                        # custom_configs_update_11015 + helpers

PRD.md                                                          # §3.1.0 + §3.6 cirúrgico
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Config: instances, Field Group, displays, disable bloco → `drush cex`
3. Twig + library/CSS (full-bleed, overlay, grid, mobile, omit empty)
4. Hook `11015` (remove legado, ensure fields, seed condicional, disable bloco)
5. PRD cirúrgico + validação quickstart (`cim` → `updb` → `cr`)

## Complexity Tracking

Nenhuma violação de gate a justificar. Full-bleed CSS breakout é a compensação necessária por sair da região `content_full` (documentado em research).
