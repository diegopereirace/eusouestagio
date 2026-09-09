# Contract: Hero de busca da home (form GET + pills)

**Consumidor**: bloco `HeroSearchBlock` (somente `<front>`, região `highlighted`)
**Provedor**: View `vagas`, display `page_1` (`/para-estudantes`) — exposed filters
**Tipo**: contrato de query string GET (não é API JSON; é o contrato entre o form estático e os filtros expostos da View)

## 1. Submissão do form hero

```http
GET /para-estudantes?cursos={valor}&regime={tid}
```

| Parâmetro | Identifier do filtro exposto | Formato do valor | Vazio |
|-----------|------------------------------|------------------|-------|
| `cursos` | `cursos` (existente, sobre `field_cursos_t`) | **Depende do widget configurado** — ver §4 | omitir parâmetro |
| `regime` | `regime` (**novo**, sobre `field_regime_t`) | TID do termo (select) | omitir parâmetro |

**Regras**:
- Os dois parâmetros combinam com AND (comportamento padrão de exposed filters da View) — FR-9.
- Submissão totalmente vazia → `/para-estudantes` sem filtros → listagem padrão, sem erro (edge case).
- O hero **nunca** envia `cidade` (FR-8/Q2); o filtro `cidade` continua disponível apenas na listagem.
- Método GET (não POST): URL compartilhável/bookmarkável; `method="get"` no `<form>`, `action="/para-estudantes"`.
- Parâmetros desconhecidos/malformados são ignorados pela View (comportamento nativo) — nunca quebram a listagem.

## 2. Pills (quick filters) — FR-10

Pills são links server-side (sem JS). Clique = submissão automática.

| Pill | URL |
|------|-----|
| Remoto | `/para-estudantes?regime={tid_do_termo_Remoto}` |
| TI | `/para-estudantes?cursos={valor_do_termo_TI}` |
| Administração | `/para-estudantes?cursos={valor_do_termo_Administração}` |
| Design | `/para-estudantes?cursos={valor_do_termo_Design}` |
| Marketing | `/para-estudantes?cursos={valor_do_termo_Marketing}` |

**Regras**:
- TID/valor resolvido em runtime por `nome do termo + vocabulário` (nunca hardcoded) — research R5.
- Termo inexistente no vocabulário → pill **não renderizada** (edge case).
- Markup: `<a>` com texto do termo; cache do bloco com tags `taxonomy_term_list:{vid}`.

## 3. Textos fixos (FR-6, SC-6)

| Elemento | Valor | Local |
|----------|-------|-------|
| Título | `Encontre seu estágio ideal hoje.` | hardcoded no Twig |
| Subtítulo | `Conectamos talentos universitários às melhores oportunidades do mercado através de uma plataforma moderna e intuitiva.` | hardcoded no Twig |
| Placeholder curso | `Qual o seu curso?` | hardcoded no Twig |
| Placeholder/label regime | `Cidade ou Remoto` | hardcoded no Twig (rótulo de UI; filtra só regime — FR-8); também é a option vazia do select |
| Botão | `Buscar` (+ seta →) | hardcoded no Twig |

Nenhum desses textos pode existir como campo administrável no banco (SC-6: zero linhas no DB).

## 4. Formato confirmado (export baseline 2026-09-09)

Filtro exposto `cursos` na View `vagas` `page_1`: plugin `taxonomy_index_tid`, `type: textfield`, `vid: curso`, identifier `cursos`.

- **Hero e pills de curso** enviam o **nome do termo** (texto livre).
- **Pill/filtro `regime`** envia o **TID** (widget select no filtro exposto).
- O filtro também aceita TID via `setExposedInput` (preprocess de vagas similares), mas o contrato do hero usa nome para `cursos`.
