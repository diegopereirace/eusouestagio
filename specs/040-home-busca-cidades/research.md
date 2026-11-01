# Research: Busca da Home — Autocomplete de Cidades

**Data**: 2026-11-01 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **reuso do padrão `custom_banners` + `hero_search`**, mapeamento correto aos filtros já existentes da View `vagas` `page_1`, e **deploy só com Git + `drush cr`**.

---

## R1 — Substituir select de regime por input de cidade (Home)

**Decision**: no `HeroSearchBlock` / Twig da home, remover o `<select name="regime">` e `loadRegimeOptions()`; introduzir `<input name="cidade">` com placeholder/label **“Cidade”** e `data-autocomplete-path="/api/cidades/autocomplete"`.

**Rationale**: FR-004 / FR-005; o slot atual filtra regime com rótulo enganoso; a listagem já expõe `cidade` (CONTAINS em `field_cidade`).

**Alternatives considered**:
- Manter select de regime e adicionar cidade como 3º campo — rejeitado (Figma / escopo: substituir o slot).
- Autocomplete Drupal Entity Reference widget — rejeitado (hero é form HTML estático GET, não Form API entity).

---

## R2 — Endpoint `/api/cidades/autocomplete` em `custom_banners`

**Decision**: novo `CidadesAutocompleteController` + rota `GET /api/cidades/autocomplete` com `_permission: access content`, espelhando `/api/cursos/autocomplete`.

**Rationale**: FR-009; mesmo módulo já hospeda o autocomplete de cursos e o hero; precedente PRD §5.1 (endpoints auxiliares JSON).

**Alternatives considered**:
- Views REST / Search API — rejeitado (dependência/infra).
- Endpoint em `custom_panel` — rejeitado (padrão de busca da home está em `custom_banners`).

---

## R3 — Agregação cidade + UF só com vagas publicadas

**Decision**: consultar nodes `type=vagas`, `status=1`, com `field_cidade` não vazio; agrupar por valor canônico de cidade + `field_estados`; retornar apenas grupos com `count >= 1`. Formato do label: `{Cidade} - {UF} - ({N})`.

**Rationale**: FR-007 / FR-008; “vaga ativa” = mesmo critério da listagem pública (`status=1`).

**Alternatives considered**:
- Incluir vagas não publicadas / rascunho — rejeitado (assumptions da spec).
- Contar só “destaque” — rejeitado (não pedido).

---

## R4 — Match case/accent-insensitive em PHP

**Decision**: normalizar string (trim → `mb_strtolower` → decompor NFD / remover marcas combinantes, ou equivalente `iconv('UTF-8','ASCII//TRANSLIT')` com fallback seguro) e filtrar pares cidade+UF **em PHP** sobre um conjunto agregado cacheável. `q` com `< 2` chars → `[]`.

**Rationale**: FR-006; o endpoint de cursos usa SQL `CONTAINS` (collation), insuficiente para “sao paulo” → “São Paulo” de forma portável; volume de cidades distintas em vagas ativas é baixo.

**Alternatives considered**:
- Extensão PostgreSQL `unaccent` — rejeitado (infra/ops nos dois ambientes).
- Collation `und-x-icu` / trigram — rejeitado (complexidade vs. ganho).

---

## R5 — Submissão: `cidade` + `estado` (hidden) para homônimos

**Decision**:
- `value` JSON = nome canônico da cidade (vai para o input `cidade`).
- Campo `estado` (UF) vem no JSON; no evento `select` do autocomplete, JS preenche `<input type="hidden" name="estado">`.
- Se o usuário editar o texto após selecionar, limpar `estado` até nova seleção.
- Digitação livre sem seleção: submete só `cidade` (View CONTAINS) — degradação aceitável (spec).

**Rationale**: View já tem filtros separados `cidade` e `estado`; homônimos (mesma cidade, UFs diferentes) exigem UF para precisão (edge case da spec).

**Alternatives considered**:
- Só `cidade` sempre — rejeitado (homônimos ambíguos).
- `value` = `"Cidade|UF"` parseado na View — rejeitado (quebraria filtro string atual).
- Alterar operator/filtro da View — rejeitado (cex/cim desnecessário).

