# Implementation Plan: Hero de busca de /vagas — Autocomplete e refino visual (Figma)

**Branch**: `main` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` e template — ausente neste repo; setup executado manualmente a partir de `.specify/feature.json` → `specs/048-vagas-hero-search-autocomplete`)  
**Date**: 2026-11-02  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/048-vagas-hero-search-autocomplete/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar no hero de `/vagas` (bloco `custom_banners_vagas_hero_search`): (1) **autocomplete assíncrono nos 3 inputs** (JS vanilla, debounce 300 ms, `AbortController`, ARIA combobox) — Input 1 com **endpoint novo** `GET /api/vagas/autocomplete` (vagas + empresas), Inputs 2/3 reaproveitando `/api/cidades|cursos/autocomplete` sem mudança de contrato; (2) busca **título OU empresa** na listagem, estendendo o mecanismo existente `custom_panel_views_query_alter()` (fold de acentos `translate(lower())` + `EXISTS` na empresa) — **sem** alterar a View nem criar `hook_update_N`; (3) refino visual Figma em CSS puro escopado (container 12 px, divisórias, botão `#58A83C`, pills `#EFF4FF`, link **Ver todas**). Zero passos manuais em produção (`git pull` → `cim` → `updb` → `cim` → `cr`).

## Technical Context

**Language/Version**: PHP 8.3+ (`declare(strict_types=1)`), Drupal 11.4+, Twig 3, JavaScript ES2018 (vanilla, sem jQuery), CSS3  
**Primary Dependencies**: Drupal core (Node, Taxonomy, Views, Block, cache); módulos custom `custom_banners` (endpoint/bloco), `custom_panel` (filtro da View); tema `default` (Bootstrap Barrio 5 / BS 5.3); `core/drupal`, `core/once`. **Nenhuma dependência Composer/npm nova.**  
**Storage**: PostgreSQL 16 (`pg_trgm` ativo; `unaccent` **não** instalada — rejeitada em 040/041). Sem tabelas/campos novos. `cache.data` para listas de sugestão  
**Testing**: PHPUnit Unit novo (`VagasSuggestionMatcherTest`, padrão de `SearchTextNormalizerTest`) + validação manual guiada em [quickstart.md](quickstart.md); sem suíte JS (fora de escopo)  
**Target Platform**: site público Drupal; desktop ≥ 992 px (linha) / mobile < 992 px (coluna), breakpoints Bootstrap 5 (feature 046)  
**Project Type**: Drupal custom modules + theme (`modules/custom/custom_banners`, `modules/custom/custom_panel`, `themes/custom/default`)  
**Performance Goals**: ≤ 1 requisição por pausa de 300 ms por input (SC-001); dropdown ≤ 1 s em 95% (SC-002; alvo ≤ 300 ms com cache); endpoint sem consulta SQL por termo (listas em `cache.data`, filtro em PHP); JS ~4–5 KB; dropdown criado sob demanda; nada carregado fora de `/vagas`  
**Constraints**: sem `core/`/`vendor/`; JS independente do da Home e sem jQuery UI; CSS escopado `.vagas-hero-search*` (nada em `.hero-search`); CSS puro (sem SCSS/PostCSS); `q` ≤ 80 chars e literal; saída só como texto; contratos 040/041 inalterados; pt-BR  
**Scale/Scope**: 1 controller + 1 classe pura + 1 rota; 1 Twig; 1 JS novo + 1 JS existente intocado; 1 CSS reescrito; 1 library bump; ajuste cirúrgico em `custom_panel.module`; PRD cirúrgico

**Estado atual verificado (2026-11-02):**

