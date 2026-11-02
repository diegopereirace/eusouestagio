# Research — 048 Hero de busca de /vagas: Autocomplete e refino visual

**Date**: 2026-11-02 · **Spec**: [spec.md](spec.md) · **Plan**: [plan.md](plan.md)

Todas as incógnitas do Technical Context foram resolvidas abaixo. Nenhum `[NEEDS CLARIFICATION]` permanece.

## Achados verificados no repositório / ambiente (2026-11-02)

| # | Achado | Evidência | Impacto |
|---|--------|-----------|---------|
| F1 | Banco é **PostgreSQL 16**; extensões ativas: `plpgsql`, `pg_trgm`. **`unaccent` disponível mas não instalada**; features 040/041 já **rejeitaram** instalá-la. | `sites/default/settings.php` (`driver => pgsql`), probe `pg_extension`, `specs/040-home-busca-cidades/research.md` | Match sem acento precisa ser feito em PHP (endpoints) ou via `translate(lower())` em SQL (View). |
| F2 | `ILIKE '%estagio%'` em `node_field_data.title` → **0** vagas; `ILIKE '%estágio%'` → **15/15**. O filtro **nativo** `title` da View (`contains`) **não** é accent-insensitive no Postgres. | Probe via `drush php:script` | A premissa da spec ("collation do banco resolve acentos") é **falsa**; requer o mecanismo abaixo (F3). |
| F3 | O filtro exposto `title` de `vagas`/`page_1` é **neutralizado** em `custom_panel_views_pre_view()` e reaplicado em `custom_panel_views_query_alter()` com `translate(lower(node_field_data.title), 'áàâã…', 'aaaa…') LIKE :param` (`escapeLike`). Idem `cidade` e `cursos` (resolvidos em PHP para IN/EXISTS). | `modules/custom/custom_panel/custom_panel.module` L276–421 | A busca "título **OU** empresa" é uma **extensão de código** nesse hook (EXISTS na empresa) — **não** requer alterar `views.view.vagas.yml`. |
| F4 | Relação canônica vaga → empresa é **`field_vaga_empresa`** (node `empresa`, card. 1; feature 032/034). `field_empresa_u` (→ `user`) é **legado** e não alimenta header nem cartão. | `field.storage.node.field_vaga_empresa.yml`, PRD §3.1 | Endpoint e filtro devem usar `node__field_vaga_empresa`, **não** `field_empresa_u`. |
| F5 | Hooks `custom_configs_update_*` vão até **`11070`**; próximo livre seria `11071`. | `custom_configs.install` | Ver D6: provavelmente **nenhum** hook é necessário. |
| F6 | Controllers de autocomplete (040/041) usam `CacheableJsonResponse`, `cache.data` (TTL 300 s), tags `node_list:vagas`, filtro em PHP via `SearchTextNormalizer`, `LIMIT = 10`, `q ≥ 2`. | `CidadesAutocompleteController`, `CursosAutocompleteController` | Padrão a replicar no endpoint novo. |
| F7 | JS atual de `/vagas` (`vagas-hero-search.js`) só normaliza o submit (NFD, minúsculas) e navega com `location.assign`. Nenhum autocomplete. Home usa `hero-search.js` + jQuery UI (`core/drupal.autocomplete`). | `themes/custom/default/assets/js/` | JS novo, vanilla, independente (FR-008). |
| F8 | `VagasQueryCanonicalSubscriber` redireciona `/vagas?title=Estágio` → versão normalizada (302). | `custom_banners/src/EventSubscriber` | FR-018 (sem JS) já coberto no servidor. |
| F9 | Existe `SearchTextNormalizerTest` (PHPUnit Unit) em `custom_banners/tests/src/Unit`. | árvore do módulo | Lógica pura nova pode ter teste unitário no mesmo padrão. |
| F10 | `VagasHeroSearchBlock` já resolve valores canônicos de cidade/curso para pré-preencher, tem `#cache` com `url.query_args` e library `default/vagas_hero_search` (v1.1.0). | `VagasHeroSearchBlock.php`, `default.libraries.yml` | FR-019 já atendido; só ajustes de Twig/library. |
| F11 | PRD §3.6 (L275) diz hero `/vagas` "pills (sem 'Ver todas')", placeholder "Cidade"; §10 lista só 2 rotas de autocomplete. | `PRD.md` L275, L517–518 | Atualização cirúrgica (FR-031). |

## Decisões

### D1 — Onde fazer o "título OU empresa" (FR-017)

