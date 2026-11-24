# Implementation Plan: Rodapé — Abrir Vaga por permissão

**Feature**: `065-rodape-abrir-vaga-permissao`  
**Date**: 2026-11-24

## Technical Context

- Drupal 11; tema `default`; bloco placement `default_footer`; template `block--default-footer.html.twig`.
- Links hardcoded no Twig (feature 008); padrão de visibilidade por papel já usado no CTA PE (`is_empresa` + `user.roles` em `default_block_build_alter`).
- Permissão: `create vagas content` (node type `vagas`).

## Approach

1. `default_preprocess_block__default_footer`: expor `can_create_vaga` = autenticado && `hasPermission('create vagas content')`; anexar cache context `user.permissions`.
2. `default_block_build_alter`: para `default_footer`, adicionar `user.permissions` na chave de cache do build.
3. Twig: montar `empresas_links` com “Cadastre sua Empresa” sempre; acrescentar “Abrir Vaga” só se `can_create_vaga`.
4. PRD §3.6 (footer): uma frase sobre visibilidade condicional.
5. `drush cr` no ambiente local.

## Deploy

Somente código de tema + docs. Destino: `git pull` → `drush cr`. Sem `cim`/`updb`.
