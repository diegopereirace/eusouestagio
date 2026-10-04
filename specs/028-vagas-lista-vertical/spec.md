# Feature Specification: Listagem Vertical de Vagas (`/vagas`)

**Feature Directory**: `specs/028-vagas-lista-vertical`  
**Created**: 2026-10-04  
**Status**: Draft  
**Input do usuário**: Refatorar a listagem principal de vagas em `/vagas` — abandonar os cards laranjas em grid e adotar lista vertical de cards brancos detalhados (Figma); campo booleano `field_vaga_destaque`; View `vagas` `page_1` com 5 itens/página, AJAX, ordenação destaques primeiro; badge “Destaque”; paginação AJAX; exclusão de escopo da sidebar de filtros e dos blocos “Recomendado para você” / “Melhore seu currículo”; deploy automatizado via `hook_update_N` + `drush cex`.

## Escopo

### Inclui

- Novo campo booleano **Destaque** (`field_vaga_destaque`) no content type `vagas`, default desmarcado (`False`), editável no formulário de criação/edição da vaga (empresas/admins).
- Atualização da View `vagas` display `page_1` (rota `/vagas`):
  - **5** itens por página;
  - **Use AJAX: Yes** (manter/garantir);
  - Ordenação primária por `field_vaga_destaque` DESC (destaques primeiro), secundária por data de criação DESC;
  - Estilo de linhas adequado à lista vertical (sem grid de 3 colunas);
  - Pager via AJAX: preferência por botão “Carregar mais” se o módulo estiver disponível; caso contrário, pager padrão do Drupal com AJAX.
- Novo layout de **card vertical branco** na listagem de `/vagas` apenas:
  - Container da listagem centralizado (largura máxima coerente com o Figma ~554–800px / coluna central);
  - Card: fundo branco, borda/sombra sutil, padding generoso, cantos arredondados, layout horizontal (logo + dados + CTA);
  - Badge “Destaque” (verde `#58A83C`, estrela + texto) no canto superior direito quando o campo estiver marcado; borda verde no card em destaque;
  - Conteúdo: logo da empresa (quando houver), título, empresa + localização + pill de regime, bolsa auxílio + carga horária, tags/tecnologias (quando houver), data relativa de publicação, botão de ação.
- Remoção/desabilitação de quaisquer block placements de “Recomendado para você” ou “Melhore seu currículo” visíveis em `/vagas`, se existirem.
- `hook_update_N` idempotente em `custom_configs.install` (próximo livre: `11045`) garantindo field storage/instance, displays, ajustes da View e limpeza dos blocos fora de escopo.
- Exportação estrutural (`drush cex` → `config/sync`) de fields, displays e View.
- Atualização cirúrgica do `PRD.md` (§3.1 content type `vagas` / §3.6 View `vagas` / rota `/vagas`) refletindo o campo Destaque e o novo layout da listagem.

### Fora

- Barra lateral esquerda de **Filtros** do Figma (não liberada; filtros expostos existentes da View podem permanecer como estão — sem redesign desta feature).
- Blocos / seções **“Recomendado para você”** e **“Melhore seu currículo”** (não implementar; remover se já houver placement).
- Contador “N vagas encontradas”, dropdown “Ordenar por”, toggle lista/grid e ícone de favoritar/bookmark do Figma (fora desta entrega; listagem vertical é o foco).
- Redesign dos cards laranja da **Home** (`block_1`), **similares** (`block_2`) ou destaque da landing **Para Estudantes** (`block_3`) — permanecem no visual atual.
- Alteração do Hero Search de `/vagas` (feature `027`) — permanece acima da listagem.
- Autenticação/fluxo completo de candidatura além do link do CTA para a página da vaga (ou rota de candidatura já existente).
- Instalação obrigatória de `views_infinite_scroll` (opcional; fallback documentado).
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/SCSS de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê lista vertical de cards brancos em `/vagas` (Priority: P1)

