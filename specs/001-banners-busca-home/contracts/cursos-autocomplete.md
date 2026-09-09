# Contract: Autocomplete de cursos (endpoint JSON auxiliar)

**Provedor**: `custom_banners` → `CursosAutocompleteController`
**Consumidor**: campo "Qual o seu curso?" do hero (JS `core/drupal.autocomplete`, progressive enhancement)
**Classificação**: endpoint JSON auxiliar ao front (não é API pública de produto — PRD §5.1, mesmo padrão de `/api/cep/{cep}`)

## Request

```http
GET /api/cursos/autocomplete?q={texto}
```

| Parâmetro | Obrigatório | Regra |
|-----------|-------------|-------|
| `q` | sim | Prefixo ou substring do nome do termo; `< 2` chars → retorna `[]` |

**Acesso**: permissão `access content` (público, read-only; apenas nomes de termos de taxonomia — sem dados sensíveis).

## Response

`200 OK`, `Content-Type: application/json`

```json
[
  {"value": "Tecnologia da Informação", "label": "Tecnologia da Informação"},
  {"value": "TI - Redes", "label": "TI - Redes"}
]
```

| Campo | Conteúdo |
|-------|----------|
| `value` | Valor a submeter no parâmetro `cursos` (formato conforme §4 de `home-search.md`: nome do termo, ou `Nome (tid)` se o widget exigir TID) |
| `label` | Texto exibido na sugestão |

**Regras**:
- Máx. 10 sugestões, ordenadas por nome; apenas termos do vocabulário de **cursos** (vid confirmado no export — gap R4).
- Somente termos de vocabulário ativo; sem paginação.
- Cache: resposta cacheável por query param (`contexts: url.query_args:q`), tags `taxonomy_term_list:{vid}`.
- Erros: vocabulário inexistente → `[]` com 200 (nunca 5xx por config ausente).

## Degradação

Sem JS ou endpoint indisponível, o campo funciona como texto livre e a submissão GET permanece válida (o filtro exposto resolve o nome) — o autocomplete é enhancement, não dependência funcional.
