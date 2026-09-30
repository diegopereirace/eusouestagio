# Contract: Deploy — Perfil em Destaque (Estudante)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-30  
**Módulo**: `custom_configs` | **Hook**: `custom_configs_update_11038`

## Objetivo

Garantir que destino (staging/prod/outro local) receba paragraph/block types, fields, displays, seed (ilustração + 3 itens + CTA), assets placeholder, placement weight `2` e estilos **sem** passos manuais no painel.

## Pré-condições

1. `settings.php` ativo com `$settings['config_sync_directory'] = 'config/sync'`.
2. Código com YAMLs em `config/sync`, Twig/CSS, assets em `modules/custom/custom_configs/assets/perfil-destaque-estudante/`, hook `11038` e form alter / validação max 3.
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

## Garantias do hook `11038` (idempotente)

| Passo | Ação | Critério de sucesso |
|-------|------|---------------------|
| 1 | Ensure `item_lista_icone_p` + fields + displays | tipo e instances presentes |
| 2 | Ensure `perfil_destaque_estudante` + fields (lista → `item_lista_icone_p`, link, image) + displays | tipo e instances presentes |
| 3 | Copiar assets seed → `public://` se ausentes | arquivos disponíveis para File entities |
| 4 | Seed bloco UUID `c0d1e2f3-a4b5-4678-c901-2def01234567` + ilustração + 3 itens + CTA | 1 published; sem duplicata; só preenche vazio |
| 5 | Ensure placement `default_perfildestaqueestudante` região `content_full` weight `2` / pages `/para-estudantes` | bloco ativo só nessa rota, após jornada |

Reexecução: no-op; **não** sobrescrever editorial divergente. **Não** alterar storage `field_itens_lista` nem placements 021/022/023.

## Configs exportáveis (origem → `drush cex`)

- `paragraphs.paragraphs_type.item_lista_icone_p.yml`
- `block_content.type.perfil_destaque_estudante.yml`
- field instances + form/view displays do paragraph e do bloco
- `block.block.default_perfildestaqueestudante.yml`
- diffs de `user.role.*.yml` (permissões do bundle)

**Não** alterar (nesta feature): storages canônicos; placements hero/benefícios/jornada/PE/QS/home; View `banners` / `vagas`.

## Assets versionados (origem)

```text
modules/custom/custom_configs/assets/perfil-destaque-estudante/
  # ilustração da coluna esquerda + 3 ícones dos itens
```

Hook copia para `public://` no destino. **Sem** commit de `sites/default/files`.

## Gates pós-deploy

1. `drush updatedb:status` sem pendência `custom_configs` `11038`.
2. `/para-estudantes` HTTP 200; ordem visual hero → benefícios → jornada → perfil com título + ilustração + 3 itens + CTA.
3. `/`, `/quem-somos`, `/para-empresas`, `/contato`: sem o bloco `perfil_destaque_estudante`; amostragem visual intacta (hero/benefícios/jornada estudantes).
4. Clique no CTA → `/painel/estudante/perfil` (ou gate de login já existente).
5. Segunda `drush updb -y`: zero blocos/paragraphs/arquivos/placements duplicados.
6. Zero criação manual de tipos/placement no destino.