- Bloco `VagasHeroSearchBlock`: título/subtítulo fixos no Twig; pills por `PILL_LABELS`; valores canônicos por URL (FR-019 já ok); library `default/vagas_hero_search` v1.1.0 (só submit-normalizer + CSS).
- Twig atual: 3 inputs `autocomplete="off"` **sem** `data-ac-*`; placeholder do Input 2 = "Cidade"; **sem** link "Ver todas".
- CSS atual: container **pílula 999 px**, padding `0.5rem 0.5rem 0.5rem 1.25rem`, botão pílula `999 px`; breakpoint único `575.98px`; pills com fundo `#fff` (Figma: `#EFF4FF`).
- **Achado crítico**: `ILIKE '%estagio%'` → 0 vagas vs. `ILIKE '%estágio%'` → 15 (Postgres sem `unaccent`); o filtro nativo da View não é accent-insensitive. O match real é feito por `custom_panel_views_query_alter()` com `translate(lower(...))` — **só no título da vaga** (sem empresa).
- Relação vaga→empresa: `field_vaga_empresa` (node `empresa`) — **não** `field_empresa_u` (legado, `user`).
- Hooks: último `custom_configs_update_11070`; **nenhum** novo previsto (ver D6).
- Endpoints 040/041 prontos (`CacheableJsonResponse`, TTL 300 s, `node_list:vagas`).
- Unit test existente: `custom_banners/tests/src/Unit/SearchTextNormalizerTest.php`.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só `custom_banners`, `custom_panel`, tema, `PRD.md` |
| Reuso / sem tipos novos | PASS | bloco, theme hook, library, placement e 2 endpoints reaproveitados; nenhum content type/field/block type |
| Deploy `cim` → `updb` → `cim` → `cr` | PASS | receita mantida; passos `cim`/`updb` são no-op por não haver config/seed novos |
| Automação de mudanças que afetam banco | PASS (N/A justificado) | nenhuma alteração de estrutura/dados no banco; filtro vive em código (D1/D6); sem passo manual no admin |
| `drush cex` de estrutural | PASS (N/A) | nenhuma config estrutural alterada (View **não** é tocada) |
| Clean URLs | PASS | `/vagas?title=&cidade=&cursos=` normalizados; subscriber 302 mantido; nova rota `/api/vagas/autocomplete` limpa |
| Performance | PASS | cache `cache.data` + HTTP 300 s; filtro PHP; debounce + abort; sem request em load |
| Segurança | PASS | `q` literal ≤ 80, sem SQL com `q`, `[]` em erro, render só texto, `access content` |
| Contrib first / custom mínimo | PASS | nenhuma lib JS externa; controller segue padrão 040/041 |
| CSS/JS isolados | PASS | `.vagas-hero-search*`; JS próprio; Home intocada |
| Qualidade de código (`mattpocock/skills` não instalado) | PASS | tipagem estrita, classe pura testável, JS com estado explícito (D9) |
| Reinício de serviços | PASS | `drush cr` ao final da implementação (CSS/JS/Twig/rota) |

**Post-design**: gates mantidos. A divergência com a spec (FR-017/FR-029 previam alterar a View + hook) está justificada em Complexity Tracking e em research F2/F3/D1/D6; não há violação injustificada.

## Design Decisions

