# Feature Specification: Cabeçalho do Detalhe da Vaga — Perfil Empresa

**Feature Directory**: `specs/032-vaga-header-empresa`  
**Created**: 2026-10-07  
**Status**: Draft  
**Predecessor**: `031-vagas-detalhe-refino` (layout duas colunas; header ainda lê empresa via usuário `field_empresa_u`)  
**Input do usuário**: Refatorar o cabeçalho (Header) da página de detalhe da Vaga — extrair perfil público da empresa para Content Type próprio (`empresa`), vincular à vaga, renderizar Logo + títulos + localização + pills conforme Figma, com deploy 100% automatizado.

## Escopo

### Inclui

- Novo Content Type **Empresa** (`empresa`): nome da empresa = título nativo; logo = campo de imagem reutilizado (`field_imagem`).
- No Content Type `vagas`:
  - Campo de referência **Empresa da vaga** (`field_vaga_empresa`) → node `empresa`, cardinalidade 1;
  - Campo **Carga horária** (`field_vaga_carga_horaria`) para o texto da pill (ex.: “30h semanais”);
  - Form e view displays atualizados para editores.
- Reuso dos campos já existentes na vaga para as demais pills e localização:
  - Regime → `field_regime_t`;
  - Bolsa/salário → `field_text_simple`;
  - Localização → `field_cidade` + `field_estados`;
  - Tempo desde a postagem → calculado a partir da data de criação do node (sem campo novo).
- Refatoração visual do **card superior (Header da Vaga)** no detalhe (`view mode` full):
  - Container branco, cantos arredondados, borda sutil, padding generoso;
  - Logo à esquerda (96×96, cobertura, cantos 12px);
  - Título da vaga (h1), linha empresa + badge de verificado + localização com ícone de pin;
  - Quatro pills: regime, carga horária, bolsa/salário, “Postado há…”.
- Seed idempotente de conteúdo de demonstração: 1 empresa (“EcoConstrutora” + logo placeholder) e 1 vaga (“Engenheiro Civil”) com todos os campos do header preenchidos e vínculo empresa↔vaga.
- Deploy automatizado (estrutura + displays + seed) sem passos manuais no admin de produção; exportação das configurações estruturais; atualização cirúrgica do `PRD.md` nas seções de content types / campos afetados.

### Fora

- Redesign completo da página de detalhe além do **Header** (seções Sobre, Requisitos, Benefícios, FAQ, sidebar, CTA — permanecem como em `031`, salvo ajustes mínimos se o header compartilhar markup).
- Página pública completa da empresa, listagem de empresas ou botão “Ver Empresa” com rota nova.
- Migração em massa de todas as vagas existentes de `field_empresa_u` (usuário) para nodes `empresa` — fora desta entrega; apenas seed + caminho novo de dados.
- Remoção ou desativação de `field_empresa_u` (permanece no modelo legado até feature futura de migração).
- Fluxos novos de candidatura, salvar ou compartilhar.
- Alterações em `core/` ou `vendor/`.
- Código de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante identifica a vaga pelo header do Figma (Priority: P1)

Visitante abre uma vaga publicada e, no topo da coluna principal, vê o card de header: logo da empresa, título da vaga, nome da empresa com indicador de verificado, localização e as quatro pills informativas — alinhado ao protótipo Figma.

**Why this priority**: É o valor visual e informativo principal desta feature; sem o header correto o detalhe não comunica empresa nem condições da vaga.

**Independent Test**: Abrir a vaga seed “Engenheiro Civil” (ou qualquer vaga com `field_vaga_empresa` e pills preenchidos) e comparar com o frame Figma do header.

**Acceptance Scenarios**:

1. **Given** uma vaga publicada com empresa vinculada, logo, localização, regime, carga horária e bolsa, **When** o visitante abre o detalhe, **Then** o header exibe logo, h1 com o título da vaga, nome da empresa, localização e as quatro pills.
2. **Given** o header renderizado, **When** o visitante observa o layout, **Then** o card tem fundo claro, cantos arredondados, borda sutil e organização logo à esquerda / textos à direita.
3. **Given** viewport mobile, **When** a página carrega, **Then** o header permanece legível (logo e textos empilham ou adaptam sem overflow horizontal nem corte do título).

---

### User Story 2 — Editor vincula Empresa à Vaga e preenche carga horária (Priority: P1)

Editor (ou perfil com permissão) cria/edita um node Empresa (nome + logo) e, no formulário da vaga, seleciona essa empresa e informa a carga horária; os demais dados das pills usam campos já existentes.

