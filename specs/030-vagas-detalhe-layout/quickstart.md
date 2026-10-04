# Quickstart: Detalhe da Vaga — Layout Duas Colunas — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-04

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com Twig/CSS/library + `custom_configs_update_11047` + YAMLs (faq, paragraph, fields, displays) commitados.
3. Ideal: ≥1 vaga publicada com empresa, descrição, e (após campos) requisitos/benefícios/etapas/FAQ preenchidos.
4. **Nenhum** dump necessário.

Modelo: [data-model.md](data-model.md). Render: [contracts/vagas-detalhe-render.md](contracts/vagas-detalhe-render.md). Deploy: [contracts/deploy-vagas-detalhe.md](contracts/deploy-vagas-detalhe.md).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # faq + beneficio_vaga_p + field_vaga_* + displays + tema + custom_configs + PRD
```

### Destino

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cim -y
docker compose exec drupal drush cr
```

**Gates pós-deploy:**

1. CT `faq` + 2 seeds; paragraph `beneficio_vaga_p`; 4 fields no form da vaga.
2. Página full com layout duas colunas; zero criação manual no admin; zero dump.

---

## B) Layout duas colunas (US1 → SC-001, SC-005)

1. Abrir canonical de uma vaga publicada (desktop ≥992px).
2. Confirmar `.container.py-5` + `.row.g-4` + col-8 / col-4; sidebar à direita.
3. Mobile &lt;992px: empilhados, sem overflow-x.
4. Ausência total de “Seu Match…” / “Por que combina…”.

---

## C) Header, sobre, requisitos, benefícios (US2 → SC-002)

1. Preencher campos novos + descrição; salvar.
2. Header: logo (se houver), h1, empresa, local, badges (regime, carga, bolsa, postado há…).
3. Sobre a Vaga com HTML formatado.
4. Requisitos com checks azuis; benefícios em grid 2/4 com ícone+título.
5. Esvaziar um campo → seção correspondente some (sem heading órfão).

---

## D) Stepper (US3 → SC-004)

1. Cadastrar ≥2 etapas em `field_vaga_etapas_processo`.
2. Verificar círculos 1…N e **último** com `#FD7B1A`.
3. Campo vazio → seção omitida.

---

## E) FAQ accordion (US4 → SC-003, SC-008)

1. Associar os 2 FAQs seed à vaga; publicar.
2. Expandir/colapsar cada item (mouse + teclado); IDs únicos.
3. Campo FAQ vazio → seção omitida.
4. `drush updb` de novo → seeds não duplicam.

---

## F) Empresa, CTA, sidebar (US5)

1. Card empresa: logo/nome/texto truncado; **sem** botão “Ver Empresa”.
2. CTA final `#023C62` + “Candidatar-se Agora” no fim da coluna principal.
3. Sidebar: Candidatar-se / Salvar / Compartilhar (fluxos atuais); Resumo (Período/Bolsa/Modelo/Vagas); Seu Perfil (barra + Completar agora).
4. Anônimo: login/cadastro sem quebrar layout.

---

## G) Editorial + isolamento + deploy (US6–US7 → SC-006–SC-009)

1. Form da vaga edita FAQ, etapas, requisitos, benefícios (paragraph).
2. `/vagas` listagem e cards laranja Home/PE/similares intactos.
3. Segunda `updb` idempotente.
4. Vaga só com descrição legada: página utilizável; seções novas omitidas (após migração, requisitos/benefícios legados podem ter sido copiados se novos estavam vazios).

---

## Checklist “deploy fácil”

| # | Critério | OK? |
|---|----------|-----|
| 1 | Estrutura via `config/sync` + `cim` | |
| 2 | CT/fields/seeds/paragraph via `updb` (`11047`), idempotente | |
| 3 | 2ª `cim` após `updb` | |
| 4 | Zero dump / zero SQL de schema / zero admin prod | |
| 5 | Full Figma 8/4 sem Match; accordion + stepper OK | |
| 6 | Listagem `/vagas` + cards laranja intactos | |

---

## Próximo passo

Se a validação do plano estiver OK: `/speckit-tasks` → implementar na ordem do plan.
