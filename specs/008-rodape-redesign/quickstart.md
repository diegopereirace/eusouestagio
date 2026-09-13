# Quickstart: Redesign do Rodapé — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-13

## Pré-requisitos

- Stack Docker do projeto no ar
- Código da feature na branch atual
- Spec/contratos: [data-model.md](data-model.md), [contracts/footer-render.md](contracts/footer-render.md)

## Deploy local

```bash
docker compose exec -T drupal vendor/bin/drush cim -y
docker compose exec -T drupal vendor/bin/drush updb -y
docker compose exec -T drupal vendor/bin/drush cr
```

Ordem obrigatória: **cim → updb → cr**. Reexecutar `updb` uma segunda vez e confirmar mensagem de no-op / sem alteração duplicada (SC-005).

Se houver mudança estrutural durante a implementação, no ambiente de origem:

```bash
docker compose exec -T drupal vendor/bin/drush cex -y
```

## Esperado

- Rodapé publicado (`default_footer` / UUID `d0326db5-fc80-4cf5-a0b9-8f779dc4aeba`)
- Desktop (≥992px): 5 colunas — marca (logo + 4 sociais circulares) | Institucional | Para Estudantes | Para Empresas | Contato
- Mobile (&lt;992px): accordion nas 3 colunas de links; marca e contato sempre visíveis
- Links conforme tabela do data-model (rotas futuras podem 404)
- Contato com `mailto:` e `tel:`; sociais externos com `target="_blank"`
- Barra inferior centralizada: copyright dinâmico + “Desenvolvido por Diego Pereira” (sem link)
- Tagline e endereço **não** aparecem
- `.whatsapp-float-block` com `position: fixed` e `z-index: 1050`
- `.cursor/rules/estagio-fluxo-dev.mdc` versionada; PRD § footer atualizado

## Config sync (T025)

Nenhuma mudança estrutural de displays/placement nesta feature (só Twig/CSS + `hook_update_N` 11011). **`drush cex` é no-op estrutural** — não há YAMLs novos a exportar além do já versionado (`block.block.default_footer`, fields do bundle `footer`).

## Aceite visual (amostra)

1. Home, listagem de vagas e cadastro — rodapé idêntico ao anexo 2 no desktop
2. Viewport estreita — expandir/recolher Institucional, Estudantes, Empresas
3. Inspecionar `href`s da tabela de rotas
4. Rolar a página — float WhatsApp fixo e acima do rodapé
5. Amostragem: sem regressão visual fora do rodapé/float
