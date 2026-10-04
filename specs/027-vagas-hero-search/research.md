# Research: Hero Search — Página de Vagas

**Data**: 2026-10-04 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **reuso do padrão `custom_banners`**, **isolamento absoluto do hero da home**, mapeamento correto aos filtros de `page_1`, e **deploy repetível** (`cim` → `updb` → `cim` → `cr`).

---

## R1 — Plugin novo (não condicionar o da home)

**Decision**: criar `VagasHeroSearchBlock` com id `custom_banners_vagas_hero_search`, theme hook e Twig próprios.

**Rationale**: FR-001 / FR-021; home mapeia “Cidade ou Remoto” → `regime` (select) e tem 2 campos + pills mistas; `/vagas` precisa 3 campos texto + pills só de curso + copy/Figma distintos.

**Alternatives considered**:
- Flags no `HeroSearchBlock` — rejeitado (risco de regressão + complexidade).
- Block Content administrável — rejeitado (FR-006 / SC-009: textos 100% em código).

---

## R2 — Módulo `custom_banners` (não `custom_panel`)

**Decision**: hospedar o plugin em `custom_banners`, onde já vive o hero da home e o autocomplete de cursos.

**Rationale**: Assumptions da spec; concentra o padrão de form GET + pills + taxonomia.

**Alternatives considered**: módulo novo — rejeitado (YAGNI); `custom_panel` — fora do padrão de busca.

---

## R3 — Região `highlighted` + weight `-50`

**Decision**:
- Placement ID: `default_custom_banners_vagas_hero_search`
- UUID: `a1b2c3d4-e5f6-4789-a012-bcdef0123456`
- Região `highlighted`, weight `-50`, pages `/vagas`, `label_display: '0'`, `status: true`

**Rationale**: FR-022 / FR-023; View page renderiza no sistema de páginas — `highlighted` fica acima do conteúdo da View (mesmo padrão do formulário exposto legado e do hero home). Weight `-50` garante prioridade sobre outros blocos da região.

**Alternatives considered**:
- `content_full` — rejeitado (não envolve View page).
- Layout Builder — rejeitado (Fora / YAGNI).
- Weight `0` — risco de ficar após mensagens/outros highlighted.

---

## R4 — Formulário exposto legado permanece desativado

**Decision**: **não** reativar `default_formularioexpostovagaspage_1` (`status: false` desde feature benefícios/estudantes).

**Rationale**: O hero substitui a necessidade visual do bloco exposto no topo; filtros laterais da página (se existirem no template da View) ficam fora do escopo desta feature.

**Alternatives considered**: reativar e estilizar o exposed form Views — rejeitado (markup Views ≠ Figma; frágil).

---

## R5 — Filtro exposto `title` na View `page_1`

**Decision**: adicionar filtro `title` em `node_field_data.title`, plugin `string`, operator `contains`, identifier `title`, exposed, não obrigatório. Ensure idempotente no hook + export `cex`.

**Rationale**: FR-003; baseline atual de `page_1` não expõe título/palavra-chave (verificado em `views.view.vagas.yml`).

**Alternatives considered**:
- Reusar `nid` — rejeitado (semântica diferente).
- Fulltext Search API — rejeitado (dependência/infra fora do escopo).

---

## R6 — Mapeamento dos três campos

**Decision**:

| UI | GET param | Filtro View |
|----|-----------|-------------|
| Cargo ou palavra-chave | `title` | novo string contains |
| Cidade ou Remoto | `cidade` | `field_cidade_value` (já existe) |
| Seu curso | `cursos` | `field_cursos_t_target_id` textfield (já existe) |

**Rationale**: FR-003–005; pedido explícito: nesta página “Cidade ou Remoto” → **cidade**, não regime (home mapeia para regime).

**Alternatives considered**: select de regime no 2º campo — rejeitado pela spec/assumptions.

---

## R7 — Valor de `cursos` = nome do termo

**Decision**: campo e pills enviam o **nome** do termo (não TID hardcoded), compatível com widget textfield do filtro.

**Rationale**: Assumptions; mesmo contrato do hero da home para pills de curso.

**Alternatives considered**: TID na query — quebraria o widget textfield atual sem alterar a View.

---

## R8 — Resolução de pills (runtime)

**Decision**: lista fixa de rótulos; load por `vid=curso` + `name` exato; fallback prefixo/case-insensitive (espírito do home); omitir se não achar; sem criar termos.

**Rationale**: FR-009–012; SC-005.

**Alternatives considered**: TIDs no código — rejeitado (FR-010); criar termos automaticamente — rejeitado (assumptions).

---

## R9 — Sem “Ver todas”

**Decision**: Twig não renderiza link/botão “Ver todas” sob as pills.

**Rationale**: FR-011 / US1; removido explicitamente do escopo.

---

## R10 — CSS / library dedicada

**Decision**:
- Library `default/vagas_hero_search` → `vagas-hero-search.css`
- Escopo: `.vagas-hero-search` (+ filhos BEM)
- **Não** alterar `hero-search.css` / `hero-search.js` / Twig da home
- Attach só no build do novo bloco

**Rationale**: FR-013–021; SC-001 / isolamento.

**Alternatives considered**: reusar `.hero-search` com overrides — rejeitado (vazamento / paddings e copy diferentes).

---

## R11 — Pré-preenchimento e cache

**Decision**: ler query args no `build()`; passar `values` ao Twig; cache contexts `url.path` + `url.query_args`; tags `taxonomy_term_list:curso`.

**Rationale**: FR-008; após pill ou nova busca os inputs refletem o estado.

---

## R12 — Sem autocomplete obrigatório (v1)

**Decision**: input texto simples para curso; **não** exigir `data-autocomplete-path` nesta feature.

**Rationale**: Escopo Fora / Assumptions; pode reusar endpoint `/api/cursos/autocomplete` depois sem mudar o contrato GET.

---

## R13 — Hook `custom_configs_update_11044`

**Decision**: update idempotente que:
1. Ensure filtro `title` em `page_1` (via `View::load('vagas')`) se ausente
2. Ensure placement do plugin (região, weight, visibility, status)
3. Não toca hero home, `block_1`/`block_3`, formulário exposto (exceto deixar desativado)

**Rationale**: FR-024; próximo livre após `11043`; padrão Entity API dos hooks 11040–11043.

---

## R14 — Ordem de deploy

**Decision**: origem `drush cex`; destino `git pull` → `cim -y` → `updb -y` → `cim -y` → `cr`.

**Rationale**: FR-026; 2ª `cim` alinha placement/View se o hook ajustar config.

---

## R15 — Heading `h1`

**Decision**: título do hero em `h1`.

**Rationale**: FR-015; `default_page_title` não cobre View `/vagas` (só nodes `page`); header da View usa `h3`.

**Alternatives considered**: `h2` — pior para a11y nesta página.

---

## R16 — PRD §3.6

**Decision**: atualização cirúrgica:
- Bullet View `page_1`: incluir filtro `title` na lista de filtros
- Novo bullet (ou extensão do hero): plugin `custom_banners_vagas_hero_search`, placement highlighted `/vagas`, hook `11044`
- Não reescrever home / PE / cards

**Rationale**: FR-027; Guardião do Escopo.
