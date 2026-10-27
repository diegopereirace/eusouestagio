# Implementation Plan: Bloco "Sobre a Empresa" no Detalhe da Vaga

**Branch**: `feature-vaga`  
**Feature Directory**: `specs/037-vaga-sobre-empresa`  
**Date**: 2026-10-27  
**Spec**: [spec.md](spec.md) | **Checklist**: [checklists/requirements.md](checklists/requirements.md)  
**Predecessor**: `034-empresa-pagina-publica` (página pública + remoção do bloco legado); `032-vaga-header-empresa` (`field_vaga_empresa`); `036-vagas-beneficios-padrao` (ordem atual da coluna principal)  
**Hook**: `custom_configs_update_11057`

## Summary

Reintroduzir, na coluna principal do detalhe da vaga, um **cartão de resumo** da empresa (logo + “Sobre a {nome}” + subtítulo curto + “Ver Empresa”), posicionado **acima do FAQ**. O subtítulo é um campo editorial próprio, distinto da descrição longa da página da empresa. Três frentes:

1. **Instância de campo** no CT `empresa`: reutilizar o storage `node.field_text_simple` (string, 255) com rótulo “Resumo curto”. Sem storage novo. Campo opcional. Entrar no form display e no view display default.
2. **Seed idempotente** (`hook_update_11057`): se a EcoConstrutora (UUID `f6a7b8c9-d0e1-4234-e567-89abcdef0123`) existir e o resumo estiver vazio, preencher com o texto do Figma. Texto editorial divergente não é sobrescrito.
3. **Cartão no Twig** do detalhe full, entre Benefícios e FAQ, com CSS no componente já carregado (`vagas_detalhe`). Só renderiza com empresa publicada e acessível via `field_vaga_empresa`. A página pública da empresa (034) permanece com a descrição longa; o resumo curto não entra nesse template.

## Technical Context

**Language/Version**: PHP 8.4, Drupal 11.4+, Twig 3, CSS3 (Bootstrap 5.3 via `bootstrap_barrio`).  
**Primary Dependencies**: Drupal core (`node`, `field`, `text`, `image`, `path`, `pathauto`); módulo custom `custom_configs` (ativo); tema `default`. **Nenhuma dependência Composer nova.**  
**Storage**: reuso de `field.storage.node.field_text_simple` (`type: string`, `max_length: 255`, cardinalidade 1). Nova instância `field.field.node.empresa.field_text_simple` (rótulo “Resumo curto”, `required: false`). A vaga continua usando a instância `node.vagas.field_text_simple` como salário/bolsa — bundles distintos, dados independentes.  
**Testing**: validação manual via [quickstart.md](quickstart.md); sem suite PHPUnit dedicada (padrão do projeto para campo + apresentação).  
**Target Platform**: detalhe público da vaga (`/vaga/<alias>`), desktop e mobile (empilha abaixo de 992px, mesmo breakpoint já usado em `vagas-detalhe.css`).  
**Project Type**: módulo custom (`modules/custom/custom_configs`) + tema (`themes/custom/default`) + config exportada (`config/sync`).  
**Performance Goals**: um nó seed por deploy; leitura do resumo no mesmo `empresa_node` que o header já carrega. Sem query extra e sem agregação de vagas.  
**Constraints**: sem `core/`/`vendor/`; sem storage novo (`field_sobre_empresa_resumo` fica de fora — ver [research.md](research.md) R1); sem restaurar `field_empresa_u`; sem alterar header, sidebar, FAQ, listagens ou a página pública da empresa; sem passo manual no admin; pt-BR.

**Estado atual verificado (2026-10-27):**

