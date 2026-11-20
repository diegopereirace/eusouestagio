# Contract: Deploy — Benefícios Para Estudantes

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-29  
**Módulo**: `custom_configs` | **Hook**: `custom_configs_update_11035`

## Objetivo

Garantir que destino (staging/prod/outro local) receba paragraph/block types, fields, displays, seed (4 cards + ícones), placement e estilos **sem** passos manuais no painel.

## Pré-condições

1. `settings.php` ativo com `$settings['config_sync_directory'] = 'config/sync'`.
2. Código com YAMLs em `config/sync`, Twig/CSS, hook `11035` e assets em `modules/custom/custom_configs/assets/beneficios-estudantes/`.
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

## Garantias do hook `11035` (idempotente)

| Passo | Ação | Critério de sucesso |
|-------|------|---------------------|
| 1 | Ensure `card_icon_text_p` + fields + displays | tipo e instances presentes |
| 2 | Ensure `beneficios_estudantes` + fields (lista → `card_icon_text_p`) + displays | tipo e instances presentes |
| 3 | Seed bloco UUID `a8b9c0d1-e2f3-4456-a789-0bcdef123456` + 4 cards + assets | 1 published; sem duplicata; só preenche vazio |
| 4 | Ensure placement `default_beneficiosestudantes` região `content_full` / pages `/para-estudantes` | bloco ativo só nessa rota |

Reexecução: no-op; **não** sobrescrever editorial divergente.

## Configs exportáveis (origem → `drush cex`)

- `paragraphs.paragraphs_type.card_icon_text_p.yml`
- `block_content.type.beneficios_estudantes.yml`
- field instances + form/view displays do paragraph e do bloco
- `block.block.default_beneficiosestudantes.yml`
- diffs de `user.role.*.yml` (permissões do bundle)

**Não** alterar (nesta feature): storages canônicos; placements PE/QS/home; View `banners` / `vagas`.

## Gates pós-deploy

1. `drush updatedb:status` sem pendência `custom_configs` `11035`.
2. `/para-estudantes` HTTP 200; seção benefícios abaixo do hero com título/subtítulo + 4 cards.
3. `/`, `/quem-somos`, `/para-empresas`, `/contato`: sem o bloco `beneficios_estudantes`; amostragem visual intacta (incl. PE “Benefícios para Empresas”).
4. Segunda `drush updb -y`: zero blocos/paragraphs/placements duplicados.
5. Zero criação manual de tipos/placement no destino.
