# Feature Specification: reCAPTCHA nos formulários anônimos

**Feature Directory**: `specs/063-recaptcha-formularios-anonimos`  
**Created**: 2026-11-20  
**Status**: Draft  
**Predecessor**: `059-cadastro-candidato-legal`, `035-refatorar-login`, `017-pagina-contato`  
**Input do usuário**: Usar o reCAPTCHA do Google de forma gerenciável, fácil para o visitante e segura em todos os formulários anônimos do site. Renderizar da melhor forma possível.

## Escopo

### Inclui

- Proteção antiabuso nos formulários que um visitante não autenticado pode enviar:
  - entrar na conta;
  - recuperar senha;
  - criar conta (rota padrão e os cadastros de estudante e de empresa);
  - enviar a mensagem de contato.
- Na maior parte dos envios, o visitante não resolve quebra-cabeça nem marca caixa. A verificação acontece em segundo plano.
- Se a verificação indicar risco, o mesmo formulário pede um desafio visual simples antes de aceitar o envio.
- O selo da verificação permanece visível e não cobre o aviso de cookies no celular.
- Administrador e moderador não são interrompidos por essa verificação. Visitante e demais usuários autenticados continuam verificados no contato e no login.
- Um único ajuste de política vale para todos esses formulários. Cada formulário pode ser ligado ou desligado sem alterar código de cadastro.
- As chaves secretas ficam fora do repositório, no arquivo de ambiente de cada servidor.
- A busca de vagas, os filtros de listagem e os formulários do painel autenticado não recebem essa verificação.

### Fora

- Troca do serviço Google por outro provedor.
- Verificação em busca, filtros de vagas ou formulários administrativos de conteúdo.
- Ligar a verificação em todo formulário do site (isso derruba o cache das páginas públicas).
- Gravar segredo de API no repositório ou neste documento.
- Alteração de `core/` ou `vendor/`.

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Visitante envia sem atrito (Priority: P1)

Visitante preenche login, cadastro, recuperação de senha ou contato e envia. Se a interação parecer legítima, o formulário segue sem caixa extra nem desafio de imagens.

**Why this priority**: O atrito atual do checkbox em todo cadastro aumenta abandono. A proteção precisa continuar.

**Independent Test**: Com a verificação ligada e chaves válidas, enviar cada formulário como visitante em rede residencial comum e concluir sem desafio visual.

**Acceptance Scenarios**:

1. **Given** visitante em `/user/login`, **When** informa credenciais válidas, **Then** entra sem marcar uma caixa de verificação.
2. **Given** visitante em `/cadastro/candidato` ou `/cadastro/empresa` com dados válidos, **When** envia, **Then** a conta é criada sem desafio visual.
3. **Given** visitante na página de contato, **When** envia a mensagem, **Then** a mensagem é aceita sem desafio visual.
4. **Given** visitante em recuperar senha, **When** informa um e-mail, **Then** o pedido segue sem desafio visual.

---

### User Story 2 — Envio suspeito pede confirmação humana (Priority: P1)

Quando a verificação em segundo plano falha ou indica automação, o visitante vê um desafio visual no próprio formulário e uma mensagem clara em português. Sem concluir o desafio, o envio não entra.

**Why this priority**: Proteção só invisível deixa passar automação com pontuação baixa. O desafio de reserva fecha essa brecha.

**Independent Test**: Simular pontuação baixa ou token ausente e confirmar que o envio é recusado até o desafio visual ser resolvido.

**Acceptance Scenarios**:

1. **Given** um envio classificado como risco, **When** o visitante tenta concluir, **Then** o formulário mostra um desafio visual e não grava o envio ainda.
2. **Given** o desafio visual resolvido, **When** o visitante reenvia, **Then** o fluxo original conclui.
3. **Given** o desafio visual ignorado ou incorreto, **When** o visitante reenvia, **Then** vê uma mensagem em português e nada é gravado.
4. **Given** pedido automatizado sem prova de verificação, **When** chega ao servidor, **Then** é recusado.

---

### User Story 3 — Operação liga e desliga por formulário (Priority: P2)

Quem administra o site vê a lista dos formulários protegidos e altera a política num só lugar. Busca e filtros de vagas continuam sem verificação.

**Why this priority**: Hoje a proteção está espalhada e o contato ficou de fora. A operação precisa de um ponto único.

**Independent Test**: Desligar a proteção de um formulário da lista e confirmar que só esse deixa de verificar; a busca de vagas nunca exibe o selo nem o desafio.

**Acceptance Scenarios**:

1. **Given** a lista de formulários protegidos, **When** o administrador desativa o contato, **Then** o contato envia sem verificação e os cadastros continuam protegidos.
2. **Given** a página de vagas, **When** o visitante filtra ou busca, **Then** não aparece selo nem desafio de verificação.
3. **Given** administrador ou moderador autenticado, **When** abre um formulário protegido, **Then** não é obrigado a passar pela verificação.
4. **Given** estudante ou empresa autenticados na página de contato, **When** enviam mensagem, **Then** a verificação continua valendo.

