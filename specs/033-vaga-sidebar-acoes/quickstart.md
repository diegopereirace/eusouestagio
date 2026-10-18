# Quickstart: Sidebar da vaga — Ações e Resumo

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-18

Comandos: `docker compose exec drupal drush <cmd>` a partir da raiz do repositório.

## Pré-requisitos

1. Stack local no ar. `config_sync_directory = ../config/sync` (ou `config/sync` no settings ativo).
2. Feature 032 aplicada (CT `empresa`, seeds EcoConstrutora e Engenheiro Civil) — o hook `11050` também cria esses nodes se faltarem.
3. Nenhum dump.

## Deploy na origem

```bash
docker compose exec drupal drush updb -y
docker compose exec drupal drush cex -y
docker compose exec drupal drush cr
```

## Destino

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

## Conferência

1. Form da vaga mostra Período e Vagas.
2. Detalhe da vaga “Engenheiro Civil”: resumo com “6 meses +”, “R$ 1.500/mês”, “Presencial”, “02”; localização do header “Curitiba, PR”.
3. Candidato autenticado: “Candidatar-se” é um link cuja URL é `/vaga/apply/{nid}`; o clique envia POST (não abre erro de método). “Salvar” alterna sem reload.
4. Anônimo: botão primário vai para o login. Não aparecem Match nem Perfil.
5. Segunda execução de `updb` não duplica os nodes seed.

Modelo: [data-model.md](data-model.md). Render: [contracts/vaga-sidebar-render.md](contracts/vaga-sidebar-render.md). Deploy: [contracts/deploy-vaga-sidebar.md](contracts/deploy-vaga-sidebar.md).