Visitante acessa `/vagas` e, abaixo do Hero Search, vê a listagem de vagas como uma coluna vertical de cards brancos detalhados — sem o grid de cards laranjas.

**Why this priority**: É a mudança visual principal e o motivo da feature.

**Independent Test**: Abrir `/vagas` em desktop e confirmar layout em lista vertical, cards brancos e ausência do grid laranja de 3 colunas.

**Acceptance Scenarios**:

1. **Given** a feature implantada, **When** o visitante abre `/vagas`, **Then** as vagas aparecem empilhadas verticalmente (uma por linha) em cards de fundo branco.
2. **Given** a listagem renderizada, **When** inspeciona o layout, **Then** não há o grid de 3 colunas com cards laranja da versão anterior nesta rota.
3. **Given** viewport desktop, **When** observa a listagem, **Then** o bloco de cards fica centralizado (não esticado em grid largo de três colunas).

---

### User Story 2 — Visitante identifica vagas em Destaque (Priority: P1)

Vagas marcadas como Destaque aparecem primeiro, com borda/badge verdes; as demais usam o card padrão sem badge “Destaque”.

**Why this priority**: Diferencial comercial e requisito explícito de ordenação + UI.

**Independent Test**: Marcar 1 vaga como Destaque no admin, limpar cache se necessário, abrir `/vagas` e verificar posição, badge e borda.

**Acceptance Scenarios**:

1. **Given** ao menos uma vaga com Destaque marcado e publicada, **When** o visitante abre `/vagas`, **Then** essa vaga aparece antes das não-destaque (a empates, mais recente primeiro).
2. **Given** uma vaga em Destaque, **When** o card é renderizado, **Then** exibe a badge verde “Destaque” (com ícone de estrela) no canto superior direito e borda verde no card.
3. **Given** uma vaga sem Destaque, **When** o card é renderizado, **Then** não exibe a badge “Destaque” nem a borda verde de destaque.
4. **Given** todas as vagas sem Destaque, **When** a listagem carrega, **Then** a ordem segue data de criação (mais recentes primeiro) e nenhum card mostra a badge.

---

### User Story 3 — Visitante lê os dados essenciais no card e age (Priority: P1)

Cada card mostra título, empresa, localização, regime, bolsa, carga horária (quando houver), tags e um botão que leva ao detalhe/candidatura da vaga.

**Why this priority**: Substitui o card laranja enxuto pelo card detalhado do Figma; sem esses dados o redesign não entrega valor.

**Independent Test**: Comparar um card da listagem com os dados do node correspondente no admin.

**Acceptance Scenarios**:

1. **Given** uma vaga com empresa, cidade e regime preenchidos, **When** o card renderiza, **Then** o visitante vê título, nome da empresa, localização e pill de regime.
2. **Given** uma vaga com bolsa (`field_text_simple`) e horários (`field_horarios`) preenchidos, **When** o card renderiza, **Then** essas informações aparecem nas áreas de bolsa auxílio e carga horária.
3. **Given** o botão de ação do card, **When** o visitante clica, **Then** navega para a página canônica da vaga (ou fluxo de candidatura já existente apontando para essa vaga).
4. **Given** vaga em Destaque, **When** observa o CTA, **Then** o rótulo prioritário é “Candidatura Rápida” (estilo sólido escuro); nas demais, “Ver Detalhes” (estilo outline) — ambos levam ao detalhe da vaga nesta entrega.

---

### User Story 4 — Visitante navega mais resultados via AJAX (Priority: P1)

Com mais de 5 vagas publicadas, a listagem mostra 5 por página e permite avançar/carregar mais sem full page reload (AJAX da View).

**Why this priority**: Requisito explícito de paginação + AJAX; melhora a exploração da lista longa.

**Independent Test**: Garantir ≥6 vagas publicadas; confirmar 5 na primeira “página” e carregamento adicional via pager AJAX.

**Acceptance Scenarios**:

