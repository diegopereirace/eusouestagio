# Research: 037-vaga-sobre-empresa

## R1 — Storage novo `field_sobre_empresa_resumo` vs. instância de `field_text_simple`

**Decision**: criar apenas a field instance `node.empresa.field_text_simple` em cima do storage existente `field.storage.node.field_text_simple` (string, `max_length: 255`). Rótulo “Resumo curto”. Sem storage novo.  
**Rationale**: `.cursor/rules/estagio-fluxo-dev.mdc` manda reutilizar `field_text_simple` antes de criar storage de texto curto. A Assumption da spec já prevê esse desvio: o machine name `field_sobre_empresa_resumo` só vale se a regra de reuso não exigir o storage existente. O limite de 255 e o rótulo editorial permanecem. A instância em `vagas` (Salário), `contato` e `depoimento` não é afetada — no Drupal o valor vive por bundle.  
**Alternatives considered**: storage novo `field_sobre_empresa_resumo` (rejeitado: nome paralelo para o mesmo tipo string 255); derivar o subtítulo truncando `field_text_long_formatted` (rejeitado: FR-009 e o escopo “Fora” exigem campo próprio).

## R2 — Onde encaixar o cartão no Twig

**Decision**: include novo entre o fim da seção Benefícios e o início do bloco FAQ em `node--vagas--full.html.twig`. Se não houver FAQ, o cartão fica como último bloco da coluna principal.  
**Rationale**: a coluna hoje é Sobre a Vaga → Requisitos → Benefícios → FAQ (linhas ~115–207). FR-003 pede “acima do FAQ” e, sem FAQ, no fim da coluna. “Processo de Contratação” não existe no template.  
**Alternatives considered**: cartão dentro do header (rejeitado: escopo fora — header não muda); cartão na sidebar (rejeitado: FR-003 fixa a coluna principal); section inline de ~40 linhas no template full (rejeitado: 033 já extrai sidebar para `includes/`; o cartão tem a mesma densidade).

## R3 — Quando o cartão não renderiza

**Decision**: incluir o cartão somente se `empresa_node` (já filtrado por `isPublished()` no template) existir e `empresa_node.access('view')` for verdadeiro.  
**Rationale**: o template da 032/034 já zera `empresa_node` quando a referência está vazia ou o nó não está publicado. `access('view')` cobre o caso “publicado porém inacessível” do FR-002 sem alterar o header (fora de escopo). Visitante anônimo em nó publicado continua vendo o cartão, porque a permissão `access content` já libera o view.  
**Alternatives considered**: só `isPublished()` (rejeitado: não cobre inacessível); checar também `field_empresa_u` como fallback (rejeitado: FR-014 proíbe o vínculo legado).

## R4 — CSS morto do cartão removido em 034

**Decision**: substituir as regras `.vaga-detalhe__empresa-card`, `-card-top`, `-card-nome`, `-sobre` e o tamanho 56px de `.vaga-detalhe__empresa-logo img`. Manter `.vaga-detalhe__empresa` no grupo de `margin-bottom: 2rem` e usar essa classe na `<section>` nova. Não mexer em `.vaga-detalhe__empresa-nome` (meta do header) nem em `.vaga-detalhe__logo` (logo 112px do header).  
**Rationale**: o Twig atual não referencia essas classes de cartão. O CSS restante aplica `line-clamp: 4` e logo 56px `contain` — o oposto de FR-006 (80×80 `cover`) e do edge case que proíbe corte no meio do texto. Substituir evita duas “verdades” visuais no mesmo arquivo.  
**Alternatives considered**: deixar o CSS morto e criar BEM novo (`.vaga-detalhe__sobre-empresa-*`) (rejeitado: o line-clamp continuaria no arquivo e a seção `.vaga-detalhe__empresa` ficaria órfã); reativar o markup antigo e só acrescentar o botão (rejeitado: fundo branco, clamp e logo 56px descumprem o Figma).

## R5 — Botão “Ver Empresa”

**Decision**: `<a class="vaga-detalhe__empresa-cta">` para `url('entity.node.canonical', { node: empresa_node.id })`, rótulo `{{ 'Ver Empresa'|t }}`. Estilo no CSS do componente: contorno navy, radius 12px, `white-space: nowrap`, sem competir com o laranja de “Candidatar-se”.  
**Rationale**: Pathauto da empresa já é `/empresa/[node:title]` (034). A rota canônica entrega a URL limpa sem hardcode. `.btn-outline-azul-escuro` em `style.css` tem radius 4px e fonte 0.8rem, pensado para o painel — visualmente destoante do cartão.  
**Alternatives considered**: reusar `.btn-outline-azul-escuro` (rejeitado: métricas do painel); botão laranja igual ao CTA de candidatura (rejeitado: duas ações primárias na mesma página); link só no título (rejeitado: FR-007 exige a ação “Ver Empresa”).

## R6 — Resumo na página pública da empresa

**Decision**: o campo entra no form display e no view display `default` do CT `empresa`. `node--empresa--full.html.twig` não passa a ler `field_text_simple`.  
**Rationale**: FR-010 fala dos displays para quem edita. O template full de 034 é markup fechado (logo, título, descrição longa, View de vagas) e o escopo “Fora” veta redesenhar essa página. Como o template não imprime `{{ content }}`, colocar o componente no view display default não vaza o subtítulo na URL pública.  
**Alternatives considered**: imprimir o resumo como linha de apoio no header da página da empresa (rejeitado: fora de escopo e mistura os dois textos); omitir o view display (rejeitado: descumpre FR-010).

## R7 — Hook e número

**Decision**: `custom_configs_update_11057` chama três helpers novos (instância, displays, seed). Não altera `custom_configs_update_11052`.  
**Rationale**: `11056` é o último update. `11052` já rodou nos ambientes que têm a página da empresa; Drupal não reexecuta update antigo. O padrão do projeto é helper idempotente + `t()` com o que foi feito. O ensure de field copia o formato de `_custom_configs_ensure_empresa_pagina_fields()` (`FieldConfig::loadByName` + `FieldConfig::create` no storage já existente). O seed copia o critério de `_custom_configs_align_empresa_pagina_seed()` (UUID fixo, só se vazio).  
**Alternatives considered**: acrescentar o campo dentro dos helpers da 034 e pedir reinstall (rejeitado: update já aplicado não roda de novo); `drush php:script` (rejeitado: fora da esteira `updb`).

## R8 — Ordem dos pesos no form

**Decision**: na primeira inserção, resumo no weight `2`; descrição longa, cidade e estado sobem um peso se ainda estiverem em 2, 3 e 4. Reexecução não reordena.  
**Rationale**: o editor preenche o subtítulo do cartão ao lado da descrição longa, não depois do estado. Empurrar pesos só na criação evita brigar com um display que o editorial tenha reordenado depois.  
**Alternatives considered**: weight `5` no fim do form, sem mexer nos outros (rejeitado: o resumo fica longe do texto longo, que é o par editorial); weight `2` empatado com a descrição longa (rejeitado: ordem instável entre requests).

## R9 — Cache

**Decision**: nenhuma bolha de cache nova. O cartão lê o mesmo `empresa_node` que o header já lê no Twig.  
**Rationale**: o acesso a campo de entity reference no Twig do node já participa do render array do detalhe. Não há View, bloco ou lazy builder novo.  
**Alternatives considered**: preprocess PHP só para montar o cartão (rejeitado: o header já faz essa resolução no Twig; um preprocess duplicaria a regra de publicado/acesso).
