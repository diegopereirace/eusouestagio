#!/bin/bash
set -e

OUT_DIR="/tmp/dumps"
rm -rf "$OUT_DIR"
mkdir -p "$OUT_DIR"

TABLES=$(PGPASSWORD='drupal' psql -U drupal -d drupal -t -A -c "SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename;")

TOTAL=$(echo "$TABLES" | grep -c .)
I=0

echo "$TABLES" | while IFS= read -r t; do
  [ -z "$t" ] && continue
  I=$((I+1))
  echo "[$I/$TOTAL] $t"
  PGPASSWORD='drupal' pg_dump -U drupal -d drupal -a --column-inserts --rows-per-insert=1 --no-owner --no-privileges --encoding=UTF8 -t "$t" -f "$OUT_DIR/$t.sql"
done

echo "---DONE---"
ls "$OUT_DIR"/*.sql | wc -l
