# Feature Specification: Detalhe da Vaga — Layout Duas Colunas (Refino)

**Feature Directory**: `specs/031-vagas-detalhe-refino`  
**Created**: 2026-10-07  
**Status**: Draft  
**Predecessor**: `030-vagas-detalhe-layout` (layout 8/4 inicial; modelo FAQ 1 pergunta = 1 node; stepper/perfil/CTA inline — a revisar)  
**Input do usuário**: Refatorar a página interna de detalhe da Vaga (`node--vagas--full`) conforme Figma — duas colunas (principal + sidebar); campos e FAQ coleção autossuficientes; CTA final via bloco `cta_v1`; **excluir** “Seu Match com a vaga”, “Processo de Contratação”, “Por que combina com você?” e “Seu Perfil”; alinhar Menu Principal (Header) ao layout global; deploy 100% automatizado.

## Escopo

### Inclui

- Layout de detalhe da vaga em duas colunas (principal ~2/3, sidebar ~1/3 em desktop; empilhadas no mobile).
- **Requisitos:** campo `field_vaga_requisitos` (Text long formatted) no Content Type `vagas`, com apresentação de lista com checks azuis (sem bullets padrão).
- **Benefícios:**
  - Paragraph Type `beneficio_vaga_p` com `field_image` (ícone) e `field_text_simple_small` (texto do benefício);
  - Campo `field_vaga_beneficios` (referência a paragraphs, ilimitado) no node `vagas`.
- **FAQ (Dúvidas Frequentes) — modelo coleção:**
  - Paragraph Type `faq_item_p` com `field_pergunta` (texto curto) e `field_resposta` (texto longo);
  - Content Type `faq` com `field_faq_itens` (lista ilimitada de `faq_item_p`) — **um único node armazena todas as perguntas** da coleção;
  - No node `vagas`, campo `field_vaga_faq` apontando para o Content Type `faq` com **cardinalidade 1** (o autor escolhe qual coleção FAQ exibir).
- Seed de **1** node `faq` de exemplo contendo **2** itens (perguntas: “Qual a duração do estágio?” e “Existe auxílio home office?”).
- **CTA final (“Pronto para o próximo passo?”):** reaproveitar o Custom Block Type `cta_v1` (instância + posicionamento), visível **apenas** em páginas de node `vagas`, no final da região de conteúdo; aparência escura (`#023C62`) isolada da versão clara usada na home / outras landings.
- Coluna principal: Header da Vaga; Sobre a Vaga; Requisitos; Benefícios; Sobre a Empresa (com botão “Ver Empresa”); Dúvidas Frequentes (accordion).
- Sidebar: Ações (Candidatar-se, Salvar, Compartilhar); Resumo da Vaga.
- Ajuste do Menu Principal (Header) para navegação clara e responsiva alinhada ao layout global.
- Remoção de quaisquer resquícios das seções foras de escopo no detalhe da vaga.
- Deploy automatizado (estrutura + seed + bloco CTA + placement) sem passos manuais no admin de produção; exportação de configurações estruturais; atualização cirúrgica do `PRD.md` nas seções de content types / campos / blocos afetados.

### Fora

- Blocos / seções **“Seu Match com a vaga”**, **“Processo de Contratação”**, **“Por que combina com você?”** e **“Seu Perfil”** (não implementar; remover se existirem no detalhe atual).
- Redesign da listagem `/vagas` e dos cards laranja de home / para-estudantes (permanecem como estão).
- Fluxo novo de candidatura, favoritos ou compartilhamento além do que o produto já oferece.
- Motor de recomendação / match / IA.
- FAQ institucional global do site (menu institucional) — apenas coleções FAQ referenciáveis por vaga.
- Código de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o detalhe da vaga em duas colunas sem seções excluídas (Priority: P1)

Visitante abre a URL canônica de uma vaga publicada e vê o layout do Figma: coluna principal com header e seções permitidas, sidebar com ações e resumo — **sem** Match, Processo de Contratação, “Por que combina” nem Seu Perfil.

