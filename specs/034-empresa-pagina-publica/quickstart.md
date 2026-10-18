# Quickstart: Página pública da Empresa + Sobre a Vaga

**Feature**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md)

Comandos: `docker compose exec drupal drush <cmd>` a partir da raiz.

## Deploy na origem

```bash
docker compose exec drupal drush updb -y
docker compose exec drupal drush cex -y
docker compose exec drupal drush cr
```

## Destino

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

## Conferência

1. `/para-estudantes/engenheiro-civil`: “Sobre a Vaga” visível; sem “Sobre a Empresa”.
2. Clique em “EcoConstrutora” → `/empresa/ecoconstrutora` (ou alias Pathauto equivalente).
3. Página empresa: logo, sobre, local, lista com Engenheiro Civil.
4. Segunda `updb` não duplica seeds.