1. **Given** 6 ou mais vagas publicadas que passam nos filtros padrão, **When** `/vagas` carrega, **Then** no máximo 5 cards aparecem inicialmente.
2. **Given** a primeira página carregada, **When** o visitante usa o controle de paginação / “Carregar mais”, **Then** novos resultados aparecem via AJAX (sem recarregar a página inteira de forma síncrona clássica).
3. **Given** `views_infinite_scroll` **não** instalado, **When** a View está com Use AJAX, **Then** o pager padrão do Drupal (números/próximo) funciona via AJAX — comportamento aceitável nesta feature.

---

### User Story 5 — Editor marca vaga como Destaque (Priority: P1)

Editor/empresa autenticada com permissão de editar vagas marca o checkbox “Destaque” no formulário; o valor persiste e reflete na listagem.

**Why this priority**: Sem o campo e o form display, o destaque visual não é operável.

**Independent Test**: Editar uma vaga, marcar/desmarcar Destaque, salvar, verificar listagem e reabrir o form.

**Acceptance Scenarios**:

1. **Given** o formulário de edição de uma vaga, **When** o editor visualiza os campos, **Then** existe o checkbox “Destaque” (desmarcado por padrão em vagas novas).
2. **Given** o editor marca Destaque e salva, **When** reabre a vaga, **Then** o campo permanece marcado.
3. **Given** o editor desmarca Destaque e salva, **When** a listagem `/vagas` atualiza, **Then** o card perde badge/borda de destaque e a ordenação deixa de priorizá-lo.

---

### User Story 6 — Deploy automatizado sem passos manuais (Priority: P1)

Após o deploy padrão, o campo, a View e o novo layout estão ativos em destino sem configuração manual no admin.

**Why this priority**: Regra permanente de deploy do projeto.

**Independent Test**: Ambiente desatualizado executa `cim` → `updb` → `cim` → `cr` e valida campo + listagem.

**Acceptance Scenarios**:

1. **Given** código e `config/sync` versionados, **When** roda o deploy padrão, **Then** `field_vaga_destaque` existe no bundle `vagas`, a View `page_1` usa 5 itens + AJAX + sort de destaque, e `/vagas` mostra o layout vertical.
2. **Given** segunda execução de `drush updb`, **When** o hook roda de novo, **Then** não duplica field storages, não corrompe a View e não recria placements indevidos (idempotência).
3. **Given** placements de “Recomendado para você” / “Melhore seu currículo” em `/vagas` (se existirem), **When** o hook roda, **Then** ficam removidos ou desabilitados.

### Edge Cases

- Vaga sem logo da empresa → card renderiza com placeholder ou omite a área de logo sem quebrar o layout.
- Vaga sem salário / sem horário / sem tags → seções correspondentes omitidas ou com fallback visual discreto; card permanece utilizável.
- Vaga sem empresa ou sem cidade → meta parcial (só o que existir); sem erro.
- Nenhuma vaga publicada → mensagem vazia já prevista pela View (“Nenhum resultado encontrado”); layout da página intacto.
- Todas as vagas em Destaque → ordenação secundária por data; todas com badge.
- Mais de 5 destaques → os 5 primeiros slots da página 1 podem ser todos destaques; demais páginas continuam a ordenação.
- Filtros GET do Hero Search (`title`, `cidade`, `cursos`) → continuam aplicando; a ordenação Destaque + data permanece sobre o resultado filtrado.
- Home / `block_3` / similares → cards laranja inalterados (regressão = falha).
- Reexecução do hook → no-op seguro.
- Blocos “Recomendado…” / “Melhore…” inexistentes → hook não falha.

## Requirements *(obrigatório)*

### Functional Requirements

**Campo Destaque**

- **FR-001**: O content type `vagas` DEVE ter um campo booleano rótulo “Destaque”, machine name `field_vaga_destaque`, valor padrão `False` (desmarcado).
- **FR-002**: O form display padrão (e demais displays de formulário usados por empresas/admins para editar vagas) DEVE expor o checkbox “Destaque” de forma editável.
- **FR-003**: Vagas existentes DEVEM permanecer com Destaque desmarcado após a criação do campo, até marcação editorial explícita.

