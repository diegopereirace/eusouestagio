# Quickstart: Bloco Missão e Visão — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-16

## Pré-requisitos

- Stack Docker do projeto no ar
- Código da feature na branch atual (inclui asset de fundo do seed)
- Artefatos: [data-model.md](data-model.md), [contracts/missao-visao-render.md](contracts/missao-visao-render.md)

## Deploy local

```bash
docker compose exec -T drupal vendor/bin/drush cim -y
docker compose exec -T drupal vendor/bin/drush updb -y
docker compose exec -T drupal vendor/bin/drush cr
```

Ordem obrigatória: **cim → updb → cr**. Reexecutar `updb` e confirmar no-op / sem bloco ou paragraphs duplicados (SC-006).

No ambiente de origem, após criar types/fields/displays/placement no admin (ou via ensure do hook):

```bash
docker compose exec -T drupal vendor/bin/drush cex -y
```

## Esperado

- Bundles `missao_visao` e `missao_visao_item_p` ativos
- `field_itens_lista` cardinality 2; título/descrição via storages canônicos
- Bloco na região `content_full` só em `/quem-somos`
- Desktop: faixa full-width, overlay, duas colunas, tipografia branca Poppins, divisória sutil
- Mobile: empilhamento legível, sem scroll horizontal do bloco
- Home, `/para-estudantes`, `/contato` **sem** o bloco
- Banner 009 e layout Sobre nós 010 visualmente intactos
- Editor altera textos/imagem no painel e vê mudança após cache
- PRD §3.6 atualizado

## Aceite visual (amostra)

1. `/quem-somos` desktop ≥768px vs. layout Figma (estrutura, overlay, colunas, tipografia)
2. Viewport ≤767.98px — empilhamento e ausência de overflow-x
3. Inspecionar `.block-missao-visao` — estilos não vazam para home / banner / Sobre nós
4. Remover itens no admin (ou deixar 0) — página não quebra
5. Remover imagem — textos legíveis com fallback escuro
6. `drush updb -y` de novo — sem duplicatas; conteúdo editorial preservado
