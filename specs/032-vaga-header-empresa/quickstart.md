# Quickstart: Header da Vaga — Perfil Empresa — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-07

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com Twig/CSS/preprocess + `custom_configs_update_11049` + YAMLs (CT empresa, fields vaga) + asset seed commitados.
3. Ideal: predecessor `031` já aplicado (layout duas colunas).
4. **Nenhum** dump necessário.

Modelo: [data-model.md](data-model.md). Render: [contracts/vaga-header-empresa-render.md](contracts/vaga-header-empresa-render.md). Deploy: [contracts/deploy-vaga-header-empresa.md](contracts/deploy-vaga-header-empresa.md).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # empresa CT + fields vaga + tema + custom_configs + PRD
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

1. CT `empresa` + fields na vaga + seed EcoConstrutora / Engenheiro Civil.
2. Header completo na vaga seed; zero criação manual no admin; zero dump.

---

## B) Header Figma — visitante (US1 → SC-001, SC-003, SC-006)

1. Abrir canonical da vaga seed “Engenheiro Civil” (desktop).
2. Confirmar card branco arredondado com borda; logo 96×96 à esquerda; h1; nome EcoConstrutora + ícone verificado; pin + localização; quatro pills (regime, 30h semanais, bolsa, Postado há…).
3. Viewport ≤576px: sem overflow horizontal; título legível.
4. Comparar com frame Figma do header (≥90% checklist visual).

---

## C) Editor — Empresa + carga (US2 → SC-002)

1. Admin → criar/editar Empresa (nome + logo); salvar.
2. Editar uma vaga: escolher “Empresa da vaga”, preencher carga horária; garantir regime/bolsa/cidade/estado.
3. Detalhe público: pills preenchidas visíveis; esvaziar uma pill → só ela some.

---

## D) Tempo relativo (US3)

1. Vaga recente (&lt;1h): texto compreensível (“menos de 1 hora” ou equivalente).
2. Ajustar `created` em ambiente de teste para &gt;24h → “X dia(s)”.
3. Sem valores negativos.

---

## E) Deploy idempotente (US4 → SC-004, SC-005)

1. Rodar receita A em ambiente limpo (ou reexecutar `updb`).
2. Confirmar uma única EcoConstrutora / Engenheiro Civil seed.
3. Revisor abre a vaga seed e valida header em ≤5 minutos sem admin estrutural.

---

## F) Regressão

1. Seção “Sobre a Empresa” / sidebar / FAQ / CTA vagas: intactos.
2. `field_empresa_u` ainda no form da vaga.
3. `/vagas` listagem e cards laranja: sem mudança visual.
4. Vaga sem `field_vaga_empresa`: header sem logo/nome quebrado; título + pills restantes OK.
