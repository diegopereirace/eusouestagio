# Feature Specification: Detalhe da Vaga — Layout Duas Colunas

**Feature Directory**: `specs/030-vagas-detalhe-layout`  
**Created**: 2026-10-04  
**Status**: Draft  
**Input do usuário**: Refatorar a página interna de detalhe da Vaga (template full do node `vagas`) conforme Figma — grid duas colunas (conteúdo 8 / sidebar 4); novo Content Type `faq` + ER `field_vaga_faq`; campos `field_vaga_etapas_processo`, `field_vaga_requisitos`, `field_vaga_beneficios` (paragraph `beneficio_vaga_p`); seções Header, Sobre, Requisitos (checks), Benefícios (grid), Stepper de contratação, Sobre a Empresa, FAQ Accordion, CTA final; sidebar com ações, resumo e card de perfil; **excluir** “Seu Match com a vaga” e “Por que combina com você?”; deploy 100% automatizado via `hook_update_N` + `drush cex`.

## Escopo

### Inclui

- Novo Content Type **FAQ** (`faq`): pergunta = `title` nativo; resposta = `field_resposta` (Text long); form/view displays básicos.
- Seed idempotente de **2 nodes FAQ** de exemplo (“Qual a duração do estágio?” e “Existe auxílio home office?”) com UUIDs fixos.
- No Content Type `vagas`, novos campos:
  - `field_vaga_faq` — Entity Reference → node bundle `faq`, cardinalidade ilimitada;
  - `field_vaga_etapas_processo` — texto simples multi-valor (etapas do processo, ex.: Inscrição, Triagem, …);
  - `field_vaga_requisitos` — texto simples multi-valor (uma linha por requisito; bullets visuais no tema);
  - `field_vaga_beneficios` — Entity Reference Revisions → paragraph `beneficio_vaga_p`, cardinalidade ilimitada.
- Novo Paragraph **`beneficio_vaga_p`**: ícone (`field_image` reutilizado) + título (`field_text_simple` reutilizado).
- Form/view displays do node `vagas` atualizados para expor os novos campos (editores/empresas).
- Refatoração do template de detalhe da vaga (view mode `full`) para o layout Figma:
  - Container `.container.py-5` + `.row.g-4`;
  - Coluna principal `.col-12.col-lg-8`;
  - Sidebar `.col-12.col-lg-4`.
- Seções da coluna principal: Header da Vaga; Sobre a Vaga; Requisitos (checks azuis); Benefícios (grid 2/4 cols); Processo de Contratação (stepper; último passo `#FD7B1A`); Sobre a Empresa (card claro + “Ver Empresa”); Dúvidas Frequentes (Accordion Bootstrap 5); CTA “Pronto para o próximo passo?” (copy/markup fixo no Twig, fundo `#023C62`, botão laranja).
- Sidebar: Ações (Candidatar-se / Salvar / Compartilhar — reaproveitando fluxos já existentes); Resumo da Vaga; Seu Perfil (markup de completude com barra + “Completar agora”, estático se a lógica de % ainda não existir).
- CSS/SCSS escopado ao detalhe da vaga (checks de requisitos + stepper + cards do Figma).
- `hook_update_N` idempotente em `custom_configs.install` (`custom_configs_update_11047`) garantindo tipos, campos, displays, seeds FAQ e (quando aplicável) associação dos FAQs de exemplo a vagas seed/demo sem sobrescrever conteúdo editorial divergente.
- Exportação estrutural (`drush cex` → `config/sync`) e atualização cirúrgica do `PRD.md` (§3.1 content types / campos `vagas`).

### Fora

