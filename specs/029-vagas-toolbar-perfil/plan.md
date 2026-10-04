# Implementation Plan: Toolbar + perfil lateral (`/vagas`)

**Branch**: ativa / `feature-vagas-new`  
**Date**: 2026-10-04  
**Spec**: [spec.md](spec.md)

## Summary

Estender a listagem `028` com toolbar (total + Ordenar por) e aside de usuário logado. Implementação: Twig/CSS/preprocess no tema + `hook_views_query_alter` em `custom_configs`. Sem YAML novo previsto.

## Technical Context

PHP 8.3 / Drupal 11 / Twig / CSS; tema `default`; módulo `custom_configs`. Validação manual via [quickstart.md](quickstart.md).

## Design Decisions

1. Contador: `$view->total_rows` (pager full já ativo).
2. Sort: GET `sort_order=DESC|ASC` + `custom_configs_views_query_alter` (não exposed sorts nativos).
3. Aside: variáveis no preprocess View; reuso de lógica do topo (avatar/nome/painel).
4. Subtítulo: candidato → “Candidato”; empresa → “Empresa”; moderador → “Moderador”.
5. CSS na library `vagas_lista_vertical`; layout `.vagas-lista-layout`.
6. Header H3 “Vagas” substituído pelo toolbar (h2 acessível no contador).

## Project Structure

```text
specs/029-vagas-toolbar-perfil/
themes/custom/default/
  templates/views/views-view--vagas--page-1.html.twig
  assets/css/components/vagas-lista-vertical.css
  default.theme
modules/custom/custom_configs/custom_configs.module
PRD.md
```