---

### User Story 4 — Selo visível e clicável (Priority: P2)

O selo da verificação aparece, não tapa o aviso de cookies no celular e, quando o desafio visual surge, o clique funciona no desktop e no mobile.

**Why this priority**: Um selo escondido viola a regra de uso do serviço. Um desafio que não recebe clique trava o cadastro.

**Independent Test**: Abrir contato e cadastro em viewport larga e em 390px, com o aviso de cookies aberto e fechado.

**Acceptance Scenarios**:

1. **Given** um formulário protegido, **When** a página carrega, **Then** o selo da verificação está visível.
2. **Given** o aviso de cookies aberto no celular, **When** o visitante olha o canto da tela, **Then** o selo não fica coberto pelo aviso.
3. **Given** o desafio visual de reserva, **When** o visitante toca ou clica, **Then** o desafio responde.
4. **Given** a home ou a listagem de vagas sem formulário protegido, **When** a página carrega, **Then** o script da verificação não é exigido para usar a busca.

### Edge Cases

- Ambiente local pode desligar a verificação por configuração de ambiente, para testes sem chaves. Produção não usa esse desligamento.
- Chave secreta ausente no servidor: o envio legítimo não pode ser aceito como se a verificação tivesse passado.
- Reimportar configuração não pode apagar a chave secreta já colocada no arquivo de ambiente.
- Reexecução do deploy não duplica a proteção nem coloca desafio em busca ou filtros.
- Dois desafios no mesmo cadastro (código antigo mais a lista) não podem aparecer juntos.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Visitantes devem ser verificados ao entrar, recuperar senha, criar conta de estudante, criar conta de empresa, criar conta pela rota padrão e enviar contato.
- **FR-002**: A verificação padrão não exige ação do visitante quando a interação é considerada legítima.
- **FR-003**: Interação com pontuação de risco, token ausente ou token inválido deve exigir um desafio visual no mesmo formulário antes de aceitar o envio.
- **FR-004**: A mensagem de falha deve estar em português do Brasil.
- **FR-005**: O limiar que separa envio livre de desafio visual é 0,5 (metade da escala, em que 1 é muito provavelmente humano).
- **FR-006**: O selo da verificação permanece visível. Não substituir o selo por texto escondido.
- **FR-007**: Busca, filtros de vagas e formulários de edição de conteúdo não recebem essa verificação.
- **FR-008**: Administrador e moderador podem ignorar a verificação. Os demais papéis, inclusive autenticados, não.
- **FR-009**: As chaves secretas existem só no arquivo de ambiente de cada servidor. O repositório guarda no máximo a chave pública já publicada.
- **FR-010**: Desligar a verificação no ambiente local continua possível pela configuração já usada nesse ambiente, sem valer para produção.
- **FR-011**: A proteção de cada formulário da lista FR-001 é independente: desligar um não desliga os outros.
- **FR-012**: O desafio visual de reserva usa tema claro e tamanho padrão, legível em desktop e mobile.

### Key Entities

- **Política de verificação**: desafio padrão, limiar, mensagem de erro e desafio de reserva. Uma política serve a todos os formulários da lista.
- **Ponto de formulário**: liga ou desliga a política num formulário identificado.
- **Segredo de ambiente**: chave secreta do serviço, presente só no servidor.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Nos cinco fluxos (login, senha, cadastro de estudante, cadastro de empresa e contato), um visitante legítimo conclui o envio sem desafio visual em condições normais de uso.
- **SC-002**: 100% desses cinco fluxos recusam envio sem prova válida de verificação quando a proteção está ligada.
- **SC-003**: Um envio de risco exibe o desafio visual em menos de um ciclo de reenvio (o visitante vê o desafio na resposta daquela tentativa).
- **SC-004**: A busca de vagas não exibe selo nem desafio, e a listagem continua utilizável sem espera extra de verificação.
- **SC-005**: No celular, com o aviso de cookies aberto, o selo permanece visível e fora da área do aviso.
- **SC-006**: Nenhuma chave secreta nova entra no repositório.

## Assumptions

- O serviço continua sendo o reCAPTCHA do Google já contratado pelo produto. Não há troca de fornecedor nesta feature.
- As chaves públicas já configuradas permanecem. Se o segredo de produção estiver só no banco, ele é copiado para o arquivo de ambiente antes de importar configuração.
- O ambiente Docker local segue com a verificação desligada até existirem segredos de teste.
- O desafio visual só aparece na reserva, não em todo envio.
- Estudante e empresa autenticados não recebem isenção; só administrador e moderador.
