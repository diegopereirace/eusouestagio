# Quickstart: Cadastro de Candidato (UX) e Páginas Legais

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-04

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`). Em produção: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

Modelo: [data-model.md](data-model.md). Contratos: [contracts/](contracts/).

## Pré-requisitos

1. Stack local no ar; `$settings['config_sync_directory'] = 'config/sync'`.
2. Form `/cadastro/candidato` já funcional (feature users existente).
3. Opcional local: `$settings['disable_captcha'] = TRUE` em `settings.php` para testar submit sem reCAPTCHA.
4. Branch com Twig/CSS/assets + `custom_configs.install` (11089) + YAMLs de sync commitados.

---

## A) Deploy

### Origem

```bash
docker compose exec drupal drush cex -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cr
```

### Destino

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cim -y
docker compose exec drupal drush cr
```

**Gates**: hook `11089` reporta created/skipped; `/termos-de-uso` e `/politica-de-privacidade` ≠ 404.

---

## B) Páginas legais (US1, US6 → SC-001, SC-007)

1. Abrir `/termos-de-uso` e `/politica-de-privacidade` → 200, títulos corretos, HTML com seções.
2. Rodapé → Institucional → mesmos destinos.
3. Editar um parágrafo no admin → recarregar URL pública → texto novo; alias estável.
4. Rodar `drush updb -y` de novo → sem Node duplicado; corpo editorial preservado.

---

## C) Banner + seções-cartão (US2 → SC-003, SC-004)

1. Confirmar `body.page-cadastro-candidato` (senão o CSS da library não aplica).
2. Desktop 1440: banner com overlay lateral + eyebrow + título canônico à esquerda; formulário em seções-cartão (`col-xl-10`/`col-xxl-9`), não full-bleed cru.
3. Inspecionar Network/DOM: `<picture>` com variante mobile; **sem** bloco View banners na região.
4. Em 1440 / 1024 / 768 / 375: `document.documentElement.scrollWidth === clientWidth` (**sem** scroll horizontal).

---

## D) Form + aceite (US3, US4 → SC-002)

1. Cada seção (Acesso, Dados Pessoais, Endereço, Formação…) em `.cadastro-section` com chip de ícone distinto.
2. Inputs grandes; labels escuros/negrito; botão laranja `#FD7B1A` full width no cartão final.
3. Checkbox: links Termos/Política → URLs canônicas, `target="_blank"`.
4. Enviar sem aceite → bloqueio com mensagem amigável.

---

## E) Submit + CAPTCHA (US5 → SC-005, SC-006)

1. Cadastro válido (CAPTCHA off local se necessário) → usuário criado + mensagem sucesso.
2. Com login automático → destino **`/painel/estudante/perfil`** (não `/meu-perfil`).
3. Staging com reCAPTCHA: ≥3 submits válidos consecutivos sem falha de layout.
4. Erro de validação → mensagens legíveis dentro do card.

---

## F) Regressões rápidas

| Rota | Esperado |
|------|----------|
| `/cadastro/empresa` | Visual/fluxo inalterados |
| `/user/login` | Card 035 intacto |
| `/` e demais | Sem CSS “vazando” de cadastro candidato |

Tempo alvo de revisão completa: ≤15 min (SC-008).
