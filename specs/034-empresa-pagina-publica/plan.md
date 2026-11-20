# Implementation Plan: Página pública da Empresa + Sobre a Vaga

**Branch**: workflow do projeto  
**Feature Directory**: `specs/034-empresa-pagina-publica`  
**Predecessor**: `033-vaga-sidebar-acoes`  
**Hook**: `custom_configs_update_11052`

## Summary

Remover “Sobre a Empresa” do detalhe da vaga; seedar “Sobre a Vaga”; linkar o nome da empresa no header; entregar página pública temporária do CT `empresa` (campos CMS + View de vagas abertas + Pathauto).

## Technical approach

1. Twig vaga: remover seção legado; link `<a class="vaga-detalhe__empresa-nome">` → canonical empresa.
2. Ensure instances em `empresa`: `field_text_long_formatted`, `field_cidade`, `field_estados` + displays.
3. Pathauto pattern `empresa` → `/empresa/[node:title]`; regenerar aliases seed.
4. View `empresa_vagas` display `block_empresa`: type=vagas, status=1, contextual `field_vaga_empresa_target_id`.
5. Twig `node--empresa--full.html.twig` + library `empresa_pagina`; embed `{{ drupal_view('empresa_vagas', 'block_empresa', node.id) }}`.
6. Seeds idempotentes sobre/local empresa + sobre/requisitos vaga.
7. `drush cex`; PRD §3.1 + §10.

## Deploy

Destino: `cim` → `updb` → `cim` → `cr`.

## Complexity

| Item | Justificativa |
|------|----------------|
| View dedicada `empresa_vagas` | Evita inflar `views.view.vagas`; contextual limpo |
| Campos reutilizados | Regra de reuso de storage do projeto |
