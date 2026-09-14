# Quickstart: Banner Quem Somos — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-14

## Pré-requisitos

- Stack Docker do projeto no ar
- Código da feature na branch atual (inclui asset PNG do seed)
- Artefatos: [data-model.md](data-model.md), [contracts/banner-render.md](contracts/banner-render.md)

## Deploy local

```bash
docker compose exec -T drupal vendor/bin/drush cim -y
docker compose exec -T drupal vendor/bin/drush updb -y
docker compose exec -T drupal vendor/bin/drush cr
```

Ordem obrigatória: **cim → updb → cr**. Reexecutar `updb` e confirmar no-op / sem node duplicado (SC-006).

No ambiente de origem, após criar display/allowed value/placements no admin:

```bash
docker compose exec -T drupal vendor/bin/drush cex -y
```

## Esperado

- `field_local_exibicao` aceita `quem_somos`
- Display `block_quem_somos` ativo; bloco só em `/quem-somos` (região `banner`)
- `/quem-somos` **não** lista mais em `banners-block_1`
- Desktop: layout bipartido, copy/CTAs Figma, overflow da imagem
- Mobile: texto→imagem, CTAs tocáveis, sem scroll horizontal do banner
- CTAs → `/para-estudantes` e `/cadastro/candidato`
- Zero banners Quem Somos publicados → sem caixa vazia
- Home e `/para-estudantes` / `/contato` sem regressão visual do banner legado/home
- PRD §3.1.1b / §3.6 atualizado

## Aceite visual (amostra)

1. `/quem-somos` desktop ≥768px vs. prints Figma (estrutura, cores, CTAs, overflow)
2. Viewport ≤767.98px — empilhamento e ausência de overflow-x
3. Inspecionar `href`s dos dois botões
4. `/para-estudantes` e home — carrosséis/banners legados intactos
5. Despublicar o banner Quem Somos — wrapper some
6. `drush updb -y` de novo — sem duplicatas
