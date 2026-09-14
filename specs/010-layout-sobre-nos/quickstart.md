# Quickstart: Layout “Sobre nós” — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-14

## Pré-requisitos

- Stack Docker do projeto no ar
- Código da feature na branch atual
- Node `quem_somos` publicado com título, texto formatado e imagem (conteúdo editorial existente)
- Artefatos: [data-model.md](data-model.md), [contracts/sobre-nos-render.md](contracts/sobre-nos-render.md)

## Deploy local

```bash
docker compose exec -T drupal vendor/bin/drush cim -y
docker compose exec -T drupal vendor/bin/drush updb -y
docker compose exec -T drupal vendor/bin/drush cr
```

Ordem obrigatória: **cim → updb → cr**. Reexecutar `updb` e confirmar no-op / sem duplicar field instances (SC-006).

No ambiente de origem, se form/view/instances forem alterados no admin:

```bash
docker compose exec -T drupal vendor/bin/drush cex -y
```

## Esperado

- Form do node: Primeiro Bloco com `field_titulo`, `field_text_long_formatted`, `field_imagem`
- `/quem-somos` desktop: texto contorna a imagem à direita; anel verde + círculo laranja no wrapper da foto
- Proporção da imagem reconhecível (~432×269); Poppins na seção
- Mobile ≤767.98px: sem float quebrado; sem scroll horizontal da seção; decorativos sem cobrir texto
- Segunda seção intacta após clearfix (sem herdar float)
- Banner 009 no topo intacto
- Home e outra interna sem círculos “fantasma”
- PRD §3.1 atualizado (`quem_somos` + layout wrap)

## Aceite visual (amostra)

1. `/quem-somos` ≥768px vs. Figma (wrap, posição da imagem, geometria)
2. Viewport ≤767.98px — legibilidade e ausência de overflow-x
3. Inspecionar HTML: negritos/parágrafos do WYSIWYG preservados no fluxo
4. Remover temporariamente a imagem (ou simular ausência) — texto legível em largura total
5. Home + uma interna — sem regressão de estilos desta feature
6. `drush updb -y` de novo — mensagem de no-op / sem duplicatas