**Why this priority**: É a mudança visual principal e o motivo da feature.

**Independent Test**: Abrir qualquer node `vagas` publicado em desktop (≥992px) e confirmar grid duas colunas + ausência das seções excluídas.

**Acceptance Scenarios**:

1. **Given** uma vaga publicada, **When** o visitante abre a página da vaga, **Then** o conteúdo está em container com linha de duas colunas (principal ~2/3, sidebar ~1/3 em desktop).
2. **Given** viewport mobile (&lt;992px), **When** abre a mesma página, **Then** as colunas empilham (sidebar abaixo do conteúdo principal) sem overflow horizontal.
3. **Given** a página renderizada, **When** inspeciona o conteúdo, **Then** não existem seções “Seu Match com a vaga”, “Processo de Contratação”, “Por que combina com você?” nem “Seu Perfil”.

---

### User Story 2 — Visitante lê header, descrição, requisitos, benefícios e empresa (Priority: P1)

No topo e no corpo, o visitante identifica empresa, localização, badges (regime/presencial, carga, bolsa, tempo desde a postagem), texto “Sobre a Vaga”, requisitos com checks, grid de benefícios e o card “Sobre a Empresa” com “Ver Empresa”.

**Why this priority**: Substitui / completa o detalhe pelos blocos informativos do Figma.

**Independent Test**: Preencher os campos em uma vaga de teste e comparar a página com o Figma.

**Acceptance Scenarios**:

1. **Given** vaga com empresa, logo (quando houver), localização, regime, horários e bolsa, **When** o header renderiza, **Then** exibe logo, título (h1), linha “Empresa • Localização” e pills horizontais (presencial/regime, carga, salário/bolsa, tempo de postagem).
2. **Given** a descrição longa da vaga preenchida, **When** a seção “Sobre a Vaga” renderiza, **Then** o texto formatado aparece abaixo do header.
3. **Given** requisitos preenchidos, **When** a seção Requisitos renderiza, **Then** a lista aparece com ícone de check azul (não bullet padrão do navegador).
4. **Given** um ou mais benefícios, **When** Benefícios renderiza, **Then** os cards aparecem em grade responsiva (2 colunas no mobile, até 4 no desktop) com ícone centralizado e texto abaixo.
5. **Given** empresa vinculada, **When** “Sobre a Empresa” renderiza, **Then** mostra box em tom azul claro com logo, descrição curta e botão outline “Ver Empresa”.
6. **Given** requisito, benefício ou empresa vazios, **When** a página carrega, **Then** a seção correspondente é omitida (sem heading órfão).

---

### User Story 3 — Visitante consulta FAQ da vaga em accordion (Priority: P1)

A vaga referencia **uma** coleção FAQ; as perguntas/respostas dessa coleção abrem/fecham no Accordion.

**Why this priority**: Novo modelo de dados (coleção) e requisito explícito de produto.

**Independent Test**: Associar o FAQ seed (com 2 itens) a uma vaga e expandir/colapsar cada item.

**Acceptance Scenarios**:

1. **Given** a vaga referencia uma coleção FAQ publicada com ≥1 item, **When** a seção “Dúvidas Frequentes” renderiza, **Then** cada item mostra a pergunta e, ao expandir, a resposta.
2. **Given** dois itens no accordion, **When** o visitante abre o segundo, **Then** o comportamento segue o padrão de accordion do site (apenas um fluxo previsível de abrir/fechar; IDs únicos por item).
3. **Given** a vaga sem FAQ associado, **When** a página carrega, **Then** a seção FAQ é omitida.
4. **Given** deploy recém-aplicado, **When** o editor lista conteúdo do tipo FAQ, **Then** existe ao menos **1** coleção seedada com as 2 perguntas de exemplo, sem duplicata após reexecução do deploy de dados.

---

### User Story 4 — Visitante age na sidebar e vê o resumo (Priority: P1)

Sidebar com ações de candidatura/salvar/compartilhar e card “Resumo da Vaga”.

