# Feature Specification: Refatoração da Vaga Interna (Benefícios + CTA)

**Feature Directory**: `specs/050-vaga-beneficios-cta`  
**Feature Branch**: `050-vaga-beneficios-cta`  
**Created**: 2026-11-02  
**Status**: Implemented  
**Predecessor**: `030-vagas-detalhe-layout` / `031-vagas-detalhe-refino` / `036-vagas-beneficios-padrao` (benefícios via paragraphs); `032-vaga-header-empresa` / `037-vaga-sobre-empresa` (empresa pública); `033-vaga-sidebar-acoes` (candidatura)  
**Input do usuário**: Refatorar edição e exibição da página interna da Vaga — ocultar campo empresa legado duplicado; migrar benefícios de paragraphs para taxonomia selecionável com ícones; restringir o CTA “Pronto para o próximo passo?” a candidatos e alinhar a ação do botão à candidatura da vaga; deploy 100% automatizado.

## Escopo

### Inclui

- **Empresa (edição):** na tela de edição da vaga, ocultar o campo legado de vínculo com conta de usuário empresa, mantendo visível apenas o vínculo com o perfil público da empresa (exibido no header/cartão do detalhe).
- **Benefícios (modelo de dados):** substituir a edição livre por paragraphs por um catálogo fixo de benefícios (vocabulário dedicado), cada um com nome + ícone; o moderador seleciona múltiplos benefícios via checkboxes na edição da vaga.
- **Catálogo inicial:** sete benefícios — quatro do layout Figma (Plano de Carreira, Vale Transporte, Vale Refeição, Horário Flexível) e três sugestões de mercado (Assistência Médica, Auxílio Academia / Gympass, Day Off no Aniversário), cada um com ícone em formato WebP versionado no produto.
- **Detalhe público:** a seção Benefícios do detalhe da vaga passa a renderizar os termos selecionados (ícone + rótulo) no grid já alinhado ao Figma.
- **CTA final (“Pronto para o próximo passo?”):**
  - Visível apenas para usuários autenticados com papel de candidato, e apenas em páginas de vaga.
  - O botão primário dispara a mesma ação de candidatura da sidebar daquela vaga (não mais um destino genérico de cadastro).
- **Deploy automatizado:** criação/garantia do vocabulário, campos, displays, termos com ícones, ocultação do campo legado, restrição de visibilidade do CTA e exportação de configuração estrutural — zero passos manuais no admin de produção.
- Atualização cirúrgica do `PRD.md` nas seções de campos da vaga, taxonomias e bloco CTA, na fase de implementação.

### Fora

- Remover fisicamente o storage do campo empresa legado ou migrar em massa painéis/candidaturas que ainda o consultam (apenas ocultar da edição da vaga).
- Remover o tipo de paragraph de benefício legado ou apagar dados históricos de paragraphs (campo legado fica oculto; storage pode permanecer para rollback).
- Redesign visual amplo do detalhe (Requisitos, FAQ, sidebar, header, Processo de Contratação) — apenas a fonte de dados da seção Benefícios e a regra do CTA.
- Benefícios da landing `/para-estudantes` ou de outras páginas.
- Alterar listagem `/vagas`, Hero Search ou cards laranja.
- Alterações em `core/` ou `vendor/`.
- Código de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Moderador edita vaga sem duplicidade de Empresa (Priority: P1)

Um moderador abre o formulário de edição de uma vaga e encontra um único campo claro para vincular a empresa pública da vaga; o campo legado de conta de usuário empresa não aparece mais no formulário.

**Why this priority**: Elimina confusão editorial (dois “Empresa” distintos) e evita vincular o perfil público errado.

**Independent Test**: Abrir edição de uma vaga existente e confirmar presença do campo de perfil público e ausência do campo legado; salvar com perfil público preenchido e verificar header do detalhe.

**Acceptance Scenarios**:

1. **Given** um moderador com permissão de editar vagas, **When** abre o formulário de edição, **Then** vê o campo de empresa pública (perfil exibido no detalhe) e **não** vê o campo legado de conta usuário-empresa.
2. **Given** uma vaga com empresa pública vinculada, **When** o visitante abre o detalhe, **Then** o header/cartão continuam usando apenas a empresa pública (sem regressão).
3. **Given** documentação/orientação de manutenção, **When** o editorial consulta a instrução, **Then** fica explícito que o vínculo público é o canônico para o detalhe e que o legado permanece no sistema só para usos internos (painel/candidaturas), sem edição na tela da vaga.