- `field.storage.node.field_text_simple` existe (string, 255). Instâncias atuais: `vagas` (rótulo Salário), `contato`, `depoimento`. **Não** há `field.field.node.empresa.field_text_simple`.
- CT `empresa`: `field_imagem` (logo), `field_text_long_formatted` (descrição longa), `field_cidade`, `field_estados`. Form e view displays default já listam esses quatro.
- Página pública: `node--empresa--full.html.twig` é markup próprio (não imprime `{{ content.field_* }}`). Pathauto `/empresa/[node:title]`.
- Detalhe da vaga: `node--vagas--full.html.twig` já resolve `empresa_node` só se `field_vaga_empresa` aponta para nó **publicado**. Coluna principal: Sobre a Vaga → Requisitos → Benefícios → FAQ. O bloco legado foi removido em 034; o comentário do Twig ainda diz “Sem bloco Sobre a Empresa”.
- CSS morto do cartão antigo em `vagas-detalhe.css` (por volta das linhas 240–269): fundo branco, logo 56px `contain`, texto com `-webkit-line-clamp: 4`, comentário “sem Ver Empresa”. A classe de seção `.vaga-detalhe__empresa` ainda entra no grupo de `margin-bottom: 2rem` e **não** é usada no Twig.
- Header da vaga usa `.vaga-detalhe__logo` (112px, `object-fit: cover`). Não confundir com `.vaga-detalhe__empresa-logo`.
- Seed EcoConstrutora: UUID `f6a7b8c9-d0e1-4234-e567-89abcdef0123`, logo via `_custom_configs_seed_empresa_ecoconstrutora()`. Descrição longa/cidade/estado via `_custom_configs_align_empresa_pagina_seed()` (só preenche vazio). Vaga seed Engenheiro Civil: UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234`.
- Último hook: `custom_configs_update_11056`. Próximo número livre: **`11057`**.
- Não há `field_permissions`. Quem edita o node Empresa edita o campo novo.
- Checkout git atual: `feature-vaga`. Identidade da feature: `037-vaga-sobre-empresa`.

## Constitution Check

Não há `.specify/memory/constitution.md`. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + `estagio-fluxo-dev.mdc` + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + `checklists/requirements.md` OK, zero `[NEEDS CLARIFICATION]` |
| Sem `core/` / `vendor/` | PASS | só `modules/custom/custom_configs` + `themes/custom/default` + `config/sync` |
| Reuso de storage (`estagio-fluxo-dev`) | PASS | instância nova de `field_text_simple` no bundle `empresa`; storage 255 já existe. Machine name `field_sobre_empresa_resumo` da Assumption é descartado de propósito |
| Deploy de config estrutural (`drupal-deploy-configs.mdc`) | PASS | hook `11057` garante instância + displays via Entity API; `drush cex` na origem versiona o YAML |
| Seeds via Entity API (não YAML editado à mão) | PASS | resumo da EcoConstrutora preenchido no hook só se vazio |
| Idempotência (reexecução = no-op) | PASS | `FieldConfig::loadByName` antes de criar; componente de display só se ausente; seed só se `isEmpty()` |
| Não sobrescrever editorial divergente | PASS | FR-012; mesmo critério de `_custom_configs_align_empresa_pagina_seed()` |
| Sem passo manual no admin | PASS | campo, displays e seed no `updb`; receita destino `cim` → `updb` → `cim` → `cr` |
| Não restaurar legado `field_empresa_u` | PASS | cartão lê só `field_vaga_empresa` |
| Página pública 034 intacta | PASS | `node--empresa--full.html.twig` não passa a imprimir o resumo |
| PRD guardião (`estagio-prd-guardian.mdc`) | A atualizar na implementação | linha do CT `empresa` em §3.1 e o parágrafo do detalhe full (hoje diz “sem bloco Sobre a Empresa”) |
| Reinício de cache (`estagio-reiniciar-servicos.mdc`) | A executar na implementação | `drush cr` após `updb` + `cim` |

**Post-design**: nenhuma violação. O descarte de `field_sobre_empresa_resumo` está coberto pela própria Assumption da spec e pela regra de reuso. A substituição do CSS morto (line-clamp) evita conflito com FR-005. Detalhe em [research.md](research.md).

## Design Decisions

1. **Reuso de `field_text_simple` no bundle `empresa`** (não criar `field_sobre_empresa_resumo`): storage string 255 já existe e a regra de reuso proíbe nome paralelo. Rótulo editorial “Resumo curto”; descrição deixa claro que é o subtítulo do cartão e não a descrição longa. `required: false` (bloco aparece sem subtítulo). `translatable: false`, alinhado às outras instâncias desse storage.
2. **Hook `11057` com três helpers novos**, sem reabrir `11052`: `_custom_configs_ensure_empresa_resumo_field()`, `_custom_configs_ensure_empresa_resumo_displays()`, `_custom_configs_seed_empresa_resumo()`. O ensure de displays da 034 não lista esse campo; um site que já rodou `11052` só ganha o campo quando `11057` roda.
3. **Ordem no formulário**: na primeira inserção, resumo fica no weight `2` (entre logo e descrição longa). Se `field_text_long_formatted` / `field_cidade` / `field_estados` ainda estiverem em 2/3/4, sobem para 3/4/5. Reexecução não mexe em pesos já ajustados. No view display default, o resumo entra com formatter `string`, label acima, weight logo após o logo (mesmo critério de empurrar os pesos seguintes só na primeira inserção).
4. **Seed só da EcoConstrutora**, texto exato: “Líder regional em construções sustentáveis e infraestrutura urbana de alto impacto.” Condição: node com o UUID existe, `hasField('field_text_simple')` e `isEmpty()`. Não varre as demais empresas.
5. **Cartão no include** `themes/custom/default/templates/content/includes/vaga-sobre-empresa.html.twig`, chamado em `node--vagas--full.html.twig` **depois** de Benefícios e **antes** do bloco FAQ. A seção usa a classe já espaçada `.vaga-detalhe__empresa`. O include recebe `empresa_node` e não lê `field_empresa_u`.
6. **Gate de render**: o Twig pai só inclui o cartão se `empresa_node` estiver definido (já exige publicado) **e** `empresa_node.access('view')`. Sem empresa, não publicada ou inacessível → zero markup (FR-002). O header existente não muda.
7. **Conteúdo do cartão**: título `Sobre a @nome` via `|t`; subtítulo só se o valor do resumo, após trim, não for vazio (sem placeholder); logo 80×80 `object-fit: cover` e `border-radius: 12px` quando `field_imagem` tiver arquivo — sem logo, a coluna da imagem some; link “Ver Empresa” para `entity.node.canonical` da empresa (Pathauto `/empresa/[node:title]`).
8. **CSS**: substituir o bloco morto `.vaga-detalhe__empresa-card*` / `.vaga-detalhe__empresa-sobre` (line-clamp) e o logo 56px. Fundo `#EFF4FF`, borda `#D5E3F7`, título `var(--vd-navy)` (`#023C62`), subtítulo `#45464D`, radius 12px, padding ~1.25rem 1.5rem. Desktop: flex em linha (logo | textos | botão), textos com `min-width: 0` e `overflow-wrap: break-word`, botão `white-space: nowrap`. Abaixo de `991.98px`: coluna, centralizado. Sem `line-clamp` e sem ellipsis — o texto de 255 caracteres quebra linha. Botão com classe própria `.vaga-detalhe__empresa-cta` (outline navy, radius 12px), sem reusar `.btn-outline-azul-escuro` (radius 4px, fonte 0.8rem, pensado para o painel).
9. **Fora**: redesenhar `node--empresa--full.html.twig`; voltar o bloco de descrição longa no detalhe; header, sidebar, FAQ, listagens; `field_empresa_u`; storage novo; migração em massa de resumos.