1. **Endpoint novo** `GET /api/vagas/autocomplete?q=` — `VagasAutocompleteController` (`custom_banners`), rota `custom_banners.vagas_autocomplete`, `_permission: access content`, `methods: [GET]`. Lista de títulos distintos de vagas publicadas + nomes distintos de empresas publicadas com ≥ 1 vaga publicada (join `node__field_vaga_empresa`), ambas em `cache.data` (TTL 300 s; tags `node_list:vagas`, `node_list:empresa`, `node_list`). Payload `{value,label,type}`; ≤ 10; contrato em [contracts/vagas-autocomplete-api.md](contracts/vagas-autocomplete-api.md).
2. **Lógica pura** em `Drupal\custom_banners\VagasSuggestionMatcher` (`final`, métodos estáticos): corte de `q` (80), `< 2` → `[]`, normalização via `SearchTextNormalizer`, match literal por `mb_strpos`, ranking (prefixo → posição → `vaga` antes de `empresa` → alfabético natural), dedupe por (`type`,`norm`), `array_slice(…, 0, 10)`. Teste unitário dedicado.
3. **Filtro título OU empresa** (FR-017) em `custom_panel_views_query_alter()` (`custom_panel.module`, bloco `title`): extrair constante/função com o mapa `translate` e gerar **uma** `addWhereExpression` `(translate(lower(node_field_data.title)…) LIKE :t OR EXISTS (SELECT 1 FROM {node__field_vaga_empresa} r JOIN {node_field_data} emp ON emp.nid = r.field_vaga_empresa_target_id WHERE r.entity_id = node_field_data.nid AND r.deleted = 0 AND emp.type='empresa' AND emp.status=1 AND translate(lower(emp.title)…) LIKE :t))`. `escapeLike` mantido. **Sem** alterar `views.view.vagas.yml`; `custom_panel_views_pre_view()` inalterado (já neutraliza o filtro nativo `title`).
4. **Twig** (`block--vagas-hero-search.html.twig`): `data-ac-endpoint`/`data-ac-kind` nos 3 inputs; placeholder/label do Input 2 → "Cidade ou Remoto"; link **Ver todas** (`<p class="vagas-hero-search__view-all"><a href="{{ action }}">`) após as pills; ícones e `autocomplete="off"` preservados. `VagasHeroSearchBlock::build()` passa `#endpoints` (`Url::fromRoute` das 3 rotas) e `#view_all_url`; `hook_theme` (`custom_banners.module`) declara as novas variáveis.
5. **JS** `themes/custom/default/assets/js/vagas-hero-autocomplete.js` (novo; `vagas-hero-search.js` do submit permanece): `Drupal.behaviors.vagasHeroAutocomplete`, `once()` por form; `fetch` + `AbortController`; debounce 300 ms; `minLength` 2; cache `Map` ≤ 50; token anti-resposta-obsoleta; dropdown via DOM/`textContent` + `<mark>`; ARIA combobox/listbox/option + live region; teclado (↑↓/Enter/Esc/Tab); fecha em blur/clique fora/erro/vazio/`pageshow` persistido; seleção só preenche (não submete). Adapter por `kind` (`vagas` → badge Vaga/Empresa; `cidades` → `label`; `cursos` → `label`; sempre preenche `value`). Contrato em [contracts/vagas-hero-ui.md](contracts/vagas-hero-ui.md).
6. **CSS** (`components/vagas-hero-search.css`, reescrita escopada): container 12 px/sombra leve/`padding 4px`/`min-height 56px`; mobile-first coluna → linha em `≥ 992px`; divisórias (vertical ≥ 992 / horizontal < 992); inputs sem borda/outline; `:focus-within` no campo; botão `#58A83C` raio 8 px `align-self: stretch` (≥ 992) e 100% (mobile); pills `#EFF4FF`/`9999px`/30 px; "Ver todas" `var(--brand-orange)`; estilos do dropdown (`.vagas-hero-search__suggestions*`); `@media (max-width: 575.98px)` para ajustes finos. **Sem** `overflow: hidden` no container (cortaria o dropdown). Confirmar relação container × botão com Figma `196-1186` (via MCP Figma) no início da implementação.
7. **Library** `default/vagas_hero_search` → `version: 1.2.0`, adiciona `assets/js/vagas-hero-autocomplete.js`; dependências `core/drupal`, `core/once`, `default/bootstrap` (sem `core/jquery.ui.*`).
8. **Sem deploy estrutural**: nenhum `hook_update_N`, nenhum `drush cex`. Número `11071` permanece livre. Receita: `git pull` → `cim -y` → `updb -y` → `cim -y` → `cr`.
9. **PRD cirúrgico** (FR-031): §3.6 L275 (hero `/vagas`: 3 autocompletes, "Ver todas", placeholder "Cidade ou Remoto", filtro `title` com empresa via `custom_panel`) e §10 (nova rota `/api/vagas/autocomplete`); §4/módulos `custom_banners` (linha ~346) menciona o novo endpoint.
10. **Spec**: acrescentar nota em Assumptions registrando D1/D6 (filtro em código; sem hook/cex) e a correção da premissa de collation (F2).
11. **Fora**: Home (`.hero-search`), cruzamento cidade×curso no `/vagas`, "Remoto" como filtro, tags/descrição na busca, SCSS, spinner, histórico, voz, `core/`/`vendor/`.
12. **Riscos & plano B**: (a) divergência exótica fold PHP × SQL → teste em lote no quickstart §4; (b) `lower()` do PG em locale `C` → estender `translate` com maiúsculas; (c) > ~5 000 títulos distintos → migrar o endpoint para `translate/LIKE ... LIMIT` em SQL; (d) pollution do page-cache por `q` arbitrário → aceito como em 040/041.

