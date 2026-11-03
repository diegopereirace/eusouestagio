# Implementation Plan: Benefícios Estudantes — Layout Figma (split + colagem)

**Branch**: `main` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo; setup executado manualmente a partir de `.specify/feature.json` → `specs/054-beneficios-estudantes-figma`)  
**Date**: 2026-11-03  
**Spec**: [spec.md](spec.md) | **Checklist**: [checklists/requirements.md](checklists/requirements.md)  
**Predecessors**: `022-beneficios-estudantes`, `053-refino-figma-para-estudantes` (mesmo bloco/placement; esta feature **substitui** o layout/copy de 4 cards)  
**Hook**: `custom_configs_update_11078`  
**Input**: Especificação em `specs/054-beneficios-estudantes-figma/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Redesenhar **in-place** a seção Benefícios em `/para-estudantes` (mesmo `beneficios_estudantes` / placement `default_beneficiosestudantes`) para o frame Figma: badge + título + subtítulo alinhados à esquerda, **6** itens em lista/grade 2 colunas (ícone à esquerda do texto, sem card-box dominante), CTA laranja “Cadastre-se gratuitamente” → `/cadastro/candidato`, e colagem circular à direita no desktop (arte composta versionada em `field_image`). Seed/fill-if-empty + migração só de legado conhecido das 4 cards; `hook_update_N` `11078` + `drush cex` se fields/displays mudarem; PRD §3.6 cirúrgico. Zero passos manuais em produção. Fora: banner, Hero, jornada, `block_3`, CTA final, outras rotas.

## Technical Context

| Item | Valor |
|------|--------|
| Language/Version | PHP 8.3+ / 8.4, Drupal 11, Twig 3, CSS3 |
| Primary Dependencies | Block Content `beneficios_estudantes`; paragraph `card_icon_text_p`; storages existentes `field_text_simple`, `field_text_simple_2`, `field_text_simple_long`, `field_itens_lista`, `field_link`, `field_image`; tema `default` (Bootstrap 5). **Sem** Composer novo |
| Storage | Sem field **storage** novo. Instances novas no bundle: `field_text_simple_2` (badge), `field_link` (CTA), `field_image` (colagem). UUID bloco `a8b9c0d1-e2f3-4456-a789-0bcdef123456`. Placement inalterado |
| Testing | Manual via [quickstart.md](quickstart.md); sem PHPUnit dedicado |
| Target Platform | `/para-estudantes`; breakpoints tema `046` (768 / 992) |
| Performance | Library `beneficios_estudantes` só no bloco; ícones e colagem `loading="lazy"`; sem JS novo |
| Constraints | Sem `core/`/`vendor/`; sem SCSS toolchain; CSS isolado sob `.block-beneficios-estudantes`; não regressar banner/Hero/jornada/`block_3`/CTA final nem PE/QS/home |
| Scale/Scope | Twig bloco + paragraph + CSS; 3 field instances + displays; hook `11078`; 6 ícones + 1 arte colagem em assets; PRD §3.6 |

**Estado atual verificado (2026-11-03):**

- Último hook: **`custom_configs_update_11077`** → próximo livre **`11078`**.
- Bundle `beneficios_estudantes` com instances: `field_text_simple`, `field_text_simple_long`, `field_itens_lista` → `card_icon_text_p`. **Sem** badge, CTA nem imagem de colagem.
- Storages reutilizáveis **existem**: `field_text_simple_2`, `field_link`, `field_image` em `block_content`.
- Twig: cabeçalho **centrado**; grid `col-12` / `col-md-6` / `col-lg-3` (1/2/4); paragraph com `.be-card` (borda, fundo `#F8F9FF`, min-height 310px), ícone no topo.
- Seed vivo: título “Encontre oportunidades…”, subtítulo (pós-053 ou legado 11035), **4** cards (`Vagas alinhadas` … `Filtros inteligentes`); assets `icon-1.png`…`icon-4.png`.
- Placement `default_beneficiosestudantes`: `content_full` weight `0`, `/para-estudantes` — **manter**.
- `mattpocock/skills`: não instalado; seguir padrões do repo.

## Constitution Check

Não há `.specify/memory/constitution.md`. Gates: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + `estagio-fluxo-dev.mdc` + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist; zero `[NEEDS CLARIFICATION]` |
| Sem `core/` / `vendor/` | PASS | só tema `default`, `custom_configs`, `config/sync` via cex, `PRD.md` |
| Reuso / sem storage paralelo | PASS | instances de storages canônicos; BT/PT existentes |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | `11078` + cex das instances/displays |
| Seeds Entity API / não sobrescrever editorial | PASS | fill-if-empty + só legado conhecido das 4 cards |
| Clean URLs | PASS | `/para-estudantes`; CTA seed `/cadastro/candidato` |
| Performance / CSS isolado | PASS | library existente; seletores sob `.block-beneficios-estudantes` |
| Contrib first / custom mínimo | PASS | sem módulo/tipo novo |
| Convivência 021–027 / 053 | PASS | só seção Benefícios; demais blocos intocados |
| PRD guardião | A atualizar na implementação | §3.6 descrição Benefícios |
| Reinício cache | A executar na implementação | `drush cr` |