**Why this priority**: Fecha o funil de conversão do Figma sem as seções excluídas.

**Independent Test**: Comparar sidebar com fluxos atuais de candidatura/salvar/compartilhar.

**Acceptance Scenarios**:

1. **Given** a sidebar, **When** observa Ações, **Then** vê botão primário largo “Candidatar-se” e, abaixo, dois botões de contorno dividindo espaço (“Salvar” e “Compartilhar”), reaproveitando os fluxos já existentes do produto.
2. **Given** candidato autenticado ainda não candidatatado, **When** clica “Candidatar-se”, **Then** o fluxo existente de candidatura é acionado.
3. **Given** visitante anônimo, **When** vê as ações, **Then** é direcionado ao login/cadastro (comportamento já existente), sem quebrar o layout.
4. **Given** a sidebar, **When** observa “Resumo da Vaga”, **Then** vê título “RESUMO DA VAGA” e pares rótulo/valor (Período, Bolsa Auxílio, Modelo, Vagas) alinhados em linha com espaço entre rótulo e valor.

---

### User Story 5 — Visitante vê o CTA final sem misturar com CTAs claros de outras páginas (Priority: P1)

No final da página de vaga, o visitante vê o CTA “Pronto para o próximo passo?” com fundo escuro; nas demais páginas, os CTAs claros existentes não mudam de aparência.

**Why this priority**: Conversão final do Figma + isolamento visual obrigatório.

**Independent Test**: Abrir uma vaga e uma landing (home / quem-somos / para-estudantes) e comparar CTAs.

**Acceptance Scenarios**:

1. **Given** página de vaga, **When** o visitante rola ao final da região de conteúdo, **Then** vê o CTA com título “Pronto para o próximo passo?”, corpo convidativo (“Sua jornada profissional começa aqui…” ou equivalente seedado) e botão “Candidatar-se Agora”, em fundo escuro `#023C62`.
2. **Given** página que não é detalhe de vaga, **When** inspeciona CTAs existentes, **Then** a instância do CTA de vagas **não** aparece e as versões claras de outras páginas permanecem inalteradas.
3. **Given** reexecução do deploy de dados, **When** o ambiente atualiza de novo, **Then** não duplica a instância nem o posicionamento do CTA de vagas.

---

### User Story 6 — Editor gerencia requisitos, benefícios e FAQ coleção (Priority: P1)

Editor/empresa com permissão edita os campos no formulário da vaga e mantém a coleção FAQ.

**Why this priority**: Sem operabilidade editorial os blocos do Figma ficam vazios em produção.

**Independent Test**: Editar FAQ coleção, associar à vaga, preencher requisitos/benefícios, salvar e reabrir a página pública.

**Acceptance Scenarios**:

1. **Given** o form de edição da vaga, **When** o editor visualiza os campos, **Then** consegue editar requisitos (texto longo formatado), benefícios (itens com ícone + texto) e selecionar **uma** coleção FAQ.
2. **Given** o editor altera itens da coleção FAQ referenciada, **When** publica, **Then** o accordion da página pública reflete a alteração.
3. **Given** o editor adiciona benefícios com ícone e texto, **When** salva, **Then** o grid de benefícios na página pública os exibe.

---

### User Story 7 — Header global permanece claro e responsivo (Priority: P2)

O Menu Principal no Header continua alinhado ao layout global: navegação clara no desktop e utilizável no mobile.

**Why this priority**: Pedido explícito de alinhamento global; não é o core do detalhe, mas evita regressão de navegação.

**Independent Test**: Abrir home e detalhe de vaga em desktop e mobile; percorrer links do menu.

**Acceptance Scenarios**:

1. **Given** desktop, **When** o visitante usa o Header, **Then** o menu principal está visível, legível e alinhado ao padrão visual global.
2. **Given** mobile, **When** abre o menu, **Then** a navegação permanece acessível (padrão offcanvas/hamburger já do site) sem quebrar o sticky do Header.

---

### User Story 8 — Deploy automatizado sem passos manuais (Priority: P1)

