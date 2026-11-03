# Implementation Plan: Refino Visual "Para Estudantes" — Conformidade com Figma

**Branch**: `main` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo; setup executado manualmente a partir de `.specify/feature.json` → `specs/053-refino-figma-para-estudantes`)  
**Date**: 2026-11-03  
**Spec**: [spec.md](spec.md) | **Checklist**: [checklists/requirements.md](checklists/requirements.md)  
**Predecessors**: `021-banner-para-estudantes`, `022-beneficios-estudantes`, `023-jornada-estudante`, `024-perfil-destaque-estudante`, `025-vagas-cards-estudantes`, `026-cta-final-estudantes`, `027-vagas-hero-search`, `052-refino-figma-para-empresas` (paridade de carrossel)  
**Hook**: `custom_configs_update_11077`  
**Input**: Especificação em `specs/053-refino-figma-para-estudantes/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Fechar as **lacunas reais** de `/para-estudantes` frente ao Figma e a `/para-empresas` / `/vagas`, sem redesenhar o que `021`–`027` já entregaram:

1. **Banner**: paridade de init/transição/setas com Para Empresas (reuso de `banner_carousel_js` + `banner_carousel_cinematic`, sem CSS da home); CTAs mobile em **768px** com larguras iguais.
2. **Hero Search**: segundo placement do **mesmo** plugin `custom_banners_vagas_hero_search`, região `highlighted`, só `/para-estudantes`; o Twig da página passa a renderizar `highlighted` entre o banner e `content_full` nessa rota.
3. **Benefícios**: alinhar cards ao Figma (ícone 64px no topo à esquerda, título negrito, descrição `#45464D`); grade 1/2/4 já existe. Hook só preenche vazio e corrige legado conhecido (subtítulo seed `11035` e título “Filtros Inteligentes”).
4. **Perfil em destaque**: `status: false` no placement `default_perfildestaqueestudante` (entidade permanece).
5. **CTA final**: zerar o vão entre a faixa escura e o footer (o `#main-wrapper` do node shell fica entre os dois). Sem alterar CTAs de Quem Somos / Para Empresas.

Zero passos manuais em produção.

## Technical Context

| Item | Valor |
|------|--------|
| Language/Version | PHP 8.3+ / 8.4, Drupal 11, Twig 3, CSS3, JS ES5 (behaviors) |
| Primary Dependencies | View `banners` `block_para_estudantes`; plugin `custom_banners_vagas_hero_search`; Block Content `beneficios_estudantes` / `perfil_destaque_estudante` / `cta_v1`; paragraph `card_icon_text_p`; tema `default` (Bootstrap 5). **Sem** Composer novo |
| Storage | Sem tipos/fields novos. Benefícios UUID `a8b9c0d1-e2f3-4456-a789-0bcdef123456`. Perfil UUID `c0d1e2f3-a4b5-4678-c901-2def01234567`. CTA UUID `e1f2a3b4-c5d6-4789-d012-3ef012345678`. Novo placement Hero UUID `e7f8a9b0-c1d2-4345-b678-0530abcdef01` |
| Testing | Manual via [quickstart.md](quickstart.md); sem PHPUnit dedicado |
| Target Platform | `/para-estudantes` (node `page`); breakpoints 768 / 992 |
| Performance | Banner LCP `eager` no 1º slide; ícones de benefícios `loading="lazy"`; Hero sem imagem |
| Constraints | Sem `core/`/`vendor/`; sem SCSS toolchain; sem novos bundles; CSS plano; não regressar `/para-empresas`, `/vagas`, home, jornada, `block_3` |
| Scale/Scope | Twig/CSS/libraries do tema + 1 hook `11077` + 1 YAML de placement (via cex) + PRD cirúrgico |

**Estado atual verificado (2026-11-03):**

