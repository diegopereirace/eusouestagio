# Contract: Autocomplete de cidades (endpoint JSON auxiliar)

**Provedor**: `custom_banners` → `CidadesAutocompleteController`  
**Consumidor**: campo “Cidade” do hero da Home (JS `hero-search.js` / `core/drupal.autocomplete`, progressive enhancement)  
**Classificação**: endpoint JSON auxiliar ao front (não API pública de produto — mesmo padrão de `/api/cursos/autocomplete` e `/api/cep/{cep}`)

## Request

```http
GET /api/cidades/autocomplete?q={texto}
```

| Parâmetro | Obrigatório | Regra |
|-----------|-------------|-------|
| `q` | sim | Substring do nome da cidade; comparação **case-insensitive** e **accent-insensitive**; `< 2` chars → `[]` |

**Acesso**: permissão `access content` (público, read-only; nomes de cidade/UF + contagens de vagas publicadas — sem dados sensíveis).

## Response

`200 OK`, `Content-Type: application/json`

```json
[
  {
    "value": "Fortaleza",
    "label": "Fortaleza - CE - (12)",
    "estado": "CE"
  },
  {
    "value": "São Paulo",
    "label": "São Paulo - SP - (48)",
    "estado": "SP"
  }
]
```

| Campo | Conteúdo |
|-------|----------|
| `value` | Nome canônico da cidade a colocar no input `name="cidade"` |
| `label` | Texto exibido no dropdown — formato **exato** `{Cidade} - {UF} - ({N})` com N ≥ 1 |
| `estado` | Sigla UF para o hidden `name="estado"` no `select` do autocomplete |

**Regras**:

- Máx. 10 sugestões; ordenação sugerida: relevância por prefixo normalizado, depois nome, depois UF.
- Somente pares cidade+UF com **pelo menos uma** vaga `type=vagas` + `status=1`.
- Cidade sem vagas ativas **nunca** aparece.
- Homônimos: uma linha por par cidade+UF.
- Erros / ausência de dados → `[]` com 200 (nunca 5xx por config vazia).
- Cache: resposta pública com max-age curto (60–300s); invalidação por tempo e/ou tags de lista de nodes `vagas`.

## Degradação

Sem JS ou endpoint indisponível, o campo funciona como texto livre (`cidade` na query); a listagem resolve via filtro CONTAINS. O autocomplete é enhancement, não dependência funcional.
