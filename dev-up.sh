#!/usr/bin/env bash
# Uso: ./dev-up.sh   |   ./dev-up.sh --skip-import   |   ./dev-up.sh --rebuild
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

SKIP_IMPORT=0
REBUILD=0
for arg in "$@"; do
  case "$arg" in
    --skip-import) SKIP_IMPORT=1 ;;
    --rebuild) REBUILD=1 ;;
    -h|--help)
      echo "Uso: ./dev-up.sh [--skip-import] [--rebuild]"
      exit 0
      ;;
  esac
done

step() { printf '\n==> %s\n' "$1"; }

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker nao encontrado. Instale o Docker Desktop e tente de novo." >&2
  exit 1
fi

step "Verificando Docker..."
if ! docker info >/dev/null 2>&1; then
  echo "Docker nao esta rodando. Abra o Docker Desktop e tente de novo." >&2
  exit 1
fi

step "Preparando arquivos locais..."
if [[ ! -f .env ]]; then
  cp .env.example .env
  echo "Criado .env a partir de .env.example"
fi

if [[ ! -f sites/default/settings.php ]]; then
  cp sites/default/default.settings.php sites/default/settings.php
  cat scripts/templates/docker-overrides.php >> sites/default/settings.php
  echo "Criado sites/default/settings.php"
fi

mkdir -p sites/default/files/translations sites/default/files/styles
chmod -R u+rwX sites/default/files || true
chmod u+w sites/default/settings.php || true

step "Subindo containers (Drupal + PostgreSQL 16)..."
if [[ "$REBUILD" -eq 1 ]]; then
  docker compose up -d --build
else
  docker compose up -d
fi

step "Aguardando Postgres..."
deadline=$((SECONDS + 180))
until [[ "$(docker inspect -f '{{.State.Health.Status}}' eusouestagio-postgres 2>/dev/null || true)" == "healthy" ]]; do
  if (( SECONDS >= deadline )); then
    echo "Postgres nao ficou healthy a tempo. Verifique: docker compose logs postgres" >&2
    exit 1
  fi
  sleep 3
done

if [[ "$SKIP_IMPORT" -eq 0 ]]; then
  step "Checando banco de dados..."
  table_count="$(docker compose exec -T postgres psql -U drupal -d drupal -tAc "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public';" | tr -d '[:space:]')"
  if [[ "${table_count:-0}" -lt 5 ]]; then
    if [[ ! -f _db/dump_vps.sql ]]; then
      echo "Dump nao encontrado em _db/dump_vps.sql" >&2
      exit 1
    fi
    echo "Importando _db/dump_vps.sql (pode levar alguns minutos)..."
    docker cp _db/dump_vps.sql eusouestagio-postgres:/tmp/dump_vps.sql
    docker compose exec -T postgres psql -U drupal -d drupal -v ON_ERROR_STOP=1 -f /tmp/dump_vps.sql
    docker compose exec -T postgres rm -f /tmp/dump_vps.sql
    echo "Dump importado."
  else
    echo "Banco ja possui dados (${table_count} tabelas). Import ignorado."
  fi
else
  echo "Import de dump ignorado (--skip-import)."
fi

step "Limpando cache Drupal..."
if ! docker compose exec -T drupal bash /var/www/html/vendor/bin/drush --root=/var/www/html cr; then
  echo "Aviso: drush cr falhou (site pode ainda estar inicializando)."
fi

PORT="$(grep -E '^\s*DRUPAL_HTTPS_PORT\s*=' .env 2>/dev/null | tail -n1 | cut -d= -f2 | tr -d '[:space:]')"
PORT="${PORT:-8082}"

printf '\nPronto.\n'
printf 'Site: https://localhost:%s\n' "$PORT"
echo "Certificado: Avancado -> Continuar para localhost"
echo "CAPTCHA: desligado neste ambiente local"
echo
echo "Parar: ./dev-down.sh"
echo
