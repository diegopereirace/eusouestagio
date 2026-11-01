# Data Model: Busca da Home — Autocomplete de Cidades

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-01

Esta feature **não** cria content types, field storages, paragraphs nem placements novos. Reusa o modelo de vagas e altera o contrato de UI/consulta do hero da Home.

## 1. Entidades reutilizadas (somente leitura / agregação)

### Node `vagas` (publicado)

| Campo | Tipo | Papel nesta feature |
|-------|------|---------------------|
| `status` | boolean | Elegibilidade: só `1` (publicado) |
| `field_cidade` | string | Nome canônico da cidade; filtro View `cidade` (CONTAINS); chave de agrupamento |
| `field_estados` | list_string (UF) | Sigla exibida e param `estado`; chave de agrupamento |
| `field_cursos_t` | entity_reference (`curso`) | Inalterado; pills/campo curso |
| `field_regime_t` | entity_reference (`regime`) | Inalterado no banco; **some do form/pill da Home** |

**Regras de agregação (autocomplete)**:

- Incluir só `type=vagas` + `status=1` + `field_cidade` não vazio (após trim).
- Grupo = `(cidade_canônica, UF)`.
- `N` = quantidade de nodes no grupo; grupos com `N < 1` não existem na saída.
- Cidade sem UF: omitir do autocomplete **ou** tratar UF vazia como não elegível (preferência: omitir — label exige UF).

### Taxonomia `curso`

Alimenta pills TI / Administração / Design / Marketing e o autocomplete de cursos (inalterado).

### Taxonomia `regime`

Permanece no sistema e no filtro exposto da View. Termo “Remoto” **não** é apagado; apenas deixa de aparecer como pill na Home.

## 2. Bloco plugin `custom_banners_hero_search` (alterado)

| Aspecto | Antes | Depois |
|---------|-------|--------|
| Campo 1 | `cursos` (texto + autocomplete) | igual |
| Campo 2 | `regime` (select TID) | `cidade` (texto + autocomplete) + hidden `estado` |
| Pills | Remoto + 4 cursos | só 4 cursos |
| Action | `/vagas` (`view.vagas.page_1`) | igual |
| Library | `default/hero_search` | igual (CSS/JS atualizados) |

**Variáveis Twig relevantes:**

| Variável | Tipo | Origem |
|----------|------|--------|
| `action` | URL | `Url::fromRoute('view.vagas.page_1')` |
| `pills` | list `{label, url}` | `resolvePills()` sem Remoto |
| (removido) | `regime_options` | — |

## 3. View `vagas` / `page_1` (sem mudança de schema)

Filtros expostos já existentes (não alterar nesta feature):

| Identifier | Campo | Uso pelo hero Home |
|------------|-------|--------------------|
| `cidade` | `field_cidade` CONTAINS | input autocomplete / texto livre |
| `estado` | `field_estados` | hidden preenchido na seleção |
| `cursos` | cursos (textfield) | campo curso + pills |
| `regime` | regime | **não** enviado pelo hero Home após 040 |
| `title` | title | não usado no hero Home |

## 3b. Alvos fixos de implementação (congelados — Phase 2)

| Alvo | Valor |
|------|-------|
| Rota | `GET /api/cidades/autocomplete` |
| Controller | `CidadesAutocompleteController` |
| Form Home | `name="cidade"` + hidden `name="estado"` |
| Label/placeholder | **Cidade** |
| Label JSON | `{Cidade} - {UF} - ({N})` |
| Pills | sem Remoto (só cursos) |
| Deploy | `git pull` + `drush cr` (sem `hook_update_N` / sem cex) |

## 4. DTO de sugestão (não persistido)

Representação lógica do item de autocomplete (ver [contracts/cidades-autocomplete.md](contracts/cidades-autocomplete.md)):

| Campo | Tipo | Regra |
|-------|------|-------|
| `cidade` / `value` | string | Nome canônico como gravado nas vagas (exibição preferencial do grupo) |
| `estado` | string (UF) | Sigla `field_estados` |
| `count` / `(N)` | int ≥ 1 | Contagem de vagas publicadas do par |
| `label` | string | `"{cidade} - {UF} - ({N})"` |

## 5. Diagrama

```text
ANTES (Home)                         DEPOIS (Home)
─────────────                        ─────────────
form GET /vagas                      form GET /vagas
  cursos ──────────────────────────►   cursos
  regime (select) ──► filtro regime    cidade (+ estado hidden) ──► filtros cidade [& estado]
pills: Remoto + cursos                 pills: só cursos

API: /api/cursos/autocomplete          API: /api/cursos/autocomplete
                                       API: /api/cidades/autocomplete
                                            └─ aggregate node vagas status=1
                                               GROUP BY field_cidade, field_estados
```

## 6. Validações (FR → dados)

| FR | Regra |
|----|-------|
| FR-004/005 | Slot Home → `cidade`, não `regime` |
| FR-006 | Match sobre forma normalizada (sem acento/case) |
| FR-007 | Label com cidade, UF e N |
| FR-008 | Só grupos com N ≥ 1 e vaga publicada |
| FR-010 | Remoto fora de `PILLS` |
| FR-012 | Sem mudança de config/taxonomia obrigatória |
