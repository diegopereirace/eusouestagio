# Quickstart: Refino Figma Para Empresas — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-03

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`). Em produção: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

Modelo: [data-model.md](data-model.md). Contratos: [contracts/para-empresas-refino-render.md](contracts/para-empresas-refino-render.md), [contracts/deploy-para-empresas-refino.md](contracts/deploy-para-empresas-refino.md).

## Pré-requisitos

1. Stack local no ar; `$settings['config_sync_directory'] = 'config/sync'`.
2. Branch com tema + `custom_configs_update_11076` (+ PRD) commitados.
3. Features `019`/`020`/`042` + shell `page` (`11028`) já aplicadas; `/para-empresas` responde 200.

---

## A) Deploy (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush updb -y
docker compose exec drupal drush cex -y
docker compose exec drupal drush cr
git status   # tema + custom_configs + PRD (+ config/sync se houver diff)
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

1. `drush config:status` limpo (ou só diffs de ambiente).
2. `/para-empresas` reflete banner/ND/depoimentos/CTA sem edição manual no admin.
3. Segunda `drush updb -y` → no-op no CTA.

---

## B) Banner vs home (US1 → SC-001, SC-008)

1. Abrir `/` e `/para-empresas` lado a lado; avançar slides por setas/indicadores.
2. **Esperado**: transição e setas com qualidade equivalente; PE com CTAs overlay no desktop.
3. Network: artes reais (sem `500×600` placeholder).
4. Home **sem** regressão visual atribuível ao reuso do JS.

---

## C) CTAs mobile do banner (US2 → SC-002)

1. Viewport 402×872 e ~767px em `/para-empresas`.
2. Medir largura dos dois botões: iguais (±2px); gap uniforme; lado a lado ou empilhados.
3. Viewport ≥768px: overlay/desktop íntegro (não forçar stack mobile).

---

## D) Nossos Diferenciais (US3 → SC-003, SC-004)

1. Desktop: topo imagem ↔ texto (±4px); anel verde atrás; dot navy na frente.
2. Computed color de subtítulo e `.nd-item__desc` = `#4E4E4E`.
3. Mobile `<768px`: sem decos absolutos complexos; sem scroll horizontal da seção.

---

## E) Depoimentos (US4 → SC-005)

1. Com ≥4 depoimentos, clicar cada bullet em sequência.
2. **Esperado**: card central = índice clicado; bullet com `is-active` + cor laranja distinta.
3. Swipe: bullet atualiza em sincronia.

---

## F) CTA por role (US5 → SC-006)

| Sessão | Botão 1 | Botão 2 |
|--------|---------|---------|
| Anônimo | Link “Cadastrar Empresa” → `/cadastro/empresa` | Ausente no DOM |
| Auth sem `empresa` | Link cadastro | Ausente no DOM |
| Auth com `empresa` | Desabilitado “Empresa Cadastrada” (fosco) | “Abrir Vaga” → `/painel/empresa/vagas/nova` |

Inspecionar HTML: sem `display:none` escondendo o botão 2 para anônimo.

---

## G) Deploy + idempotência (US6 → SC-007)

1. Ambiente “atrasado”: receita da seção A → página completa sem admin.
2. Se CTA ainda tinha “Falar com Especialista”, após `updb` vira “Abrir Vaga”.
3. Editor altera copy do CTA para texto custom ≠ legado → `updb` de novo → texto preservado.
4. `updb` duplo → no-op.

---

## H) Regressão (SC-008)

Metodologia, Benefícios, O Que Fazemos, header/footer e home: smoke visual rápido sem quebra óbvia.