- Blocos / seções **“Seu Match com a vaga”** e **“Por que combina com você?”** (não implementar; não criar campos/markup para eles).
- Redesign da listagem `/vagas` (features `028`/`029`) — permanece como está.
- Fluxo novo de candidatura, favoritos ou compartilhamento além do que já existe (`js-candidatar-vaga`, `js-salvar-vaga`, links sociais).
- Cálculo real de “% de perfil completo” se ainda não houver capa/serviço no projeto — nesta entrega basta markup preparado (barra + CTA) com valor estático ou variável opcional do preprocess.
- Motor de recomendação / match / IA.
- FAQ global do site (menu institucional); apenas FAQs referenciados por vaga.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/CSS de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o detalhe da vaga em duas colunas (Priority: P1)

Visitante abre a URL canônica de uma vaga publicada e vê o layout do Figma: coluna principal com header e seções, sidebar com ações e resumo.

**Why this priority**: É a mudança visual principal e o motivo da feature.

**Independent Test**: Abrir qualquer node `vagas` publicado em desktop (≥992px) e confirmar grid 8/4, cards e ausência dos blocos de Match.

**Acceptance Scenarios**:

1. **Given** uma vaga publicada, **When** o visitante abre a página da vaga, **Then** o conteúdo está em container com linha de duas colunas (principal ~2/3, sidebar ~1/3 em desktop).
2. **Given** viewport mobile (&lt;992px), **When** abre a mesma página, **Then** as colunas empilham (sidebar abaixo do conteúdo principal) sem overflow horizontal.
3. **Given** a página renderizada, **When** inspeciona o conteúdo, **Then** não existem seções “Seu Match com a vaga” nem “Por que combina com você?”.

---

### User Story 2 — Visitante lê header, descrição, requisitos e benefícios (Priority: P1)

No topo e no corpo, o visitante identifica empresa, localização, badges (regime, carga, bolsa, tempo desde a postagem), texto “Sobre a Vaga”, lista de requisitos com ícones de check e grid de benefícios com ícone + título.

**Why this priority**: Substitui o detalhe atual pelos blocos informativos do Figma; sem eles a página não entrega o valor visual.

**Independent Test**: Preencher os campos novos/existentes em uma vaga de teste e comparar a página com o Figma.

**Acceptance Scenarios**:

1. **Given** vaga com empresa, logo (quando houver), cidade/estado, regime, horários e bolsa, **When** o header renderiza, **Then** exibe logo, título (h1), nome da empresa, localização e badges horizontais (regime, carga, bolsa, “Postado há…”).
2. **Given** `field_text_long_formatted` preenchido, **When** a seção “Sobre a Vaga” renderiza, **Then** o texto formatado aparece abaixo do header.
3. **Given** um ou mais itens em `field_vaga_requisitos`, **When** a seção Requisitos renderiza, **Then** cada item aparece em lista com ícone de check azul (não bullet padrão).
4. **Given** um ou mais paragraphs em `field_vaga_beneficios`, **When** Benefícios renderiza, **Then** os cards aparecem em grid responsivo (2 colunas no mobile, até 4 no desktop) com ícone centralizado e título abaixo.
5. **Given** requisito ou benefício vazio, **When** a página carrega, **Then** a seção correspondente é omitida (sem heading órfão).

---

### User Story 3 — Visitante entende o processo de contratação (Priority: P1)

Etapas cadastradas em `field_vaga_etapas_processo` aparecem como stepper horizontal numerado; a última etapa usa destaque laranja `#FD7B1A`.

**Why this priority**: Elemento visual explícito do Figma e novo dado estrutural.

**Independent Test**: Cadastrar 5 etapas em uma vaga e verificar círculos 1…N, labels e cor do último.

**Acceptance Scenarios**:

1. **Given** N etapas preenchidas (N ≥ 1), **When** “Processo de Contratação” renderiza, **Then** há N círculos numerados com rótulos e uma linha conectora.
2. **Given** N ≥ 2, **When** observa o stepper, **Then** apenas o **último** círculo usa a cor de destaque laranja `#FD7B1A`.
3. **Given** campo de etapas vazio, **When** a página carrega, **Then** a seção do processo é omitida.

---

### User Story 4 — Visitante consulta FAQ da vaga em accordion (Priority: P1)

