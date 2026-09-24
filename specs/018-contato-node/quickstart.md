# Quickstart: Contato como Node — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-24

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com YAMLs do Content Type + storages/instances/displays + Twig/CSS + `custom_configs_update_11023` commitados.
3. Asset `modules/custom/custom_configs/assets/contato/img-contato.png` presente (já versionado na 017).
4. **Nenhum** dump necessário para subir a estrutura a outro ambiente.

Modelo de dados: [data-model.md](data-model.md). Contrato visual: [contracts/contato-node-render.md](contracts/contato-node-render.md).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # config/sync + tema + custom_configs + PRD
# commit / push
```

### Destino (staging / prod / outro local)

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cim -y
docker compose exec drupal drush cr
```

**Gates pós-deploy:**

1. `drush config:status` limpo (ou só diffs esperados de ambiente).
2. UI: Content Type **Contato** com webform, imagem, e-mail, WhatsApp, textos.
3. Alias `/contato` → Node Contato (HTTP 200).
4. Em `/contato`: **um** formulário; zero bloco `layout_contato` / ID 16.
5. Zero criação manual de fields no destino; zero dump.

---

## B) Visitante desktop (US1 → SC-001, SC-008)

1. Abrir `/contato` em viewport ≥992px.
2. Duas colunas: esquerda (H2 “Envie sua mensagem” + form + atalhos); direita (painel `#E5EEFF` + imagem + textos).
3. Inspecionar DOM: classes `.node--contato` / `.layout-contato-node`; ausência de `.block-layout-contato`.
4. Comparar com Figma (≥95% checklist visual).

---

## C) Envio do formulário (US2 → SC-003)

1. Formulário com campos da 017 (Nome, E-mail, Telefone, Categoria, Assunto, Mensagem).
2. Enviar com dados válidos → confirmação de sucesso + submission.
3. E-mail inválido / obrigatório vazio → bloqueio com feedback.

---

## D) Atalhos gerenciáveis (US3 → SC-004)

1. Seed: E-mail → `mailto:contato@eusouestagio.com`; WhatsApp → `https://wa.me/5561999999999`.
2. Editar Node: alterar e-mail/telefone; recarregar `/contato` → links atualizados.
3. Esvaziar um field → caixa correspondente omite.

---

## E) Mobile (US4 → SC-002)

1. Viewport ≤575.98px: form acima da imagem; sem scroll horizontal do layout.
2. Inputs e atalhos usáveis.

---

## F) Editor (US5 → SC-005)

1. Editar Node Contato: imagem, e-mail, WhatsApp, “Time de especialistas” / texto 24h.
2. Salvar e recarregar `/contato` (respeitar cache) — refletido em &lt;3 min.

---

## G) Idempotência e limpeza (US6 → SC-006, SC-007)

1. Confirmar bloco ID 16 ausente (`drush ev` / admin Content blocks).
2. Rodar `drush updb -y` de novo → sem Node Contato duplicado; editorial divergente preservado.
3. Confirmar placement `default_layoutcontato` inativo/ausente.

---

## H) Regressão visual (SC-009)

Amostrar home, `/quem-somos` e rodapé — sem alteração visual causada pelo CSS do Contato.

---

## Checklist rápido

| # | Gate | OK? |
|---|------|-----|
| 1 | cim → updb → cim → cr | |
| 2 | `/contato` = Node Contato | |
| 3 | Um único form | |
| 4 | ID 16 ausente | |
| 5 | mailto + wa.me do Node | |
| 6 | Mobile sem overflow | |
| 7 | 2ª updb idempotente | |
| 8 | Home / Quem Somos / rodapé OK | |
