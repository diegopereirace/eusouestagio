# Implementation Plan: Layout “Sobre nós” (wrap texto + imagem)

**Branch**: `dev` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo, setup executado manualmente)  
**Date**: 2026-09-14  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/010-layout-sobre-nos/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Revisar a primeira seção da página `/quem-somos` (node `quem_somos`) para o layout Figma: título + **único** corpo WYSIWYG contornando a imagem à direita (float), com anel verde e círculo laranja no wrapper da foto. Reutilizar fields canônicos já anexados (`field_titulo`, `field_text_long_formatted`, `field_imagem`); manter a segunda seção (`*_2`) em grid de duas colunas; CSS/library isolados sob o tipo; `custom_configs_update_11013` idempotente (garantia defensiva de fields/displays) + `drush cex` se houver alteração estrutural; PRD cirúrgico.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Node, Image, Text, Field); tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3); `twig_tweak`/`file_url` já usados no Twig atual. **Nenhuma dependência contrib nova.**  
**Storage**: PostgreSQL; estrutura em `config/sync`; conteúdo editorial **não** é seedado nesta feature  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (desktop ≥768px / mobile &lt;768px — breakpoint Bootstrap `md`)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS encapsulado em library dedicada; sem JS novo; imagem com `loading="lazy"` (abaixo do banner)  
**Constraints**: sem alteração em `core/`/`vendor/`; sem field storages paralelos; deploy `cim` → `updb` → `cr`; pt-BR; banner 009 intacto  
**Scale/Scope**: 1 Twig (`node--quem-somos.html.twig`), 1 CSS + library, 1 hook `11013`, PRD §3.1 (cirúrgico); config/sync só se displays/instances mudarem

**Estado atual verificado (2026-09-14):**

- Bundle `quem_somos` com instances: `field_titulo`, `field_text_long_formatted`, `field_imagem`, `field_titulo_2`, `field_text_long_formatted_2`, `field_imagem_2`.
- Form display: field groups “Primeiro Bloco” / “Segundo Bloco” já expõem os seis campos.
- View display: apenas modo `default` (`core.entity_view_display.node.quem_somos.default`); **não** existe `full` separado — página completa consome `default`.
- Twig atual: `themes/custom/default/templates/content/node--quem-somos.html.twig` — **grid duas colunas** (`col-md-7` + `col-md-5`) na 1ª seção (não é wrap/float).
- Classe Drupal gerada: `node--type-quem-somos` (bundle `quem_somos`).
- Último hook: `custom_configs_update_11012` → próximo livre **`11013`**.
- Tokens globais: `--brand-green: #5eb344`, `--brand-orange: #ff8a22`, Poppins já em `style.css`.
- `field_imagem` no bundle: `required: true`; alt opcional no field config (template usa `node.field_imagem.alt`).
- Banner `/quem-somos` (feature 009) na região `banner` — fora do escopo desta feature.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, eventual `config/sync`, `PRD.md` |
| Reuso de field storages | PASS | zero storages novos; nomes canônicos já no bundle |
| Deploy `cim` → `updb` → `cr` + hook idempotente | PASS | `11013` defensivo + `cex` se estrutural |
| Clean URLs | PASS | rota `/quem-somos` inalterada |
| Performance / CSS isolado | PASS | library + seletores sob tipo/wrapper |
| Contrib first / custom mínimo | PASS | sem módulo novo; só Twig/CSS + ensure no install |

**Post-design**: gates mantidos; sem violação injustificada.

## Design Decisions

1. **Fields**: reutilizar `field_titulo` + `field_text_long_formatted` + `field_imagem` (FR-001/002). Sem `field_text_formatted_long` / `field_image` paralelos.
2. **View mode**: permanecer em `default` (único display existente); não criar `full` nesta feature (YAGNI — Assumptions da spec).
3. **Layout 1ª seção**: abandonar grid `col-md-7|5` em favor de container `clearfix` + wrapper da imagem com `float-md-end` + margens; texto único após/ao redor da imagem no fluxo.
4. **2ª seção**: manter `row` duas colunas atual; clearfix da 1ª impede herança de float (FR-013).
5. **Decorativos**: `::before` (anel verde vazado ~185×185, topo direito) e `::after` (círculo laranja sólido, canto inferior direito) no wrapper `position-relative` da imagem — sem markup extra de SVG se CSS bastar.
6. **Cores decorativas**: tokens locais sob o wrapper (`--sobre-nos-green` ← `--brand-green`; `--sobre-nos-orange` ← `#FD7B1A` alinhado ao Figma/banner 009, **sem** mutar `--brand-orange` global).
7. **Escopo CSS**: library `layout_sobre_nos`; seletores sob `.node--type-quem-somos` e classe BEM `.sobre-nos` no container da 1ª seção (equivalente ao “`.node-quem-somos`” da spec).
8. **Imagem**: `img-fluid` + `max-width` ~432px em `md+`; proporção visual ~432×269; omitir wrapper+decorativos se entidade de arquivo ausente.
9. **Hook `11013`**: idempotente — se instance/display já corretos, no-op; se ausentes, reanexa/atualiza (defensivo pós-`cim`). **Não** altera conteúdo editorial do node.
10. **PRD**: acrescentar §3.1.x `quem_somos` (campos + layout wrap) e corrigir machine name da tabela §3.1 (`quem_somos`, não `quem-somos`).

## Project Structure

### Documentation (this feature)

```text
specs/010-layout-sobre-nos/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/sobre-nos-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
  templates/content/node--quem-somos.html.twig   # 1ª seção → wrap/float
  assets/css/layout-sobre-nos.css                # NOVO
  default.libraries.yml                          # + layout_sobre_nos

modules/custom/custom_configs/
  custom_configs.install                         # custom_configs_update_11013

config/sync/                                     # só se form/view/instances mudarem na origem
  core.entity_form_display.node.quem_somos.default.yml   # (possível no-op)
  core.entity_view_display.node.quem_somos.default.yml   # (possível no-op)
  field.field.node.quem_somos.*                          # (já existem)

PRD.md                                           # §3.1 tabela + novo § quem_somos
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Twig 1ª seção (float + clearfix) + library/CSS (proporção, Poppins, geometria, mobile)
3. Hook `11013` defensivo (fields/displays)
4. `drush cex` se houver mudança estrutural; PRD cirúrgico
5. Validação quickstart (`cim` → `updb` → `cr`)

## Complexity Tracking

Nenhuma violação de gate a justificar.
