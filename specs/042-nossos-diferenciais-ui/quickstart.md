# Quickstart: Refatorar UI — Nossos Diferenciais

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-01

Drush local: `docker compose exec drupal drush <cmd>`.

## Pré-requisitos

1. Feature **004** ativa (bloco seed + placements Home e `/para-empresas`).
2. Branch com tema `default` (Twig + library desta feature).
3. Referência Figma da seção (ou medidas da spec) para checklist visual.

## A) Cache

```bash
docker compose exec drupal drush cr
```

## B) Tipografia do cabeçalho (SC-002 / FR-007–008)

1. Abrir Home ≥992px; rolar até `#nossos-diferenciais`.
2. Inspecionar `.nd-header__title`: cor `#000000`, weight ~600, ~30px / ~38px.
3. Inspecionar `.nd-header__subtitle`: ~14px / ~20px, max-width ≈663px.
4. Confirmar que **não** usa `var(--brand-navy)` no título.

## C) Layout desktop + colagem (SC-001 / US1)

1. Viewport ≥992px: colagem (esquerda) e lista (direita) lado a lado, `align-items-center`.
2. Âncora CMS visível; fatias e decos na posição relativa do Figma (atrás/ao redor).
3. Checklist visual ≥90% dos pontos do frame.

## D) Lista e cores (US2)

1. Itens com ícone | título+descrição; gap consistente.
2. Títulos: 1º verde, 2º navy, 3º laranja (e ciclam se N>3).
3. Descrição longa quebra sem overflow horizontal.

## E) Mobile (SC-003 / US3)

1. Viewport ≤575px e ~768px: empilhamento cabeçalho → mídia → lista.
2. Sem scroll horizontal da página causado pela seção.
3. Fatias secundárias ocultas ou escala segura em viewport estreita.

## F) Editorial (SC-005 / US4)

1. Editar título/subtítulo/itens/imagem no admin; salvar.
2. `drush cr` se necessário; Home reflete conteúdo.
3. Sem imagem: placeholder mantém coluna; sem erro Twig.

## G) Reuso PE (SC-006)

1. Abrir `/para-empresas`; mesma apresentação atualizada.
2. Placement e conteúdo sem regressão.

## H) Deploy (contrato)

Ver [contracts/deploy-nossos-diferenciais-ui.md](contracts/deploy-nossos-diferenciais-ui.md): Git + `drush cr` apenas.
