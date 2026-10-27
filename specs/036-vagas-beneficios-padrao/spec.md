# Feature Specification: Benefícios Padrão em Todas as Vagas

**Feature Directory**: `specs/036-vagas-beneficios-padrao`  
**Feature Branch**: `036-vagas-beneficios-padrao`  
**Created**: 2026-10-27  
**Status**: Draft  
**Predecessor**: `030-vagas-detalhe-layout` / `031-vagas-detalhe-refino` (estrutura de benefícios já existe; seção some quando o campo está vazio)  
**Input do usuário**: Garantir presença e renderização do bloco "Benefícios" em todas as vagas — seed dos 4 benefícios padrão do Figma (ícones + textos), backfill das vagas existentes sem benefícios, tornar o campo obrigatório e alinhar a apresentação visual ao layout Figma (grid de cards).

## Escopo

### Inclui

- Garantir que **toda vaga** (existente e nova) exiba a seção **Benefícios** com os quatro itens padrão do Figma, quando ainda não houver benefícios editoriais:
  1. Plano de Carreira (ícone mala de couro)
  2. Vale Transporte (ícone maleta azul)
  3. Vale Refeição (ícone talheres)
  4. Horário Flexível (ícone relógio)
- Disponibilizar os quatro ícones como arquivos gerenciados do produto (a partir de assets versionados no módulo de configuração), reutilizáveis no seed.
- Preencher automaticamente, em deploy, todas as vagas cujo campo de benefícios estiver **vazio**, sem sobrescrever benefícios já cadastrados pelo editorial.
- Tornar o campo de benefícios da vaga **obrigatório** para que novas vagas não sejam publicadas sem ao menos um benefício.
- Alinhar a apresentação pública da seção Benefícios no detalhe da vaga ao layout Figma: título da seção, grade responsiva de cards (2 colunas no mobile / 4 no desktop), card com fundo branco, borda sutil, cantos arredondados, ícone centralizado com tamanho controlado e texto semibold em azul escuro.
- Deploy 100% automatizado (backfill + obrigatoriedade do campo + exportação de configuração estrutural); atualização cirúrgica do `PRD.md` nas seções de campos da vaga, se aplicável.

### Fora

- Criar novo tipo de paragraph ou novo campo de storage (reutilizar o modelo já existente de benefícios da vaga das features 030/031).
- Alterar benefícios já preenchidos editorialmente (mesmo que diferentes do padrão Figma).
- Redesign de outras seções do detalhe da vaga (Sobre, Requisitos, FAQ, sidebar, CTA, header).
- Listagem `/vagas`, cards de home / para-estudantes, ou benefícios de outras landings (`022-beneficios-estudantes`, PE).
- Upload manual obrigatório pelo editor como etapa de deploy (ícones vêm dos assets versionados).
- Código de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê Benefícios em toda vaga publicada (Priority: P1)

Um visitante abre o detalhe de qualquer vaga publicada e encontra a seção "Benefícios" com cards (ícone + texto), alinhados ao layout visual do produto, em vez de a seção desaparecer por falta de dados.

**Why this priority**: É o valor de negócio principal — acabar com páginas de vaga sem a seção Benefícios.

**Independent Test**: Abrir vagas que antes não tinham benefícios e confirmar título + quatro cards padrão; abrir uma vaga que já tinha benefícios customizados e confirmar que o conteúdo editorial permanece.

**Acceptance Scenarios**:

1. **Given** uma vaga publicada que antes não tinha benefícios, **When** o visitante abre a página da vaga após o deploy, **Then** a seção "Benefícios" aparece com os quatro itens padrão (Plano de Carreira, Vale Transporte, Vale Refeição, Horário Flexível), cada um com ícone e texto.
2. **Given** uma vaga publicada que já tinha um ou mais benefícios preenchidos, **When** o visitante abre a página, **Then** os benefícios editoriais existentes permanecem inalterados (sem substituição pelo padrão).
3. **Given** viewport mobile e desktop, **When** a seção Benefícios renderiza, **Then** os cards aparecem em grade 2 colunas (mobile) e até 4 colunas (desktop), com card branco, borda sutil, cantos arredondados, ícone centralizado sem distorção e texto legível em azul escuro.

---

### User Story 2 — Editor não publica vaga sem benefícios (Priority: P1)

