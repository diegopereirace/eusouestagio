# Quickstart: Página de Contato — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-23

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com YAMLs + Twig/CSS/library + asset `img-contato.png` + `custom_configs_update_11021` commitados.
3. **Nenhum** dump necessário para subir a estrutura a outro ambiente.

Modelo de dados: [data-model.md](data-model.md). Contrato visual: [contracts/layout-contato-render.md](contracts/layout-contato-render.md).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # config/sync + tema + custom_configs + PRD + asset
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
2. UI: webform Contato com os 6 campos + submit “Enviar Mensagem”.
3. UI: tipo de bloco **Layout de Contato** com webform + imagem.
4. Placement em `content_full`, só `/contato`.
5. Alias `/contato` responde HTTP 200 com o layout.
6. Zero criação manual de fields no destino; zero dump.

---

## B) Visitante desktop (US1 → SC-001)

1. Abrir `/contato` em viewport ≥992px.
2. Duas colunas: esquerda (H2 “Envie sua mensagem” + form + atalhos); direita (painel `#E5EEFF` + imagem).
3. Comparar com Figma (≥95% checklist visual).

---

## C) Envio do formulário (US2 → SC-003, SC-004)

1. Conferir placeholders e pares lado a lado (Nome|E-mail, Telefone|Categoria).
2. Enviar com dados válidos → “Mensagem enviada!” / sucesso + submission registrada.
3. E-mail inválido ou obrigatório vazio → bloqueio com erro; demais campos válidos preservados.

---

## D) Atalhos (US3 → SC-005)

1. E-mail → `mailto:contato@eusouestagio.com`.
2. WhatsApp → `https://wa.me/5561999999999`.

---

## E) Mobile (US4 → SC-002)

1. Viewport ≤575.98px: form acima da imagem; sem scroll horizontal do bloco.
2. Inputs/select usáveis; botão “Enviar Mensagem” clicável.

---

## F) Editor (US5 → SC-006)

1. Editar bloco Layout de Contato: trocar imagem e/ou webform; salvar.
2. Recarregar `/contato` (após cache esperado) em &lt; 3 min.

---

## G) Idempotência e regressão (US6 → SC-007–SC-009)

1. Rodar `drush updb -y` de novo → sem bloco/página duplicados; editorial divergente intacto.
2. Amostrar home, `/quem-somos` e rodapé → sem regressão visual por CSS desta feature.
3. Confirmar ausência de banner legado em `/contato`.

---

## H) Checklist rápido de aceite

| # | Critério | OK? |
|---|----------|-----|
| 1 | `/contato` duas colunas desktop | |
| 2 | Placeholders + flexbox linhas 1–2 | |
| 3 | Submit laranja “Enviar Mensagem” | |
| 4 | Envio válido / inválido | |
| 5 | mailto + wa.me | |
| 6 | Mobile empilhado sem overflow-x | |
| 7 | Editor altera imagem | |
| 8 | Deploy `cim`→`updb`→`cim`→`cr` sem admin manual | |
| 9 | 2ª `updb` idempotente | |
| 10 | Sem regressão home / Quem Somos / footer | |