- **Decision**: estender `custom_panel_views_query_alter()` (bloco `title`) para gerar uma única `addWhereExpression` do tipo
  `( translate(lower(node_field_data.title), …) LIKE :t OR EXISTS ( SELECT 1 FROM {node__field_vaga_empresa} r INNER JOIN {node_field_data} emp ON emp.nid = r.field_vaga_empresa_target_id WHERE r.entity_id = node_field_data.nid AND r.deleted = 0 AND emp.type = 'empresa' AND emp.status = 1 AND translate(lower(emp.title), …) LIKE :t ) )`.
  Fold de acentos em constante única reutilizada nas duas pontas.
- **Rationale**: o mecanismo de F3 já existe, é testado e evita o problema F2; **zero** mudança estrutural na View (sem `cex`, sem `hook_update`), deploy = `git pull` + `cr`. `EXISTS` evita JOIN duplicando linhas/pager (sem `DISTINCT`).
- **Alternatives**:
  - *Filtro `combine` da View + relationship `field_vaga_empresa`*: exige relationship + campos, JOIN duplica linhas com pager, continua sem accent-fold (F2) → rejeitado.
  - *Filtro agrupado OR na View*: um identificador exposto só aceita um handler → inviável.
  - *Instalar `unaccent`*: rejeitado em 040/041 (infra nos dois ambientes).
  - *Coluna normalizada/índice de busca*: over-engineering para o volume atual.

### D2 — Endpoint do Input 1 (FR-010..FR-013)

- **Decision**: novo `VagasAutocompleteController` em `custom_banners`, rota `custom_banners.vagas_autocomplete` → `GET /api/vagas/autocomplete?q=`. Duas listas **distintas e cacheadas** em `cache.data` (TTL 300 s): (a) títulos distintos de vagas publicadas; (b) nomes distintos de empresas publicadas com ≥ 1 vaga publicada (via `field_vaga_empresa`). Filtro/ranking em PHP por `SearchTextNormalizer::normalize()`. Lógica pura extraída para `VagasSuggestionMatcher` (classe `final`, estática, testável).
- **Rationale**: mesmo padrão de 040/041 (F6) → consistência, zero hit de banco por tecla, sem `unaccent`. Cache invalida por `node_list:vagas` + `node_list:empresa` (+ `node_list`).
- **Alternatives**:
  - *SQL `translate/LIKE` por requisição*: alinha 100% com o filtro da View, mas gera query por termo (mais carga) — mantido como plano B se o volume de títulos distintos passar de ~5 000.
  - *Search API / Solr*: dependência nova, fora de escopo.

### D3 — Consistência sugestão ↔ listagem (SC-003)

- **Decision**: duas implementações de fold (PHP NFD no endpoint; `translate` no SQL da View). Mitigação: (1) lista `translate` cobre os diacríticos do português (`áàâãä éèêë íìîï óòôõö úùûü ç ñ`); (2) teste de quickstart que percorre **todas** as sugestões e valida ≥ 1 resultado; (3) se o `lower()` do Postgres não minusculizar acentuadas maiúsculas (locale `C`), estender a lista com maiúsculas — verificado no quickstart (caso "ÁGIL").
- **Alternatives**: usar só SQL nos dois lados (ver D2, plano B).

### D4 — Frontend do autocomplete (FR-001..FR-009)

- **Decision**: `assets/js/vagas-hero-autocomplete.js`, vanilla ES2018, `Drupal.behaviors.vagasHeroAutocomplete` + `once()`; `fetch` + `AbortController` por input; debounce 300 ms; mínimo 2 caracteres; cache em memória (`Map`, máx. 50 entradas, chave `endpoint|termo normalizado`); descarte de resposta obsoleta por token incremental; DOM criado via `createElement/textContent` (destaque do trecho com `<mark>`, nunca `innerHTML`); padrão ARIA 1.2 *combobox + listbox* (`role=combobox`, `aria-expanded`, `aria-controls`, `aria-autocomplete="list"`, `aria-activedescendant`; itens `role=option`, `aria-selected`); região `aria-live="polite"` visually-hidden com contagem. Endpoints entram por `data-ac-endpoint` renderizado no Twig a partir de `Url::fromRoute` (respeita base path).
- **Rationale**: FR-008 proíbe jQuery UI e dependência do `hero-search.js`; `core/drupal.autocomplete` conflita (já documentado na library da home). Vanilla = menos bytes e sem dupla inicialização.
- **Alternatives**: jQuery UI Autocomplete (reuso da home) — rejeitado por FR-008; web component / lib externa — dependência nova.
- **Detalhes de comportamento**:
  - Enter com opção ativa → seleciona (`preventDefault`); sem opção ativa/dropdown fechado → submit nativo (US3-4).
  - Esc fecha sem limpar; Tab fecha e segue; clique fora / blur fecha (`mousedown` nas opções com `preventDefault` para não perder foco antes do clique).
  - Seleção só preenche o input e fecha; **não** submete automaticamente.
  - Valores vindos da URL não disparam busca (nenhum evento `input` no load).
  - Erro/abort → fecha silenciosamente; vazio → sem dropdown (apenas atualiza a live region).
  - `pageshow` com `event.persisted` → fecha dropdowns (bfcache).