- Último hook: **`custom_configs_update_11076`** → próximo livre **`11077`**.
- Banner estudantes: Twig Bootstrap **sem** classes `banner-carousel` / `banner-carousel--cinematic`, **sem** `data-bs-pause="hover"`, library **só CSS** + Bootstrap. CTAs saem do overlay em **991.98px** (PE já usa **767.98px** + larguras iguais).
- PE (pós-052): library depende de `banner_carousel_js` + `banner_carousel_cinematic`; intervalo `7000`; pause hover.
- Hero `/vagas`: placement `default_custom_banners_vagas_hero_search` em `highlighted` weight `-50`, pages `/vagas`. **Não** há placement em `/para-estudantes`.
- Em `/para-estudantes`, `content_full_after_banner` renderiza `content_full` **antes** de `highlighted` e **antes** de `#main-wrapper`. Colocar o Hero em `highlighted` sem reordenar o Twig o jogaria depois do CTA.
- Benefícios: grid `col-12` / `col-md-6` / `col-lg-3` já é 1/2/4; card centralizado (`text-center`); descrição `#0f172a` (alvo `#45464D`); ícone já 64px. Seed `11035` subtítulo ≠ copy Figma; 4º título = `Filtros Inteligentes`.
- Perfil: placement `status: true`, `content_full` weight `2`, pages `/para-estudantes`.
- CTA: último de `content_full` (weight `4`), mas o node shell (`#main` / `.section`) fica **entre** o CTA e o `<footer>`. Footer `#023c62` + padding interno 64px. CSS do CTA estudantes não zera essa folga.
- `mattpocock/skills`: não instalado.

## Constitution Check

Não há `.specify/memory/constitution.md`. Gates: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + `estagio-fluxo-dev.mdc` + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist; zero `[NEEDS CLARIFICATION]` |
| Sem `core/` / `vendor/` | PASS | só tema `default`, `custom_configs`, `PRD.md`, `config/sync` via cex |
| Reuso / sem tipos novos | PASS | mesmo plugin Hero; BT/PT existentes |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | `11077` + cex do placement novo e do `status: false` |
| Seeds Entity API / não sobrescrever editorial | PASS | fill-if-empty + só strings legado conhecidas |
| Clean URLs | PASS | `/para-estudantes` e GET → `/vagas` inalterados |
| Performance / CSS isolado | PASS | libraries por seção; sem pipeline SCSS |
| Contrib first / custom mínimo | PASS | segundo placement do plugin existente |
| Convivência 021–027 / 052 | PASS | só lacunas; jornada e `block_3` permanecem; PE/vagas sem regressão |
| Tokens locais (não mutar `--brand-orange` global) | PASS | `#45464D` só em `.be-card__text` |
| PRD guardião | A atualizar na implementação | §3.6 composição `/para-estudantes` |
| Reinício cache | A executar na implementação | `drush cr` |

**Post-design**: gates mantidos. Trade-offs em [research.md](research.md) e Complexity Tracking. Sem violação injustificada.

## Architecture

```text
/para-estudantes (node page)
├─ região banner
│   └─ View banners / block_para_estudantes
│        Twig + library banner_para_estudantes
│          └─ depends → banner_carousel_js + banner_carousel_cinematic
│        CTAs overlay; mobile <768px equal-width
├─ região highlighted   ← movida no Twig para logo após o banner
│   └─ NOVO placement default_custom_banners_vagas_hero_search_estudantes
│        mesmo plugin de /vagas; GET continua em /vagas
├─ content_full
│   ├─ beneficios_estudantes (w0) — CSS/Twig alinhados ao Figma
│   ├─ jornada_estudante (w1) — FORA (permanece)
│   ├─ perfil_destaque — placement status false (entidade permanece)
│   ├─ View vagas block_3 (w3) — FORA (permanece)
│   └─ cta_v1 default_ctav1paraestudantes (w4)
└─ #main-wrapper do node shell — padding/margin 0 nesta rota
     footer encosta no CTA (vão 0)
hook 11077 — disable perfil + ensure hero + ensure benefícios (legado conhecido)
```

## Design Decisions

