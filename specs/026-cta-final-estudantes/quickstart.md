# Quickstart: CTA Final (Para Estudantes) — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-30

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com Twig/CSS/library + `custom_configs_update_11043` + YAML do placement commitados.
3. Features `021`–`025` já aplicadas (composição PE até `block_3`).
4. **Nenhum** dump necessário.

Modelo: [data-model.md](data-model.md). Render: [contracts/cta-final-estudantes-render.md](contracts/cta-final-estudantes-render.md). Deploy: [contracts/deploy-cta-final-estudantes.md](contracts/deploy-cta-final-estudantes.md).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # placement + tema + custom_configs + PRD
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

1. Placement `default_ctav1paraestudantes` em `content_full` weight 4, só `/para-estudantes`.
2. Bloco editável (título, corpo, dois links) — tipo `CTA v1` já existente.
3. Zero criação manual de fields; zero dump.

---

## B) Visitante desktop (US1 → SC-001, SC-006)

1. Abrir `/para-estudantes` em viewport ≥768px; rolar até o fim do conteúdo.
2. Conferir faixa escura full-bleed **antes** do footer, após os 3 cards de vagas.
3. Gradiente `#023C62`→`#011A2B`; padding ≈64/40; textos brancos centralizados.
4. Botões lado a lado: primário laranja; secundário outline branco.
5. Copy do seed (ou editorial já salvo).

---

## C) Visitante mobile (US2 → SC-002)

1. Viewport ≤575.98px: botões empilhados; sem scroll horizontal causado pelo bloco.

---

## D) Navegação dos botões (US3 → SC-003)

1. “Cadastre-se Gratuitamente” → `/cadastro/candidato`.
2. “Explorar Vagas” → `/vagas`.

---

## E) Isolamento (US4 → SC-004)

1. `/quem-somos`: CTA permanece card claro (`#D3E4FE`) — sem gradiente escuro.
2. `/para-empresas`: CTA mantém visual anterior — sem outline branco desta feature.
3. Home / outras rotas: placement ausente.

---

## F) Editor (US5 → SC-005)

1. Editar só a instância “CTA v1 Para Estudantes”; salvar.
2. Recarregar `/para-estudantes` (após cache); cronometrar &lt; 3 min.
3. Confirmar que instâncias QS/PE empresas não mudaram.

---

## G) Idempotência e fallbacks (US6 → SC-007–SC-009)

1. `drush updb -y` de novo → sem duplicar; editorial divergente preservado.
2. Esvaziar campos em ambiente de teste → elementos omitidos / faixa omitida se tudo vazio.
3. Ordem content_full: w0–w3 intactos; CTA em w4.

---

## Checklist “deploy fácil”

| # | Critério | OK? |
|---|----------|-----|
| 1 | Estrutura via `config/sync` + `cim` | |
| 2 | Seed + placement via `updb` (`11043`), idempotente | |
| 3 | 2ª `cim` após `updb` | |
| 4 | Zero dump / zero SQL de schema / zero admin prod | |
| 5 | Weight 4 após vagas `block_3` (3) | |
| 6 | CSS/Twig global `cta_v1` e features 021–025 intactos | |

---

## Próximo passo

Se a validação do plano estiver OK: `/speckit-tasks` → implementar na ordem do plan.
