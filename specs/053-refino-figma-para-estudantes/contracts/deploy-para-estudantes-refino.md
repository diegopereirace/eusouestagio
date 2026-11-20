# Contract: deploy 053 — Refino Figma `/para-estudantes`

## Origem (após implementação)

1. Tema: banner (Twig/CSS/libraries), `page.html.twig` (ordem do `highlighted`), benefícios (Twig do paragraph + CSS), CSS do vão CTA.
2. `custom_configs_update_11077` em `custom_configs.install`:
   - placement `default_perfildestaqueestudante` → `status: false`;
   - placement `default_custom_banners_vagas_hero_search_estudantes` garantido;
   - benefícios UUID `a8b9c0d1-e2f3-4456-a789-0bcdef123456` fill-if-empty + legado conhecido (subtítulo seed `11035`, título `Filtros Inteligentes`) + ícones ausentes;
   - reativar `default_beneficiosestudantes` se estiver desabilitado.
3. `drush updb -y`.
4. `drush cex -y` — esperado:
   - `config/sync/block.block.default_custom_banners_vagas_hero_search_estudantes.yml` (novo);
   - `config/sync/block.block.default_perfildestaqueestudante.yml` com `status: false`.
5. `drush cr`.
6. Commit: tema + `custom_configs` + `config/sync` + PRD cirúrgico + specs.

Não editar YAML de `config/sync` à mão para inventar o placement. O hook cria/ajusta; o `cex` versiona.

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
| 1º `cim` | Importa placement do Hero e `status: false` do perfil |
| `updb` (`11077`) | Garante os mesmos estados + copy/ícones legado; no-op se já aplicados |
| 2º `cim` | Consolida pós-hook (placements que dependem do que o update gravou) |
| `cr` | Twig/CSS/JS do banner, benefícios e vão do CTA |

Invocação no servidor: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html` (ver `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`).

Ambiente local: `docker compose exec drupal drush <cmd>`.

## Pré-requisitos

- `$settings['config_sync_directory'] = 'config/sync'` no `settings.php` ativo.
- Features `021`–`027` já aplicadas (banner, benefícios, jornada, perfil, CTA, Hero de `/vagas`, shell `page` em `/para-estudantes`).
- Plugin `custom_banners_vagas_hero_search` presente (módulo `custom_banners` habilitado) **antes** do `cim` que cria o placement novo.
- Assets `modules/custom/custom_configs/assets/beneficios-estudantes/icon-1.png` … `icon-4.png` no deploy (não commitar `sites/default/files`).

## Idempotência

| Reexecução | Resultado |
|------------|-----------|
| 2º `updb` | Perfil continua `false`; Hero não duplica; subtítulo/título já canônicos não mudam; copy editorial divergente não é sobrescrita; ícones já preenchidos permanecem |
| 2º `cim` | Sem diff funcional nos dois YAML desta feature |

## Fora

- Passo manual no Block Layout de produção.
- Delete do `block_content` de perfil em destaque.
- Alteração do placement `default_custom_banners_vagas_hero_search` (`/vagas`).
- `core/` ou `vendor/`.

## PRD

Atualização cirúrgica em `PRD.md` §3.6: composição de `/para-estudantes` passa a ser banner → Hero Search (mesmo plugin de `/vagas`) → benefícios → jornada → destaque `block_3` → CTA; perfil em destaque **fora** da superfície pública (placement desabilitado). Citar hook `11077`.
