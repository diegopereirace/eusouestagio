# Contract: Deploy — Jornada do Estudante

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-30  
**Módulo**: `custom_configs` | **Hook**: `custom_configs_update_11037`

## Objetivo

Garantir que destino (staging/prod/outro local) receba paragraph/block types, fields, displays, seed (4 passos), placement weight `1` e estilos **sem** passos manuais no painel.

## Pré-condições

1. `settings.php` ativo com `$settings['config_sync_directory'] = 'config/sync'`.
2. Código com YAMLs em `config/sync`, Twig/CSS, hook `11037` e (se aplicável) form alter / validação max 4.
3. Invocação Drush: `docker compose exec drupal drush <cmd>` (local) ou `php vendor/bin/drush.php <cmd>` (VPS).

## Receita destino (padrão)

```text
git pull
drush cim -y
drush updb -y
drush cim -y
drush cr
```

Ordem: **`cim` antes de `updb`**; **2ª `cim`** após `updb` para placements que dependam de UUIDs seedados.

**Não** é o caso especial “updb antes do cim” (esse é só remoção de bundle com conteúdo legado, ex. `11028`).

## Garantias do hook `11037` (idempotente)

| Passo | Ação | Critério de sucesso |
|-------|------|---------------------|
| 1 | Ensure `passo_jornada_p` + fields + displays | tipo e instances presentes |
| 2 | Ensure `jornada_estudante` + fields (lista → `passo_jornada_p`) + displays | tipo e instances presentes |
| 3 | Seed bloco UUID `b9c0d1e2-f3a4-4567-b890-1cdef0123456` + 4 passos | 1 published; sem duplicata; só preenche vazio |
| 4 | Ensure placement `default_jornadaestudante` região `content_full` weight `1` / pages `/para-estudantes` | bloco ativo só nessa rota, após benefícios |

Reexecução: no-op; **não** sobrescrever editorial divergente. **Não** alterar storage `field_itens_lista` nem placements 021/022.

## Configs exportáveis (origem → `drush cex`)

- `paragraphs.paragraphs_type.passo_jornada_p.yml`
- `block_content.type.jornada_estudante.yml`
- field instances + form/view displays do paragraph e do bloco
- `block.block.default_jornadaestudante.yml`
- diffs de `user.role.*.yml` (permissões do bundle)

**Não** alterar (nesta feature): storages canônicos; placements hero/benefícios/PE/QS/home; View `banners` / `vagas`.

## Gates pós-deploy

1. `drush updatedb:status` sem pendência `custom_configs` `11037`.
2. `/para-estudantes` HTTP 200; ordem visual hero → benefícios → jornada com título + 4 passos (badges 1–4; último laranja).
3. `/`, `/quem-somos`, `/para-empresas`, `/contato`: sem o bloco `jornada_estudante`; amostragem visual intacta (hero/benefícios estudantes).
4. Segunda `drush updb -y`: zero blocos/paragraphs/placements duplicados.
5. Zero criação manual de tipos/placement no destino.