Perguntas referenciadas em `field_vaga_faq` abrem/fecham no Accordion Bootstrap 5; título do FAQ = pergunta; `field_resposta` = resposta.

**Why this priority**: Novo Content Type + ER; requisito explícito de produto.

**Independent Test**: Associar os 2 FAQs seed a uma vaga e expandir/colapsar cada item.

**Acceptance Scenarios**:

1. **Given** a vaga referencia ≥1 FAQ publicado, **When** a seção “Dúvidas Frequentes” renderiza, **Then** cada item mostra a pergunta e, ao expandir, a resposta.
2. **Given** dois itens no accordion, **When** o visitante abre o segundo, **Then** o comportamento segue o padrão Accordion Bootstrap 5 (IDs de collapse únicos por item).
3. **Given** `field_vaga_faq` vazio, **When** a página carrega, **Then** a seção FAQ é omitida.
4. **Given** deploy recém-aplicado, **When** o editor lista conteúdo do tipo FAQ, **Then** existem ao menos os 2 exemplos seedados (perguntas do Figma), sem duplicata após reexecução do update.

---

### User Story 5 — Visitante vê empresa, CTA final e age na sidebar (Priority: P1)

Card “Sobre a Empresa” (logo, nome, texto truncado, “Ver Empresa”), CTA final “Pronto para o próximo passo?”, sidebar com Candidatar-se / Salvar / Compartilhar, Resumo da Vaga e card Seu Perfil.

**Why this priority**: Fecha o funil de conversão e a sidebar do Figma.

**Independent Test**: Comparar sidebar e CTA com fluxos atuais de candidatura/salvar; abrir “Ver Empresa” se rota existir.

**Acceptance Scenarios**:

1. **Given** `field_empresa_u` preenchido com `field_sobre_empresa`, **When** “Sobre a Empresa” renderiza, **Then** mostra logo (se houver), nome, texto truncado e botão/link “Ver Empresa”.
2. **Given** a página completa, **When** o visitante rola ao final da coluna principal, **Then** vê o card CTA escuro `#023C62` com botão laranja “Candidatar-se Agora”.
3. **Given** candidato autenticado ainda não candidatatado, **When** clica “Candidatar-se” (sidebar) ou o CTA equivalente, **Then** o fluxo existente de candidatura é acionado (mesmas classes/hooks já usados no site).
4. **Given** visitante anônimo, **When** vê as ações, **Then** é direcionado ao login/cadastro (comportamento já existente), sem quebrar o layout.
5. **Given** a sidebar, **When** observa “Resumo da Vaga”, **Then** vê pares rótulo/valor (Período, Bolsa Auxílio, Modelo, Vagas) alinhados em linha (`espaço entre` rótulo e valor).
6. **Given** a sidebar, **When** observa “Seu Perfil”, **Then** há barra de progresso e link “Completar agora” (valor % estático ou variável de preprocess se disponível).

---

### User Story 6 — Editor gerencia FAQ, etapas, requisitos e benefícios (Priority: P1)

Editor/empresa com permissão edita os novos campos no formulário da vaga e cria/associa nodes FAQ.

**Why this priority**: Sem operabilidade editorial os blocos do Figma ficam vazios em produção.

**Independent Test**: Criar FAQ, associar à vaga, preencher etapas/requisitos/benefícios, salvar e reabrir.

**Acceptance Scenarios**:

1. **Given** o form de edição da vaga, **When** o editor visualiza os campos, **Then** consegue editar FAQ (referência), etapas, requisitos e benefícios (paragraphs com ícone + título).
2. **Given** o editor cria um node FAQ e associa à vaga, **When** publica, **Then** o accordion da página pública reflete a associação.
3. **Given** o editor adiciona paragraphs de benefício com ícone e título, **When** salva, **Then** o grid de benefícios na página pública os exibe.

---

### User Story 7 — Deploy automatizado sem passos manuais (Priority: P1)

Após o deploy padrão, Content Type FAQ, campos, displays, seeds e o novo layout estão ativos sem configuração manual no admin de produção.