**View `vagas` / `page_1`**

- **FR-004**: O display `page_1` DEVE listar **5** itens por página.
- **FR-005**: O display `page_1` DEVE ter **Use AJAX** habilitado.
- **FR-006**: A ordenação de `page_1` DEVE priorizar `field_vaga_destaque` DESC e, em seguida, data de criação DESC.
- **FR-007**: O estilo de linha de `page_1` DEVE deixar de forçar o grid de três colunas (classes tipo `col-md-6 col-lg-4`); a listagem DEVE fluir como coluna vertical única no container centralizado.
- **FR-008**: O pager de `page_1` DEVE operar com AJAX; se `views_infinite_scroll` estiver disponível e aprovado no plano, usar formato “Carregar mais”; senão, pager padrão Drupal via AJAX é aceitável.
- **FR-009**: A renderização de cada linha em `page_1` DEVE usar o novo template/layout de card vertical (não o card laranja `.item-vaga--destaque` da listagem atual).

**Card vertical (apresentação)**

- **FR-010**: A listagem DEVE ficar em container centralizado com largura máxima adequada à coluna central do Figma (ex.: equivalente a ~8/12 ou max-width ~560–720px), sem sidebar direita nesta feature.
- **FR-011**: Cada card DEVE ter fundo branco, padding generoso, cantos arredondados, borda sutil ou sombra leve, e `position` que permita badge absoluta.
- **FR-012**: Quando `field_vaga_destaque` estiver marcado, o card DEVE exibir badge verde `#58A83C` com ícone de estrela e texto “Destaque”, posicionada no canto superior direito, e borda verde no contorno do card.
- **FR-013**: O card DEVE exibir, quando houver dados: logo da empresa (quadrado ~64×64, object-fit cover), título da vaga, empresa, localização, pill de regime, bolsa auxílio, carga horária, tags/tecnologias ou benefícios disponíveis nos campos existentes.
- **FR-014**: O card DEVE exibir indicação de publicação relativa (ex.: “Publicada há X”) quando a data estiver disponível.
- **FR-015**: CTA do card em Destaque: botão sólido escuro “Candidatura Rápida”; CTA do card padrão: botão outline “Ver Detalhes”; ambos DEVEM apontar para a página da vaga (ou candidatura existente da vaga).
- **FR-016**: Estilos novos DEVEM ser escopados à listagem de `/vagas` (ex.: `.css-vagas-page` / classe do card vertical), **sem** alterar o visual dos cards laranja da Home, similares ou `block_3`.

**Escopo negativo e limpeza**

- **FR-017**: Esta feature **NÃO** DEVE implementar a sidebar de filtros do Figma nem redesenhar os filtros laterais existentes.
- **FR-018**: Esta feature **NÃO** DEVE implementar “Recomendado para você” nem “Melhore seu currículo”.
- **FR-019**: Se existirem block placements desses blocos exclusos na rota `/vagas`, o deploy DEVE removê-los ou desabilitá-los de forma idempotente.

**Deploy e documentação**

