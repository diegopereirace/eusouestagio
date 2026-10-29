# Quickstart: Seed 15 vagas + empresas

**Feature**: [spec.md](spec.md) | **Data**: [data-model.md](data-model.md) | **Deploy**: [contracts/deploy-seed-vagas-validacao.md](contracts/deploy-seed-vagas-validacao.md)

## Aviso

`custom_configs_update_11061` **apaga todas as vagas e candidaturas** do ambiente e substitui nodes `empresa` pelo conjunto seed.

## Credenciais seed (users)

| Campo | Valor |
|-------|--------|
| Usernames | `empresa-seed-01` … `empresa-seed-15` |
| Senha | `SeedEmpresa!2026` |
| E-mails | `empresa-seed-NN@example.local` |

## Destino

```bash
git pull
drush cim -y
drush updb -y
drush cim -y
drush cr
```

Local (Docker):

```bash
docker compose exec -T drupal vendor/bin/drush cim -y
docker compose exec -T drupal vendor/bin/drush updb -y
docker compose exec -T drupal vendor/bin/drush cim -y
docker compose exec -T drupal vendor/bin/drush cr
```

## Gates

1. `/vagas` lista 15 vagas; nenhuma legada (Engenheiro Civil sumiu).
2. Detalhe de 2–3 vagas: header com logo, benefícios, FAQ, sobre empresa.
3. Login `empresa-seed-01` / `SeedEmpresa!2026` → perfil com logo.
4. `/cadastro/empresa` aceita upload de logo.
