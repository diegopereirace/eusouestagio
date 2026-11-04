# Contract: `system.performance` (preprocess CSS/JS)

**Feature**: [../spec.md](../spec.md) | **Data**: 2026-11-04

## Estado alvo

```yaml
css:
  preprocess: true
js:
  preprocess: true
```

Demais chaves (`compress`, `cache.page.max_age`, `fast_404`, …) **fora** do mandato desta feature, salvo preservação do valor atual.

## Garantia automatizada

| Mecanismo | Responsabilidade |
|-----------|------------------|
| `custom_configs_update_11090` | Idempotente: se false → true + save; se já true → no-op com mensagem |
| `config/sync/system.performance.yml` | Espelho exportado via `drush cex` na origem |

## Contrato do hook

- Entrada: ambiente Drupal com `custom_configs` habilitado.
- Saída: `css.preprocess === TRUE` e `js.preprocess === TRUE`.
- Reexecução: sem Exception; mensagem de status.
- Proibido: alterar `cache.page.max_age` ou outras chaves “de passagem”.

## Observabilidade pós-deploy

Visitante anônimo na home (agregação ativa, cache limpo):

- HTML referencia folhas/scripts **agregados** do Drupal para assets elegíveis (URLs combinadas), vs. lista fragmentada com preprocess false.
- Smoke visual das seções principais sem regressão óbvia.

## Override local

Desenvolvedor **pode** forçar preprocess false em `settings.local.php` / config override local **desde que** produção/staging recebam TRUE via sync + hook. O contrato de destino é preprocess true.

## Proibido

- Ativar agregação só no admin de produção (sem hook/cex).
- Editar YAML de sync “inventando” chaves novas sem passar por API/admin + cex.
