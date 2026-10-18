# Contract: deploy da sidebar (campos + seed)

**Feature**: `033-vaga-sidebar-acoes`  
**Hooks**: `custom_configs_update_11050` (campos/seed) + `custom_configs_update_11051` (repair requisitos text_long + remap PA→PR legado)  
**Módulo**: `custom_configs`

## Origem

```bash
docker compose exec drupal drush updb -y
docker compose exec drupal drush cex -y
docker compose exec drupal drush cr
```

`drush cex` versiona storages, instances e displays. Não editar esses YAMLs à mão.

## Destino

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

`cim` antes de `updb` quando o sync já contém os fields. A segunda passada de `cim` reaplica displays caso o hook tenha gravado UUID local. Neste caso os fields nascem no hook **e** no sync exportado; a ordem acima cobre os dois.

## O que o hook garante

1. Storage + instance `field_vaga_periodo` e `field_vaga_quantidade` no bundle `vagas`.
2. Componentes no form display `node.vagas.default` e hidden no view display `node.vagas.default`.
3. Node empresa EcoConstrutora (UUID `f6a7b8c9-d0e1-4234-e567-89abcdef0123`) se ausente.
4. Node vaga Engenheiro Civil (UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234`) se ausente, vinculado à empresa.
5. Preenchimento dos campos vazios do resumo e alinhamento do trio legado 032, conforme [data-model.md](../data-model.md).

Reexecução = no-op quando campos e valores-alvo já existem. Não duplica por título. Não apaga `field_horarios` nem `field_empresa_u`.

## Fora do hook

- Rotas `custom_panel.vagas_candidatar` e `custom_panel.vagas_salvas_toggle` (já em código).
- Markup Twig/CSS/JS (deploy por git, cache rebuild).
- Match, Perfil, listagem, header.