---

## R6 — Remoção da pill “Remoto” só em código

**Decision**: apagar a entrada `'Remoto' => …` de `HeroSearchBlock::PILLS`. Não tocar taxonomia `regime`, View, nem `hook_update_N`.

**Rationale**: FR-010–FR-012 (origem confirmada: lista hardcoded); deploy = Git + `drush cr`.

**Alternatives considered**:
- Despublicar termo Remoto — rejeitado (Fora da spec; regime ainda usado na listagem).
- `hook_update_N` — rejeitado (nada no banco a mudar).

---

## R7 — CSS Figma ~896×86 na library existente

**Decision**: ajustar `themes/custom/default/assets/css/components/hero-search.css` (library `default/hero_search`) para a caixa global desktop aproximar 896×86; manter responsividade mobile (stack, sem overflow horizontal). Bump de versão da library se o tema versionar assets.

**Rationale**: FR-001 / FR-002; assets já versionados no tema — sem CSS no admin.

**Alternatives considered**:
- Library nova — rejeitado (mesmo bloco/comportamento).
- Inline CSS no bloco — rejeitado (FR-002).

---

## R8 — Estender `hero-search.js` (não biblioteca nova)

**Decision**: reusar behavior atual (já liga qualquer `input[data-autocomplete-path]`); acrescentar lógica de `select`/`change` para o campo cidade + hidden `estado`. Dependências existentes (`core/drupal.autocomplete`, jQuery) bastam.

**Rationale**: FR-002; YAGNI; progressive enhancement (sem JS o campo continua texto livre).

**Alternatives considered**:
- JS separado `cidades-autocomplete.js` — rejeitado (duplicação).
- Autocomplete obrigatório sem fallback — rejeitado (edge case JS off).

---

## R9 — Copy residual do hero `/vagas`

**Decision**: alterar apenas label/placeholder de “Cidade ou Remoto” para **“Cidade”** em `block--vagas-hero-search.html.twig`. Sem mudança de CSS/layout/campos.

**Rationale**: spec Fora permite alinhar residual identificado no plano; evita inconsistência pós-Home; escopo mínimo.

**Alternatives considered**:
- Redesign 027 — rejeitado (Fora).
- Não alterar — rejeitado (copy enganosa após a Home deixar de falar em Remoto nesse slot).

---

## R10 — Sem `hook_update_N` / sem `drush cex` obrigatório

**Decision**: esta feature é **código + assets**. Destino: `git pull` → `drush cr`. A receita completa `cim` → `updb` → `cim` → `cr` pode rodar (no-op) se a esteira for única. Próximo hook livre permanece `11062` se outra feature precisar.

**Rationale**: FR-012–FR-015; nada estrutural novo em `config/sync`; pills/form/rota vivem em PHP/Twig/YML de módulo.

**Alternatives considered**:
- Hook vazio só para “forçar deploy” — rejeitado (ruído).
- Inventar YAML em `config/sync` na mão — rejeitado (regra do projeto).

---

## R11 — Cache do endpoint

**Decision**: resposta pública com `max-age` curto (ex. 60–300s); cache tags de lista de nodes `vagas` (e/ou invalidação por tempo). Agregação completa (mapa cidade+UF→N) pode ser cacheada internamente e filtrada por `q` normalizado.

**Rationale**: SC-002; contagens não precisam ser tempo-real absoluto; evita query pesada a cada tecla.

**Alternatives considered**: cache eterno — rejeitado (cidades com N→0 sumiriam tarde demais além do aceitável).

---

## R12 — PRD cirúrgico

**Decision**: atualizar PRD nos pontos que ainda descrevem o hero da home com regime/pill Remoto e documentar `/api/cidades/autocomplete`.

**Rationale**: FR implícito da spec (atualizar PRD se mudança comportamental confirmada) + Guardião do Escopo.

---

## Pendências não bloqueantes

| Item | Onde resolve |
|------|--------------|
| Copy exata do Figma se diferir de “Cidade” | implementação / QA visual |
| Version string exata em `default.libraries.yml` | task de assets |
| Mensagem UI quando dropdown vazio | opcional; `[]` + sem crash já atende edge case |