Após o deploy padrão, tipos, campos, displays, seed FAQ (1 coleção / 2 itens), instância + placement do CTA e o layout refinado estão ativos sem configuração manual no admin de produção.

**Why this priority**: Regra permanente de deploy do projeto.

**Independent Test**: Ambiente desatualizado executa o fluxo de deploy do projeto e valida estrutura + página.

**Acceptance Scenarios**:

1. **Given** código e configurações estruturais versionadas, **When** roda o deploy padrão, **Then** o modelo FAQ coleção, os campos de requisitos/benefícios/FAQ da vaga, o seed e o CTA de vagas existem; a página full usa o layout refinado sem as seções excluídas.
2. **Given** segunda execução do passo de atualização de dados, **When** o hook/update roda de novo, **Then** não duplica tipos, fields, seeds FAQ, bloco CTA nem placement (idempotência).
3. **Given** conteúdo editorial já divergente do seed, **When** o update roda, **Then** não sobrescreve esse conteúdo; preenche apenas ausente/vazio.

### Edge Cases

- Vaga sem logo da empresa → header e card da empresa usam placeholder ou omitem a área de imagem sem quebrar o layout.
- Vaga sem empresa → omite “Sobre a Empresa”; header usa fallback (“Anônima” ou equivalente já usado).
- Campos novos vazios → seções omitidas; página permanece utilizável só com descrição/header existentes.
- FAQ referenciado unpublished → não aparece para anônimos (respeitar regras de acesso do produto).
- Benefício sem ícone → card com placeholder ou só texto, sem erro.
- Texto “Sobre a Empresa” muito longo → truncamento visual + “Ver Empresa”.
- “Ver Empresa” sem página pública dedicada → destino documentado nas Assumptions (sem inventar rota nova nesta feature).
- Candidato já candidatatado / vaga já salva → estados existentes dos botões preservados.
- Reexecução do update → no-op seguro; seed FAQ coleção e CTA não duplicam.
- Listagem `/vagas` e cards home/landing → sem regressão visual.
- Conteúdo legado do modelo FAQ 030 (1 node = 1 pergunta) → não bloqueia o deploy; a fonte canônica passa a ser a coleção; migração/associação detalhada fica para o plano.

## Requirements *(obrigatório)*

### Functional Requirements

**Modelo FAQ (coleção)**

- **FR-001**: O sistema DEVE ter o Content Type `faq` capaz de armazenar uma **coleção** de perguntas via `field_faq_itens` (lista ilimitada de itens `faq_item_p`).
- **FR-002**: Cada item FAQ DEVE ter `field_pergunta` (texto curto) e `field_resposta` (texto longo).
- **FR-003**: O deploy DEVE cadastrar de forma idempotente **um** node `faq` de exemplo contendo **dois** itens com as perguntas “Qual a duração do estágio?” e “Existe auxílio home office?” (respostas placeholder razoáveis).
- **FR-004**: No Content Type `vagas`, `field_vaga_faq` DEVE referenciar o Content Type `faq` com **cardinalidade 1**.

**Campos de conteúdo da vaga**

- **FR-005**: `field_vaga_requisitos` DEVE ser Text long formatted no bundle `vagas` (lista editável pelo autor; checks visuais no tema).
- **FR-006**: `field_vaga_beneficios` DEVE referenciar paragraphs `beneficio_vaga_p` com cardinalidade ilimitada.
- **FR-007**: O paragraph `beneficio_vaga_p` DEVE ter ícone (`field_image`) e texto do benefício (`field_text_simple_small`).
- **FR-008**: Form/view displays de `vagas`, `faq` e dos paragraphs envolvidos DEVEM permitir editar/visualizar os campos desta feature conforme o modo.

**Layout da página de detalhe**

