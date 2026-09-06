#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"
echo "Parando containers..."
docker compose down
echo "Parado. Dados do Postgres permanecem no volume docker."