- **FR-020**: Um `hook_update_N` idempotente em `custom_configs` (`custom_configs_update_11045`) DEVE garantir: (1) field storage + instance `field_vaga_destaque` no bundle `vagas`; (2) form/view displays necessários; (3) atualização da View `page_1` (5 itens, AJAX, sorts, estilo de lista); (4) limpeza dos placements exclusos, se houver.
- **FR-021**: Alterações estruturais DEVEM ser exportadas com `drush cex` para `config/sync` e versionadas no Git ao final do desenvolvimento na origem.
- **FR-022**: O fluxo de deploy em destino DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr`; zero passos manuais no admin de produção.
- **FR-023**: A seção relevante do `PRD.md` DEVE ser atualizada de forma cirúrgica refletindo `field_vaga_destaque` e o novo comportamento/layout da listagem `/vagas`.

### Key Entities

- **Node `vagas`**: vaga de estágio; ganha atributo booleano Destaque (`field_vaga_destaque`); reutiliza campos existentes de bolsa (`field_text_simple`), horários (`field_horarios`), empresa (`field_empresa_u`), cidade/estado, regime (`field_regime_t`), cursos/tags multi-valor quando aplicável.
- **View `vagas` / display `page_1`**: listagem pública em `/vagas`; 5/página; AJAX; sort Destaque + created; container/estilos de lista vertical.
- **Empresa (`field_empresa_u`)**: fornece nome fantasia e, quando existir, logo/imagem para o card.
- **Placement de blocos fora de escopo**: quaisquer configs `block.block.*` de “Recomendado para você” / “Melhore seu currículo” restritas a `/vagas` — alvo de remoção/desabilitação se presentes.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, a listagem `/vagas` é reconhecível frente ao Figma (cards brancos verticais, badge Destaque, CTA) em ≥90% dos critérios do checklist visual desta feature.
- **SC-002**: 100% das vagas com Destaque marcado exibem badge “Destaque” e aparecem antes das não-destaque na ordenação padrão (sem filtro que altere o conjunto).
- **SC-003**: Com ≥6 vagas publicadas, a primeira carga de `/vagas` mostra no máximo 5 cards.
- **SC-004**: Avançar/carregar a página seguinte atualiza a listagem via AJAX em 100% das tentativas de aceite (sem full reload síncrono da página).
- **SC-005**: Editor consegue marcar/desmarcar Destaque no formulário da vaga em ≤2 cliques + salvar; o valor persiste em 100% dos testes de aceite.
- **SC-006**: Home, `block_3` (landing PE) e similares mantêm o visual laranja atual — zero regressão visual nos checklists dessas superfícies.
- **SC-007**: Sidebar de filtros do Figma e blocos “Recomendado para você” / “Melhore seu currículo” **não** estão presentes como entregas novas; se placements legados existiam em `/vagas`, ficam ausentes/desabilitados após deploy.
- **SC-008**: Ambiente desatualizado reproduz o comportamento com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cim -y` + `drush cr`, com zero passos manuais no painel.
- **SC-009**: Segunda execução de `drush updb` não duplica o field storage nem corrompe a View (idempotência confirmada).

## Assumptions

- Baseline atual de `page_1`: path `/vagas`, `items_per_page: 12`, `use_ajax: true`, estilo com `row_class` de grid (`col-12 col-md-6 col-lg-4`), cards laranja via template Views Custom Text `views-view-field--vagas--page-1--nothing.html.twig` (não via `node--vagas--teaser`).
- A implementação poderá (a) migrar `page_1` para row de conteúdo + view mode dedicado (`lista_vertical` / `teaser`), ou (b) substituir o Twig Custom Text de `page_1` pelo novo markup — desde que o resultado visual e o escopo (só `/vagas`) sejam atendidos; a escolha fica no `/speckit-plan`.
- Logo da empresa virá do usuário/empresa referenciado por `field_empresa_u` (campo de imagem já existente no perfil, se houver); se inexistente, placeholder/omissão.
- Tags do Figma (“React”, “+4 benefícios”) mapeiam aos campos multi-valor já existentes na vaga (`field_text_simple_multiple` / `field_text_simple_multiple_2` / cursos), sem criar taxonomia nova nesta feature; se `field_tecnologias_t` não estiver no bundle `vagas`, não inventar storage paralelo — reutilizar o que existir.
- Carga horária mapeia a `field_horarios`; bolsa a `field_text_simple` (já usado como salário/bolsa no produto).
- `views_infinite_scroll` **não** está no `composer.json` atual → default = pager AJAX padrão do Drupal; instalar o módulo só se o plano justificar e o produto aprovar a dependência.
- Hook: `custom_configs_update_11045` (último existente no install: `11044` da feature `027`).
- Hero Search (`027`) permanece acima da listagem; esta feature não o altera.
- Textos desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação (PHP `hook_update_N`, Twig, SCSS/CSS, `drush cex`) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