**Why this priority**: Regra permanente de deploy do projeto.

**Independent Test**: Ambiente desatualizado executa `cim` → `updb` → `cim` → `cr` e valida estrutura + página.

**Acceptance Scenarios**:

1. **Given** código e `config/sync` versionados, **When** roda o deploy padrão, **Then** o tipo `faq`, o paragraph `beneficio_vaga_p`, os quatro campos em `vagas` e os 2 FAQs seed existem; a página full usa o novo layout.
2. **Given** segunda execução de `drush updb`, **When** o hook roda de novo, **Then** não duplica tipos, fields, displays nem nodes FAQ seed (idempotência).
3. **Given** vaga editorial já com requisitos/benefícios nos campos legados, **When** o hook roda, **Then** não apaga nem sobrescreve conteúdo divergente; migração opcional só preenche campos novos quando estiverem vazios.

### Edge Cases

- Vaga sem logo da empresa → header e card da empresa usam placeholder ou omitem a área de imagem sem quebrar o layout.
- Vaga sem empresa (`field_empresa_u` vazio) → omite “Sobre a Empresa”; header mostra fallback (“Anônima” ou equivalente já usado).
- Campos novos vazios → seções omitidas; página permanece utilizável só com descrição/header existentes.
- Uma única etapa no processo → stepper com um círculo (ainda com destaque laranja no único/último).
- Muitas etapas (≥6) → stepper permanece legível (quebra de linha / scroll horizontal discreto no mobile, sem sobrepor labels).
- FAQ referenciado unpublished → não aparece para anônimos (respeitar acesso do Drupal).
- Benefício sem ícone → card com placeholder ou só título, sem erro.
- Texto “Sobre a Empresa” muito longo → truncamento visual (ex.: ~160–200 caracteres ou CSS line-clamp) + “Ver Empresa”.
- Candidato já candidatatado / vaga já salva → estados existentes dos botões preservados.
- Reexecução do hook → no-op seguro; seeds FAQ não duplicam (UUID fixo).
- Listagem `/vagas` e cards home/landing → sem regressão visual.

## Requirements *(obrigatório)*

### Functional Requirements

**Content Type FAQ**

- **FR-001**: O sistema DEVE ter o Content Type `faq` (rótulo “FAQ”), com `title` como pergunta e campo `field_resposta` (Text long) como resposta.
- **FR-002**: Form e view displays padrão de `faq` DEVEM permitir criar/editar pergunta e resposta.
- **FR-003**: O deploy DEVE cadastrar de forma idempotente **dois** nodes `faq` de exemplo com perguntas “Qual a duração do estágio?” e “Existe auxílio home office?” (respostas placeholder razoáveis; UUIDs fixos).

**Campos no bundle `vagas`**

- **FR-004**: `field_vaga_faq` DEVE referenciar nodes do bundle `faq` com cardinalidade ilimitada.
- **FR-005**: `field_vaga_etapas_processo` DEVE ser texto simples multi-valor (ilimitado) para as etapas do processo.
- **FR-006**: `field_vaga_requisitos` DEVE ser texto simples multi-valor (ilimitado), um item por requisito.
- **FR-007**: `field_vaga_beneficios` DEVE referenciar paragraphs `beneficio_vaga_p` com cardinalidade ilimitada.
- **FR-008**: O paragraph `beneficio_vaga_p` DEVE ter ícone (reuso de storage `field_image`) e título (reuso de storage `field_text_simple`).
- **FR-009**: Form/view displays de `vagas` DEVEM expor os quatro campos novos de forma editável/visível conforme o modo.

**Layout da página de detalhe (view mode full)**

