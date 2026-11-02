# Data Model — 048 Hero de busca de /vagas

**Spec**: [spec.md](spec.md) · **Plan**: [plan.md](plan.md) · **Research**: [research.md](research.md)

Nenhum content type, field, taxonomia, paragraph ou config nova. Só leitura de entidades existentes + estruturas em cache/estado de UI.

## 1. Entidades existentes (somente leitura)

| Entidade | Campos usados | Regra de inclusão nas sugestões |
|----------|---------------|---------------------------------|
| Node `vagas` | `title`, `status`, `field_vaga_empresa` (→ node `empresa`), `field_cidade`, `field_estados`, `field_cursos_t` | `status = 1`, `default_langcode = 1` |
| Node `empresa` | `title` (nome), `status` | `status = 1` **e** ≥ 1 vaga publicada apontando via `field_vaga_empresa` |
| Taxonomia `curso` | `name` | já tratada por `/api/cursos/autocomplete` (041) |

> `field_empresa_u` (→ `user`) é legado e **não** é usado (PRD §3.1).

## 2. Estruturas em cache (`cache.data`, TTL 300 s)

| CID | Conteúdo | Tags |
|-----|----------|------|
| `custom_banners:vagas_autocomplete_titles` | `list<array{name: string, norm: string}>` — títulos **distintos** de vagas publicadas | `node_list:vagas`, `node_list` |
| `custom_banners:vagas_autocomplete_empresas` | `list<array{name: string, norm: string}>` — nomes **distintos** de empresas publicadas com ≥ 1 vaga publicada | `node_list:vagas`, `node_list:empresa`, `node_list` |

`norm` = `SearchTextNormalizer::normalize(name)` (minúsculas, sem diacríticos).

## 3. Sugestão (payload JSON)

### Input 1 — `/api/vagas/autocomplete` (novo)

```json
{ "value": "Estágio em Marketing", "label": "Estágio em Marketing", "type": "vaga" }
```

| Campo | Tipo | Regras |
|-------|------|--------|
| `value` | string | Texto a preencher no input (nome original, com acento) |
| `label` | string | Texto exibido (igual a `value`) |
| `type` | `"vaga"` \| `"empresa"` | Define o rótulo discreto (“Vaga”/“Empresa”) no dropdown |

Unicidade: par (`type`, `norm(value)`). Máx. **10** itens.

### Inputs 2 e 3 — contratos existentes (sem mudança)

- Cidades: `{ value, label: "{Cidade} - {UF} - ({N})", estado, count }`
- Cursos: `{ value, label }`

## 4. Parâmetros de busca (query string GET para `/vagas`)

| Param | Origem | Normalização | Observação |
|-------|--------|--------------|------------|
| `title` | Input 1 | minúsculas, sem acento (JS; servidor também via `VagasQueryCanonicalSubscriber`) | casa `title` da vaga **OU** nome da empresa |
| `cidade` | Input 2 | idem | `custom_panel_cities_matching()` |
| `cursos` | Input 3 | idem | `custom_panel_course_tids_matching()` |

Só parâmetros preenchidos entram. Todos vazios → `/vagas` limpo.

## 5. Ranking e regras do matcher (`VagasSuggestionMatcher`)

Entrada: `q` (string), listas `titles`, `empresas`. Saída: `list<array{value,label,type}>`.

1. `q = mb_substr(trim(q), 0, 80)`; se `mb_strlen(q) < 2` → `[]`.
2. `needle = normalize(q)`; vazio → `[]`.
3. Candidato casa se `mb_strpos(norm, needle) !== false` (literal; `%`, `_`, aspas não são curingas).
4. Ordenação: (a) prefixo (`pos === 0`) antes de substring; (b) menor `pos`; (c) `type`: `vaga` antes de `empresa`; (d) `strnatcasecmp(name)`.
5. Dedupe por (`type`, `norm`); `array_slice(…, 0, 10)`.

## 6. Estado de UI do autocomplete (por input)

```text
idle ──(input ≥2 chars)──▶ debouncing(300 ms) ──▶ loading ──▶ open(items>0)
  ▲                              │ (input <2)          │        │ │
  │                              ▼                     ▼        │ └─(↑/↓)─▶ open(active=i)
  └──────── closed ◀──(Esc | blur | clique fora | seleção | erro | items=0)──┘
```

| Campo de estado | Tipo | Notas |
|-----------------|------|-------|
| `timer` | handle | debounce; limpo a cada tecla |
| `controller` | `AbortController \| null` | abortado antes de nova requisição |
| `token` | int | incrementa por requisição; resposta com token ≠ atual é descartada |
| `items` | `list<Suggestion>` | normalizadas para `{value, label, badge?}` |
| `activeIndex` | int (`-1` = nenhum) | espelhado em `aria-activedescendant` |
| `cache` | `Map<string, Suggestion[]>` | máx. 50 chaves (`endpoint\|termo-normalizado`), descarte FIFO |

## 7. Atributos/DOM contratuais

Ver [contracts/vagas-hero-ui.md](contracts/vagas-hero-ui.md).

## 8. Fora do modelo

Sem tabela/coluna/índice novos; sem `key_value`; sem estado de sessão. Remoto não vira filtro (`regime`).
