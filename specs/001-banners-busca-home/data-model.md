# Data Model: Centralização de Banners e Busca da Home

**Data**: 2026-09-09 | **Fonte**: [spec.md](spec.md) + [research.md](research.md)

## 1. Entidade nova: node `banners`

Content type único e centralizador de todos os banners do site (FR-1). Estrutura criada via UI local e exportada para `config/sync` (FR-12) — **não** criada em código.

| Campo | Tipo | Obrigatório | Regras |
|-------|------|-------------|--------|
| `title` | string (core) | sim | Rótulo administrativo do banner |
| `field_imagem_desktop` | image | **sim** | Alt obrigatório; style `banner_carousel` (home) / `banner_internas` (internas) |
| `field_imagem_mobile` | image | não | Fallback para desktop quando vazio (edge case); styles `*_mobile` |
| `field_local_exibicao` | list_string | **sim** | Valores: `home` = "Home", `internas` = "Internas" (FR-2); sem valor → não exibe em local nenhum até correção (edge case) |
| `field_peso` | integer | **sim** (default 0) | Ordenação manual: menor valor aparece primeiro; desempate `created DESC` |
| `status` | boolean (core) | — | Publicado/despublicado controla exibição imediata (Cenário 2) |
| `created` | timestamp (core) | — | Desempate da ordenação quando pesos iguais |

**Deliberadamente ausentes** (YAGNI + decisões de clarify):
- `field_text_simple_long` (overlay) — **não portado** (Q1-B); texto legado vai para o `alt` da imagem desktop.
- Campo de link/CTA — arte completa embute CTA na imagem (padrão já vigente no carrossel atual).

**Regras de renderização (derivadas dos edge cases)**:
- View filtra `status = 1` + `field_local_exibicao = {local}`; sort `field_peso ASC`, `created DESC`; zero resultados → seção omitida (sem container vazio).
- `field_imagem_mobile` vazio → `<source>` mobile usa a imagem desktop.
- 1º slide da home: `fetchpriority="high"` + `loading="eager"`; demais: `loading="lazy"` (FR-4).

## 2. Mapeamento de migração (hook_update_N — `custom_banners_update_11001()`)

### 2.1 Origem: node `banner_internas` → destino: node `banners` (local = `internas`)

| Origem (`banner_internas`) | Destino (`banners`) | Nota |
|---------------------------|---------------------|------|
| `title` | `title` | direto |
| `field_imagem` | `field_imagem_desktop` | arquivo reutilizado (mesmo `fid`, sem duplicar mídia) |
| campo mobile legado* | `field_imagem_mobile` | *machine name confirmado no export; se ausente/vazio → fallback desktop |
| `field_text_simple_long` | **`alt` de `field_imagem_desktop`** | overlay eliminado (Q1-B); texto preservado no alt |
| `status`, `created`, `uid` | `status`, `created`, `uid` | preservados (ordenação por `created` mantém sequência editorial) |
| — | `field_local_exibicao = internas` | fixo |

### 2.2 Origem: block_content `banner` (carrossel home) → destino: node `banners` (local = `home`)

| Origem (block `banner`) | Destino (`banners`) | Nota |
|------------------------|---------------------|------|
| `field_image` (multi, delta N) | node novo: `field_imagem_desktop` | **1 node por slide** (delta → node), alt do item preservado |
| `field_image_mobile` (delta N) | `field_imagem_mobile` do mesmo node | pareamento por delta; ausente → fallback |
| `info`/título do bloco + índice | `title` | ex.: "Banner home — {alt ou #delta}" |
| `created` do bloco (+ delta como offset de segundos) | `created` | preserva ordem visual atual dos slides via `created DESC` |
| — | `field_local_exibicao = home`, `status = 1` | fixo |

**Pós-migração (ainda no R1)**: blocos legados desposicionados via config (placements removidos do sync); tipos antigos permanecem vazios até o R2 (ver research R2). Idempotência: o update hook verifica existência de nodes `banners` já migrados (marcador em `key_value` ou checagem por contagem nas origens) antes de inserir — seguro para re-run em `updb`.

## 3. Entidades alteradas (sem mudança de schema)

### View `banners` (config)
- Displays `block_1`/`block_2`/`block_3`: filtro de tipo passa a ser `banners` + `field_local_exibicao = internas`; sort `created DESC`; campos renderizados: `field_imagem_desktop`, `field_imagem_mobile` (row style mantém field `nothing` reescrito pelos templates do tema).
- Display **novo** `block_home`: mesmos filtros com `field_local_exibicao = home`; sem pager (todos os slides); cache tags de lista de nodes.

### View `vagas` `page_1` (config)
- **Novo** filtro exposto: `field_regime_t` (taxonomia regime), identifier `regime`, widget select, operador padrão `ou`/igualdade, combinável com `cursos`/`cidade`/`estados`/`escolaridade` existentes (FR-8, FR-9).
- Filtro `cidade` existente **inalterado** (busca por cidade só na listagem — FR-8/Q2).

### Taxonomias (somente leitura nesta feature)
- Vocabulário de **cursos** (alimenta `field_cursos_t`, filtro `cursos`, pills TI/Administração/Design/Marketing).
- Vocabulário de **regime** (alimenta `field_regime_t`, filtro `regime`, pill Remoto).
- Machine names confirmados no export baseline e documentados no PRD §3.4 (fecha gap R4). Pills resolvem TID por `nome do termo + vid` em runtime (research R5).

## 4. Diagrama

```text
ANTES                                    DEPOIS (R1 + R2)
───────────────                          ─────────────────────────────
block_content banner ──┐                 node banners
 (field_image,         │                  ├─ field_imagem_desktop (req)
  field_image_mobile)  ├──migrate──────►  ├─ field_imagem_mobile (opt→fallback)
                       │                  ├─ field_local_exibicao: home|internas
node banner_internas ──┘                  ├─ created (ordenação DESC)
 (field_imagem,                           └─ status
  field_text_simple_long ──► alt)
        │                                      │
        ▼                                      ▼
View banners block_1/2/3 (internas)      View banners: block_home (home, carrossel)
                                         + block_1/2/3 (internas, <picture> sem overlay)

View vagas page_1: filtros [nid, cursos, estados, cidade, escolaridade]
                           + NOVO [regime] ◄── hero (GET) + pills (links)
```

## 5. Validações resumo (rastreio FR → dados)

| FR | Regra de dados |
|----|----------------|
| FR-1/FR-2 | tipo único + `field_local_exibicao` obrigatório filtra todas as listagens |
| FR-3 | 2 campos imagem, mobile opcional com fallback |
| FR-5/5a/5b | mapeamentos §2.1/§2.2; overlay não portado; texto → alt |
| FR-8 | filtro `regime` sobre `field_regime_t`; `field_cidade` fora do hero |
| FR-13 | migração 100% em `hook_update_N` via Entity API |
