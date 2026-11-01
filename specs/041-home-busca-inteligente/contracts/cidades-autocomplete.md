# Contract: Autocomplete de cidades (com constraint opcional de curso)

**Provedor**: `CidadesAutocompleteController`  
**Path**: `GET /api/cidades/autocomplete`

| Parâmetro | Obrigatório | Regra |
|-----------|-------------|-------|
| `q` | condicional | ≥2 chars sem constraint; pode ser vazio **com** `curso` (probe) |
| `curso` | não | Restringe pares cidade+UF a vagas daquele curso (match normalizado: igualdade ou prefixo — não substring solta) |

**Response**: `[{ "value", "label", "estado", "count" }, …]` — label `{Cidade} - {UF} - ({N})`, N ≥ 1 filtrado pelo curso quando presente.

- Sem `curso`: comportamento 040 (todas as cidades com vagas).
- Com `curso`: só cidades com vagas daquele curso; `N` = contagem filtrada.
- Cache público max-age 60–300s; contexts `url.query_args:q|curso`; tags `node_list:vagas`.
- Erro → `[]` 200.
