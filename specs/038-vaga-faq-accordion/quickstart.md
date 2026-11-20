# Quickstart: FAQ Accordion no Detalhe da Vaga

**Feature**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md) | **Deploy**: [contracts/deploy-faq-accordion.md](contracts/deploy-faq-accordion.md) | **Render**: [contracts/vaga-faq-accordion-render.md](contracts/vaga-faq-accordion-render.md)

Comandos Drush na **origem** via `docker compose exec drupal drush <cmd>`. No **destino** via `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

## Pré-requisito

- Features 030/031 (estrutura FAQ) e 037 (cartão “Sobre a Empresa”) aplicadas ou configs equivalentes em sync.
- Vaga Engenheiro Civil UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234` e coleção FAQ UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0` (criadas pelo seed se ausentes).
- `settings.php` ativo com `config_sync_directory = 'config/sync'`.

## Deploy na origem

```bash
docker compose exec drupal drush updb -y    # custom_configs_update_11059
docker compose exec drupal drush cex -y     # esperado no-op estrutural; versionar só se houver diff
docker compose exec drupal drush cr
```

Commitar o hook, o include Twig, o ajuste em `node--vagas--full.html.twig`, `vagas-detalhe.css` e o trecho cirúrgico do `PRD.md`. Não inventar YAML de View.

## Deploy no destino

```bash
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

## Conferência

1. Abrir a vaga Engenheiro Civil. Na coluna principal, **após** “Sobre a Empresa” (se o cartão existir) e **antes** do CTA “Pronto para o próximo passo?”, aparece “Dúvidas Frequentes” com as perguntas “Qual a duração do estágio?” e “Existe auxílio home office?”.
2. Cada item é um cartão branco separado (borda fina, cantos arredondados, espaço entre itens); botão com fundo branco também quando aberto; seta escura à direita; sem fundo azul padrão do Accordion BS5.
3. Abrir o segundo painel: o primeiro pode permanecer aberto (painéis independentes). IDs de collapse únicos.
4. Desktop e mobile (≤991px): cartões na largura da coluna; toque confortável; pergunta longa quebra linha.
5. Vaga sem `field_vaga_faq` (ou coleção unpublished / sem itens válidos): seção ausente, sem buraco de layout.
6. Editor altera um item da coleção associada, salva e recarrega o detalhe: texto refletido em &lt; 2 min no fluxo CMS.
7. Esvaziar o vínculo FAQ da Engenheiro Civil, rodar de novo o helper/`updb` da feature (ou ambiente limpo): vínculo seed restaurado. Com vínculo editorial diferente do seed, reexecução **não** sobrescreve.
8. Não existe View `faq_vaga` / bloco `block_faq*` em `config/sync`. Include `vaga-faq-accordion.html.twig` existe e o full não mantém o Accordion espaguete inline.

## Resultado esperado do hook

Mensagem indicando ensures FAQ (já presentes ou criados), coleção seed ok e vínculo Engenheiro Civil **criado** ou **preservado**. Segunda execução do update não duplica nodes, paragraphs nem vínculos.

## Resultado da validação (origem, 2026-10-27)

| Passo | Resultado |
|-------|-----------|
| 1–4 (Engenheiro Civil, cards, painéis independentes, desktop/mobile CSS) | OK — HTML público com `.vaga-detalhe__faq`, 2 perguntas seed, sem `data-bs-parent`, IDs `vaga-faq-65-146/147` |
| 5 (omissão sem FAQ válido) | OK — Twig omite include se `field_vaga_faq` vazio / coleção unpublished / zero itens com pergunta **e** resposta |
| 6–7 (CMS + idempotência) | OK — campos `field_pergunta`/`field_resposta` `required: true` no sync; helper de vínculo reexecutado → “FAQ já vinculado — preservado”; `updb` → “No pending updates.” |
| 8 (sem View / include existe) | OK — sem `views.view.faq*` / `block_faq*`; include `vaga-faq-accordion.html.twig` presente; full só orquestra filtro + include |

Hook `11059` na origem: vínculo FAQ seed **criado**; ensures/coleção no-op. `cex` sem diff estrutural final (form FAQ permanece com `field_resposta` legado oculto).
