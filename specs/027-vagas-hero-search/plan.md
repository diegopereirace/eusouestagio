# Implementation Plan: Hero Search — Página de Vagas

**Branch**: `feature-vagas-new` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo, setup executado manualmente via `.specify/feature.json`)  
**Date**: 2026-10-04  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/027-vagas-hero-search/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar o **primeiro bloco** de `/vagas` — Hero Search com título/subtítulo fixos, formulário GET de três campos (`title`, `cidade`, `cursos`) alinhados aos filtros da View `vagas` `page_1`, pills de curso (sem “Ver todas”) e visual Figma (wrapper 1280px, paddings 48/32/40, barra pílula, botão `#58A83C`). Implementação: **Block Plugin novo** em `custom_banners` (distinto do hero da home), CSS/library escopados, filtro exposto `title` na View, placement `highlighted` só em `/vagas`, `custom_configs_update_11044` idempotente + `drush cex`; PRD §3.6 cirúrgico.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Block, Views, System visibility, Taxonomy); módulo `custom_banners` (padrão `HeroSearchBlock`); tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3). **Nenhuma dependência Composer nova.**  
**Storage**: PostgreSQL; estrutura em `config/sync` (View + placement); texts/pills em código (plugin + Twig)  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile ≤575.98px / md+ — breakpoints Bootstrap)  
**Project Type**: Drupal theme + custom modules (`themes/custom/default`, `modules/custom/custom_banners`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS encapsulado em library dedicada; sem JS novo obrigatório (v1 sem autocomplete); cache contexts `url.path` + `url.query_args`; tags de taxonomia `curso`  
**Constraints**: sem `core/`/`vendor/`; **não** alterar o hero da home (`custom_banners_hero_search` / `.hero-search`); deploy `cim` → `updb` → `cim` → `cr`; pt-BR; filtros laterais / cards / contador fora do escopo  
**Scale/Scope**: 1 Block Plugin, 1 theme hook + Twig, 1 library/CSS, 1 filtro View, 1 placement, 1 hook `11044`, PRD cirúrgico

**Estado atual verificado (2026-10-04):**

- Hook desta feature: `custom_configs_update_11044` (`11043` já usado para CTA final estudantes).
- Hero home: plugin `custom_banners_hero_search`, placement `default_custom_banners_hero_search` (`highlighted`, weight `-50`, só `<front>`), Twig `block--hero-search.html.twig`, library `default/hero_search` — **não modificar de forma regressiva**.
- View `vagas` `page_1`: path `vagas`; filtros expostos `nid`, `cursos`, `estado`, `cidade`, `escolaridade`, `regime`; **sem** filtro de título; `exposed_block: true`; formulário exposto placement `default_formularioexpostovagaspage_1` já **desativado** (`status: false`).
- Região correta acima da View page: `highlighted` (mesmo padrão do hero home e do formulário exposto legado).
- `default_page_title` só em nodes `page` — em `/vagas` o hero pode usar `h1` sem conflito com page title block.
- Resolução de pills no home: match exato + prefixo case-insensitive; `cursos` envia **nome do termo** (textfield).

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só `custom_banners`, `custom_configs`, tema, `config/sync`, `PRD.md` |
| Reuso / custom mínimo | PASS | reusa módulo `custom_banners` e padrão do home; zero block type / field storage novo |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | `11044` (filtro View + placement) + `drush cex` estrutural |
| Clean URLs | PASS | `action`/`pills` → `/vagas` + query params |
| Performance / CSS isolado | PASS | library dedicada; classes `.vagas-hero-search*` (não `.hero-search`) |
| Contrib first | PASS | Views + Block plugin; sem Composer novo |
| Sem dump / Entity API | PASS | placement via Block entity; View via `View::load()` |
| Convivência home hero + listagem | PASS | plugin/placement/CSS distintos; cards/filtros laterais intocados |

**Post-design**: gates mantidos. Isolamento visual via BEM/`library` própria justifica Complexity Tracking. Sem violação injustificada.

## Design Decisions

1. **Plugin novo** `custom_banners_vagas_hero_search` (`VagasHeroSearchBlock`) — **não** reutilizar/condicionar o plugin da home.
2. **Form GET** `action` = rota `view.vagas.page_1` (`/vagas`); names: `title`, `cidade`, `cursos`.
3. **Filtro View** `title` (string, operator `contains`, identifier `title`) no display `page_1` — ensure no hook + export `cex`.
4. **Campo “Cidade ou Remoto”** → parâmetro `cidade` (texto livre; **não** `regime` — diferença explícita vs home).
5. **Campo “Seu curso”** → texto livre `cursos` (v1 sem autocomplete obrigatório).
6. **Pills** (só `curso`): Tecnologia, Marketing, Administração, Engenharia, Saúde, Design, Direito — resolução runtime (exato → prefixo); omitir se termo ausente; **sem** “Ver todas”.
7. **Textos fixos** no Twig (não administráveis):
   - Título: `Encontre a oportunidade ideal para sua carreira.`
   - Subtítulo: `Explore milhares de vagas de estágio em empresas parceiras e dê o próximo passo na sua jornada profissional.`
8. **Pré-preenchimento** a partir de `?title=&cidade=&cursos=` (`#cache` contexts: `url.path`, `url.query_args`).
9. **Apresentação**:
   - Theme hook `custom_banners_vagas_hero_search` → Twig dedicado no módulo.
   - Classe raiz `.vagas-hero-search` (nunca reusar `.hero-search` da home).
   - Library `default/vagas_hero_search` → `assets/css/components/vagas-hero-search.css`.
   - Tokens: wrapper `max-width: 1280px`; padding `48/32/40`; botão `#58A83C`; subtítulo `#45464D`; pílula + pills outline azul claro.
10. **Heading**: `h1` no título do hero (page title block não cobre View `/vagas`).
11. **Placement**:
    - Config ID: `default_custom_banners_vagas_hero_search`
    - UUID: `a1b2c3d4-e5f6-4789-a012-bcdef0123456`
    - Tema `default`, região `highlighted`, weight **`-50`**, `label_display: '0'`
    - Visibility `request_path` = `/vagas`
    - Status `true`
12. **Hook `11044`**:
    - `_custom_configs_ensure_vagas_page_1_title_filter()` — adiciona filtro `title` se ausente (não remove/altera outros filtros).
    - `_custom_configs_ensure_vagas_hero_search_placement()` — create/update região, weight, visibility, status.
    - **Não** reativar `default_formularioexpostovagaspage_1`; **não** tocar hero home.
13. **PRD** §3.6: documentar plugin, filtro `title`, placement, hook `11044`; atualizar bullet da View `page_1`.
14. **Fora**: redesign cards/contador/filtros laterais; link “Ver todas”; alterar home hero; autocomplete avançado.

## Project Structure

### Documentation (this feature)

```text
specs/027-vagas-hero-search/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── vagas-hero-search-render.md
│   └── deploy-vagas-hero-search.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
modules/custom/custom_banners/
  src/Plugin/Block/VagasHeroSearchBlock.php          # novo
  templates/block--vagas-hero-search.html.twig       # novo
  custom_banners.module                              # + theme hook

themes/custom/default/
  assets/css/components/vagas-hero-search.css        # novo
  default.libraries.yml                              # + vagas_hero_search
  # NÃO alterar de forma regressiva:
  #   assets/css/components/hero-search.css
  #   assets/js/hero-search.js
  #   HeroSearchBlock / block--hero-search.html.twig

modules/custom/custom_configs/
  custom_configs.install                             # custom_configs_update_11044 + helpers

config/sync/
  views.view.vagas.yml                               # + filtro title em page_1 (cex)
  block.block.default_custom_banners_vagas_hero_search.yml  # criar (cex)

PRD.md                                               # §3.6 cirúrgico
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Plugin + theme hook + Twig (form GET, pills, pré-fill)
3. Library/CSS escopada (tokens Figma, desktop row / mobile stack)
4. Ensure filtro `title` + placement no hook `11044`
5. Origem: `drush cex` → versionar View + placement
6. PRD §3.6 + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Plugin/CSS duplicados vs. condicionais no hero home | Spec exige bloco distinto + zero regressão na home; campos e mapeamentos diferem (cidade vs regime; 3 campos vs 2) | Flags/config no `HeroSearchBlock` — acoplamento alto e risco de regressão visual |
| Ensure do filtro View no hook + cex | Mesmo padrão 11040–11042; destino sem mexer no admin | Só cex — frágil se sync parcial; só hook sem cex — drift de config |