---

### User Story 2 — Moderador seleciona benefícios fixos por checkbox (Priority: P1)

Ao criar ou editar uma vaga, o moderador marca benefícios de uma lista fixa (com ícones no catálogo), em vez de montar paragraphs com upload de imagem e texto livre.

**Why this priority**: Padroniza o catálogo, reduz erro editorial e habilita o grid público previsível.

**Independent Test**: No formulário da vaga, marcar 3 benefícios do catálogo, salvar e abrir o detalhe público; conferir ícone + rótulo corretos.

**Acceptance Scenarios**:

1. **Given** o formulário de vaga após o deploy, **When** o moderador inspeciona o campo de benefícios, **Then** vê **um único** campo Benefícios com checkboxes dos termos do catálogo (ao menos os sete seedados) e **não** vê editor de paragraphs nem texto livre multi-valor (`field_text_simple_multiple_2`).
2. **Given** uma vaga com “Vale Refeição” e “Horário Flexível” marcados, **When** o visitante abre o detalhe, **Then** a seção Benefícios exibe exatamente esses itens (ícone + nome), em grade responsiva alinhada ao layout.
3. **Given** tentativa de salvar vaga sem nenhum benefício selecionado, **When** o moderador submete, **Then** o sistema impede o salvamento (benefícios continuam obrigatórios).

---

### User Story 3 — Catálogo seedado com 7 benefícios e ícones (Priority: P1)

Após o deploy, o vocabulário de benefícios já contém os quatro itens do Figma e três sugestões extras, cada um com ícone WebP, sem cadastro manual em produção.

**Why this priority**: Sem seed, a seleção por checkbox não tem valor de negócio imediato.

**Independent Test**: Após a atualização de deploy, listar termos do vocabulário e conferir nomes + presença de ícone; reexecutar e confirmar idempotência.

**Acceptance Scenarios**:

1. **Given** ambiente após deploy, **When** um administrador lista o catálogo de benefícios, **Then** encontra exatamente estes sete nomes: Plano de Carreira, Vale Transporte, Vale Refeição, Horário Flexível, Assistência Médica, Auxílio Academia / Gympass, Day Off no Aniversário.
2. **Given** cada um dos sete termos, **When** inspecionado, **Then** possui ícone associado (formato WebP) adequado ao rótulo (mala, maleta, talheres, relógio, assistência médica, academia, aniversário).
3. **Given** reexecução do deploy, **When** a atualização roda de novo, **Then** não duplica termos nem sobrescreve ícones/textos já divergentes editorialmente (cria/preenche apenas o ausente).

---

### User Story 4 — Candidato vê CTA e candidata-se pela mesma ação da sidebar (Priority: P1)

Um usuário autenticado com papel candidato, na página de uma vaga, vê o bloco “Pronto para o próximo passo?” e, ao acionar “Candidatar-se Agora”, executa a mesma candidatura daquela vaga que o botão da sidebar.

**Why this priority**: Fecha o funil no final da página com ação correta e com restrição de audiência.

**Independent Test**: Logar como candidato em uma vaga ainda não candidatada; clicar no CTA; confirmar candidatura registrada e UI coerente com a sidebar. Logar como outro papel / anônimo e confirmar ausência do bloco.

**Acceptance Scenarios**:

1. **Given** usuário autenticado com papel candidato em página de vaga, **When** a página renderiza, **Then** o bloco CTA final “Pronto para o próximo passo?” está visível.
2. **Given** visitante anônimo, usuário empresa ou administrador sem papel candidato, **When** abre a mesma vaga, **Then** o bloco CTA final **não** é exibido.
3. **Given** candidato ainda não candidatada na vaga, **When** clica em “Candidatar-se Agora” no CTA, **Then** a candidatura é enviada com o mesmo comportamento do botão “Candidatar-se” da sidebar (feedback de envio / “Candidatura Enviada” / sem navegação para cadastro genérico).
4. **Given** candidato já candidatada, **When** a página renderiza, **Then** o CTA não oferece uma ação que reenvie candidatura indevida (estado alinhado à sidebar: desabilitado ou equivalente “já enviado”).

---