Um editor cria ou edita uma vaga e o sistema exige ao menos um benefício antes de salvar/publicar, evitando novas vagas “sem seção Benefícios”.

**Why this priority**: Previne regressão — o backfill cobre o passado; a obrigatoriedade cobre o futuro.

**Independent Test**: Tentar salvar uma vaga nova sem benefícios e confirmar bloqueio/validação; salvar com os quatro padrões (ou outro conjunto válido) e confirmar sucesso.

**Acceptance Scenarios**:

1. **Given** formulário de vaga com o campo de benefícios vazio, **When** o editor tenta salvar, **Then** o sistema impede o salvamento e indica que benefícios são obrigatórios.
2. **Given** formulário com pelo menos um benefício válido (ícone e/ou texto conforme regras do tipo), **When** o editor salva, **Then** a vaga é persistida e a seção Benefícios aparece no detalhe público.

---

### User Story 3 — Operação aplica o padrão em massa no deploy (Priority: P1)

A equipe de operação aplica a atualização no ambiente de destino e, sem passos manuais no admin, todas as vagas sem benefícios passam a ter o padrão, os ícones ficam disponíveis como arquivos gerenciados e a obrigatoriedade do campo entra em vigor.

**Why this priority**: Regra de produto — zero passos manuais em produção para ativar a feature.

**Independent Test**: Em ambiente com vagas sem benefícios e assets dos quatro ícones presentes, executar a receita de deploy e auditar amostra de vagas + formulário + export de configuração.

**Acceptance Scenarios**:

1. **Given** ambiente com N vagas sem benefícios e os quatro arquivos de ícone versionados nos assets do módulo, **When** a atualização de deploy roda, **Then** essas N vagas passam a ter exatamente os quatro benefícios padrão com ícones e textos corretos.
2. **Given** a mesma atualização, **When** reexecutada (idempotência), **Then** não duplica benefícios nem recria arquivos desnecessariamente; vagas já preenchidas continuam intactas.
3. **Given** deploy concluído, **When** a configuração estrutural é exportada/consolidada, **Then** a obrigatoriedade do campo de benefícios da vaga está refletida na configuração versionada do projeto.

---

### Edge Cases

- Vaga com campo de benefícios **parcialmente** preenchido (já tem itens): não recebe o seed padrão; permanece como está.
- Arquivo de ícone ausente na pasta de assets no momento do deploy: a atualização falha de forma explícita (mensagem clara) e **não** marca o campo como obrigatório sem ter concluído o backfill com sucesso.
- Volume alto de vagas: o preenchimento em massa deve concluir sem esgotar memória do processo e sem deixar lote parcial silencioso — progresso e resultado devem ser reportáveis (quantas vagas atualizadas / já ok / erros).
- Vaga em rascunho ou não publicada: também recebe o padrão se o campo estiver vazio (todas as vagas do tipo, independentemente do status de publicação).
- Ícone ou texto individual inválido no editorial futuro: a renderização pública omite o card incompleto sem quebrar a página; a seção só aparece se houver ao menos um card válido.

## Requirements *(obrigatório)*

### Functional Requirements

- **FR-001**: O produto MUST exibir a seção "Benefícios" no detalhe público de toda vaga que possua ao menos um benefício válido.
- **FR-002**: O produto MUST disponibilizar quatro benefícios padrão com textos exatamente: "Plano de Carreira", "Vale Transporte", "Vale Refeição" e "Horário Flexível", associados respectivamente aos ícones 1–4 do Figma (mala de couro, maleta azul, talheres, relógio).
- **FR-003**: No deploy, o sistema MUST preencher automaticamente todas as vagas cujo campo de benefícios estiver vazio com os quatro benefícios padrão (ícone + texto).
- **FR-004**: O preenchimento automático MUST NOT sobrescrever benefícios já existentes em uma vaga.
- **FR-005**: Após o backfill bem-sucedido, o campo de benefícios da vaga MUST tornar-se obrigatório para criação/edição futuras.
- **FR-006**: Os ícones padrão MUST ser armazenados como arquivos gerenciados a partir de assets versionados no módulo de configuração do projeto (pasta dedicada de benefícios), sem commit de arquivos públicos de upload no repositório.
- **FR-007**: A atualização de deploy MUST ser idempotente: reexecução não duplica benefícios nem corrompe dados editoriais.
- **FR-008**: Em volumes grandes de vagas, o backfill MUST concluir sem falha por esgotamento de memória em catálogos típicos do produto, processando o conjunto de forma escalável e previsível.
- **FR-009**: A apresentação pública MUST seguir o layout Figma da seção Benefícios: título da seção; grade responsiva 2×2 (mobile) / 4 colunas (desktop); cards com fundo branco, borda fina sutil, cantos arredondados, padding generoso; ícone limitado (~48×48, sem distorção) com espaço abaixo; texto menor, semibold, azul escuro.
- **FR-010**: A configuração estrutural resultante (incluindo obrigatoriedade do campo) MUST ser exportável e versionada junto ao código da feature.
- **FR-011**: A feature MUST reutilizar o modelo de dados já existente de benefícios da vaga (paragraph de benefício + campo de referência na vaga), sem criar storage paralelo.

