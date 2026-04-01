#!/usr/bin/env bash
set -Eeuo pipefail

KEY_PATH="${1:-$HOME/.ssh/eusouestagio_github}"
KEY_COMMENT="${DEPLOY_KEY_COMMENT:-deploy@eusouestagio}"

log() {
  printf '[ssh-setup] %s\n' "$*"
}

require_command() {
  command -v "$1" >/dev/null 2>&1 || {
    printf '[ssh-setup:erro] comando obrigatorio nao encontrado: %s\n' "$1" >&2
    exit 1
  }
}

require_command ssh-keygen
require_command ssh-keyscan

mkdir -p "$HOME/.ssh"
chmod 700 "$HOME/.ssh"

if [[ -f "$KEY_PATH" || -f "$KEY_PATH.pub" ]]; then
  printf '[ssh-setup:erro] chave ja existe em %s\n' "$KEY_PATH" >&2
  exit 1
fi

log "Gerando chave ed25519 em $KEY_PATH"
ssh-keygen -t ed25519 -C "$KEY_COMMENT" -f "$KEY_PATH" -N ""

log "Registrando github.com em known_hosts"
touch "$HOME/.ssh/known_hosts"
chmod 600 "$HOME/.ssh/known_hosts"
ssh-keyscan -H github.com >> "$HOME/.ssh/known_hosts"

log "Chave publica gerada. Cadastre o conteudo abaixo como Deploy Key read-only no GitHub:"
cat "$KEY_PATH.pub"

cat <<EOF

Use esta configuracao de SSH no servidor em ~/.ssh/config:

Host github.com
  HostName github.com
  User git
  IdentityFile $KEY_PATH
  IdentitiesOnly yes

Depois teste com:
  ssh -T git@github.com
EOF