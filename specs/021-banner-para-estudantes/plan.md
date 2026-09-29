# Implementation Plan: Banner (Hero) Para Estudantes

**Branch**: `feature-para-estudantes` (scaffolding Spec Kit — `.specify/scripts` ausente neste repo; setup a partir de `.specify/feature.json` → `specs/021-banner-para-estudantes`)  
**Date**: 2026-09-29  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/021-banner-para-estudantes/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Substituir o banner legado `banners-block_1` em `/para-estudantes` por um hero dedicado: display `block_para_estudantes` da View `banners` (filtro `field_local_exibicao=para_estudantes`), placement na região `banner`, layout duas colunas (copy/CTAs no Twig | ilustração do nó), container `max-width: 1280px` e paddings Figma `64px 40px 48px 40px`, seed de **um** banner + asset em `custom_configs/assets/banner-para-estudantes/`, hook idempotente `custom_configs_update_11033` + `drush cex` → `config/sync`, PRD cirúrgico. Arquitetura espelha 019 (PE); markup espelha 009 (Quem Somos bipartido). Sem carrossel; sem `core/`/`vendor/`; sem dependência Composer nova.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3 do tema  
**Primary Dependencies**: Drupal core (Node, Views, Block, Image, Path); tema `default` (Bootstrap Barrio 5). **Nenhuma dependência Composer/npm nova**.  
**Storage**: PostgreSQL; estrutura em `config/sync`; seed via `hook_update_N` + Entity API  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile &lt;992px / lg+ ≥992px — breakpoints Bootstrap)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: library `banner_para_estudantes` só na página; omit empty; 1 imagem eager; sem JS de carrossel  
**Constraints**: sem `core/`/`vendor/`; zero field storages novos; deploy `cim` → `updb` → (2ª `cim`) → `cr`; pt-BR; isolamento CSS sob wrapper do display; não alterar home / Quem Somos / PE / Contato  
**Scale/Scope**: 1 valor lista + 1 View display + 1 placement + desativação legado + 1 node seed + Twig/CSS + hook `11033` + PRD §3.1.1b / §3.6

**Estado atual verificado (2026-09-29):**

- Último hook: `custom_configs_update_11032` → próximo livre **`11033`**.
- `field_local_exibicao` allowed: `home` | `internas` | `quem_somos` | `para_empresas` — **sem** `para_estudantes`.
- View `banners`: displays `block_home`, `block_1`…, `block_quem_somos`, `block_para_empresas` — **sem** `block_para_estudantes`.
- Placement legado `default_views_block__banners_block_1`: região `banner`, **pages = `/para-estudantes` apenas** (único consumidor restante).
- Padrão visual bipartido existente: Twig Quem Somos (`banner_media` + copy no Twig); PE = carrossel full-bleed (não reutilizar markup PE).
- Rota listagem: View `vagas` `page_1` path `para-estudantes`; âncora estável `#main-content` em `page.html.twig`.
- Cadastro estudante: `/cadastro/candidato` (PRD §2.2).

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storages | PASS | só `banners` + `field_local_exibicao`; zero storage novo |
| Deploy `cim` → `updb` → `cr` (+ 2ª `cim`) + hook idempotente | PASS | `11033` + `drush cex` estrutural |
| Clean URLs | PASS | visibilidade `/para-estudantes`; CTAs canônicos |
| Performance / CSS isolado | PASS | library `banner_para_estudantes`; wrapper `.hero-estudantes-wrapper` |
| Contrib first / custom mínimo | PASS | sem módulo novo; reuso View `banners` |
| Sem dump / Entity API | PASS | seed + ensure via Entity API |
| Convivência home / QS / PE / Contato | PASS | placement só `/para-estudantes`; CSS escopado |

**Post-design**: gates mantidos; R2 (copy no Twig) e R4 (desativar `block_1`) justificados em research. Sem violação injustificada.

## Design Decisions

1. **Valor `para_estudantes`**: allowed value em `field_local_exibicao` (label “Para Estudantes”), espelhando `para_empresas` / `quem_somos`.
2. **Display**: `block_para_estudantes`; filtros `status=1`, `type=banners`, `local=para_estudantes`; sort `field_peso` ASC; pager **`some` items_per_page=1** (hero único).
3. **Placement**: `views_block:banners-block_para_estudantes`, região `banner`, tema `default`, pages só `/para-estudantes`, `label_display: '0'`.
4. **Legado**: `default_views_block__banners_block_1.status = false` (não esvaziar `pages` — path vazio com negate=false = todas as rotas).
5. **Copy/CTAs no Twig**; nó seed alimenta **somente** imagem(ns) + peso + local (padrão Quem Somos / PE).
6. **CTAs**: “Encontrar minha vaga” → `/para-estudantes#main-content`; “Criar meu perfil” → `/cadastro/candidato`.
7. **Layout**: `.hero-estudantes-wrapper` max-width 1280px, padding `64px 40px 48px 40px`; `.row.align-items-center`; colunas `.col-12.col-lg-6`.
8. **Hook `11033`**: ensure allowed value + display defensivo + placement + seed + desativar `block_1`; idempotente; não sobrescrever editorial.
9. **Asset**: `modules/custom/custom_configs/assets/banner-para-estudantes/hero.png` (ou `slide-1.png`) → `public://` no hook.
10. **PRD**: §3.1.1b (`para_estudantes`) + §3.6 (display + convivência com `block_1` desativado).

## Project Structure

### Documentation (this feature)

```text
specs/021-banner-para-estudantes/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── banner-para-estudantes-render.md
│   └── deploy-banner-para-estudantes.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  field.storage.node.field_local_exibicao.yml          # + para_estudantes
  views.view.banners.yml                               # + block_para_estudantes
  block.block.default_views_block__banners_block_para_estudantes.yml
  block.block.default_views_block__banners_block_1.yml # status: false

themes/custom/default/
  templates/views/views-view--banners--block-para-estudantes.html.twig
  templates/views/views-view-unformatted--banners--block-para-estudantes.html.twig
  assets/css/banner-para-estudantes.css
  default.libraries.yml                                # + banner_para_estudantes
  default.theme                                        # preprocess banner_media (espelho QS)

modules/custom/custom_configs/
  custom_configs.install                               # custom_configs_update_11033 + helpers
  assets/banner-para-estudantes/                       # hero.png (ilustração Figma)

PRD.md                                                 # §3.1.1b + §3.6
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. Config: allowed value + View display + placements → `drush cex`
3. Twig + CSS + preprocess (`banner_media`)
4. Hook `11033` + asset seed
5. PRD + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Copy no Twig (não em fields) | `banners` só tem imagens/peso/local; mesma decisão 009/019; YAGNI | Novos storages de texto/link — fora do escopo |
| Desativar `block_1` em vez de esvaziar pages | Pages vazio = bloco em **todas** as rotas (risco) | Remover config do sync — perda histórica desnecessária |
| Pager `some`/1 vs. `none` | US3: segundo banner editorial não deve quebrar layout 2 colunas | `none` + confiança no editor — frágil |
