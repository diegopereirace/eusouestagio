# Quickstart: CTA v1 (Quem Somos) — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-22

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com YAMLs + Twig/CSS/library + `custom_configs_update_11019` commitados.
3. **Nenhum** dump necessário para subir a estrutura a outro ambiente.

Modelo de dados: [data-model.md](data-model.md). Contrato visual: [contracts/cta-v1-render.md](contracts/cta-v1-render.md).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # config/sync + tema + custom_configs + PRD
# commit / push
```

Esse passo integra o **deploy do layout v2** de `/quem-somos` (hooks `11012`–`11018` + **`11019`**).

### Destino (staging / prod / outro local)

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cr
```

**Gates pós-deploy:**

1. `drush config:status` limpo (ou só diffs esperados de ambiente).
2. UI: `Estrutura → Tipos de bloco` lista **CTA v1**.
3. UI: bloco editável com título, subtítulo, dois links.
4. Placement em `content_full` weight 12, só `/quem-somos`.
5. Zero criação manual de fields no destino; zero dump.

---

## B) Visitante desktop (US1 → SC-001, SC-005)

1. Abrir `/quem-somos` em viewport ≥768px.
2. Conferir card abaixo dos números: fundo `#D3E4FE`, radius ~32px, padding ~64px, largura contida (~1200px).
3. Copy do seed (ou editorial já salvo); botões lado a lado (primário laranja `#FD7B1A`, secundário branco).
4. Gap textos↔botões ≈ 24px.
5. Conferir que Diferenciais e Impact in Numbers permanecem visualmente intactos.

---

## C) Visitante mobile (US2 → SC-002)

1. Viewport ≤575.98px: botões empilhados; sem scroll horizontal causado pelo bloco.
2. Gap textos↔botões ≈ 24px.

---

## D) Navegação dos botões (US3 → SC-003)

1. Clicar “Buscar vagas” → `/vagas`.
2. Clicar “Cadastrar gratuitamente” → `/cadastro/candidato`.

---

## E) Editor (US4 → SC-004)

1. Editar o bloco CTA v1: alterar título/subtítulo/um link; salvar.
2. Recarregar `/quem-somos` (após cache esperado).
3. Cronometrar &lt; 3 min.

---

## F) Idempotência e fallbacks (US5 → SC-006–SC-008)

1. Rodar `drush updb -y` de novo → sem duplicar bloco; editorial divergente preservado.
2. Esvaziar campos em ambiente de teste → elementos omitidos / card omitido se tudo vazio; página sem erro.
3. Página home e outras rotas → CTA v1 **não** aparece.

---

## G) Isolamento visual (SC-009)

Amostrar Impact in Numbers, Diferenciais Quem Somos, home (CTO) — sem regressão causada pelo CSS de `.block-cta-v1`.

---

## Checklist “deploy fácil”

| # | Critério | OK? |
|---|----------|-----|
| 1 | Estrutura só via `config/sync` + `cim` | |
| 2 | Seed + placement via `updb` (`11019`), idempotente | |
| 3 | Mesma receita em todos os ambientes | |
| 4 | Zero dump / zero SQL de schema | |
| 5 | Weight 12 após Impact Numbers (11) | |
| 6 | Features 009–014 e bloco CTO não alterados | |

---

## Próximo passo

Se a validação do plano estiver OK: `/speckit-tasks` → implementar na ordem do plan.
