# Quickstart — 049 Link no título (`/vagas`)

**Spec**: [spec.md](spec.md) · **Plan**: [plan.md](plan.md)

## Pré-requisitos

- Docker local com site em `https://localhost:8082` (ou porta do ambiente).
- Vagas publicadas na listagem.

## Deploy (todos os ambientes)

Esta feature é **somente tema** (Twig + CSS). **Não** há `drush cex`, `drush cim` nem `hook_update_N`.

```text
git pull
php vendor/bin/drush.php cr
```

Local (Docker):

```powershell
docker compose exec -T drupal vendor/bin/drush cr
```

Produção (`/var/www/html`):

```bash
git pull
php vendor/bin/drush.php cr
```

## Validação

1. Abrir `/vagas`.
2. Confirmar que o título de cada card é um link.
3. Clicar no título → página de detalhe (`node--vagas--full`, alias `/para-estudantes/...`).
4. Confirmar que o CTA do mesmo card aponta para a **mesma** URL.
5. Tab até o título → foco visível; Enter navega.
