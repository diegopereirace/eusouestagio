# Quickstart: Refatorar UI — Nossos Diferenciais

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-01

Drush local: `docker compose exec drupal vendor/bin/drush.php <cmd>`.

## Pré-requisitos

1. Feature **004** ativa (bloco seed + placements Home e `/para-empresas`).
2. Branch com tema `default` (Twig + library desta feature).
3. Referência Figma da seção (ou medidas da spec) para checklist visual.
4. Imagem CMS da colagem completa **ou** assets seed em `modules/custom/custom_configs/assets/nossos-diferenciais/` + `drush updb` (`11064`).

## A) Cache e seed de mídia

```bash
docker compose exec drupal vendor/bin/drush.php updb -y
docker compose exec drupal vendor/bin/drush.php cr
```

`drush cr` é **obrigatório** após deploy de Twig/CSS (library dedicada).

## B) Tipografia do cabeçalho (SC-002 / FR-007–008)

1. Abrir Home ≥768px (tokens Figma do título); confirmar também ≥992px.
2. Inspecionar `.nd-header__title`: cor `#000000`, weight ~600, ~30px / ~38px.
3. Inspecionar `.nd-header__subtitle`: ~14px / ~20px, max-width ≈663px.
4. Confirmar que **não** usa `var(--brand-navy)` no título.

## C) Layout desktop + colagem (SC-001 / US1)

1. Viewport ≥992px: colagem (`col-lg-7`) e lista (`col-lg-5`) lado a lado, `align-items-center`.
2. Imagem CMS (colagem completa) visível; anéis atrás; ponto navy na frente da âncora; sem overflow da página.
3. `<img.nd-media__img>` usa style `wide`, com `width`/`height` e `loading="lazy"`.
4. Checklist visual ≥90% dos pontos do frame.

## D) Lista e cores (US2)

1. Itens com ícone | título+descrição; gap consistente.
2. Ícones via `image_style('thumbnail')` (não PNG original); `width`/`height` 72.
3. No DevTools, computed `color` dos títulos: 1º verde, 2º navy, 3º laranja (ciclam se N>3).
4. Confirmar que o seletor efetivo é `.field__item:nth-child(3n+k)` (não `.nd-item:nth-child`).
5. Descrição longa quebra sem overflow horizontal.

## E) Mobile (SC-003 / US3)

1. Viewport ≤575px e ~768px: empilhamento cabeçalho → mídia → lista.
2. Sem scroll horizontal da página causado pela seção (`overflow: hidden`).
3. Ring cinza oculto abaixo de 992px; colagem escala com o container.

## F) Editorial (SC-005 / US4)

1. Editar título/subtítulo/itens/imagem no admin; salvar.
2. `drush cr` se necessário; Home reflete conteúdo.
3. Sem imagem: placeholder mantém coluna; sem erro Twig.
4. Troca da imagem CMS substitui a colagem inteira (fatias embutidas no asset).
5. Ambiente só-seed: `11064` preenche colagem/ícones vazios sem sobrescrever editorial.

## G) Reuso PE (SC-006)

1. Abrir `/para-empresas`; mesma apresentação atualizada.
2. Placement e conteúdo sem regressão.

## H) Smoke pós-deploy (manual / CI local)

1. View-source da Home: CSS `nossos-diferenciais.css` (library `default/nossos_diferenciais`) presente.
2. `#nossos-diferenciais img.nd-media__img` com `src` contendo `/styles/wide/`.
3. Ícones `.nd-item__icon img` com `src` contendo `/styles/thumbnail/`.

## I) Deploy (contrato)

Ver [contracts/deploy-nossos-diferenciais-ui.md](contracts/deploy-nossos-diferenciais-ui.md): Git + `updb` + **`drush cr` obrigatório**.
