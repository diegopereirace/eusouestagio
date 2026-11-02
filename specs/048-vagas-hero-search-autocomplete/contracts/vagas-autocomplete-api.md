# Contrato — `GET /api/vagas/autocomplete`

**Feature**: 048 · **Módulo**: `custom_banners` · **Rota**: `custom_banners.vagas_autocomplete`  
**Controller**: `\Drupal\custom_banners\Controller\VagasAutocompleteController::autocomplete`

## Requisição

| Item | Valor |
|------|-------|
| Método | `GET` (somente) |
| Path | `/api/vagas/autocomplete` |
| Permissão | `access content` (anônimo OK) |
| Query | `q` — texto digitado |

Regras de `q`: `trim`; cortado em **80** caracteres; `< 2` caracteres → `[]`; tratado como literal (sem curingas); ignora acento e caixa.

## Resposta `200 application/json`

Lista (sempre array, possivelmente vazio), **máx. 10** itens:

```json
[
  { "value": "Estágio em Marketing", "label": "Estágio em Marketing", "type": "vaga" },
  { "value": "Marketing Digital Ltda", "label": "Marketing Digital Ltda", "type": "empresa" }
]
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `value` | string | Texto a preencher no input |
| `label` | string | Texto exibido (= `value`) |
| `type` | `"vaga"` \| `"empresa"` | Origem da sugestão |

Garantias:

- Só **vagas publicadas**; empresas só **publicadas com ≥ 1 vaga publicada** (`field_vaga_empresa`).
- Sem duplicatas por (`type`, texto normalizado).
- Ordem: prefixo antes de substring → menor posição → `vaga` antes de `empresa` → ordem alfabética natural.
- Erro interno → `200 []` (nunca stack trace, nunca `5xx` por exceção de consulta).
- Somente dados de texto; o cliente DEVE renderizar como **texto** (não HTML).

## Cache

| Camada | Configuração |
|--------|--------------|
| HTTP | `Cache-Control: public, max-age=300` |
| Drupal | `CacheableJsonResponse`; contexto `url.query_args:q`; tags `node_list:vagas`, `node_list:empresa`; `max-age 300` |
| Dados | `cache.data` (listas de títulos/empresas, TTL 300 s, mesmas tags) — invalida ao salvar/despublicar vaga ou empresa |

## Exemplos de verificação

```text
GET /api/vagas/autocomplete?q=est        → itens com "est" em título/empresa
GET /api/vagas/autocomplete?q=estagio    → mesmos de "Estágio" (sem acento)
GET /api/vagas/autocomplete?q=e          → []
GET /api/vagas/autocomplete?q=%25%25     → []  (sem curinga)
GET /api/vagas/autocomplete?q=<script>   → [] ou itens; nada interpretado como HTML
```

## Contratos reaproveitados (sem mudança)

| Rota | Uso no hero `/vagas` | Chamada |
|------|----------------------|---------|
| `/api/cidades/autocomplete` | Input 2 | `?q=<termo>` (sem `curso`) |
| `/api/cursos/autocomplete` | Input 3 | `?q=<termo>` (sem `cidade`/`estado`) |

## Contrato do filtro de listagem (View `vagas`/`page_1`)

`/vagas?title=<norm>` casa, sem acento/caixa (contém): `node_field_data.title` **OU** `title` da empresa publicada em `field_vaga_empresa`. Implementado em `custom_panel_views_query_alter()` (ver [plan.md](../plan.md) — D1). Identificador `title` e demais filtros (`cidade`, `cursos`, `estado`, `escolaridade`, `regime`, `nid`) inalterados.
