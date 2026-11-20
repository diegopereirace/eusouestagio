# Contract: deploy 038 — FAQ Accordion (vínculo seed + tema)

## Origem

Comandos via `docker compose exec drupal drush <cmd>` (ou equivalente no container ativo).

1. Implementar `custom_configs_update_11059` (ensures FAQ existentes + seed coleção + link Engenheiro Civil se vazio), include Twig e CSS.
2. `drush updb -y` — roda `11059`.
3. `drush cex -y` — esperado **no-op estrutural** se nada mudou em fields/displays; versionar só se o diff mostrar alteração real. **Não** editar YAML à mão; **não** inventar View `faq_vaga`.
4. `drush cr`.
5. Atualizar o `PRD.md` de forma cirúrgica no parágrafo do detalhe full (FAQ em cards Accordion + menção ao vínculo seed / hook `11059`), sem inventar View.

Não há asset de imagem novo.

## Destino

`cim` → `updb` → `cim` → `cr` (receita normativa; runbook em `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`).

- 1º `cim`: importa configs já versionadas (FAQ 031 etc.); tipicamente sem YAML novo desta feature.
- `updb`: `11059` — ensures no-op; seed coleção no-op se presente; vínculo Engenheiro Civil só se `field_vaga_faq` vazio.
- 2º `cim`: no-op esperado.
- `cr`: Twig/CSS do tema.

Invocação no servidor: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

## Pré-requisito

- `settings.php` ativo com `$settings['config_sync_directory'] = 'config/sync'`.
- Features 030/031 (estrutura FAQ) e vaga seed Engenheiro Civil / coleção UUID canônicos. Se a vaga ou a coleção não existirem, o helper de vínculo reporta ausência e não inventa vaga nova.

## Idempotência

- Ensures de CT/paragraph/fields → não recriam se presentes.
- Coleção seed → não duplica node nem perguntas já existentes.
- Engenheiro Civil com `field_vaga_faq` preenchido → não sobrescreve.
- Segunda passagem de `updb` → “No pending updates.”
- Reexecução do helper de vínculo isolado (se chamado de novo em update futuro) → no-op se já vinculado.

## O que o hook não faz

- Não cria View `faq_vaga` nem block placement.
- Não altera cardinalidade de `field_vaga_faq`.
- Não sobrescreve itens/respostas editoriais da coleção seed.
- Não vincula outras vagas em massa.
