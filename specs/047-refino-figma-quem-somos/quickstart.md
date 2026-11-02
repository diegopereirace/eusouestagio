# Quickstart: Refino Figma Quem Somos — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-02

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`). Em produção: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

Modelo: [data-model.md](data-model.md). Contrato: [contracts/quem-somos-refino-render.md](contracts/quem-somos-refino-render.md).

## Pré-requisitos

1. Stack local no ar; `$settings['config_sync_directory'] = 'config/sync'`.
2. Branch com Image Styles + displays + Twig/CSS + `custom_configs_update_11069` (+ assets ícones) commitados.
3. Features `009`–`015` já aplicadas no ambiente (página `/quem-somos` funcional).

---

## A) Deploy (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # config/sync (image.style.* + displays) + tema + custom_configs + PRD
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
2. Existem `image.style.quem_somos_sobre_nos`, `quem_somos_missao_visao`, `diferencial_simples_icon`.
3. `/quem-somos` reflete ícones, parallax, outline CTA sem edição manual no admin.
4. Segunda `drush updb -y` → no-op (sem duplicar arquivos/ícones).

---

## B) Banner mobile / desktop (US1 → SC-001)

1. DevTools → Network → Img; viewport `<768px`; reload `/quem-somos`.
2. **Esperado**: coluna mídia oculta; **zero** request de arte do banner (apenas data-URI se aparecer).
3. Viewport `≥768px`: imagem desktop visível, `loading="eager"`, `fetchpriority="high"`.
4. Copy e botões idênticos em ambos.

---

## C) Sobre nós (US2 → SC-002)

1. Inspecionar `src` da foto → `/files/styles/quem_somos_sobre_nos/...`.
2. `loading="lazy"`; anel verde + círculo laranja presentes.
3. Gap texto↔imagem ≈ Figma (±4px); sem scroll horizontal.

---

## D) Missão / Visão parallax (US3 → SC-003)

1. Desktop: computed `background-attachment: fixed`; fundo não acompanha o scroll do conteúdo.
2. URL do fundo → `/files/styles/quem_somos_missao_visao/...`.
3. Mobile: `background-attachment: scroll`; scroll da página sem travar.
4. Overlay + texto branco + `min-height` ≥ 380px.

---

## E) Diferenciais (US4 → SC-004)

1. 8 ícones finais (não provisórios, se assets entregues).
2. Caixa 80×80 desktop / 64×64 mobile; URL com `diferencial_simples_icon`; `lazy`.
3. Grid 2×4; títulos/subtextos centralizados.

---

## F) Impact in Numbers (SC-005)

Regressão visual: fundo `#0F172A`, número `#FD7B1A`, padding/max-width inalterados.

---

## G) CTA outline (US5 → SC-006)

1. Botão “Cadastrar gratuitamente”: borda `1px solid #0F172A` visível.
2. “Buscar vagas” inalterado (`#FD7B1A`).
3. Mobile: botões empilhados; contorno permanece.

---

## H) Deploy + idempotência (US6 → SC-007, SC-008)

1. Ambiente “atrasado”: receita da seção A → página completa sem admin.
2. Editor altera texto do node → `updb` de novo → texto preservado.
3. `updb` duplo → sem duplicar ícones em `public://`.

---

## I) Isolamento (SC-009)

Amostrar home, `/para-empresas`, `/para-estudantes` — sem regressão causada por CSS/Image Styles desta feature (CTA vagas/estudantes intactos).

---

## Checklist “deploy fácil”

| # | Critério | OK? |
|---|----------|-----|
| 1 | Image Styles + displays só via `config/sync` + `cim` | |
| 2 | Ícones/textos vazios via `updb` (`11069`), idempotente | |
| 3 | Receita destino: `cim` → `updb` → `cim` → `cr` | |
| 4 | Zero passos manuais no admin de produção | |
| 5 | SC-001…SC-009 conferidos | |
