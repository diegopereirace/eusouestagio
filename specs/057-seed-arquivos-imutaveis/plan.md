# Implementation Plan: Seed — arquivos gerenciáveis imutáveis

**Branch**: `main` (workflow do projeto)  
**Date**: 2026-11-03  
**Spec**: [spec.md](spec.md)  
**Hook novo**: nenhum  
**Input**: Política de imutabilidade pós-1º materialização de arquivos gerenciáveis / assets seed

## Summary

Endurecer `_custom_configs_seed_file_from_module_asset` (`$replace = FALSE`), remover forces do banner Para Estudantes e converter `sync_*` agressivos (MD5 / filename seed-like) em fill-if-empty / copy-if-missing. Atualizar regras Cursor. Spec Kit feature `057`.

## Technical Context

| Item | Valor |
|------|--------|
| Language | PHP 8.3+ / Drupal 11 |
| Primary change | `modules/custom/custom_configs/custom_configs.install` |
| Rules | `.cursor/rules/estagio-fluxo-dev.mdc`, `drupal-deploy-configs.mdc` |
| Testing | Manual — re-chamar seed em ambiente com arquivo já presente |
| Constraints | Sem hook de replace; sem mexer em `core/`/`vendor/` |

## Architecture

```text
seed/sync → helper($replace=FALSE)
  ├─ URI/disco existe → reuse (no copy)
  ├─ ausente → copy + File entity
  └─ $replace=TRUE (só hook explícito futuro) → Replace
```

## Deploy

Sem `hook_update_N` novo. Destino que já rodou seeds anteriores: comportamento muda apenas em **chamadas futuras** aos helpers/syncs (ex.: novo update que reutilize a função). Nenhum `cim` estrutural obrigatório.
