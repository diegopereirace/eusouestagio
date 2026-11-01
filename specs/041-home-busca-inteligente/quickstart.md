# Quickstart: Home — Busca Inteligente

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-01

Drush local: `docker compose exec drupal drush <cmd>`.

## Pré-requisitos

1. Feature **040** ativa; seeds de vagas (**039**) com cursos/cidades conhecidos.
2. Branch com `custom_banners` + tema `default`.

## A) Cache

```bash
docker compose exec drupal drush cr
```

## B) Um ou dois campos (SC-001)

1. Só curso → Buscar → `/vagas?cursos=…`
2. Só cidade → Buscar → `/vagas?cidade=…` (+ `estado` se selecionado)
3. Ambos → ambos os params
4. Nenhum → listagem padrão

## C) Cruzamento (SC-002)

1. Selecionar cidade → digitar no curso: só cursos com vagas na cidade.
2. Selecionar curso → digitar na cidade: só cidades com vagas do curso; `N` filtrado.
3. Endpoint: `GET /api/cursos/autocomplete?q=&cidade=Fortaleza&estado=CE`
4. Endpoint: `GET /api/cidades/autocomplete?q=&curso=Administração`

## D) Disable + hint (SC-003)

1. Constraint que zera o oposto → campo `disabled` + hint.
2. Limpar origem → reabilita; hint some.

## E) Latência (SC-004)

1. Digitar 2+ chars (com/sem constraint): dropdown ≤1s.
2. Digitar enquanto probe do outro campo: origem não trava.

## F) Deploy (SC-005)

Git + `drush cr` apenas; repetir B–E sem admin.
