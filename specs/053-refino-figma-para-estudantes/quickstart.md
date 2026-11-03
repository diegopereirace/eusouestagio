# Quickstart: Refino Figma Para Estudantes — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-03

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir do container `/var/www/html`). Em produção: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

Modelo: [data-model.md](data-model.md). Contratos: [contracts/para-estudantes-refino-render.md](contracts/para-estudantes-refino-render.md), [contracts/deploy-para-estudantes-refino.md](contracts/deploy-para-estudantes-refino.md).

## Pré-requisitos

1. Stack local no ar; `$settings['config_sync_directory'] = 'config/sync'`.
2. Branch com tema + `custom_configs_update_11077` + `config/sync` do placement novo commitados.
3. `/para-estudantes`, `/para-empresas` e `/vagas` respondem 200 antes da validação visual final.

---

## A) Deploy (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush updb -y
docker compose exec drupal drush cex -y
docker compose exec drupal drush cr
git status
```

**Esperado no `git status`:** tema, `custom_configs.install`, PRD, specs, e em `config/sync` o YAML novo do Hero estudantes mais `status: false` em `block.block.default_perfildestaqueestudante.yml`.

### Destino

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cim -y
docker compose exec drupal drush cr
```

**Gates pós-deploy:**

1. `drush config:status` limpo (ou só diffs de ambiente).
2. `/para-estudantes` mostra banner, Hero, benefícios e CTA sem edição manual no Block Layout.
3. Segunda `drush updb -y` → no-op (perfil segue oculto, sem bloco duplicado).

---

## B) Banner vs Para Empresas (US1 → SC-001)

1. Abrir `/para-estudantes` e `/para-empresas` lado a lado no desktop. Se houver mais de um slide, avançar por setas e indicadores.
2. **Esperado**: transição, setas e indicadores com a mesma qualidade; arte real (sem placeholder genérico); CTAs legíveis.
3. Viewport &lt;768px: os dois CTAs de estudantes com largura igual (±2px).
4. `/para-empresas` **sem** regressão de carrossel ou CTAs.

---

## C) Hero Search (US2 → SC-002, FR-004)

1. Em `/para-estudantes`, logo abaixo do banner: título “Encontre a oportunidade ideal para sua carreira.”, subtítulo, três campos, “Buscar Vagas” e pills.
2. Preencher cargo/cidade/curso e submeter.
3. **Esperado**: navegação para `/vagas` com a query aplicada; a listagem respeita os filtros.
4. Repetir a busca a partir do Hero de `/vagas` e comparar campos, pills e botão (mesma estrutura).
5. Confirmar que `/vagas` não perdeu o Hero nem o autocomplete.

---

## D) Benefícios (US3 → SC-003)

1. Desktop ≥992px: quatro cards numa linha, ícone no topo à esquerda (~64px), título negrito, descrição cinza.
2. Tablet (~768–991px): duas colunas. Mobile estreito: uma coluna, sem scroll horizontal da seção.
3. Ordem: Vagas alinhadas, Modelos de trabalho, Vários segmentos, Filtros inteligentes.
4. Título e subtítulo da seção batem com o Figma, salvo copy editorial que o hook deve preservar (ver data-model).

---

## E) Perfil em destaque (US4 → SC-004)

1. Abrir `/para-estudantes` e buscar o texto “Seu perfil em destaque”.
2. **Esperado**: seção ausente.
3. `drush cget block.block.default_perfildestaqueestudante status` → `false`.
4. `drush updb -y` de novo não reativa o placement.

---

## F) CTA e footer (US5 → SC-005)

1. Rolar até “Pronto para dar o próximo passo?” no desktop e no mobile.
2. Inspecionar a junção com `.site-footer-custom`.
3. **Esperado**: 0px entre o fim do gradiente do CTA e o início do fundo do footer. Sem faixa branca. O padding interno do rodapé não conta como vão.
4. Abrir `/quem-somos` e `/para-empresas`: CTAs com a aparência anterior.

---

## G) Regressão rápida

- Jornada do estudante e os 3 cards de vagas (`block_3` + “Ver todas as vagas”) continuam na landing, entre benefícios e CTA.
- Home sem mudança de carrossel atribuível a esta feature.
