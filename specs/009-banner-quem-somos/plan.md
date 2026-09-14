# Implementation Plan: Banner da página Quem Somos

**Branch**: `dev` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo, setup executado manualmente)  
**Date**: 2026-09-14  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/009-banner-quem-somos/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar o banner Figma da página `/quem-somos`: caixa branca bipartida (copy + CTAs fixos no Twig à esquerda; foto editorial à direita com overflow controlado). Reutilizar o content type `banners` e a View `banners` com display exclusivo `block_quem_somos`, filtro `field_local_exibicao = quem_somos`, placement na região `banner` só em `/quem-somos`, remoção desse path do bloco legado `banners-block_1`, seed idempotente `custom_configs_update_11012` + asset PNG, CSS/Twig isolados sob `.banner-quem-somos-wrapper`, e exportação via `drush cex` → `config/sync`.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Node, Views, Block, Image, Options); tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3); `twig_tweak` se já usado para `image_style`/`file_url`. **Nenhuma dependência contrib nova.**  
**Storage**: PostgreSQL; estrutura em `config/sync`; conteúdo seed via `hook_update_N`  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (desktop ≥768px / mobile &lt;768px — breakpoint Bootstrap `md`)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS encapsulado; imagem única com `fetchpriority="high"`; sem JS novo  
**Constraints**: sem alteração em `core/`/`vendor/`; sem fields novos no tipo `banners`; textos/CTAs 100% no Twig; deploy `cim` → `updb` → `cr`; pt-BR  
**Scale/Scope**: 1 allowed value, 1 display View, 2 block placements (novo + ajuste legado), 1–2 Twigs, 1 CSS/library, 1 hook + asset, PRD § banners

**Estado atual verificado (2026-09-14):**

- `field.storage.node.field_local_exibicao`: só `home` | `internas`.
- View `banners`: displays `block_1`/`block_2`/`block_3`/`block_home`; sort canônico `field_peso ASC` + `created DESC` no default.
- `block.block.default_views_block__banners_block_1`: região `banner`, pages incluem `/quem-somos` (a remover).
- `block.block.default_views_block__banners_block_home`: região `banner`, `<front>` — não alterar.
- Último hook: `custom_configs_update_11011` → próximo livre **`11012`**.
- Token global `--brand-orange: #ff8a22` diverge do Figma `#FD7B1A` — override **só** sob o wrapper (ver research).
- Asset seed ainda não versionado; destino planejado: `modules/custom/custom_configs/assets/banner-quem-somos/`.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storages | PASS | zero storages novos; só allowed value + View/bloco |
| Deploy `cim` → `updb` → `cr` + seed idempotente | PASS | `11012` + `drush cex` estrutural |
| Clean URLs | PASS | CTAs `/para-estudantes`, `/cadastro/candidato`; visibilidade `/quem-somos` |
| Performance / CSS isolado | PASS | estilos sob `.banner-quem-somos-wrapper`; library dedicada |
| Contrib first / custom mínimo | PASS | sem módulo novo; reusa View `banners` |

**Post-design**: gates mantidos; sem violação injustificada.

## Design Decisions

1. **Allowed value `quem_somos`**: acrescentar em `field.storage.node.field_local_exibicao` (rótulo “Quem Somos”); exportar via `cex`.
2. **Display `block_quem_somos`**: filtro `status=1` + tipo `banners` + `local=quem_somos`; herda sorts do default; pager/limit 1 item; sem empty area (zero resultados = sem markup).
3. **Placement**: `views_block:banners-block_quem_somos` → região `banner`, tema `default`, pages só `/quem-somos`; weight alinhado ao legado (0) — convivência por path, não por peso.
4. **Legado `block_1`**: remover `/quem-somos` da lista; manter `/para-estudantes`, `/para-empresas`, `/contato`.
5. **Copy/CTAs no Twig**: tag, título bipartido, parágrafo e botões fixos (padrão 001/002); node só entrega imagem.
6. **Cores**: título escuro `#0F172A`; laranja Figma `#FD7B1A` via variável local no wrapper (não alterar `--brand-orange` global).
7. **Imagem**: `<picture>` com mobile fallback = desktop; preferir URI/estilo que preserve alpha do PNG; overflow desktop via CSS (`overflow` no wrapper + margens negativas na coluna de mídia).
8. **Seed `11012`**: se não existir node `banners` publicado com `quem_somos`, criar com UUID fixo + anexar PNG versionado; nunca sobrescrever mídia editorial; defensivo: garantir allowed value / paths de visibilidade se `cim` já aplicou o restante.
9. **PRD**: atualizar §3.1.1b (`field_local_exibicao`) e §3.6 (View banners / Quem Somos) de forma cirúrgica.

## Project Structure

### Documentation (this feature)

```text
specs/009-banner-quem-somos/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/banner-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  field.storage.node.field_local_exibicao.yml   # + quem_somos
  views.view.banners.yml                        # + display block_quem_somos
  block.block.default_views_block__banners_block_quem_somos.yml  # NOVO
  block.block.default_views_block__banners_block_1.yml           # - /quem-somos

themes/custom/default/
  templates/views/views-view--banners--block-quem-somos.html.twig
  templates/views/views-view-unformatted--banners--block-quem-somos.html.twig
  assets/css/banner-quem-somos.css
  default.libraries.yml                         # library banner_quem_somos

modules/custom/custom_configs/
  custom_configs.install                        # custom_configs_update_11012
  assets/banner-quem-somos/quem-somos-img.png   # seed (PNG transparente)

PRD.md                                          # §3.1.1b + §3.6
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Config: allowed value + display View + placements → `drush cex`
3. Twig bipartido + library/CSS isolados
4. Seed `11012` + asset PNG
5. PRD cirúrgico + validação quickstart (`cim` → `updb` → `cr`)

## Complexity Tracking

Nenhuma violação de gate a justificar.
