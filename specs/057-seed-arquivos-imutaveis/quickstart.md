# Quickstart: Seed — arquivos gerenciáveis imutáveis

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-03

## O que muda

Nenhum `updb` novo obrigatório. Helpers/syncs passam a **preservar** arquivos e fields já preenchidos.

## Validação local (opcional)

1. Garantir que um banner/bloco seed já tem imagem em `public://…`.
2. Em `drush php:eval`, chamar o helper/sync correspondente (ou um update que o reutilize).
3. Esperado: MD5 do arquivo inalterado; `target_id` do field inalterado.
4. Para forçar troca (só sob ordem explícita futura): chamar o helper com `$replace = TRUE` em um `hook_update_N` dedicado.

## Deploy destino

Receita padrão se houver outros updates na mesma release:

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

Não há passo manual de admin para esta feature.