**Why this priority**: Sem o modelo de dados e o formulário, o header não pode ser alimentado de forma sustentável.

**Independent Test**: Criar uma empresa de teste, associá-la a uma vaga, preencher carga horária e salvar; reabrir o detalhe público e confirmar o header.

**Acceptance Scenarios**:

1. **Given** permissão de criar conteúdo Empresa, **When** o editor cria “EcoConstrutora” com logo, **Then** o node é publicado e o título é o nome da empresa.
2. **Given** o formulário de edição de uma vaga, **When** o editor escolhe a empresa em “Empresa da vaga” e preenche carga horária (ex.: “30h semanais”), **Then** os valores persistem após salvar.
3. **Given** regime, bolsa, cidade e estado já preenchidos na vaga, **When** o detalhe renderiza, **Then** as pills correspondentes exibem esses valores sem exigir novos campos além da carga horária.

---

### User Story 3 — Visitante lê tempo relativo desde a postagem (Priority: P2)

A quarta pill mostra quanto tempo passou desde a publicação da vaga (“Postado há X hora(s)/dia(s)”), calculado automaticamente a partir da data de criação do conteúdo.

**Why this priority**: Completa o conjunto de pills do Figma sem custo de edição editorial.

**Independent Test**: Comparar a pill com a data de criação conhecida da vaga seed (ou ajustar a data em ambiente de teste e recarregar).

**Acceptance Scenarios**:

1. **Given** uma vaga criada há menos de 24 horas, **When** o header renderiza, **Then** a pill usa unidade em horas (ex.: “Postado há 3 horas”) quando aplicável.
2. **Given** uma vaga criada há 1 ou mais dias, **When** o header renderiza, **Then** a pill usa “Postado há X dia(s)” com pluralização correta em português.
3. **Given** a vaga acabou de ser criada, **When** o header renderiza, **Then** a mensagem permanece compreensível (ex.: “Postado há pouco” / “Postado há menos de 1 hora”) sem valores negativos.

---

### User Story 4 — Deploy automatizado leva estrutura e mock para o destino (Priority: P1)

Após o deploy padrão do projeto, o Content Type Empresa, os campos na vaga, displays e o conteúdo seed existem sem configuração manual no admin de produção.

**Why this priority**: Regra permanente do projeto: zero passos manuais estruturais em produção.

**Independent Test**: Em ambiente limpo (ou após reexecução idempotente), aplicar a receita de deploy da feature e abrir a vaga seed.

**Acceptance Scenarios**:

1. **Given** ambiente de destino sem o Content Type Empresa, **When** a atualização automatizada roda, **Then** `empresa`, campos e displays passam a existir.
2. **Given** a atualização já executada uma vez, **When** roda de novo, **Then** não duplica a empresa/vaga seed nem corrompe conteúdo editorial já divergente.
3. **Given** deploy concluído, **When** o visitante abre a vaga seed, **Then** o header está completo (empresa vinculada + pills).

---

### Edge Cases

- Vaga sem `field_vaga_empresa` → omite logo e nome da empresa (ou usa fallback textual neutro já adotado no tema); pills e título da vaga continuam; layout não quebra.
- Empresa vinculada sem logo → reserva o espaço do logo com placeholder ou omite a imagem sem deslocar o título de forma quebrada.
- Cidade ou estado vazios → omite o trecho de localização e o separador “•” quando não houver o que separar.
- Pill individual vazia (regime, carga ou bolsa) → omite só aquela pill; as demais permanecem.
- Empresa referenciada não publicada / inacessível ao visitante → trata como ausência de empresa (não vaza título de conteúdo restrito).
- `field_empresa_u` preenchido mas `field_vaga_empresa` vazio → nesta feature o header **prioriza** `field_vaga_empresa`; não é obrigatório ler o usuário legado no header (pode permanecer só em outras seções herdadas de `031` até migração futura).

## Requirements *(obrigatório)*

### Functional Requirements