## Project Structure

### Documentation (this feature)

```text
specs/037-vaga-sobre-empresa/
├── spec.md
├── checklists/requirements.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/
│   ├── deploy-sobre-empresa.md
│   └── vaga-sobre-empresa-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
modules/custom/custom_configs/
  custom_configs.install
    # + custom_configs_update_11057
    # + _custom_configs_ensure_empresa_resumo_field
    # + _custom_configs_ensure_empresa_resumo_displays
    # + _custom_configs_seed_empresa_resumo

themes/custom/default/
  templates/content/node--vagas--full.html.twig
    # include do cartão entre Benefícios e FAQ; comentário do arquivo atualizado
  templates/content/includes/vaga-sobre-empresa.html.twig
  assets/css/components/vagas-detalhe.css
    # substitui o cartão morto (line-clamp / 56px) pelo cartão Figma

config/sync/   # somente via drush cex na origem, nunca editado à mão
  field.field.node.empresa.field_text_simple.yml
  core.entity_form_display.node.empresa.default.yml
  core.entity_view_display.node.empresa.default.yml

PRD.md          # cirúrgico: linha do CT empresa em §3.1 + parágrafo do detalhe full
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. Helpers + `hook_update_11057` em `custom_configs.install`
3. Include Twig + chamada no detalhe full
4. CSS do cartão no lugar do bloco morto
5. Origem: `drush updb -y` → `drush cex -y` → `drush cr`
6. Atualização cirúrgica do `PRD.md` (§3.1 CT `empresa` e parágrafo do detalhe)
7. Validação via [quickstart.md](quickstart.md)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Instância `field_text_simple` em vez de `field_sobre_empresa_resumo` | Regra de reuso de storage; o storage string 255 já existe. A Assumption da spec abre exatamente essa exceção. Rótulo e limite de 255 permanecem | Storage novo com o nome da Assumption (rejeitado: duplica `field_text_simple`) |
| Substituir CSS morto do cartão antigo | O bloco removido em 034 ainda tem `line-clamp: 4` e logo 56px. Reusar essas classes do jeito que estão descumpre FR-006 e o “sem truncar” do edge case | Deixar o CSS morto e criar seletores paralelos (rejeitado: duas definições de “cartão empresa” no mesmo arquivo) |
| `access('view')` além de `isPublished()` | FR-002 pede ocultar empresa inacessível, não só despublicada. O header continua só com o critério atual (fora de escopo alterar o header) | Só `isPublished()` (rejeitado: nó publicado com acesso negado ainda mostraria o cartão) |
| Resumo fora da página pública da empresa | FR-010 pede o campo nos displays CMS. O Twig full de 034 é markup próprio e está fora do escopo redesenhar | Imprimir o resumo em `node--empresa--full.html.twig` (rejeitado: mistura subtítulo do cartão com “Sobre a Empresa” da página) |
