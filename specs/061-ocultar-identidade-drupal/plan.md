# Implementation Plan: Ocultar identificação pública do Drupal

**Branch**: `main` (workflow do projeto)  
**Date**: 2026-11-05  
**Spec**: [spec.md](spec.md)  
**Input**: Remover Generator meta + headers X-Generator / X-Powered-By via `custom_configs` + scaffold htaccess.

## Summary

1. `custom_configs_page_attachments_alter()` remove `system_meta_generator` e qualquer meta `name=Generator`.
2. `RemoveCmsIdentityHeadersSubscriber` remove `X-Generator` e `X-Powered-By` em `KernelEvents::RESPONSE`.
3. Scaffold Apache `Header always unset` para os mesmos headers.
4. Sem `hook_update_N` / sem `config/sync`; deploy = `git pull` + `drush cr` (+ reload Apache se htaccess mudou).

## Technical Context

| Item | Valor |
|------|--------|
| Language/Version | PHP 8.3+ / Drupal 11 |
| Primary Dependencies | `custom_configs` (módulo já ativo); Symfony HttpKernel events |
| Storage | Nenhum (runtime only) |
| Testing | Manual: View Source + `curl -I` ([quickstart.md](quickstart.md)) |
| Constraints | Sem `core/`/`vendor/`; sem remover `X-Drupal-Cache*`; Metatag SEO intacto |

## Constitution Check

| Gate | Status |
|------|--------|
| SDD — spec antes do código | PASS |
| Sem `core/` / `vendor/` | PASS |
| Deploy automatizado | PASS (código + htaccess; `cr` suficiente) |
| Seeds / 057 | N/A |
| PRD guardião | PASS — menção cirúrgica M7 |

## Project Structure

```text
modules/custom/custom_configs/
  custom_configs.module          # page_attachments_alter
  custom_configs.services.yml    # novo
  src/EventSubscriber/
    RemoveCmsIdentityHeadersSubscriber.php
scaffold/htaccess-security-append.txt
.htaccess                        # espelho do append
specs/061-ocultar-identidade-drupal/
```

## Implementation approach

- Formato `html_head`: `[$element, $key]` — filtrar `$item[1] === 'system_meta_generator'` e `name=Generator`.
- Subscriber prioridade **-10** (após core `ResponseGeneratorSubscriber` default 0).
- Htaccess: bloco `BEGIN/END eusouestagio-security` via scaffold append.
