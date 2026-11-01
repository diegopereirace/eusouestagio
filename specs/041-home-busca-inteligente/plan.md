# Implementation Plan: Home — Busca Inteligente (curso × cidade)

**Branch**: `feat/home-busca-inteligente`  
**Date**: 2026-11-01  
**Spec**: [spec.md](spec.md)  
**Input**: Spec + plano Cursor `home_busca_inteligente` (cruzamento + latência)

## Summary

Estender os autocompletes da Home para cruzar **curso ↔ cidade**: params opcionais nos endpoints existentes; agregação cacheável + filtro em PHP; JS com constraints, debounce 300ms no probe, disable/hint; busca com 1 ou 2 campos preservada. Deploy = Git + `drush cr`.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, JS (Drupal behaviors + jQuery UI autocomplete)  
**Primary Dependencies**: `custom_banners`, tema `default`; zero Composer novo  
**Storage**: campos existentes `field_cidade`, `field_estados`, `field_cursos_t` — sem schema novo  
**Testing**: manual via [quickstart.md](quickstart.md)  
**Constraints**: sem `core/`/`vendor/`; sem `hook_update_N`/`cex` obrigatório; só Home; ≤1s latência percebida

## Design Decisions

1. Params novos: cursos endpoint `cidade`/`estado`; cidades endpoint `curso`.
2. Índice compartilhado `vaga × cidade × UF × curso` em `cache.data` (1h, tags `node_list:vagas` + `taxonomy_term_list:curso`); filtro `q` + constraints em PHP.
3. Sem constraint: cursos = todos os termos publicados (atual); cidades = grupos com vagas (atual).
4. `q` vazio permitido **só** com constraint (probe / top 10).
5. JS: delay AC 80ms; probe debounce 300ms; cache key `path|q|constraints`; abort XHR; select → probe imediato.
6. Hints Twig + CSS disabled; Buscar sempre ativo.
7. Deploy: `drush cr` apenas.

## Project Structure

```text
specs/041-home-busca-inteligente/
modules/custom/custom_banners/src/Controller/
  CursosAutocompleteController.php
  CidadesAutocompleteController.php
  VagaLocationCourseIndex.php          # NOVO — índice cacheável
themes/custom/default/assets/js/hero-search.js
themes/custom/default/assets/css/components/hero-search.css
modules/custom/custom_banners/templates/block--hero-search.html.twig
PRD.md                                 # cirúrgico §3.6 / §5 / rotas
```

## Complexity Tracking

| Trade-off | Justificativa | Alternativa rejeitada |
|-----------|---------------|------------------------|
| Índice único em cache vs SQL por tecla | Latência ≤1s / ≤300ms hit | Query join a cada keystroke |
| Probe debounce 300ms | Não trava digitação | Probe a cada tecla |
| Sem services.yml | YAGNI; instanciação no controller | Serviço novo só para DI |

## Constitution Check

PASS — SDD, sem core/vendor, reuso endpoints 040, deploy Git+cr, Clean URLs, performance, segurança read-only.
