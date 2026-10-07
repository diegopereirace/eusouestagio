# Quickstart: Detalhe da Vaga — Refino — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-07

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com Twig/CSS/libraries + `custom_configs_update_11048` + YAMLs (faq coleção, requisitos text_long, CTA placement) commitados.
3. Ideal: ≥1 vaga publicada com empresa, descrição, requisitos, benefícios e FAQ coleção associada.
4. **Nenhum** dump necessário.

Modelo: [data-model.md](data-model.md). Render: [contracts/vagas-detalhe-refino-render.md](contracts/vagas-detalhe-refino-render.md). Deploy: [contracts/deploy-vagas-detalhe-refino.md](contracts/deploy-vagas-detalhe-refino.md).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # faq_item_p + field_faq_itens + requisitos text_long + CTA placement + tema + custom_configs + PRD
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

1. CT `faq` coleção + 1 seed / 2 itens; `field_vaga_faq` card. 1; requisitos text_long; CTA placement vagas.
2. Página full refinada; zero criação manual no admin; zero dump.

---

## B) Layout duas colunas sem seções excluídas (US1 → SC-001, SC-004)

1. Abrir canonical de uma vaga publicada (desktop ≥992px).
2. Confirmar grid duas colunas (principal ~8, sidebar ~4).
3. Mobile &lt;992px: empilhados, sem overflow-x.
4. Ausência total de Match / Processo de Contratação / “Por que combina…” / Seu Perfil / CTA inline `.vaga-detalhe__cta`.

---

## C) Header, sobre, requisitos, benefícios, empresa (US2 → SC-002)

1. Preencher campos + descrição; salvar.
2. Header: logo (se houver), h1, empresa, local, badges.
3. Sobre a Vaga com HTML formatado.
4. Requisitos com checks azuis (HTML lista, não bullets padrão).
5. Benefícios em grid 2/4 com ícone+texto.
6. Empresa: box azul claro; **sem** botão “Ver Empresa”.
7. Esvaziar um campo → seção some (sem heading órfão).

---

## D) FAQ coleção accordion (US3 → SC-003, SC-006, SC-008)

1. Associar a coleção seed (2 itens) à vaga; publicar.
2. Expandir/colapsar cada item (mouse + teclado); IDs únicos.
3. Campo FAQ vazio → seção omitida.
4. Admin → conteúdo FAQ: **1** coleção seed com 2 perguntas; `drush updb` de novo → sem duplicata.

---

## E) Sidebar ações + resumo (US4)

1. Ações: Candidatar-se largo; abaixo, Salvar | Compartilhar lado a lado (outline).
2. Compartilhar: botão abre popover com WhatsApp / Facebook / LinkedIn (URL canônica da vaga).
3. Resumo: Período / Bolsa Auxílio / Modelo / Vagas (“Não informado” se aplicável).
4. Anônimo e candidato: estados preservados (anônimo: login + Criar conta | Compartilhar).

---

## F) CTA final `cta_v1` (US5 → SC-005)

1. Final da página de vaga: CTA escuro `#023C62`, título “Pronto para o próximo passo?”, botão “Candidatar-se Agora”.
2. Home / quem-somos / para-estudantes: CTA de vagas **ausente**; CTAs claros (e CTA escuro PE estudantes) sem regressão.
3. `drush updb` 2ª vez → sem duplicar bloco/placement.

---

## G) Editor (US6 → SC-006)

1. Form da vaga: requisitos (texto longo), benefícios (paragraphs), FAQ (1 coleção).
2. Editar itens da coleção FAQ → accordion público reflete após salvar (+ cache se preciso).

---

## H) Header global (US7 → SC-010)

1. Home + detalhe de vaga: menu desktop legível.
2. Mobile: offcanvas/hamburger utilizável; sticky do Header ok.

---

## I) Isolamento listagem / cards (SC-009)

1. `/vagas` e cards laranja home/landing: checklist visual sem regressão.

---

## J) Idempotência (US8 → SC-007, SC-008)

```bash
docker compose exec drupal drush updb -y
docker compose exec drupal drush cr
```

Sem duplicar CT/fields/coleção FAQ/CTA/placement; editorial divergente preservado.
