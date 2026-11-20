# Contract: Hero de busca da Home (form GET + pills) — pós-040

**Consumidor**: bloco `custom_banners_hero_search` (`HeroSearchBlock`, `<front>`, região `highlighted`)  
**Provedor**: View `vagas`, display `page_1` (`/vagas`) — exposed filters  
**Tipo**: contrato de query string GET (substitui o mapeamento regime do contrato legado em `specs/001-banners-busca-home/contracts/home-search.md` **somente para a Home**)

## 1. Submissão do form hero

```http
GET /vagas?cursos={slug_termo}&cidade={slug_cidade}&estado={uf}
(slugs: minúsculas, sem acento — ex. `administracao`, `sao paulo`, `ce`; match normalizado no backend)
```

| Parâmetro | Identifier View | Formato | Quando omitir |
|-----------|-----------------|---------|---------------|
| `cursos` | `cursos` | nome do termo (textfield) | vazio |
| `cidade` | `cidade` | nome canônico da cidade (texto) | vazio |
| `estado` | `estado` | sigla UF (ex. `CE`) | sem seleção de autocomplete / vazio |

**Regras**:

- Parâmetros combinam com AND (exposed filters da View).
- Submissão totalmente vazia → `/vagas` listagem padrão, sem erro.
- O hero Home **não** envia mais `regime`.
- Método GET; `action` = rota `view.vagas.page_1`.
- Após escolher sugestão do autocomplete de cidades: `cidade` = `value` canônico; `estado` = UF do item.
- Digitação livre sem seleção: pode enviar só `cidade`; `estado` omitido.

## 2. Pills (quick filters)

Links server-side (sem JS). Clique = navegação com query.

| Pill | URL |
|------|-----|
| TI | `/vagas?cursos={nome_termo}` |
| Administração | `/vagas?cursos={nome_termo}` |
| Design | `/vagas?cursos={nome_termo}` |
| Marketing | `/vagas?cursos={nome_termo}` |

**Regras**:

- **Sem** pill Remoto / regime.
- Resolução por `vid=curso` + label em runtime; termo ausente → pill omitida.
- Cache tags de taxonomia `curso`.

## 3. Textos fixos (UI)

| Elemento | Valor |
|----------|-------|
| Placeholder/label localização | `Cidade` (sem a palavra “Remoto”) |
| Placeholder curso | `Qual o seu curso?` (inalterado) |
| Botão | `Buscar` |

## 4. Relação com hero `/vagas` (027)

O hero `custom_banners_vagas_hero_search` já usa `name="cidade"`. Nesta feature apenas o copy label/placeholder alinha para **“Cidade”**; o contrato GET de 027 permanece (`title`, `cidade`, `cursos`).