## Project Structure

### Documentation (this feature)

```text
specs/048-vagas-hero-search-autocomplete/
├── spec.md
├── checklists/requirements.md
├── plan.md                         # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── vagas-autocomplete-api.md
│   └── vagas-hero-ui.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
modules/custom/custom_banners/
  custom_banners.routing.yml                        # + custom_banners.vagas_autocomplete
  custom_banners.module                             # hook_theme: + endpoints, view_all_url
  src/Controller/VagasAutocompleteController.php    # novo
  src/VagasSuggestionMatcher.php                    # novo (puro)
  src/Plugin/Block/VagasHeroSearchBlock.php         # + #endpoints, #view_all_url
  templates/block--vagas-hero-search.html.twig      # data-ac-*, placeholder, "Ver todas"
  tests/src/Unit/VagasSuggestionMatcherTest.php     # novo

modules/custom/custom_panel/
  custom_panel.module                               # title: título OU empresa (EXISTS) em custom_panel_views_query_alter

themes/custom/default/
  default.libraries.yml                             # vagas_hero_search v1.2.0 + novo JS
  assets/js/vagas-hero-autocomplete.js              # novo
  assets/css/components/vagas-hero-search.css       # reescrita escopada (Figma + dropdown)

PRD.md                                              # §3.6 (hero /vagas), §10 (rota), módulos
specs/048-.../spec.md                               # nota em Assumptions (D1/D6/F2)

# NÃO alterados: config/sync/**, views.view.vagas.yml, custom_configs.install,
# hero-search.js, block--hero-search.html.twig, CidadesAutocompleteController, CursosAutocompleteController
```

## Phases

1. **Spec/plan/research/data-model/contracts/quickstart** (esta entrega).
2. **Backend**: `VagasSuggestionMatcher` + teste unitário → `VagasAutocompleteController` + rota → ajuste do filtro em `custom_panel.module` (título OU empresa).
3. **Bloco/Twig**: `#endpoints`/`#view_all_url`, hook_theme, Twig (data-ac-*, placeholder, "Ver todas").
4. **Frontend**: confirmar Figma (container × botão) → CSS reescrito → `vagas-hero-autocomplete.js` → library 1.2.0.
5. **Validação**: `drush cr` + `phpunit` + [quickstart.md](quickstart.md) (endpoint, SC-003 em lote, debounce, teclado/ARIA, visual 1280/768/375, regressão Home/040/041).
6. **Documentação**: PRD cirúrgico + nota na spec; receita de deploy (sem `cex`/hook).

## Complexity Tracking

| Violação / divergência | Justificativa | Alternativa rejeitada |
|------------------------|---------------|------------------------|
| **Divergência da spec** (FR-017/FR-029): filtro "título OU empresa" implementado em `custom_panel_views_query_alter()` em vez de alterar a View via `hook_update_N` + `cex` | O filtro nativo da View não casa sem acento no Postgres (F2); o mecanismo `translate(lower())` já vive em código (F3); evita YAML manual, JOIN duplicando linhas e hook ocioso | Filtro `combine`/relationship na View — exige JOIN, mantém bug de acento, acopla a YAML |
| Dois folds (PHP no endpoint × SQL `translate` na listagem) | Padrão do projeto (PHP nos endpoints 040/041; SQL no filtro) sem instalar `unaccent`; custo por tecla zero | Tudo em SQL — mais carga por requisição; `unaccent` — rejeitado em 040 |
| JS novo em vez de reutilizar `hero-search.js`/jQuery UI | FR-008 (independência da Home, sem jQuery UI); evita dupla inicialização com `core/drupal.autocomplete` | Reuso do autocomplete da Home — acoplamento e risco de regressão na Home |
| Nenhum `hook_update_N`/`cex` apesar da regra de deploy | Nenhuma mudança de config/seed; criar hook vazio seria ruído | Hook no-op "para cumprir" a FR-029 |
