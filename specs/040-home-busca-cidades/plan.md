# Implementation Plan: Busca da Home — Autocomplete de Cidades

**Branch**: `feat/home-busca-autocomplete-cidades` (sugerida na spec; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo; setup via `.specify/feature.json` → `specs/040-home-busca-cidades`)  
**Date**: 2026-11-01  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/040-home-busca-cidades/spec.md` (checklist requirements OK; zero `[NEEDS CLARIFICATION]` na spec)

## Summary

Alinhar a barra de busca da Home ao Figma (~896×86 no desktop), substituir o select de **regime** (“Cidade ou Remoto”) por **autocomplete exclusivo de cidades** (só cidades com vagas publicadas; match sem acento/case; label `Cidade - UF - (N)`), mapear a submissão para os filtros `cidade` (+ `estado` quando a sugestão for escolhida), remover a pill **Remoto** da lista hardcoded, e alinhar o copy residual “Cidade ou Remoto” do hero de `/vagas` para **“Cidade”** (só texto, sem redesign). Deploy 100% automatizado: código + libraries + `drush cr` (sem `hook_update_N` / sem `cim` obrigatório nesta feature).

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, JS (Drupal behaviors + jQuery UI autocomplete via `core/drupal.autocomplete`)  
**Primary Dependencies**: Drupal core (Block, Views, Entity Query / Database API); módulo `custom_banners` (hero home + autocomplete cursos); tema `default`. **Nenhuma dependência Composer nova.**  
**Storage**: PostgreSQL; campos existentes `field_cidade` (string) e `field_estados` (list_string UF) no CT `vagas`; **sem** field storage / View / placement novos  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (desktop ≥992px / mobile ≤575.98px — Bootstrap)  
**Project Type**: Drupal theme + custom modules (`themes/custom/default`, `modules/custom/custom_banners`)  
**Performance Goals**: endpoint JSON cacheável (max-age curto, ex. 60–300s); agregação cidade+UF limitada (top N sugestões, máx. 10); CSS/JS só via library `default/hero_search`; SC-002 ≤1s sob carga normal  
**Constraints**: sem `core/`/`vendor/`; zero cliques no admin de produção; pt-BR; **não** redesenhar o hero visual de `/vagas` (027); vocabulário `regime` permanece no sistema  
**Scale/Scope**: 1 controller novo, 1 rota, alterações em `HeroSearchBlock` + Twig home, CSS/JS theme, copy mínima no Twig `/vagas`, PRD cirúrgico

**Estado atual verificado (2026-11-01):**

- Plugin `custom_banners_hero_search` (`HeroSearchBlock.php`): form GET → `view.vagas.page_1` (`/vagas`); campos `cursos` + select `regime` (option vazia “Cidade ou Remoto”); library `default/hero_search`.
- Pills hardcoded em `HeroSearchBlock::PILLS` incluem **Remoto** (`vid=regime`) + TI/Administração/Design/Marketing (`vid=curso`) — remoção = só código.
- Autocomplete de cursos: `GET /api/cursos/autocomplete` + `CursosAutocompleteController` + `hero-search.js` (qualquer `input[data-autocomplete-path]`).
- View `vagas` `page_1`: filtros expostos `cidade` (`field_cidade` CONTAINS), `estado` (`field_estados`), `cursos`, `regime`, `title`, …; publicados `status=1`.
- CSS `hero-search.css`: barra flex sem caixa alvo ~896×86 (max-width interno 1200px).
- Hero `/vagas`: campo já é `name="cidade"`, mas label/placeholder ainda dizem “Cidade ou Remoto”.
- Último hook em `custom_configs.install`: `11061` — **não** necessário para esta feature (sem seed/config estrutural).

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist; este plan |
| Sem `core/` / `vendor/` | PASS | só `custom_banners`, tema `default`, `PRD.md` |
| Reuso / custom mínimo | PASS | espelha `/api/cursos/autocomplete`; reusa filtros View existentes |
| Deploy automatizado | PASS | Git + `drush cr`; sem admin; sem dump |
| Config Management | PASS | sem alteração estrutural exportável nesta feature; se surgir, `cex`/`cim` |
| Clean URLs | PASS | form GET `/vagas?cidade=&estado=&cursos=`; endpoint auxiliar `/api/cidades/autocomplete` |
| Performance | PASS | library versionada; cache HTTP no JSON; agregação limitada |
| Segurança | PASS | endpoint read-only, `access content`, só nomes públicos + contagens |
| Contrib first | PASS | zero Composer novo; Entity/DB API |

**Post-design**: gates mantidos. Trade-off de filtragem accent-insensitive em PHP (não extensão PG `unaccent`) documentado em Complexity Tracking — justificado e sem violação de constituição.

## Design Decisions

1. **Campo Home**: trocar select `regime` por input texto `name="cidade"`, placeholder/label **“Cidade”**; remover `loadRegimeOptions()` / `#regime_options` do build/theme.
2. **Endpoint**: `GET /api/cidades/autocomplete?q=` em `custom_banners` (`CidadesAutocompleteController`), espelhando cursos; resposta `[{value, label, estado}]`.
3. **Elegibilidade**: apenas nodes `vagas` com `status=1`, `field_cidade` não vazio; agrupar por cidade canônica + UF (`field_estados`); `N` = contagem do grupo; omitir N=0.
4. **Match**: normalizar query e nomes (lowercase + strip diacríticos via `Normalizer`/equivalente PHP) **em memória** após agregação cacheável — não depender de `unaccent` no Postgres.
5. **Submissão**: `value` = nome canônico da cidade → param `cidade`; ao selecionar sugestão, JS preenche hidden `estado` com a UF; digitação livre sem seleção → só `cidade` (degradação aceitável).
6. **Label dropdown**: exatamente `Cidade - UF - (N)` (ex.: `Fortaleza - CE - (12)`).
7. **Pills**: remover entrada `Remoto` de `PILLS`; manter só cursos; vocabulário `regime` intacto.
8. **CSS**: ajustar `hero-search.css` (+ BEM se necessário) para caixa global desktop ~896×86; manter stack mobile usável sem scroll horizontal.
9. **JS**: estender `hero-search.js` para (a) segundo path de autocomplete e (b) handler `select` que seta `estado` e limpa `estado` se o texto do input mudar sem nova seleção.
10. **Copy `/vagas`**: trocar label/placeholder “Cidade ou Remoto” → “Cidade” em `block--vagas-hero-search.html.twig` (sem CSS/layout).
11. **Deploy**: destino típico desta feature = `git pull` → `drush cr`. Receita padrão `cim` → `updb` → `cim` → `cr` permanece válida (no-op se sem YAML/hook novos).
12. **PRD**: atualização cirúrgica §3.4/§3.6/§5 — hero home envia `cidade`/`estado` (não `regime`); pills sem Remoto; endpoint `/api/cidades/autocomplete`.
13. **Fora**: redesign visual 027; autocomplete de cursos; apagar termo Remoto da taxonomia; filtro de regime na listagem (continua nos filtros laterais/View).

