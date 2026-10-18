# Feature Specification: Sidebar do Detalhe da Vaga — Ações e Resumo

**Feature Directory**: `specs/033-vaga-sidebar-acoes`  
**Created**: 2026-10-18  
**Status**: Draft  
**Predecessor**: `032-vaga-header-empresa` (header com CT `empresa`; sidebar de ações/resumo ainda incompleta no resumo)  
**Input do usuário**: Refatorar a sidebar do detalhe da vaga com cartão de Ações (Candidatar-se, Salvar, Compartilhar) e cartão Resumo da Vaga, campos em falta, ligações às rotas já existentes e conteúdo de teste portável.

## Escopo

### Inclui

- Cartão **Ações** no topo da sidebar do detalhe full da vaga:
  - **Candidatar-se**: link para a rota já existente de candidatura, descoberta no projeto: **`custom_panel.vagas_candidatar`** (`POST /vaga/apply/{node}`, parâmetro = ID do node da vaga). Não há página HTML de formulário separada; o endpoint cria o node `candidatura` e responde em JSON. O link usa o nome dessa rota; o clique do candidato autenticado continua a submeter em POST (a rota recusa GET).
  - **Salvar**: aciona o toggle de favoritos já existente **`custom_panel.vagas_salvas_toggle`** (`POST /painel/estudante/vagas-salvas/toggle`), passando o ID da vaga, sem recarregar a página.
  - **Compartilhar**: permanece ao lado de Salvar, com o popover já existente (WhatsApp, Facebook, LinkedIn).
- Cartão **Resumo da Vaga** com quatro linhas (rótulo à esquerda, valor à direita):
  - Período → campo novo `field_vaga_periodo` (texto, ex.: “6 meses +”);
  - Bolsa Auxílio → `field_text_simple` (já existe);
  - Modelo → `field_regime_t` (já existe);
  - Vagas → campo novo `field_vaga_quantidade` (inteiro, ex.: 2, exibido com dois dígitos “02”).
- Form e view displays desses campos no bundle `vagas`, para o editor preencher sem passo manual em produção.
- Conteúdo de demonstração portável, idempotente, reutilizando os seeds da feature 032 (não duplicar):
  - Empresa “EcoConstrutora”;
  - Vaga “Engenheiro Civil” vinculada a essa empresa, com localização “Curitiba, PR”, regime “Presencial”, carga “30h semanais”, bolsa “R$ 1.500/mês”, período “6 meses +” e quantidade 2.
- Visual do Figma limitado a estes dois cartões: botão primário laranja `#FD7B1A`, texto branco em negrito, altura 80px, largura total; botões secundários lado a lado, outline (fundo branco, borda e texto cinza); resumo em fundo `#F4F6F9`.
- Deploy automatizado (campos + displays + seed) e atualização cirúrgica do `PRD.md` nas linhas de campos da vaga.

### Fora

- Blocos “Seu Match com a vaga” e “Seu Perfil”.
- Header, Sobre a Vaga, Requisitos, Benefícios, Sobre a Empresa, FAQ e CTA final (permanecem como em `032` / `031`).
- Listagem `/vagas`, cards da home e Hero.
- Nova rota de candidatura ou de favoritos (reutilizam as rotas já publicadas).
- Migração em massa de `field_horarios` (turno Manhã/Tarde/Noite) para o período. O resumo deixa de ler o turno; `field_horarios` permanece no modelo.
- Alterações em `core/` ou `vendor/`.

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante lê o resumo da vaga (Priority: P1)

Visitante abre o detalhe de uma vaga e, na sidebar, vê o cartão “RESUMO DA VAGA” com período, bolsa, modelo e quantidade, cada um numa linha com rótulo à esquerda e valor em negrito à direita.

**Why this priority**: É a informação que o layout pede e que hoje falta (período de duração e quantidade aparecem como “Não informado” ou vêm do turno errado).

**Independent Test**: Abrir a vaga seed “Engenheiro Civil” e conferir as quatro linhas contra o layout.

**Acceptance Scenarios**:

1. **Given** a vaga seed publicada com período, bolsa, regime e quantidade preenchidos, **When** o visitante abre o detalhe, **Then** o resumo mostra “6 meses +”, “R$ 1.500/mês”, “Presencial” e “02”.
2. **Given** o cartão de resumo, **When** o visitante observa o layout, **Then** o fundo é azul muito claro, o título está em maiúsculas e cada linha separa rótulo (cinza) e valor (negrito).
3. **Given** uma vaga sem um desses dados, **When** o detalhe renderiza, **Then** só a linha vazia mostra “Não informado”; as demais e o restante da página permanecem.

---

### User Story 2 — Candidato se candidata e salva a partir da sidebar (Priority: P1)

Candidato autenticado usa o botão laranja “Candidatar-se” e, abaixo, “Salvar” e “Compartilhar” lado a lado. Candidatar-se registra a candidatura na vaga atual. Salvar alterna o favorito sem sair da página.