- **FR-010**: O template full da vaga DEVE usar container + row com coluna principal `.col-12.col-lg-8` e sidebar `.col-12.col-lg-4`.
- **FR-011**: O header DEVE exibir logo (quando houver), título h1, nome da empresa, localização e badges horizontais de regime, carga horária, bolsa e tempo relativo desde a publicação.
- **FR-012**: “Sobre a Vaga” DEVE renderizar `field_text_long_formatted`.
- **FR-013**: “Requisitos” DEVE renderizar `field_vaga_requisitos` com ícones de check azuis no lugar de bullets.
- **FR-014**: “Benefícios” DEVE renderizar `field_vaga_beneficios` em grid responsivo (2 cols mobile / até 4 desktop) com cards brancos de borda sutil, ícone e título.
- **FR-015**: “Processo de Contratação” DEVE iterar `field_vaga_etapas_processo` em stepper numerado; o último círculo DEVE usar `#FD7B1A`.
- **FR-016**: “Sobre a Empresa” DEVE usar dados de `field_empresa_u` (logo, nome, `field_sobre_empresa` truncado) e CTA “Ver Empresa” apontando para a rota/perfil público da empresa quando existir; se não houver rota pública, o link aponta para destino já usado no produto ou fica omitido com fallback documentado no plano.
- **FR-017**: “Dúvidas Frequentes” DEVE renderizar `field_vaga_faq` com Accordion Bootstrap 5 (pergunta = título; resposta = `field_resposta`); IDs de collapse únicos.
- **FR-018**: O CTA final “Pronto para o próximo passo?” DEVE ser markup fixo no Twig (fundo `#023C62`, botão laranja “Candidatar-se Agora”) e acionar o mesmo destino/fluxo do botão principal de candidatura.
- **FR-019**: A sidebar DEVE incluir card de ações (Candidatar-se laranja largo + Salvar + Compartilhar), card “Resumo da Vaga” (fundo claro ~`#F3F6F9`, pares Período / Bolsa Auxílio / Modelo / Vagas) e card “Seu Perfil” (barra + “Completar agora”).
- **FR-020**: Esta feature **NÃO** DEVE implementar “Seu Match com a vaga” nem “Por que combina com você?”.

**Estilo e mapeamento de dados**

- **FR-021**: Estilos novos DEVEM ser escopados à página de detalhe da vaga (classes próprias), sem alterar listagem `/vagas` nem cards laranja de home/landing.
- **FR-022**: Resumo da Vaga DEVE mapear: Período ← `field_horarios` (valores unidos ou primeiro valor); Bolsa ← `field_text_simple`; Modelo ← `field_regime_t`; Vagas ← texto “Não informado” (ou omitir a linha) enquanto não houver campo dedicado de quantidade — sem criar campo novo nesta feature.
- **FR-023**: Carga horária do badge do header DEVE usar `field_horarios` quando disponível; tempo de postagem DEVE ser relativo à data de criação/publicação do node.

**Deploy e documentação**

