# Feature Specification: Seed — arquivos gerenciáveis imutáveis após o 1º deploy

**Feature Directory**: `specs/057-seed-arquivos-imutaveis`  
**Created**: 2026-11-03  
**Status**: Implemented  
**Hook**: nenhum novo (endurece helpers/syncs existentes; replace só com ordem explícita futura)

## Problema

Deploys seguintes (`drush updb`) podiam **alterar bytes** de arquivos já materializados em `public://` e/ou **reapontar** fields de imagem, porque:

1. `_custom_configs_seed_file_from_module_asset` sempre usava `FileExists::Replace`.
2. Vários `sync_*` re-sincronizavam quando MD5 ≠ asset do módulo ou o filename “parecia seed”.
3. O seed do banner Para Estudantes forçava troca por placeholder PE ou ratio &lt; 2.

Isso conflita com ambientes que divergem de propósito (arte gerada no local, upload único, produção estável).

## Solução

- Helper central com `$replace = FALSE` por padrão: se managed file ou arquivo no disco já existir, **reutilizar sem overwrite**.
- Seeds/syncs: **fill-if-empty** / **copy-if-missing** apenas.
- Ambientes **podem** divergir (local ≠ produção).
- **Ordem explícita**: só um `hook_update_N` novo, sob pedido do produto, passa `$replace = TRUE`.

## Requisitos funcionais

- **FR-001**: `_custom_configs_seed_file_from_module_asset` NÃO deve substituir bytes existentes quando `$replace === FALSE`.
- **FR-002**: Campos de imagem já preenchidos NÃO devem ter `target_id` alterado por seeds/syncs sem `$replace`.
- **FR-003**: Assets seed em `public://` (órbita, ícones, colagem) só são copiados se o destino estiver **ausente**.
- **FR-004**: Banner Para Estudantes só anexa imagem se desktop/mobile estiver **vazio** (sem force por placeholder/ratio).
- **FR-005**: Documentação Cursor (`estagio-fluxo-dev`, `drupal-deploy-configs`) registra a política e a exceção `$replace = TRUE`.

## Critérios de sucesso

- **SC-001**: Reexecutar um seed/sync que chama o helper com URI já existente **não** altera MD5 do arquivo em disco (`$replace = FALSE`).
- **SC-002**: Campo de imagem preenchido permanece com o mesmo `target_id` após re-seed.
- **SC-003**: Novo hook futuro pode passar `$replace = TRUE` e sobrescrever (caminho explícito preservado na API).

## Fora de escopo

- Trocar o placeholder `1200 x 433` em produção neste PR.
- Criar `hook_update_N` de replace de conteúdo.
- Alterar `core/` / `vendor/` ou inventar YAML em `config/sync`.
