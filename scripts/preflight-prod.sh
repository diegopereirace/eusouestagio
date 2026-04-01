#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP_BIN="${DEPLOY_PHP_BIN:-php}"
COMPOSER_BIN="${DEPLOY_COMPOSER_BIN:-composer}"
DRUSH_CMD=("$PHP_BIN" "$ROOT_DIR/vendor/drush/drush/drush.php")

log() {
  printf '[preflight] %s\n' "$*"
}

check_cmd() {
  if command -v "$1" >/dev/null 2>&1; then
    printf '[ok] comando disponivel: %s\n' "$1"
  else
    printf '[erro] comando ausente: %s\n' "$1" >&2
    return 1
  fi
}

check_file() {
  if [[ -f "$1" ]]; then
    printf '[ok] arquivo encontrado: %s\n' "$1"
  else
    printf '[erro] arquivo ausente: %s\n' "$1" >&2
    return 1
  fi
}

log "Validando dependencias de deploy em $ROOT_DIR"
check_cmd git
check_cmd "$PHP_BIN"
check_cmd "$COMPOSER_BIN"
check_file "$ROOT_DIR/composer.json"
check_file "$ROOT_DIR/vendor/drush/drush/drush.php"

if git -C "$ROOT_DIR" rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  printf '[ok] diretorio esta sob controle do git\n'
else
  printf '[erro] diretorio nao esta sob controle do git\n' >&2
  exit 1
fi

printf '[info] branch atual: %s\n' "$(git -C "$ROOT_DIR" rev-parse --abbrev-ref HEAD)"
printf '[info] remote origin: %s\n' "$(git -C "$ROOT_DIR" remote get-url origin)"

if git -C "$ROOT_DIR" diff --quiet --ignore-submodules -- && git -C "$ROOT_DIR" diff --cached --quiet --ignore-submodules --; then
  printf '[ok] working tree limpo\n'
else
  printf '[warn] working tree possui alteracoes locais\n'
fi

printf '[info] versao do PHP: %s\n' "$(command -v "$PHP_BIN")"
printf '[info] versao do Composer: %s\n' "$($COMPOSER_BIN --version)"
printf '[info] versao do Git: %s\n' "$(git --version)"

log "Validando acesso do Drush"
"${DRUSH_CMD[@]}" status --fields=bootstrap,database,drupal-version,drush-version,php

log "Preflight concluido"