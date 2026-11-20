# Research: Home — Busca Inteligente

**Data**: 2026-11-01 | **Feature**: [spec.md](spec.md)

## R1 — Params de constraint nos endpoints existentes

**Decision**: `GET /api/cursos/autocomplete?q=&cidade=&estado=` e `GET /api/cidades/autocomplete?q=&curso=`.

**Rationale**: FR-002/003; sem rotas novas; View já filtra por esses GET.

## R2 — Índice cacheável vaga×cidade×UF×curso

**Decision**: classe `VagaLocationCourseIndex` carrega triples agregados (cidade, UF, nome curso, count) uma vez; controllers filtram em PHP.

**Rationale**: latência; evita fragmentação excessiva de CID; tags `node_list:vagas` + `taxonomy_term_list:curso`.

## R3 — q vazio só com constraint

**Decision**: probe de “há opções?” usa `q=` vazio + constraint → top 10 / existência.

**Rationale**: JS precisa saber se desabilita o outro campo sem forçar digitação.

## R4 — Debounce 300ms no probe, 80ms no AC

**Decision**: delay jQuery UI permanece 80ms; só o probe cruzado usa 300ms; `select` dispara probe imediato.

**Rationale**: FR-007; digitação fluida.

## R5 — Disable + hint, não ocultar campo

**Decision**: `disabled` + classe + hint; limpa valor/`estado`.

**Rationale**: layout 040 (~896×86) permanece; usuário entende por que o campo sumiu de uso.

## R6 — Sem hook/cex

**Decision**: só código + assets + `drush cr`.

**Rationale**: FR-010; nada estrutural.