- **FR-024**: Um `hook_update_N` idempotente `custom_configs_update_11047` DEVE garantir: (1) tipo `faq` + `field_resposta` + displays; (2) 2 nodes FAQ seed; (3) paragraph `beneficio_vaga_p` + fields; (4) campos `field_vaga_faq`, `field_vaga_etapas_processo`, `field_vaga_requisitos`, `field_vaga_beneficios` no bundle `vagas`; (5) form/view displays de `vagas`.
- **FR-025**: Alterações estruturais DEVEM ser exportadas com `drush cex` para `config/sync` e versionadas no Git ao final do desenvolvimento na origem.
- **FR-026**: O fluxo de deploy em destino DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr`; zero passos manuais no admin de produção.
- **FR-027**: O `PRD.md` DEVE ser atualizado de forma cirúrgica (§3.1) com o tipo `faq`, o paragraph `beneficio_vaga_p` e os novos campos de `vagas`.
- **FR-028**: Campos legados `field_text_simple_multiple` (requisitos) e `field_text_simple_multiple_2` (benefícios) DEVEM permanecer no bundle para compatibilidade; a página full passa a preferir os campos novos. Migração idempotente opcional: se o campo novo estiver vazio e o legado tiver valores, copiar títulos/itens para o novo (benefícios sem ícone).

### Key Entities

- **Node `faq`**: pergunta (`title`) + resposta (`field_resposta`); referenciável por vagas.
- **Node `vagas`**: ganha FAQ, etapas, requisitos e benefícios estruturados; reutiliza descrição, regime, horários, bolsa, empresa.
- **Paragraph `beneficio_vaga_p`**: ícone + título para o grid de benefícios da vaga.
- **User empresa (`field_empresa_u`)**: logo, nome fantasia, `field_sobre_empresa` para o card “Sobre a Empresa”.
- **Candidatura / vagas_salvas (existentes)**: ações da sidebar continuam dependendo desses fluxos.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, a página de detalhe da vaga é reconhecível frente ao Figma (header, seções, stepper, FAQ, CTA, sidebar) em ≥90% dos critérios do checklist visual desta feature.
- **SC-002**: 100% das seções com dados preenchidos (requisitos, benefícios, etapas, FAQ, empresa) aparecem; 100% das seções sem dados ficam ocultas.
- **SC-003**: Accordion FAQ abre/fecha corretamente para 100% dos itens associados em testes de aceite (teclado e mouse).
- **SC-004**: O último passo do stepper usa o laranja `#FD7B1A` em 100% dos casos com ≥1 etapa.
- **SC-005**: Blocos “Seu Match…” e “Por que combina…” estão ausentes em 100% das páginas de vaga após a feature.
- **SC-006**: Editor consegue associar FAQ e editar etapas/requisitos/benefícios no formulário da vaga; alterações refletem na página pública após salvar (e cache clear se necessário) em 100% dos testes de aceite.
- **SC-007**: Ambiente desatualizado reproduz estrutura + layout com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cim -y` + `drush cr`, com zero passos manuais no painel.
- **SC-008**: Segunda execução de `drush updb` não duplica o Content Type, fields nem os 2 FAQs seed (idempotência confirmada).
- **SC-009**: Listagem `/vagas` e cards laranja de home/landing não regredem no checklist visual dessas superfícies.

## Assumptions

- Template alvo: view mode `full` do node `vagas`. Hoje o markup vive em `node--vagas.html.twig`; a implementação pode manter esse arquivo ou introduzir `node--vagas--full.html.twig` — desde que só o detalhe full seja afetado (decisão no `/speckit-plan`).
- Benefícios usam paragraph dedicado `beneficio_vaga_p` (não reutilizar `card_icon_text_p`), alinhado ao pedido e ao padrão do projeto de paragraphs por seção.
- Requisitos: texto simples multi-valor (não Text long formatted), porque o Figma é lista de checks item a item.
- “Vagas disponíveis” no resumo: sem campo estrutural nesta feature; linha com “Não informado” ou omitida.
- “Ver Empresa”: se não houver página pública de empresa, o plano documentará fallback (perfil painel, `#`, ou omitir botão) sem bloquear o restante do layout.
- Card “Seu Perfil”: markup estático com % exemplo (ex.: 75%) e link para rota de edição de perfil do candidato (`/painel/estudante/...` já existente); preprocess pode substituir % depois sem mudar o markup.
- CTA final e botão sidebar reutilizam as classes JS / destinos já usados (`js-candidatar-vaga` etc.).
- Seed FAQ: respostas placeholder curtas em pt-BR; associação automática dos 2 FAQs a todas as vagas **não** é obrigatória — editor associa; opcionalmente o hook pode anexar aos seeds de demo se existirem e o campo estiver vazio.
- Hook: `custom_configs_update_11047` (`11045` = feature 028; `11046` reservado/opcional pela 029).
- Cores de marca: azul escuro `#023C62`, laranja `#FD7B1A`, fundo resumo ~`#F3F6F9`.
- Textos desta fase somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação (PHP `hook_update_N`, Twig, SCSS/CSS, `drush cex`) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
- Referências visuais: mockups Figma anexados à solicitação (header/sidebar, requisitos/benefícios, stepper/empresa/FAQ, CTA/footer).
