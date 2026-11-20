# Quickstart: Bloco "Sobre a Empresa" no Detalhe da Vaga

**Feature**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md) | **Deploy**: [contracts/deploy-sobre-empresa.md](contracts/deploy-sobre-empresa.md) | **Render**: [contracts/vaga-sobre-empresa-render.md](contracts/vaga-sobre-empresa-render.md)

Comandos Drush na **origem** via `docker compose exec drupal drush <cmd>`. No **destino** via `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

## Pré-requisito

- Features 032/034 aplicadas: CT `empresa`, `field_vaga_empresa`, EcoConstrutora (UUID `f6a7b8c9-d0e1-4234-e567-89abcdef0123`) e vaga Engenheiro Civil (UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234`).
- `settings.php` ativo com `config_sync_directory = 'config/sync'`.

## Deploy na origem

```bash
docker compose exec drupal drush updb -y    # custom_configs_update_11057
docker compose exec drupal drush cex -y     # instância + form/view display da empresa
docker compose exec drupal drush cr
```

Commitar o hook, o include Twig, o ajuste em `node--vagas--full.html.twig`, `vagas-detalhe.css`, os três YAML de `config/sync` listados no contrato de deploy e o trecho cirúrgico do `PRD.md`.

## Deploy no destino

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

## Conferência

1. Abrir a vaga Engenheiro Civil. Na coluna principal, acima de “Dúvidas Frequentes”, o cartão mostra logo 80×80, título “Sobre a EcoConstrutora”, o texto “Líder regional em construções sustentáveis e infraestrutura urbana de alto impacto.” e o botão “Ver Empresa”.
2. Clicar em “Ver Empresa” leva a `/empresa/ecoconstrutora` (ou o alias canônico vigente). A página da empresa continua com a descrição longa, sem trocá-la pelo resumo curto.
3. Desktop (≥992px): logo, textos e botão na mesma linha. Mobile (≤991px): logo, textos e botão empilhados, sem scroll horizontal. Título longo e resumo no limite de 255 caracteres quebram linha, sem reticências.
4. No formulário da Empresa, o campo “Resumo curto” aceita até 255 caracteres e recusa o 256º. Esvaziar o resumo, salvar e recarregar a vaga: o cartão permanece, sem a linha de subtítulo.
5. Vaga sem `field_vaga_empresa` (ou com empresa despublicada): o cartão não aparece e não sobra espaço vazio no lugar.
6. Empresa com nome e logo e sem resumo: cartão com título e botão, sem subtítulo. Sem logo: cartão sem a imagem.
7. Reexecutar `drush updb -y` depois de editar o resumo da EcoConstrutora para um texto diferente do Figma: o texto editorial permanece.
8. `config/sync/field.field.node.empresa.field_text_simple.yml` existe, `required: false`, dependência de `field.storage.node.field_text_simple`. Não existe `field.storage.node.field_sobre_empresa_resumo`.

## Resultado esperado do hook

Mensagem indicando instância criada (ou já existente), displays garantidos e resumo da EcoConstrutora preenchido **ou** preservado. Segunda execução do update não duplica o campo.
