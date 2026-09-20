# Quickstart: Quem Somos — Missão e Visão no Node — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-20

## Pré-requisitos

- Stack Docker do projeto no ar
- Código da feature na branch atual
- Artefatos: [data-model.md](data-model.md), [contracts/quem-somos-missao-visao-render.md](contracts/quem-somos-missao-visao-render.md)

## Deploy local

```bash
docker compose exec -T drupal vendor/bin/drush cim -y
docker compose exec -T drupal vendor/bin/drush updb -y
docker compose exec -T drupal vendor/bin/drush cr
```

Ordem obrigatória: **cim → updb → cr**. Reexecutar `updb` e confirmar no-op / sem fields ou grupos duplicados (SC-007).

No ambiente de origem, após criar instances/grupo/displays e desabilitar o bloco:

```bash
docker compose exec -T drupal vendor/bin/drush cex -y
```

## Esperado

- Bundle `quem_somos` com `group_missao_visao` e fields `field_imagem_desktop`, `field_text_simple_long`, `field_text_simple_long_2`
- Sem “Segundo Bloco” / fields `*_2` no formulário
- `/quem-somos` desktop: faixa full-width, overlay, duas colunas, tipografia branca Poppins, divisória sutil
- Mobile: empilhamento legível, sem scroll horizontal da seção
- **Uma** faixa Missão/Visão (Node); bloco `default_missaovisao` **não** aparece
- Banner 009 e Sobre nós 010 visualmente intactos
- Editor altera imagem/textos no Node e vê mudança após cache
- PRD §3.1.0 / §3.6 atualizados

## Aceite visual (amostra)

1. `/quem-somos` desktop ≥768px vs. Anexo 1 (estrutura, overlay, colunas, tipografia)
2. Confirmar ausência do layout Anexo 2 (foto lateral + ícones Missão/Visão/Valores)
3. Viewport ≤767.98px — empilhamento e ausência de overflow-x
4. Inspecionar `.quem-somos-missao-visao` — estilos não vazam para home / banner / Sobre nós
5. Esvaziar ambos textos e imagem — seção omitida; página não quebra
6. Remover só a imagem — textos legíveis com fallback escuro
7. Contar faixas Missão/Visão = 1; home / internas amostradas sem a seção do Node
8. `drush updb -y` de novo — sem duplicatas; conteúdo editorial preservado
