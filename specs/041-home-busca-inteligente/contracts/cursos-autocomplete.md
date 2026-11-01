# Contract: Autocomplete de cursos (com constraint opcional)

**Provedor**: `CursosAutocompleteController`  
**Path**: `GET /api/cursos/autocomplete`

| Parâmetro | Obrigatório | Regra |
|-----------|-------------|-------|
| `q` | condicional | ≥2 chars sem constraint; pode ser vazio **com** `cidade` (probe) |
| `cidade` | não | Restringe a cursos com vagas publicadas nessa cidade (match normalizado: igualdade ou prefixo) |
| `estado` | não | Refina com UF quando presente (igualdade normalizada) |

**Response**: `[{ "value": "Administração", "label": "Administração" }, …]` máx. 10.

- Sem `cidade`: só cursos com ≥1 vaga publicada (`status=1`); termos órfãos (ex.: “Linux Administration” sem vaga) NÃO aparecem.
- Com `cidade`: só cursos com ≥1 vaga `status=1` na localização (match cidade/estado normalizado).
- Cache público max-age 60–300s; contexts `url.query_args:q|cidade|estado`; tags `taxonomy_term_list:curso` + `node_list:vagas` (sempre).
- Erro → `[]` 200.