### User Story 5 — Operação ativa tudo sem admin manual (Priority: P1)

A equipe aplica a feature no destino e, sem editar formulários/blocos no admin, o campo legado some da edição, o catálogo existe, as vagas usam o novo seletor, o detalhe renderiza a taxonomia e o CTA respeita o papel candidato.

**Why this priority**: Regra permanente de deploy do produto.

**Independent Test**: Rodar a receita de destino em ambiente com vagas existentes; auditar formulário, catálogo, detalhe e CTA; reexecutar para idempotência; exportar configuração na origem.

**Acceptance Scenarios**:

1. **Given** código + assets versionados no destino, **When** a receita de deploy (import de config + atualizações + caches) conclui, **Then** todos os itens do Escopo/Inclui estão ativos sem passos manuais no admin.
2. **Given** a mesma atualização reexecutada, **When** termina, **Then** não há duplicação de termos, arquivos ou regressão editorial.
3. **Given** ambiente de origem após implementação, **When** a configuração estrutural é exportada, **Then** vocabulário/campos/displays/placement do CTA com restrição de papel ficam versionados no repositório.

---

### Edge Cases

- Vaga com benefícios antigos em paragraphs: no deploy, mapear por título (comparação normalizada) aos termos do catálogo quando houver correspondência; títulos sem correspondente não criam termos novos automaticamente; o detalhe passa a usar só a seleção taxonomia.
- Vaga sem nenhum benefício mapeável após o deploy: fica com o novo campo vazio até o editorial selecionar; a obrigatoriedade impede novas publicações sem seleção (vagas já publicadas vazias devem ser reportáveis para correção editorial).
- Ícone WebP ausente na pasta de assets no momento do deploy: a atualização falha de forma explícita para aquele termo/arquivo e não “inventa” termo sem ícone silenciosamente.
- CTA em páginas que não são vaga: permanece oculto (visibilidade por tipo de conteúdo + papel).
- Candidato sem permissão de candidatar naquela vaga (regra de negócio existente): o CTA respeita o mesmo resultado/erro da sidebar.
- Papel candidato + outro papel simultâneo: o bloco permanece visível (basta possuir o papel candidato).
- Reexecução do seed de termos: não altera nome/ícone se o termo já existe e já tem ícone; apenas completa ausências.

## Requirements *(obrigatório)*

### Functional Requirements

- **FR-001**: O formulário de edição da vaga MUST ocultar o campo legado de vínculo com conta de usuário empresa, mantendo-o no sistema para usos internos já existentes.
- **FR-002**: O formulário de edição da vaga MUST exibir o campo de vínculo com o perfil público da empresa como a única escolha editorial de “Empresa” no detalhe.
- **FR-003**: O produto MUST oferecer um catálogo reutilizável de benefícios (vocabulário dedicado), cada termo com nome e ícone.
- **FR-004**: O catálogo MUST aceitar ícones no formato WebP.
- **FR-005**: A vaga MUST possuir um campo de seleção múltipla para benefícios do catálogo, apresentado como checkboxes na edição.
- **FR-006**: O formulário da vaga MUST deixar de expor o editor de paragraphs de benefício como forma de edição ativa (campo legado oculto no formulário e na apresentação pública).
- **FR-007**: No deploy, o sistema MUST garantir os sete benefícios nomeados no Escopo, cada um com ícone WebP proveniente de assets versionados do produto (sem commit de uploads públicos no repositório).
- **FR-008**: A seção Benefícios do detalhe público MUST renderizar somente os termos selecionados na vaga (ícone + rótulo), em grade responsiva coerente com o layout já adotado.
- **FR-009**: Benefícios na vaga MUST permanecer obrigatórios (ao menos um termo selecionado) após a troca de modelo.
- **FR-010**: O bloco CTA final da vaga (“Pronto para o próximo passo?”) MUST ser exibido apenas a usuários autenticados que possuam o papel candidato, além da restrição já existente a páginas de vaga.
- **FR-011**: O botão primário desse CTA, em página de vaga, MUST acionar a candidatura da vaga corrente com o mesmo comportamento do botão de candidatar da sidebar.
- **FR-012**: Visitantes anônimos e usuários sem papel candidato MUST NOT ver esse bloco CTA no detalhe da vaga.
- **FR-013**: Toda garantia estrutural e de seed MUST ser idempotente e automatizada no fluxo de deploy do projeto; a configuração estrutural resultante MUST ser exportável e versionada.
- **FR-014**: Orientação de manutenção MUST deixar claro: (a) empresa pública = canônica no detalhe; (b) benefícios = só catálogo; (c) CTA final = só candidatos, ação = candidatura da vaga.