- **FR-009**: O detalhe full da vaga DEVE usar layout em duas colunas (principal e sidebar) conforme o Figma, empilhando no mobile.
- **FR-010**: O header DEVE exibir logo (quando houver), título h1, “Empresa • Localização” e pills de regime, carga horária, bolsa e tempo relativo desde a publicação.
- **FR-011**: “Sobre a Vaga” DEVE renderizar a descrição longa formatada da vaga.
- **FR-012**: “Requisitos” DEVE renderizar `field_vaga_requisitos` com checks azuis no lugar de bullets padrão.
- **FR-013**: “Benefícios” DEVE renderizar `field_vaga_beneficios` em grade responsiva (2 cols mobile / até 4 desktop) com cards brancos de borda sutil, ícone (máx. ~48px) e texto.
- **FR-014**: “Sobre a Empresa” DEVE usar dados da empresa vinculada (logo, nome, descrição curta) e CTA outline “Ver Empresa”.
- **FR-015**: “Dúvidas Frequentes” DEVE ler a coleção referenciada em `field_vaga_faq` e listar seus itens em Accordion, com identificadores únicos por item.
- **FR-016**: A sidebar DEVE incluir card de ações (Candidatar-se largo + Salvar + Compartilhar) e card “Resumo da Vaga” (fundo claro ~`#F3F6F9`, pares Período / Bolsa Auxílio / Modelo / Vagas).
- **FR-017**: Esta feature **NÃO** DEVE implementar nem manter no detalhe: “Seu Match com a vaga”, “Processo de Contratação”, “Por que combina com você?” nem “Seu Perfil”.

**CTA final e Header**

- **FR-018**: O CTA final DEVE reutilizar o tipo de bloco `cta_v1`, com textos do layout (título “Pronto para o próximo passo?”, corpo convidativo, botão “Candidatar-se Agora”), posicionado no final da região de conteúdo e restrito a nodes do tipo `vagas`.
- **FR-019**: O estilo escuro `#023C62` do CTA de vagas DEVE ser isolado para não afetar a versão clara usada em outras páginas.
- **FR-020**: O Menu Principal (Header) DEVE permanecer claro e responsivo, alinhado ao layout global (desktop + mobile).

**Estilo, dados e exclusões**

- **FR-021**: Estilos novos do detalhe DEVEM ser escopados à página de detalhe da vaga, sem alterar listagem `/vagas` nem cards laranja de home/landing.
- **FR-022**: Resumo da Vaga DEVE mapear Período, Bolsa Auxílio e Modelo a partir dos dados já existentes da vaga; “Vagas” usa “Não informado” (ou omite a linha) enquanto não houver campo dedicado — sem criar campo novo nesta feature.

**Deploy e documentação**

- **FR-023**: Um update idempotente no módulo de configs customizadas DEVE garantir tipos, campos, displays, seed FAQ coleção, instância `cta_v1` de vagas e placement com visibilidade correta.
- **FR-024**: Alterações estruturais DEVEM ser exportadas para o diretório de sync de configurações e versionadas no Git ao final do desenvolvimento na origem.
- **FR-025**: O fluxo de deploy em destino DEVE seguir a receita padrão do projeto (`cim` → `updb` → `cim` → `cr`); zero passos manuais no admin de produção.
- **FR-026**: O `PRD.md` DEVE ser atualizado de forma cirúrgica com o modelo FAQ coleção, os campos refinados de `vagas`, o paragraph `faq_item_p` / `field_text_simple_small` e o CTA de vagas.
- **FR-027**: Se a predecessor `030` tiver deixado modelo FAQ 1:1, stepper/perfil/CTA inline ou tipo de campo de requisitos incompatível, o plano de implementação DEVE prever migração/ajuste idempotente sem perda editorial injustificada.

### Key Entities

