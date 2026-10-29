# Contract: deploy 039 — Seed 15 vagas + empresas

## Origem

1. Commitar assets `modules/custom/custom_configs/assets/seed-vagas-validacao/logo-*.png`.
2. Commitar form/perfil + `custom_configs_update_11061` + helpers.
3. `drush cex -y` somente se display/config estrutural mudou (esta feature não exige CT novo).
4. Commitar specs `039-seed-vagas-validacao`.

## Destino

`cim` → `updb` → `cim` → `cr`.

- `updb` executa `11061`: purge candidaturas + vagas + empresas fora do seed; seed 15 empresas/users/vagas.

## Pré-requisito

- `config_sync_directory = 'config/sync'`.
- Assets legíveis no path do módulo após `git pull`.
- Features 032–038 já aplicadas (fields de vaga/empresa/FAQ/benefícios).

## Idempotência / reexecução

- O hook Drupal `11061` roda **uma vez** por ambiente.
- Helpers são seguros se rechamados: purge reconstrói o estado canônico dos 15 seeds.

## Destruição

- **Todas** as `vagas` e `candidatura` são removidas.
- Nodes `empresa` cujo UUID não está no catálogo seed são removidos (inclui EcoConstrutora).
- Users fora do prefixo `empresa-seed-` **não** são apagados.
