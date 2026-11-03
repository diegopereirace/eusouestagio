# Contract: deploy 052 — Refino Figma `/para-empresas`

## Origem (após implementação)

1. Tema: banner (Twig/CSS/libraries), ND CSS, depoimentos CSS/(JS), CTA override + preprocess + CSS disabled.
2. `custom_configs_update_11076` (+ helper legado CTA PE) em `custom_configs.install`.
3. `drush updb -y` — aplica correção de legado se necessário.
4. `drush cex -y` — **somente se** houver diff estrutural (esperado: nenhum YAML novo; confirmar com `git status`).
5. `drush cr`.
6. Commit: tema + `custom_configs` + PRD cirúrgico + specs (+ `config/sync` se cex gerou diff).

## Destino

Receita normativa:

```text
git pull
drush cim -y
drush updb -y
drush cim -y
drush cr
```

| Passo | Efeito esperado |
|-------|-----------------|
| 1º `cim` | Qualquer config estrutural (se houver) |
| `updb` (`11076`) | CTA PE legado → “Abrir Vaga” + URI canônica (ou no-op) |
| 2º `cim` | Consolida pós-hook |
| `cr` | Twig/CSS/JS |

Invocação no servidor: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html` (ver `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`).

## Pré-requisitos

- `$settings['config_sync_directory'] = 'config/sync'` no `settings.php` ativo.
- Features `019`/`020`/`042` e migração `11028` já aplicadas (página `page` em `/para-empresas`).
- Bloco CTA UUID `a7b8c9d0-e1f2-4345-a678-90abcdef0123` existente (ou recriável por seeds anteriores).

## Idempotência

- Reexecução `updb`: título já “Abrir Vaga” com URI canônica → no-op; editorial divergente ≠ legado → **não** sobrescreve.
- Sem duplicar blocos/nodes.
- Sem passo manual no admin de produção.

## Falhas explícitas

- Bundle `cta_v1` ou bloco UUID ausente → mensagem `t()` clara; não inventar bundle.
- Não editar `core/` / `vendor/`.

## Verificação mínima pós-deploy

1. `/para-empresas`: banner com setas/transição; CTAs `<768px` larguras iguais.
2. ND: desc `#4E4E4E`; mobile sem overflow.
3. Depoimentos: clique em cada bullet navega e marca ativo (laranja).
4. Matriz 3 perfis no CTA final (anônimo / sem empresa / com empresa).
5. Segunda `drush updb -y` → no-op.