### Key Entities

- **Vaga**: Anúncio de estágio; possui coleção de benefícios; após esta feature, benefícios passam a ser obrigatórios.
- **Benefício da vaga**: Item da coleção (ícone + rótulo curto); os quatro padrões Figma são o seed inicial quando a coleção está vazia.
- **Ícone de benefício (arquivo gerenciado)**: Imagem associada a cada benefício padrão; originada dos assets versionados e reutilizada no seed.
- **Configuração do campo de benefícios**: Define se o campo é obrigatório; passa de opcional para obrigatório após o backfill.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: 100% das vagas que estavam sem benefícios passam a exibir a seção Benefícios com os quatro itens padrão após o deploy.
- **SC-002**: 0% das vagas que já tinham benefícios editoriais têm esses dados alterados ou duplicados pelo seed.
- **SC-003**: Em amostragem de validação (desktop e mobile), 100% das páginas de vaga com benefícios renderizam a grade de cards conforme o layout acordado (título visível, 2/4 colunas, ícones nítidos sem distorção).
- **SC-004**: Tentativa de salvar vaga nova sem benefícios falha em 100% dos casos após a feature; com benefícios válidos, o salvamento sucede.
- **SC-005**: Reexecução do deploy de backfill em ambiente já atualizado resulta em zero duplicações e zero alterações editoriais (idempotência verificável por contagem antes/depois).
- **SC-006**: Backfill em catálogo com centenas de vagas conclui sem interrupção por falta de memória e reporta quantas vagas foram atualizadas.

## Assumptions

- O modelo de dados de benefícios da vaga já existe (features 030/031): campo de referência na vaga → itens de benefício com imagem e texto curto. O pedido verbal `field_text_simple_small` mapeia para o campo canônico já em uso no paragraph (`field_text_simple`).
- Os quatro arquivos de ícone serão fornecidos/colocados em `modules/custom/custom_configs/assets/beneficios/` com nomes `beneficios-01.png` … `beneficios-04.png` antes da execução do deploy no ambiente de origem/destino.
- “Campo vazio” significa ausência total de itens na coleção de benefícios; coleções parciais não recebem complemento automático.
- Todas as vagas do tipo (qualquer status de publicação) entram no backfill quando vazias.
- Ordem no deploy: primeiro popular vagas vazias; só então aplicar obrigatoriedade do campo — evitando estado inconsistente.
- Alinhamento visual reutiliza o detalhe full da vaga já existente; ajustes ficam isolados à seção Benefícios (markup da grade/cards e estilos dedicados).
- Práticas de qualidade na implementação (código limpo, tipagem estrita em PHP, processamento em lotes) seguem as regras do projeto e, quando aplicável, revisão com skills do ecossistema (ex.: `mattpocock/skills@code-review`); não há skill mattpocock específica para PHP/batch Drupal no inventário consultado — o padrão de hooks idempotentes do próprio `custom_configs` permanece a referência de implementação.
- Atualização cirúrgica do `PRD.md` (§ campos da vaga / obrigatoriedade) faz parte da entrega de implementação, não desta etapa de especificação.

## Dependências

- Estrutura e templates base do detalhe da vaga (`030` / `031`).
- Módulo de configuração customizada e pasta de assets versionados (padrão de deploy do projeto).
- Receita de destino do projeto: import de configuração + atualizações de banco + rebuild de caches, conforme runbook vigente.
- Exportação final de configuração (`drush cex`) no ambiente de origem após tornar o campo obrigatório.
