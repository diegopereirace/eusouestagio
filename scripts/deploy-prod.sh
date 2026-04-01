#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BRANCH="${1:-main}"
IMPORT_CONFIG="${DEPLOY_IMPORT_CONFIG:-0}"
BACKUP_DB="${DEPLOY_BACKUP_DB:-1}"
BACKUP_DIR="${DEPLOY_BACKUP_DIR:-$ROOT_DIR/.deploy-backups}"
PHP_BIN="${DEPLOY_PHP_BIN:-php}"
COMPOSER_BIN="${DEPLOY_COMPOSER_BIN:-composer}"
DRUSH_CMD=("$PHP_BIN" "$ROOT_DIR/vendor/drush/drush/drush.php")
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"

log() {
  printf '[deploy] %s\n' "$*"
}

fail() {
  printf '[deploy:erro] %s\n' "$*" >&2
  exit 1
}

run_drush() {
  "${DRUSH_CMD[@]}" "$@"
}

require_clean_worktree() {
  if ! git -C "$ROOT_DIR" diff --quiet --ignore-submodules --; then
    fail "Existem alteracoes nao commitadas no working tree. Faca commit/stash antes do deploy."
  fi

  if ! git -C "$ROOT_DIR" diff --cached --quiet --ignore-submodules --; then
    fail "Existem alteracoes staged no working tree. Faca commit/stash antes do deploy."
  fi
}

require_command() {
  command -v "$1" >/dev/null 2>&1 || fail "Comando obrigatorio nao encontrado: $1"
}

require_file() {
  [[ -f "$1" ]] || fail "Arquivo obrigatorio nao encontrado: $1"
}

backup_database() {
  mkdir -p "$BACKUP_DIR"
  local backup_file="$BACKUP_DIR/db_${TIMESTAMP}.sql"
  log "Gerando backup do banco em $backup_file"
  run_drush sql:dump --gzip --result-file="$backup_file"
}

log "Iniciando deploy do branch $BRANCH"
require_command git
require_command "$PHP_BIN"
require_command "$COMPOSER_BIN"
require_file "$ROOT_DIR/composer.json"
require_file "$ROOT_DIR/vendor/drush/drush/drush.php"

require_clean_worktree

log "Atualizando refs remotas"
git -C "$ROOT_DIR" fetch --prune origin

if [[ "$BACKUP_DB" == "1" ]]; then
  backup_database
fi

CURRENT_BRANCH="$(git -C "$ROOT_DIR" rev-parse --abbrev-ref HEAD)"
if [[ "$CURRENT_BRANCH" != "$BRANCH" ]]; then
  log "Trocando branch atual de $CURRENT_BRANCH para $BRANCH"
  git -C "$ROOT_DIR" checkout "$BRANCH"
fi

log "Sincronizando codigo com origin/$BRANCH"
git -C "$ROOT_DIR" pull --ff-only origin "$BRANCH"

log "Instalando dependencias Composer"
"$COMPOSER_BIN" --working-dir="$ROOT_DIR" install --no-dev --optimize-autoloader

log "Executando updatedb"
run_drush updatedb -y

if [[ "$IMPORT_CONFIG" == "1" ]]; then
  log "Importando configuracao ativa"
  run_drush config:import -y
else
  log "Config import desabilitado (DEPLOY_IMPORT_CONFIG=0)"
fi

log "Limpando caches"
run_drush cache:rebuild

log "Verificando status final"
run_drush status --fields=bootstrap,database,drupal-version,drush-version,php

log "Deploy concluido"