### Key Entities

- **Vaga**: Anúncio de estágio; vincula-se a uma empresa pública e a um conjunto de benefícios do catálogo; página de detalhe com sidebar de candidatura e CTA final.
- **Empresa pública**: Perfil público associado à vaga para header/cartão; distinto da conta de usuário empresa legada.
- **Conta empresa legada**: Vínculo usuário-empresa usado por painel/candidaturas; permanece no sistema, oculto na edição da vaga.
- **Catálogo de Benefícios**: Conjunto fixo de benefícios reutilizáveis (nome + ícone) selecionáveis nas vagas.
- **Benefício (termo)**: Item do catálogo com rótulo curto e ícone WebP.
- **CTA final da vaga**: Bloco de chamada à ação no fim do detalhe; audiência restrita a candidatos; ação alinhada à candidatura.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em 100% das aberturas do formulário de vaga pós-deploy, o campo empresa legado não aparece e o campo de empresa pública permanece editável.
- **SC-002**: Após o deploy, 100% dos sete benefícios seedados existem no catálogo com ícone associado.
- **SC-003**: Em amostragem de vagas com benefícios selecionados, 100% dos detalhes públicos exibem somente os termos marcados (ícone + nome), sem depender do editor de paragraphs.
- **SC-004**: 100% das tentativas de salvar vaga nova sem benefício selecionado são bloqueadas.
- **SC-005**: Em testes com papéis distintos, o CTA final aparece em 100% das sessões de candidato em página de vaga e em 0% das sessões anônimas/sem papel candidato.
- **SC-006**: Em 100% dos cliques válidos de “Candidatar-se Agora” por candidato elegível, o resultado da candidatura é o mesmo obtido pelo botão da sidebar na mesma vaga.
- **SC-007**: Reexecução do deploy em ambiente já atualizado produz zero duplicação de termos/ícones e zero regressão nos placements/displays exportados.

## Assumptions

- O campo legado de empresa na edição da vaga é o vínculo com conta de usuário empresa; o campo canônico para o detalhe público é o vínculo com o perfil público (já usado no header/cartão). Apenas ocultação no formulário — sem exclusão de storage nesta feature.
- A migração de benefícios existentes mapeia títulos de paragraphs para termos do catálogo por comparação normalizada de texto; os quatro padrões Figma e títulos idênticos às três sugestões são cobertos; demais textos livres ficam de fora do mapeamento automático.
- O campo antigo de paragraphs deixa de ser a fonte de verdade na UI (oculto) mas não é apagado do banco nesta feature, para permitir rollback seguro.
- Ícones dos quatro benefícios Figma podem ser convertidos/espelhados a partir dos assets já versionados da feature 036; as três sugestões novas ganham assets WebP dedicados na pasta de assets do módulo de configuração.
- Restringir o CTA a candidatos é intencional: anônimos e outros papéis continuam com a sidebar (login / estados existentes) e não veem o CTA final.
- “Mesmo comportamento” do botão da sidebar significa a mesma ação de candidatura da vaga corrente (incluindo estados já candidatada / feedback), não um redirecionamento para página de cadastro genérico.
- Práticas de qualidade na implementação (código limpo, tipagem estrita em PHP, revisão com skills do ecossistema quando aplicável) seguem as regras do projeto; o padrão de hooks idempotentes de `custom_configs` permanece a referência de deploy.
- Machine names concretos, rotas e detalhes de Entity API ficam para `/speckit-plan` / implementação, desde que respeitem Configuration Management e a receita `cim` → `updb` → `cim` → `cr` do projeto.

## Dependências

- Detalhe full da vaga e seção Benefícios (`030` / `031` / `036`).
- Empresa pública no header/cartão (`032` / `034` / `037`).
- Sidebar de candidatura e rota de apply (`033` / painel).
- Instância e placement do CTA de vagas (`031`).
- Módulo de configuração customizada, assets versionados e runbook de deploy (`016`).
- Exportação final na origem (`drush cex`) após garantir estrutura e seeds.