**Why this priority**: São as ações principais do detalhe e já têm contrato de backend; o layout só precisa ligá-las de forma visível e correta.

**Independent Test**: Com um usuário `candidato`, candidatar-se na vaga seed e salvar/remover; recarregar e confirmar o estado.

**Acceptance Scenarios**:

1. **Given** um candidato autenticado que ainda não se candidatou, **When** aciona “Candidatar-se”, **Then** a candidatura é registrada para aquela vaga e o botão passa a indicar envio, sem navegar para uma página de erro.
2. **Given** um candidato autenticado, **When** aciona “Salvar”, **Then** a vaga entra (ou sai) dos favoritos e o rótulo do botão reflete o novo estado, sem recarregar a página.
3. **Given** visitante anônimo, **When** vê o cartão de ações, **Then** o botão primário leva ao login (com retorno à vaga) em vez de chamar a candidatura, que exige a role `candidato`.
4. **Given** o cartão de ações, **When** o candidato observa os botões secundários, **Then** Salvar e Compartilhar ocupam a mesma linha, cada um cerca de metade da largura, com ícone e estilo outline.

---

### User Story 3 — Editor preenche período e quantidade (Priority: P1)

Editor edita a vaga e informa o período (texto) e a quantidade de vagas (número). Os valores aparecem no resumo público depois de salvar.

**Why this priority**: Sem os campos, o resumo não tem fonte sustentável além do seed.

**Independent Test**: Editar outra vaga, preencher os dois campos, salvar e abrir o detalhe.

**Acceptance Scenarios**:

1. **Given** o formulário da vaga, **When** o editor preenche período “6 meses +” e quantidade 2, **Then** os valores persistem.
2. **Given** a vaga salva, **When** o detalhe público renderiza, **Then** o resumo mostra esses valores (quantidade com dois dígitos).

---

### User Story 4 — Deploy leva campos e mock sem passo manual (Priority: P1)

Depois do deploy padrão, os campos existem e a vaga seed “Engenheiro Civil” / empresa “EcoConstrutora” exibem o resumo do layout, sem configuração no admin de produção.

**Why this priority**: Regra permanente do projeto — zero passos manuais estruturais em produção.

**Independent Test**: Aplicar a receita de deploy e abrir a vaga seed. Reexecutar a atualização e confirmar que não duplica os nodes.

**Acceptance Scenarios**:

1. **Given** um ambiente sem `field_vaga_periodo` e `field_vaga_quantidade`, **When** a atualização automatizada roda, **Then** os campos e os displays passam a existir.
2. **Given** a atualização já executada, **When** roda de novo, **Then** não duplica EcoConstrutora nem Engenheiro Civil e não sobrescreve texto editorial que já divergiu do seed.
3. **Given** o seed da feature 032 ainda com os valores originais daquele seed (São Paulo / SP / “R$ 1.800,00”) e sem edição posterior, **When** esta atualização roda, **Then** a vaga seed passa a espelhar o layout desta feature (Curitiba, PR, “R$ 1.500/mês”, período e quantidade).

---

### Edge Cases

- Vaga sem período, bolsa, regime ou quantidade → a linha correspondente mostra “Não informado”; o cartão não quebra.
- Quantidade 0 é valor preenchido e deve aparecer (“00”), distinta de campo vazio.
- Candidato que já se candidatou → botão primário inativo com “Candidatura Enviada”; não cria segunda candidatura.
- Usuário autenticado sem role `candidato` (empresa, moderador) → não exibe Candidatar-se nem Salvar; Compartilhar permanece.
- Falha de rede no Salvar ou no Candidatar-se → mensagem de erro e o botão volta ao estado anterior.
- Empresa ou vaga seed já existentes com UUID da feature 032 → reutiliza; não cria segunda cópia pelo título.

## Requirements *(obrigatório)*

### Functional Requirements