## Project Structure

### Documentation (this feature)

```text
specs/040-home-busca-cidades/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── home-search-cidade.md
│   ├── cidades-autocomplete.md
│   └── deploy-home-busca-cidades.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
modules/custom/custom_banners/
  src/Plugin/Block/HeroSearchBlock.php              # PILLS sem Remoto; sem regime options
  src/Controller/CidadesAutocompleteController.php  # NOVO
  custom_banners.routing.yml                        # + /api/cidades/autocomplete
  templates/block--hero-search.html.twig            # input cidade + hidden estado
  templates/block--vagas-hero-search.html.twig      # copy “Cidade” (só texto)

themes/custom/default/
  assets/css/components/hero-search.css             # ~896×86 desktop
  assets/js/hero-search.js                          # cidade + estado on select
  default.libraries.yml                             # hero_search (já existe; version bump se necessário)

PRD.md                                              # §3.4 / §3.6 / rotas §5 cirúrgico
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. Controller + rota cidades + contrato JSON
3. HeroSearchBlock + Twig home (cidade, hidden estado, pills)
4. JS select/`estado` + CSS Figma
5. Copy mínima hero `/vagas`
6. PRD cirúrgico + validação quickstart (`drush cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Match accent-insensitive em PHP + cache, não `unaccent` PG | Portabilidade (Docker/VPS), zero extensão DB, volume de pares cidade+UF é pequeno | Extensão Postgres `unaccent` / collation custom — infra extra |
| Enviar `estado` hidden além de `cidade` | Homônimos (ex. mesma cidade em UFs distintas) quebrariam só com CONTAINS em `field_cidade` | Alterar View para filtro composto — fora do mínimo; risco de cex/cim sem necessidade |
| Copy alinhada em `/vagas` sem redesign | Spec permite alinhar residual “Cidade ou Remoto”; 1 string Twig evita inconsistência de produto | Deixar residual — confunde visitante pós-Home |
