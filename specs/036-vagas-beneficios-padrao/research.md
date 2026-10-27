# Research: 036-vagas-beneficios-padrao

## R1 — Criar storage novo vs. reusar `beneficio_vaga_p`

**Decision**: reusar o modelo existente (paragraph `beneficio_vaga_p` + `field_vaga_beneficios`).
**Rationale**: features 030/031 já entregaram o storage e o Twig/CSS da seção; FR-011 e a regra de reuso de storage do projeto (`estagio-fluxo-dev.mdc`) vedam criar campo/paragraph type paralelo. A feature só precisa popular dados e tornar o campo obrigatório.
**Alternatives considered**: criar novo paragraph type `beneficio_padrao_vaga_p` (rejeitado: duplicação de storage, quebra regra de reuso); criar campo `field_vaga_beneficios_padrao` (rejeitado: mesma duplicação).

## R2 — Backfill via `hook_update_N` vs. migration Drupal

**Decision**: `hook_update_11056` com `loadByProperties(['type' => 'vagas'])` + `foreach` + `isEmpty()` + `Paragraph::create()` + `node->save()`.
**Rationale**: padrão já estabelecido por `custom_configs_update_11054` (`_custom_configs_backfill_vagas_layout_fields`) e `11055` (`_custom_configs_backfill_vaga_carga_horaria`) para o mesmo CT `vagas`. Idempotente, sem dependência de `migrate_plus`, sem mapeamento YAML. Mensagem `t()` reporta atualizadas / já ok / total.
**Alternatives considered**: migration Drupal com `migrate_plus` (rejeitado: overhead de configuração para um backfill pontual; exige mapeamento YAML e rollback próprio); `drush php:script` (rejeitado: não versionado, não idempotente por padrão, fora da esteira de `updb`).

## R3 — Bump de ícone 32→48px vs. manter 32px

**Decision**: bump para 48px no CSS (`max-width/max-height` de `.vaga-detalhe__beneficio-icon img`) e nos atributos `width/height` do `<img>` nos dois templates Twig.
**Rationale**: FR-009 exige ícone ~48×48 conforme Figma; o CSS atual (linhas 217–231 de `vagas-detalhe.css`) usa `2.5rem` (40px) de container com `max-width/height: 32px`. Ajuste é puramente numérico no seletor existente; container sobe para `3rem` (48px) e `max-width/height` para 48px. Twig recebe `width="48" height="48"` como hint de layout (evita reflow).
**Alternatives considered**: manter 32px (rejeitado: descumpre FR-009); criar novo seletor `.vaga-detalhe__beneficio-icon--lg` (rejeitado: inflaria CSS sem ganho, pois todos os cards usam o mesmo tamanho); usar `object-fit: contain` sem mudar tamanho (rejeitado: ícone ficaria menor que o padrão Figma).

## R4 — Flip de `required` via Entity API vs. só YAML

**Decision**: hook carrega `field_config` de `node.vagas.field_vaga_beneficios`, seta `required: TRUE` e salva; `drush cex` na origem consolida em `config/sync/field.field.node.vagas.field_vaga_beneficios.yml`.
**Rationale**: garantia dupla — no destino, o `cim` aplica o `required: true` do YAML; o `updb` (que roda depois) faz o backfill. Se por algum motivo o `cim` falhar parcialmente, o hook ainda flipa em runtime. Reforça FR-005 e FR-010. A regra `estagio-fluxo-dev.mdc` veda editar YAML de `config/sync` à mão.
**Alternatives considered**: editar o YAML à mão (rejeitado: vedado); deixar o flip só no YAML sem tocar no hook (rejeitado: se o `cim` falhar, o backfill rodaria sem obrigatoriedade em vigor, estado inconsistente).

## R5 — Ordem dentro do hook: backfill antes do flip

**Decision**: hook faz (a) checar legibilidade dos 4 assets → (b) backfill das vagas vazias → (c) flip `required: TRUE` só se (a) e (b) concluídos.
**Rationale**: o Edge Case do spec exige que asset ausente **não** flipa `required` (evita campo obrigatório sem backfill concluído). A ordem backfill → flip garante que, ao tornar o campo obrigatório, todas as vagas já têm benefícios.
**Alternatives considered**: flipar `required` antes do backfill (rejeitado: entre o flip e o backfill, qualquer save de vaga vazia falharia por validação); flipar `required` em hook separado (rejeitado: aumenta complexidade sem ganho, pois o backfill é pré-requisito do flip).

## R6 — Ícones como managed files vs. SVG inline / FA

**Decision**: 4 PNGs em `modules/custom/custom_configs/assets/beneficios/beneficios-0{1..4}.png`, copiados para `public://beneficios/` via `_custom_configs_seed_file_from_module_asset()`, anexados a cada paragraph `beneficio_vaga_p` via `field_image`.
**Rationale**: FR-006 exige arquivos gerenciados a partir de assets versionados; o helper já existe e é idempotente (reusa managed file por URI, marca `permanent`, registra `file.usage`); o Twig já prevê `<img>` com fallback `fa-gift` quando não há ícone. Padrão idêntico ao de `beneficios-estudantes` (feature 022) e `diferenciais-quem-somos` (013).
**Alternatives considered**: SVG inline no Twig (rejeitado: não é arquivo gerenciado, descumpre FR-006); FA icons (rejeitado: os 4 ícones do Figma — mala de couro, maleta azul, talheres, relógio — não têm correspondentes exatos no FA, e FR-006 exige assets versionados).

## R7 — Nomes dos arquivos de ícone

**Decision**: `beneficios-01.png` … `beneficios-04.png` em `modules/custom/custom_configs/assets/beneficios/`.
**Rationale**: Assumption do spec fixa esse esquema; convenção do projeto usa `<feature>/icon-N.png` (beneficios-estudantes) ou `<feature>/icon-N.png` (diferenciais-quem-somos). O esquema `beneficios-NN.png` segue o padrão `slide-N.png` de `banner-para-empresas` quando há numeração com zero à esquerda.
**Alternatives considered**: `icon-1.png` … `icon-4.png` (rejeitado: diverge do Assumption do spec); nomes semânticos `plano-carreira.png` etc. (rejeitado: acopla nome de arquivo a copy editorial, dificulta renomear texto sem renomear arquivo).

## R8 — Textos padrão dos benefícios

**Decision**: títulos exatos conforme FR-002 — "Plano de Carreira", "Vale Transporte", "Vale Refeição", "Horário Flexível", nesta ordem, alinhados aos ícones 1–4 do Figma.
**Rationale**: FR-002 é explícito; o `field_text_simple` do paragraph já é `required: true`, garantindo que cada card tem título.
**Alternatives considered**: usar `t()` para os títulos no hook (rejeitado: `t()` em runtime sem contexto de locale do hook; o título fica em pt-BR no seed, que é o idioma canônico do projeto; tradução futura via `config_translation` se necessário).
