# Contract: Deploy — Home busca cidades (040)

**Feature**: [spec.md](../spec.md) | **Plan**: [plan.md](../plan.md)

## Artefatos versionados

| Artefato | Obrigatório nesta feature |
|----------|---------------------------|
| `modules/custom/custom_banners/**` (block, controller, routing, Twig) | sim |
| `themes/custom/default/assets/{css,js}/…` + `default.libraries.yml` | sim |
| `PRD.md` (cirúrgico) | sim |
| `config/sync/**` | **não** (sem mudança estrutural) |
| `custom_configs_update_11062+` | **não** (último hook existente: `11061`; Remoto permanece na taxonomia) |

## Origem (após implementar)

```bash
# Sem cex obrigatório se git status não listar config/sync
docker compose exec drupal drush cr
git status
```

## Destino (produção / homolog)

### Receita mínima (código + assets)

```bash
git pull
# a partir de /var/www/html — ver runbook de produção
php vendor/bin/drush.php cr
```

### Receita esteira padrão do projeto (compatível; no-op se sem YAML/hook)

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

## Gates pós-deploy

1. Home: caixa de busca alinhada ao Figma; campo “Cidade” (sem “Remoto” no label).
2. `GET /api/cidades/autocomplete?q=sao` retorna JSON no formato contratado (quando houver vagas).
3. Pill “Remoto” ausente; pills de curso presentes quando os termos existem.
4. Zero cliques no admin; zero dump de banco.

## Proibido

- Colar CSS/JS no admin para “ativar” a feature.
- Apagar manualmente o termo Remoto da taxonomia como passo de deploy.
- Editar `core/` ou `vendor/`.
