# Contract: deploy 050 — Benefícios taxonomia + CTA candidato

## Origem (após implementação)

1. Versionar `modules/custom/custom_configs/assets/beneficios/beneficios-0{1..7}.webp`.
2. Garantir `custom_configs_update_11071` (+ helpers) em `custom_configs.install`.
3. `drush updb -y` — cria/garante vocab, fields, displays, seed 7 termos, migração paragraphs→termos, ajustes de CTA/access conforme hook.
4. `drush cex -y` — exporta vocab, storages, instances, displays, `block.block.default_ctav1vagas` (com `user_role: candidato`), form/view da vaga.
5. `drush cr`.
6. Commit: código + assets + `config/sync` + PRD cirúrgico + specs.

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
| 1º `cim` | Importa vocab/fields/displays/placement (UUIDs de termos ainda podem depender do updb) |
| `updb` (`11071`) | Ensure + seed termos/ícones + migração de vagas + coerência Entity API |
| 2º `cim` | Consolida placements/displays que dependam de estado pós-hook |
| `cr` | Twig/CSS/registry |

Invocação no servidor: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html` (ver `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`).

## Pré-requisitos

- `$settings['config_sync_directory'] = 'config/sync'` no `settings.php` ativo.
- Sete arquivos `.webp` presentes no módulo após `git pull`.
- Role `candidato` já existente (`user.role.candidato`).

## Idempotência

- Reexecução `updb`: termos existentes com ícone → no-op; `field_vaga_beneficios_t` já preenchido → não sobrescreve; managed files reusados por URI.
- Reexecução `cim` com sync idêntico → no-op.

## Falhas explícitas

- Asset WebP ausente/ilegível no momento do seed → mensagem `t()` clara; **não** criar termo sem ícone.
- Não há passo manual no admin de produção.

## Verificação mínima pós-deploy

1. Form vaga: sem `field_empresa_u`; com checkboxes de benefícios; sem editor paragraphs de benefício.
2. Vocab `beneficios`: 7 termos com ícone.
3. Detalhe público: grid Benefícios a partir dos termos selecionados.
4. CTA: visível só para `candidato`; clique = mesma candidatura da sidebar.
5. Reexecutar `updb` → zero duplicação de termos.