- **FR-001**: O sistema DEVE oferecer o Content Type Empresa cujo título nativo é o nome público da empresa e que possui campo de imagem para logo.
- **FR-002**: O Content Type Vaga DEVE permitir vincular exatamente uma Empresa via campo de referência dedicado.
- **FR-003**: O Content Type Vaga DEVE permitir informar carga horária textual para exibição no header (ex.: “30h semanais”).
- **FR-004**: O header do detalhe da vaga DEVE obter logo e nome da empresa a partir da Empresa referenciada (não inventar dados estáticos no markup).
- **FR-005**: O header DEVE exibir o título da vaga como heading principal (h1).
- **FR-006**: O subtítulo do header DEVE exibir nome da empresa, indicador visual de verificado ao lado do nome, separador e localização (cidade/estado) com ícone de pin, quando esses dados existirem.
- **FR-007**: O header DEVE exibir até quatro pills: regime, carga horária, bolsa/salário e tempo relativo desde a criação do node; omitindo pills sem valor.
- **FR-008**: O tempo relativo DEVE ser calculado dinamicamente a partir da data de criação do node, em português do Brasil, sem campo editorial adicional.
- **FR-009**: O card do header DEVE seguir o contrato visual do Figma: fundo branco, cantos ~16px, borda cinza sutil, padding generoso, logo 96×96 com cantos ~12px e cobertura da imagem, tipografia do título em peso semibold e cor escura, pills com cantos totalmente arredondados e fundo azul muito claro.
- **FR-010**: Em viewport estreita, o header DEVE permanecer utilizável sem overflow horizontal.
- **FR-011**: A atualização automatizada DEVE criar/garantir Content Type, campos, form/view displays e seed (1 empresa + 1 vaga vinculadas) de forma idempotente.
- **FR-012**: Configurações estruturais novas/alteradas DEVEM ser exportáveis e versionáveis no repositório de sync do projeto.
- **FR-013**: O `PRD.md` DEVE ser atualizado de forma cirúrgica para documentar o Content Type Empresa e os novos campos da vaga.
- **FR-014**: O campo legado de referência a usuário empresa (`field_empresa_u`) NÃO DEVE ser removido nesta feature.

### Key Entities

- **Empresa (`empresa`)**: perfil público da organização; atributos mínimos: nome (título), logo (imagem). Relaciona-se com zero ou muitas vagas.
- **Vaga (`vagas`)**: anúncio de estágio; passa a referenciar uma Empresa; reutiliza regime, bolsa, cidade, estado; ganha carga horária; data de criação alimenta a pill “Postado há…”.
- **Header da Vaga**: composição de apresentação (não entidade de dados) que agrega título da vaga + dados da Empresa + localização + pills.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite (desktop), o header da vaga seed é reconhecível frente ao Figma em ≥90% dos critérios do checklist visual desta feature (logo, tipografia do título, subtítulo com verificado + pin, quatro pills, card).
- **SC-002**: 100% das pills com dado preenchido aparecem; 100% das pills sem dado ficam ocultas.
- **SC-003**: Em 100% das vagas com Empresa publicada e logo, o visitante vê o nome e a imagem da empresa corretos no header (sem markup hardcoded da seed).
- **SC-004**: Após a receita de deploy da feature, um revisor consegue abrir a vaga seed e validar o header completo em ≤5 minutos, sem criar tipos/campos manualmente no admin.
- **SC-005**: Reexecução da atualização automatizada não cria segundo node seed duplicado da empresa/vaga de demonstração (idempotência verificável).
- **SC-006**: Em viewport ≤576px, o header não provoca scroll horizontal na página de detalhe.

## Assumptions

- Predecessor `031` já entregou o layout duas colunas e o template de detalhe; esta feature **substitui/refina apenas o bloco Header** e o modelo de dados da empresa pública.
- Storage de imagem `field_imagem` (ou equivalente já usado no projeto para logos) será reutilizado no Content Type Empresa quando existir; caso contrário, será criado seguindo o padrão do projeto.
- Badge “verificado” no header é **apresentação fixa** sempre que houver empresa vinculada nesta versão (não depende de flag de verificação real no CMS).
- Texto das pills de regime, carga e bolsa é o valor armazenado no campo (sem normalização complexa); formatação de moeda já existente em `field_text_simple` é suficiente.
- Pluralização “hora(s)” / “dia(s)” segue português do Brasil; “Postado há menos de 1 hora” (ou equivalente curto) cobre o intervalo &lt; 1 hora.
- Coexistência: `field_empresa_u` permanece para seções/legado; o **Header** desta feature lê `field_vaga_empresa`.
- Seed usa asset placeholder versionado no módulo custom (nunca `sites/default/files` no Git), copiado para o filesystem público no update — padrão do projeto.
- Princípios de código limpo e reuso de variáveis Twig (incluindo diretrizes do pacote `mattpocock/skills`, se disponível no ambiente do agente) serão aplicados na fase de implementação, não nesta especificação.
- Receita de destino alinha-se ao runbook do projeto (`cim` → `updb` → `cim` → `cr`, salvo exceções documentadas no plano).
