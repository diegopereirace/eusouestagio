# Research: Ocultar identificação pública do Drupal

**Date**: 2026-11-05

## R1 — Fonte da metatag Generator

Core emite via `BareHtmlPageRenderer::systemPageAttachments()` com key `system_meta_generator` no formato `[$element, $key]` em `#attached['html_head']`. Remoção correta: `hook_page_attachments_alter` no módulo custom (não patch de core).

## R2 — Fonte do header X-Generator

`Drupal\Core\EventSubscriber\ResponseGeneratorSubscriber` em `KernelEvents::RESPONSE` (prioridade default). Subscriber custom com prioridade negativa remove após o set.

## R3 — X-Powered-By

Vem do PHP (`expose_php=On`) via `mod_php`, fora do Symfony Response — o EventSubscriber sozinho não basta. Solução canônica:

1. `expose_php=Off` em `docker/php/custom.ini` (local; montado em `docker-compose.yml`) e no `php.ini` de produção;
2. Scaffold Apache: `Header unset` **e** `Header always unset` para `X-Powered-By` / `X-Generator` (tabelas onsuccess + always).

## R4 — Fora de escopo X-Drupal-Cache*

Headers de cache do Drupal são operacionais (CDN/debug). Removê-los não foi pedido e prejudica diagnóstico; mantidos.

## R5 — Sem hook_update / config

Alteração 100% runtime + htaccess. Metatag module não define `generator` em `config/sync`.