- **Node `faq` (coleção)**: agrupa vários itens pergunta/resposta; referenciável por vagas (1 coleção por vaga).
- **Paragraph `faq_item_p`**: uma pergunta + uma resposta.
- **Node `vagas`**: requisitos (texto longo), benefícios (paragraphs), referência a uma coleção FAQ; reutiliza descrição, regime, horários, bolsa, empresa.
- **Paragraph `beneficio_vaga_p`**: ícone + texto curto do benefício.
- **User empresa**: logo, nome fantasia, descrição para o card “Sobre a Empresa”.
- **Bloco `cta_v1` (instância vagas)**: CTA final escuro reutilizando o tipo existente.
- **Candidatura / vagas salvas (existentes)**: ações da sidebar continuam dependendo desses fluxos.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, a página de detalhe da vaga é reconhecível frente ao Figma (header, seções permitidas, FAQ, CTA final, sidebar) em ≥90% dos critérios do checklist visual desta feature.
- **SC-002**: 100% das seções com dados preenchidos (requisitos, benefícios, FAQ, empresa) aparecem; 100% das seções sem dados ficam ocultas.
- **SC-003**: Accordion FAQ abre/fecha corretamente para 100% dos itens da coleção associada em testes de aceite (teclado e mouse).
- **SC-004**: Blocos “Seu Match…”, “Processo de Contratação”, “Por que combina…” e “Seu Perfil” estão ausentes em 100% das páginas de vaga após a feature.
- **SC-005**: O CTA escuro de vagas aparece em 100% das páginas de detalhe de vaga testadas e em 0% das landings de controle (home / quem-somos / para-estudantes); CTAs claros dessas landings não regredem visualmente.
- **SC-006**: Editor consegue associar **uma** coleção FAQ e editar requisitos/benefícios no formulário da vaga; alterações refletem na página pública após salvar (e limpeza de cache se necessário) em 100% dos testes de aceite.
- **SC-007**: Ambiente desatualizado reproduz estrutura + layout com apenas o fluxo de deploy padrão do projeto, com zero passos manuais no painel.
- **SC-008**: Segunda execução do update de dados não duplica Content Type, fields, a coleção FAQ seed, o bloco CTA nem o placement (idempotência confirmada).
- **SC-009**: Listagem `/vagas` e cards laranja de home/landing não regredem no checklist visual dessas superfícies.
- **SC-010**: Header permanece utilizável em desktop e mobile no checklist de navegação global (≥1 smoke test home + detalhe de vaga).

## Assumptions

- Esta feature **refina** o detalhe entregue em `030-vagas-detalhe-layout`: o shell duas colunas pode ser reaproveitado; o modelo FAQ 1:1, stepper, card de perfil e CTA inline do Twig saem do escopo de produto.
- Template alvo: view mode `full` do node `vagas` (`node--vagas--full` ou equivalente que isole só o detalhe).
- Benefícios usam paragraph dedicado `beneficio_vaga_p` com texto em `field_text_simple_small` (contrato do pedido).
- Requisitos: Text long formatted (não lista multi-valor de strings), porque o autor edita HTML/lista e o tema aplica checks via CSS.
- FAQ: **coleção** (1 node com N itens), não 1 node por pergunta.
- “Vagas disponíveis” no resumo: sem campo estrutural nesta feature; linha com “Não informado” ou omitida.
- “Ver Empresa”: se não houver página pública de empresa no produto, o plano documentará o melhor destino existente (ex.: perfil canônico do usuário empresa) ou omitirá o link mantendo o botão só quando houver destino válido — sem criar rota nova nesta feature.
- CTA final via tipo `cta_v1` já existente; isolamento visual por instância/placement (padrão já usado em outras landings).
- Seed FAQ: 1 coleção com 2 itens; associação automática a todas as vagas **não** é obrigatória — o editor associa; o plano pode opcionalmente amarrar a vagas demo se o campo estiver vazio.
- Cores de marca: azul escuro `#023C62`, laranja de CTA/ação, fundo resumo ~`#F3F6F9`.
- Textos desta fase somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Número exato do `hook_update_N` e detalhes de migrate legado→novo ficam para `/speckit-plan` (último hook conhecido da 030: `11047`).
- Entregáveis de implementação (PHP, Twig, CSS, `drush cex`) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, **não** nesta especificação.
- Skills de domínio (`mattpocock/skills@domain-modeling`) orientam o glossário: **FAQ** = coleção; **FAQ Item** = pergunta+resposta; **FAQ da Vaga** = referência única à coleção.
