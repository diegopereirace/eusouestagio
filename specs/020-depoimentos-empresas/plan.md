# Implementation Plan: Bloco de Depoimentos — Para Empresas

**Branch**: `feature-para-empresas` (scaffolding Spec Kit — `.specify/scripts` ausente neste repo; setup executado manualmente a partir de `.specify/feature.json`)  
**Date**: 2026-09-26  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/020-depoimentos-empresas/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Adicionar na landing `/para-empresas` a seção **Depoimentos** entre Benefícios e o CTA final: content type `depoimento` (reuso de `field_text_simple`, `field_text_simple_long`, `field_imagem` + título = autor), View `depoimentos_carousel` display `block_depoimentos_empresas`, carrossel center mode (CSS+JS próprio, dots, autoplay off), seed ≥4 nodes com avatares em `custom_configs/assets/depoimentos/`, placement `content_full` weight **4** + CTA PE weight **5**, hook idempotente `custom_configs_update_11032` + `drush cex` → `config/sync`, PRD cirúrgico. Sem dependência Composer nova; sem `core/`/`vendor/`.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3 / JS ES5+ do tema  
**Primary Dependencies**: Drupal core (Node, Views, Block, Image, Path alias); tema `default` (Bootstrap Barrio 5). **Nenhuma dependência Composer/npm nova** (carrossel = library própria).  
**Storage**: PostgreSQL; estrutura em `config/sync`; seeds via `hook_update_N` + Entity API  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile &lt;992px / lg+ ≥992px — breakpoints Bootstrap)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS/JS encapsulados na library `depoimentos_carousel`; omit empty; sem autoplay; imagens via style `thumbnail`  
**Constraints**: sem `core/`/`vendor/`; zero storages novos; deploy `cim` → `updb` → (2ª `cim`) → `cr`; pt-BR; não alterar visual home/Quem Somos/banner/CTA além do weight do CTA PE  
**Scale/Scope**: 1 content type + 3 field instances + displays + 1 View display + 1 placement + ajuste weight CTA; ≥4 nodes seed; 1 hook `11032`; Twig View + CSS/JS; PRD §3.1 / §3.6

**Estado atual verificado (2026-09-26):**

- Último hook: `custom_configs_update_11031` → próximo livre **`11032`**.
- Storages disponíveis: `field_text_simple`, `field_text_simple_long`, `field_imagem` — **sem** tipo `depoimento` ainda.
- Composição PE `content_full`: weights 0–4 (Diferenciais → Metodologia → OQF → Benefícios → CTA `default_ctav1paraempresas` weight **4**).
- Carrosséis existentes = Bootstrap (banners); **sem** Swiper/Splide no tema.
- Image style `thumbnail` (100×100) disponível para avatar + CSS 48px.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storages | PASS | `field_text_simple` / `_long` / `field_imagem`; zero storage novo |
| Deploy `cim` → `updb` → `cr` (+ 2ª `cim`) + hook idempotente | PASS | `11032` + `drush cex` estrutural |
| Clean URLs | PASS | visibilidade `/para-empresas` |
| Performance / CSS isolado | PASS | library `depoimentos_carousel`; wrapper `.depoimentos-empresas` |
| Contrib first / custom mínimo | PASS | sem módulo novo; JS próprio em vez de Swiper |
| Sem dump / Entity API | PASS | seeds + ensure via Entity API |
| Convivência home / Quem Somos | PASS | placement só `/para-empresas`; weight CTA PE isolado |

**Post-design**: gates mantidos; R3 (JS próprio) e R2 (CTA weight 5) justificados em research. Sem violação injustificada.

## Design Decisions

1. **Bundle `depoimento`**: título = nome do autor; instances com labels editoriais claras (ver research R1).
2. **View**: `depoimentos_carousel` / `block_depoimentos_empresas`; publicados; `created` DESC; pager none.
3. **Placement**: `content_full`, só `/para-empresas`, weight **4**; CTA PE → weight **5**.
4. **Carrossel**: library `default/depoimentos_carousel` (CSS+JS); center mode + dots + opacidade laterais; autoplay off; sem loop infinito na v1.
5. **Card**: tokens Figma (branco, 16px, 32px, ~404px, avatar 48px); style `thumbnail` + CSS.
6. **Hook `11032`**: seeds ≥4 + assets + ensure placement/weights; idempotente; não sobrescrever editorial.
7. **Assets**: `modules/custom/custom_configs/assets/depoimentos/avatar-1.png` … `avatar-4.png` (placeholders).
8. **PRD**: §3.1 content type; §3.1.0b / §3.6 composição + View + hook `11032`.
9. **Fallback**: se center mode custom falhar no aceite visual, avaliar Swiper vendored no tema (sem Composer) — fora do caminho feliz.

## Project Structure

### Documentation (this feature)

```text
specs/020-depoimentos-empresas/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── depoimentos-carousel-render.md
│   └── composition-para-empresas.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  node.type.depoimento.yml
  field.field.node.depoimento.field_text_simple.yml
  field.field.node.depoimento.field_text_simple_long.yml
  field.field.node.depoimento.field_imagem.yml
  core.entity_form_display.node.depoimento.default.yml
  core.entity_view_display.node.depoimento.default.yml
  core.entity_view_display.node.depoimento.teaser.yml   # se usado pela View
  views.view.depoimentos_carousel.yml
  block.block.default_views_block__depoimentos_carousel_block_depoimentos_empresas.yml
  block.block.default_ctav1paraempresas.yml             # weight: 5

themes/custom/default/
  templates/views/views-view--depoimentos-carousel--block-depoimentos-empresas.html.twig
  templates/views/views-view-unformatted--depoimentos-carousel--block-depoimentos-empresas.html.twig
  templates/node/node--depoimento--teaser.html.twig     # opcional se row = content
  assets/css/depoimentos-carousel.css
  assets/js/depoimentos-carousel.js
  default.libraries.yml                                   # + depoimentos_carousel

modules/custom/custom_configs/
  custom_configs.install                                  # custom_configs_update_11032 + helpers
  assets/depoimentos/                                     # avatar-1.png … avatar-4.png

PRD.md                                                    # §3.1 + §3.1.0b + §3.6
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. Config: tipo + fields + displays + View + placements/weights → `drush cex`
3. Twig + CSS/JS carrossel center mode
4. Hook `11032` + assets seed
5. PRD + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| JS de carrossel custom em vez de Swiper | Evita dependência/vendor; comportamento exigido é limitado (center + dots + opacity) | Swiper via Composer/CDN — só fallback se SC-001 falhar |
| CTA PE weight 4→5 | Necessário para encaixar Depoimentos entre Benefícios e CTA | Weight fracionário — impossível no Block API |
| Reuso `thumbnail` em vez de style 48px | YAGNI; CSS cobre o tamanho visual | Novo image style — adicionar só se nitidez/perf exigir |