### D5 — Visual (FR-020..FR-028)

- **Decision**: CSS puro em `components/vagas-hero-search.css` (sem SCSS, feature 046), tudo sob `.vagas-hero-search*`; breakpoint de layout **≥ 992 px** (`@media (min-width: 992px)` para linha; base mobile-first em coluna). Container: `background #fff`, `border 1px solid #e2e8f0`, `border-radius 12px`, `box-shadow 0 4px 12px rgba(0,0,0,.05)`, `padding 4px`, `min-height 56px` (botão ≈ 48 px "altura útil"; **confirmar contra Figma `196-1186` na implementação** — se o Figma pedir botão rente ao container, ajustar sem `overflow:hidden`, que cortaria o dropdown). Foco acessível via `:focus-within` no `.vagas-hero-search__field` (contorno 2 px azul do tema), sem outline nativo no `<input>`. Pills `#EFF4FF` / borda azul clara / `9999px` / 30 px. "Ver todas" na cor de destaque laranja (`--brand-orange`) abaixo das pills.
- **Rationale**: atende FR-020..FR-027; `overflow: hidden` no container é evitado por cortar o dropdown absoluto.
- **Alternatives**: SCSS/PostCSS — fora de escopo (046).

### D6 — Deploy e `hook_update_N` (FR-029/FR-030)

- **Decision**: **nenhuma** mudança de config estrutural nem de conteúdo seed → **nenhum `hook_update_N`** e **nenhum `drush cex`** são necessários; `11071` permanece livre. Receita: `git pull` → `drush cim -y` (no-op) → `drush updb -y` (no-op) → `drush cim -y` → `drush cr` (rota nova + Twig/CSS/JS). Divergência consciente da spec (que previa alterar a View); registrada no plano e em Assumptions da spec.
- **Rationale**: diretriz do projeto exige automação do que afeta banco/config; aqui o filtro vive em código (`custom_panel`). Criar hook "vazio" só para cumprir o texto da FR-029 seria ruído.
- **Alternatives**: manter hook que reescreve a View (YAML + updb) — rejeitado: não resolve F2 e acopla ao YAML.

### D7 — Performance, segurança e cache

- Cache: `CacheableJsonResponse` público, `max-age 300`, contexto `url.query_args:q`, tags `node_list:vagas`, `node_list:empresa`; `cache.data` com as mesmas tags.
- Segurança: `q` cortado em 80 caracteres (`mb_substr`), tratado como literal (match por `mb_strpos`, sem SQL com `q`); saída JSON só de `value/label/type`; `try/catch` → `[]`; permissão `access content`; método GET.
- Risco aceito (igual a 040/041): URLs com `q` arbitrário criam entradas no *page cache* anônimo. Mitigação: `q` ≤ 80, TTL 300 s; sem agravante novo.
- JS: sem polling; 1 requisição por pausa de 300 ms; dropdown criado sob demanda; nenhum request em load.
- Remoto: sem tratamento especial; "Cidade ou Remoto" é só placeholder (decisão do usuário).

### D8 — Testes

- **PHPUnit Unit** novo: `VagasSuggestionMatcherTest` (acento/caixa, prefixo antes de substring, dedupe, limite 10, `%`/`_`/aspas literais, `q` < 2, `q` > 80).
- **Manual** (quickstart): matriz de 8 combinações, teclado, ARIA, rede (DevTools: 1 request por pausa, abort), visual a 1280/768/375, regressão Home e endpoints 040/041, SC-003 em lote.
- Sem suíte JS automatizada no repositório → não introduzida (fora de escopo).

### D9 — Skills `mattpocock/skills`

- Não instalado (Assumption da spec). Diretrizes aplicadas diretamente: tipagem estrita PHP (`declare(strict_types=1)`, tipos de retorno, `final`, DI), JS pequeno e puro com estado explícito, funções puras testáveis (`VagasSuggestionMatcher`), sem comentários óbvios.
