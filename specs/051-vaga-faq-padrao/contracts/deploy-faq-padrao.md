# Contract: deploy 051 — FAQ padrão único nas vagas

## Origem (após implementação)

1. Garantir `custom_configs_update_11074` (+ helpers) em `custom_configs.install`.
2. Garantir `custom_configs_load_faq_padrao()` (ou equivalente) em `custom_configs.module`.
3. Tema: preprocess + Twig full usando FAQ padrão (não `field_vaga_faq`).
4. `drush updb -y` — ensure structure + seed coleção + hide form.
5. `drush cex -y` — exporta `core.entity_form_display.node.vagas.default.yml` (`field_vaga_faq` hidden).
6. `drush cr`.
7. Commit: código + `config/sync` + PRD cirúrgico + specs.

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
| 1º `cim` | Form display com `field_vaga_faq` hidden (se já no sync) |
| `updb` (`11074`) | Seed FAQ padrão se ausente; ensure hide via Entity API |
| 2º `cim` | Consolida displays pós-hook |
| `cr` | Twig/registry |

Invocação no servidor: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html` (ver `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`).

## Pré-requisitos

- `$settings['config_sync_directory'] = 'config/sync'` no `settings.php` ativo.
- Bundle `faq`, paragraph `faq_item_p` e storages já existentes (features 030/038) ou recriáveis pelos ensures do hook.

## Idempotência

- Reexecução `updb`: coleção existente → no-op (ou só preenche ausências); form já hidden → no-op; **0** duplicatas da coleção.
- Não sobrescreve perguntas/respostas editoriais já divergentes do seed.
- Não exige limpar `field_vaga_faq` nas vagas.

## Falhas explícitas

- Bundle/field FAQ ausente sem possibilidade de ensure → mensagem `t()` clara; não inventar storage paralelo.
- Sem passo manual no admin de produção.

## Verificação mínima pós-deploy

1. ≥2 vagas publicadas exibem o **mesmo** Accordion (perguntas/ordem).
2. Form de Vagas **sem** “FAQ da vaga”.
3. `drush updb -y` de novo → mensagem de no-op / sem duplicar FAQ.