- **FR-001**: O detalhe full da vaga DEVE exibir, na sidebar, o cartão de Ações acima do cartão Resumo da Vaga. Match e Perfil NÃO entram nesta entrega.
- **FR-002**: “Candidatar-se”, para candidato autenticado ainda não candidatado, DEVE apontar para a rota `custom_panel.vagas_candidatar` com o ID da vaga atual. A submissão DEVE ser POST, coerente com o contrato da rota (métodos permitidos: POST).
- **FR-003**: “Salvar”, para candidato autenticado, DEVE alternar o favorito via `custom_panel.vagas_salvas_toggle`, enviando o ID da vaga, sem recarregar a página.
- **FR-004**: “Compartilhar” DEVE permanecer na mesma linha de “Salvar”, abrindo o popover social já existente.
- **FR-005**: Visitante anônimo DEVE ver o botão primário apontando para o login, com retorno à vaga atual. A rota de candidatura não é chamada nesse estado.
- **FR-006**: O sistema DEVE oferecer no bundle `vagas` o campo texto `field_vaga_periodo` (rótulo Período) e o campo inteiro `field_vaga_quantidade` (rótulo Vagas), editáveis no formulário da vaga.
- **FR-007**: O resumo DEVE listar, nesta ordem: Período (`field_vaga_periodo`), Bolsa Auxílio (`field_text_simple`), Modelo (`field_regime_t`) e Vagas (`field_vaga_quantidade`). O turno `field_horarios` NÃO alimenta mais o período do resumo.
- **FR-008**: A quantidade preenchida DEVE ser exibida com dois dígitos (2 → “02”).
- **FR-009**: O botão primário DEVE ter fundo `#FD7B1A`, texto branco em negrito, altura 80px e largura 100%. Os botões secundários DEVEM ser outline (fundo branco, borda e texto/ícone cinza-escuro), lado a lado. O resumo DEVE ter fundo `#F4F6F9` e título “RESUMO DA VAGA” em maiúsculas, menor, azul-escuro e negrito.
- **FR-010**: A atualização de deploy DEVE ser idempotente: criar os campos se ausentes, garantir displays, garantir os nodes seed pelos UUIDs já fixados na feature 032 e preencher apenas campos vazios — com a exceção documentada de alinhar a vaga seed quando ela ainda carrega exatamente o trio de localização/bolsa do seed 032, sem edição posterior.
- **FR-011**: Conteúdo editorial já divergente do seed NÃO DEVE ser sobrescrito.
- **FR-012**: O `PRD.md` DEVE registrar os dois campos novos e que o resumo da sidebar não usa mais `field_horarios` como período.

### Key Entities

- **Vaga (`vagas`)**: ganha Período (`field_vaga_periodo`, texto) e Quantidade (`field_vaga_quantidade`, inteiro). Bolsa continua em `field_text_simple`. Modelo continua em `field_regime_t`. Localização continua em `field_cidade` + `field_estados`. Carga horária continua em `field_vaga_carga_horaria`. Empresa pública continua em `field_vaga_empresa`.
- **Empresa (`empresa`)**: seed “EcoConstrutora” (UUID já existente `f6a7b8c9-d0e1-4234-e567-89abcdef0123`). Sem campos novos.
- **Candidatura**: inalterada; criada pela rota `custom_panel.vagas_candidatar`.
- **Favorito**: inalterado; alternado pela rota `custom_panel.vagas_salvas_toggle`.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Na vaga seed, um visitante identifica período, bolsa, modelo e quantidade em até 5 segundos, sem abrir o corpo da vaga.
- **SC-002**: Um candidato autenticado conclui “Candidatar-se” ou “Salvar” a partir da sidebar em uma única ação, sem trocar de página no sucesso.
- **SC-003**: 100% das quatro linhas do resumo da vaga seed correspondem ao layout (período, bolsa, modelo, quantidade “02”).
- **SC-004**: Depois do deploy, um ambiente novo exibe a sidebar completa da vaga seed sem qualquer preenchimento manual no admin.
- **SC-005**: Reexecutar a atualização não aumenta a quantidade de nodes “EcoConstrutora” nem “Engenheiro Civil” com os UUIDs de seed.

## Assumptions

- A rota de candidatura encontrada no código é `custom_panel.vagas_candidatar` (`/vaga/apply/{node}`, POST, role `candidato`). Não existe outra rota de formulário HTML de candidatura em `*.routing.yml`. O link nomeia essa rota; o envio permanece POST para respeitar o contrato (GET responderia método não permitido).
- A rota de favoritos é `custom_panel.vagas_salvas_toggle` (`/painel/estudante/vagas-salvas/toggle`, POST, corpo `nid`).
- `field_text_simple` e `field_regime_t` já cobrem bolsa e modelo; não se criam campos paralelos para o mesmo fim.
- `field_horarios` é turno (Manhã/Tarde/Noite), distinto do período de duração pedido no layout. Por isso `field_vaga_periodo` é campo novo.
- Não há storage inteiro reutilizável no bundle `vagas` para quantidade; `field_vaga_quantidade` é storage novo.
- Seeds da feature 032 (UUIDs empresa `f6a7b8c9-d0e1-4234-e567-89abcdef0123` e vaga `a7b8c9d0-e1f2-4345-f678-9abcdef01234`) são a fonte portável. Esta feature enriquece esses registros em vez de criar homônimos.
- O trio São Paulo / SP / “R$ 1.800,00” é o seed intocado da 032, não conteúdo editorial. Só nesse caso a localização e a bolsa da vaga seed são alinhadas a Curitiba / PR / “R$ 1.500/mês”.
- Visitante anônimo e papéis sem `candidato` mantêm o comportamento já publicado (login ou só compartilhar).
- O tema não tem pipeline SCSS para este componente; o estilo vive na folha CSS já anexada ao detalhe da vaga.
- Predecessor visual e de dados: `032-vaga-header-empresa`. Match e Perfil continuam fora.
