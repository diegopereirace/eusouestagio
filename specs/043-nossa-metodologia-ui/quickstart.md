# Quickstart: Refatorar UI — Nossa Metodologia

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-01

Drush local: `docker compose exec drupal drush <cmd>`.

## Pré-requisitos

1. Feature **005** ativa (bloco seed + placements Home e `/para-empresas`).
2. Branch com tema `default` (Twig + library desta feature).
3. Referência Figma da seção (ou medidas da spec) para checklist visual.
4. Bloco com ≥3 etapas e ≥3 passos publicados.

## A) Cache

```bash
docker compose exec drupal drush cr
```

## B) Tipografia do cabeçalho (SC-002 / FR-010)

1. Abrir Home ≥992px; rolar até `#nossa-metodologia`.
2. Inspecionar `.nm-header__title`: cor `#000000`, weight ~600, ~30px / ~38px / ls ≈ −0.3px.
3. Confirmar que **não** usa `var(--brand-navy)` / `#003060` no título.
4. Cabeçalho alinhado à direita; subtítulo na mesma margem direita.

## C) Larguras das duas linhas (SC-003 / US1–US2)

1. Viewport ≥992px: DevTools → box de `.nm-etapas` ≤ 770px, centralizado.
2. Box de `.nm-passos` ≤ 1160px, centralizado.
3. Setas verdes alinhadas ao centro vertical dos círculos (não “penduradas” no rótulo).
4. Checklist visual ≥90% dos pontos do frame.

## D) Mobile / tablet (SC-004 / US3)

1. Viewport &lt;992px: wrappers das linhas com largura efetiva 100% (não forçam 770/1160).
2. Viewport ≤575px: empilhamento em coluna; zero overflow horizontal da página.
3. Tablet com wrap de passos: setas horizontais omitidas ou reorientadas de forma coerente.

## E) Editorial (SC-006 / US4)

1. Editar título/subtítulo/etapas/passos no admin; salvar.
2. `drush cr` se necessário; Home reflete conteúdo.
3. Sem etapas ou sem passos: cabeçalho e linha restante sem erro Twig.

## F) Reuso PE (SC-007)

1. Abrir `/para-empresas`; mesma apresentação atualizada.
2. Placement e conteúdo sem regressão.

## G) Deploy (contrato)

Ver [contracts/deploy-nossa-metodologia-ui.md](contracts/deploy-nossa-metodologia-ui.md): Git + `drush cr` apenas.