1. **Banner JS**: classes `banner-carousel` + `banner-carousel--cinematic` + `data-bs-pause="hover"` + intervalo `7000`; dependency só do JS e do CSS cinematográfico, sem `banner-carousel.css` da home (R1).
2. **Breakpoint CTAs**: `767.98px`, larguras iguais, espelhando PE; entre 768 e 991 os CTAs permanecem em overlay (R2).
3. **Hero**: segundo placement em `highlighted` weight `-50`, pages só `/para-estudantes`; reordenar `page.html.twig` quando `content_full_after_banner` (R3).
4. **Sem plugin/copy novos**: mesmo Twig, mesmos IDs (uma instância por página), mesma query string (R3).
5. **Benefícios**: alinhar à esquerda no Twig do paragraph exclusivo; descrição `#45464D`; título do card `font-weight: 700`. Grade atual permanece (R4).
6. **Copy**: hook troca só o subtítulo seed `11035` e o título exato `Filtros Inteligentes`; qualquer outro texto editorial fica (R5).
7. **Perfil**: `setStatus(FALSE)` sempre; não apagar o `block_content` (R6).
8. **CTA/footer**: zerar padding/margin de `#main-wrapper` / `.section` e margin do último bloco em `body.path-para-estudantes`; sem borda branca inventada; sem tocar `cta-v1.css` (R7).
9. **Origem**: `drush cex -y` (placement novo + `status: false` do perfil).
10. **Destino**: `git pull` → `cim` → `updb` → `cim` → `cr`.
11. **PRD** cirúrgico §3.6 (composição: Hero após o banner; perfil fora da superfície pública).
12. **Fora**: SCSS; novo BT/PT; jornada; `block_3`; autocomplete novo; deletar entidade do perfil; regressão PE/`/vagas`/home.

## Project Structure

### Documentation (this feature)

```text
specs/053-refino-figma-para-estudantes/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── para-estudantes-refino-render.md
│   └── deploy-para-estudantes-refino.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
  default.libraries.yml
      # banner_para_estudantes → depends banner_carousel_js + banner_carousel_cinematic
  templates/layout/page.html.twig
      # content_full_after_banner: highlighted entre banner e content_full
  templates/views/views-view--banners--block-para-estudantes.html.twig
  templates/paragraphs/paragraph--card-icon-text-p.html.twig
      # ícone/título/texto alinhados ao topo à esquerda
  assets/css/banner-para-estudantes.css
      # breakpoint 767.98px + larguras iguais
  assets/css/beneficios-estudantes.css
      # descrição #45464D; título 700
  assets/css/cta-v1-para-estudantes.css
      # vão CTA→footer = 0 só em body.path-para-estudantes

modules/custom/custom_configs/
  custom_configs.install
      # custom_configs_update_11077

config/sync/
  block.block.default_custom_banners_vagas_hero_search_estudantes.yml   # via cex
  block.block.default_perfildestaqueestudante.yml                       # status: false via cex

PRD.md    # §3.6 cirúrgico
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. `/speckit-tasks` → tasks ordenadas
3. Implementação: banner (library/Twig/CSS) → reorder highlighted + placement Hero → benefícios Twig/CSS → disable perfil → CSS do vão CTA → hook `11077` → `updb` → `cex` → PRD → `cr`
4. Validação [quickstart.md](quickstart.md)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Segundo placement do mesmo plugin | FR-002/FR-004 exigem a mesma UI e o mesmo contrato de busca | Plugin ou Twig duplicado |
| Reordenar `highlighted` só em `/para-estudantes` | Hoje `content_full` sobe antes de `highlighted`; sem o reorder o Hero ficaria depois do CTA | Placement em `content_full` weight negativo (perde o wrapper `.container.section` de `/vagas`) |
| Reusar JS/CSS cinematográfico, não o CSS `banner-carousel` da home | Paridade de transição sem quebrar overlay de CTAs | Unificar libraries CSS |
| Hook reescreve duas strings legado conhecidas | Copy Figma ≠ seed `11035`; editorial livre continua preservado | Overwrite sempre, ou deixar o subtítulo antigo |
| Colapsar `#main` em vez de mover o CTA | O vão é o shell do node entre `content_full` e o footer | Mover o CTA para outra região |
| `mattpocock/skills` ausente | Pacote não instalado; padrões do repo (plugin + CSS por library) já cobrem o reuso | Bloquear a feature à espera do pacote |