**Post-design**: gates mantidos. Trade-offs em [research.md](research.md) e Complexity Tracking. Sem violação injustificada.

## Architecture

```text
/para-estudantes
├─ banner / highlighted (Hero)     — FORA
├─ content_full
│   ├─ beneficios_estudantes (w0)  ← ESTA FEATURE
│   │    ├─ field_text_simple_2    badge
│   │    ├─ field_text_simple      título
│   │    ├─ field_text_simple_long subtítulo
│   │    ├─ field_itens_lista      card_icon_text_p × 6
│   │    ├─ field_link             CTA
│   │    └─ field_image            colagem composta
│   ├─ jornada / block_3 / cta_v1  — FORA
hook 11078 — ensure fields/displays + migrate legado 4→6 + seed CTA/colagem/ícones
```

## Design Decisions

1. **Reuso do bundle**: não criar segundo bloco; redesenho in-place do UUID `a8b9…` (R1).
2. **Badge** → instance `field_text_simple_2`; **CTA** → `field_link`; **colagem** → `field_image` (arte única composta) (R2).
3. **Itens**: manter `card_icon_text_p`; Twig passa a linha ícone+texto; grid `col-12 col-md-6` (2 colunas ≥768); remover chrome de card (R3).
4. **Layout split**: desktop ≥992 — coluna texto/itens/CTA à esquerda + colagem à direita; &lt;992 — colagem ocultada (conteúdo empilhado legível) (R4).
5. **Migração copy**: substituir título/subtítulo/itens **somente** se vazio ou legado conhecido das 4 cards; editorial divergente preservado (R5).
6. **Assets**: `modules/custom/custom_configs/assets/beneficios-estudantes/icon-1.png`…`icon-6.png` + `collage.png` (ou nome equivalente); hook copia → `public://` se ausente (R6).
7. **Hook `11078`**: ensure instances/displays; seed/migrate; ensure placement ativo; idempotente (R7).
8. **Origem**: `drush updb -y` → `drush cex -y` → `drush cr`. **Destino**: `cim` → `updb` → `cim` → `cr`.
9. **PRD** §3.6 cirúrgico (badge, 6 itens, CTA, colagem, hook `11078`).
10. **Fora**: SCSS; novo BT/PT; edição foto-a-foto da órbita; remover CTA final `026`; regressão outras seções/rotas.

## Project Structure

### Documentation (this feature)

```text
specs/054-beneficios-estudantes-figma/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── beneficios-estudantes-figma-render.md
│   └── deploy-beneficios-estudantes-figma.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/   # via drush cex (não editar à mão para inventar)
  field.field.block_content.beneficios_estudantes.field_text_simple_2.yml
  field.field.block_content.beneficios_estudantes.field_link.yml
  field.field.block_content.beneficios_estudantes.field_image.yml
  core.entity_form_display.block_content.beneficios_estudantes.default.yml
  core.entity_view_display.block_content.beneficios_estudantes.default.yml

themes/custom/default/
  templates/block/block--block-beneficios-estudantes.html.twig
  templates/paragraph/paragraph--card-icon-text-p.html.twig
  assets/css/beneficios-estudantes.css
  # default.libraries.yml — library beneficios_estudantes já existe

modules/custom/custom_configs/
  custom_configs.install          # custom_configs_update_11078 + helpers
  assets/beneficios-estudantes/   # icon-1…6 + collage

PRD.md                            # §3.6 cirúrgico
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. `/speckit-tasks` → tasks ordenadas
3. Implementação: field instances/displays → Twig/CSS split → assets → hook `11078` → `updb` → `cex` → PRD → `cr`
4. Validação [quickstart.md](quickstart.md)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Instance `field_text_simple_2` para badge (não listado explicitamente na Assumption da spec) | Storage canônico já existe; título já ocupa `field_text_simple`; sem storage paralelo | Hardcode do badge (piora US3) ou storage novo |
| Colagem = 1 `field_image` composta | Spec permite arte versionada; editor não itemiza órbita nesta versão | 7 imagens + montagem CSS (escopo CMS maior) |
| Hook reescreve set legado das 4 cards → 6 | FR-002/FR-007; seed antigo ≠ Figma; editorial livre preservado | Overwrite sempre, ou deixar 4 cards |
| Ocultar colagem &lt;992px | FR-004; evita compressão ilegível | Sempre empilhar (ocupa viewport sem ganho editorial) |
| `mattpocock/skills` ausente | Padrões do repo cobrem Twig/CSS/hook | Bloquear feature |
