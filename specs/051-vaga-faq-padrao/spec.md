# Feature Specification: FAQ Padrão Único em Todas as Vagas

**Feature Directory**: `specs/051-vaga-faq-padrao`  
**Feature Branch**: `051-vaga-faq-padrao`  
**Created**: 2026-11-02  
**Status**: Draft  
**Predecessor**: `038-vaga-faq-accordion` (Accordion “Dúvidas Frequentes” no detalhe; CT `faq` + `field_vaga_faq` selecionável por vaga); `036-vagas-beneficios-padrao` (precedente de conteúdo padrão + deploy idempotente)  
**Input do usuário**: No tipo de conteúdo Vagas, o FAQ exibido SEMPRE será o mesmo — não é selecionável a cada vaga. Definir um FAQ padrão e exibi-lo em todas as vagas. Facilitar o deploy.

## Escopo

### Inclui

- Definir **uma única coleção FAQ padrão** do produto (reutilizar a coleção seed já canônica das features 030/038, com conteúdo editorial gerenciável no CMS).
- Exibir essa mesma coleção na seção **Dúvidas Frequentes** do detalhe público de **todas** as vagas publicadas que tenham FAQ padrão válido (publicado + ≥1 item com pergunta e resposta).
- Remover a necessidade de o editor escolher FAQ por vaga: o campo de associação por vaga deixa de aparecer no formulário de edição de Vagas (e deixa de ser a fonte de verdade da renderização pública).
- Manter o Accordion e a posição visual já entregues na feature 038 (coluna principal, após “Sobre a Empresa”, antes do CTA final); esta feature muda **fonte de dados**, não o redesign visual.
- Garantir no deploy, de forma idempotente: existência da coleção FAQ padrão; renderização global ativa; formulário de Vagas sem seletor por vaga; zero passos manuais no admin de produção.
- Exportar configurações estruturais alteradas (`drush cex` na origem) e atualizar cirurgicamente o `PRD.md` (FAQ da vaga deixa de ser “associável por vaga”).

### Fora

- Redesign do Accordion, tipografia, CSS ou ordem das seções do detalhe (já cobertos por 038).
- FAQ global de páginas institucionais (Quem Somos, Contato, etc.).
- Permitir FAQ distinto por vaga, empresa ou curso (explicitamente fora — o produto exige FAQ único).
- Criar novo Content Type ou paragraph type de FAQ.
- Alterações em `core/` ou `vendor/`.
- Código de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o mesmo FAQ em qualquer vaga (Priority: P1)

Um visitante abre o detalhe de qualquer vaga publicada e encontra a seção “Dúvidas Frequentes” com as mesmas perguntas e respostas do FAQ padrão do produto (quando este estiver válido).

**Why this priority**: É o valor de negócio principal — acabar com FAQ diferente (ou ausente) por vaga e garantir consistência no funil.

**Independent Test**: Abrir duas ou mais vagas publicadas distintas e comparar o conteúdo do Accordion; deve ser idêntico.

**Acceptance Scenarios**:

1. **Given** o FAQ padrão publicado com ≥1 item válido, **When** o visitante abre qualquer vaga publicada, **Then** a seção “Dúvidas Frequentes” aparece com exatamente os itens desse FAQ padrão.
2. **Given** duas vagas publicadas quaisquer, **When** o visitante compara as seções FAQ, **Then** perguntas, respostas e ordem dos itens são as mesmas.
3. **Given** a seção renderizada, **When** o visitante expande um item, **Then** o Accordion se comporta como na feature 038 (cards isolados, sem sair da página).

---

### User Story 2 — Editor não escolhe FAQ por vaga (Priority: P1)

Um editor cria ou edita uma vaga e **não** vê (nem precisa preencher) o campo “FAQ da vaga”; para alterar as dúvidas exibidas em todas as vagas, edita apenas o conteúdo do FAQ padrão.

**Why this priority**: Remove atrito editorial e evita erro humano de FAQ errado/ausente por anúncio.

**Independent Test**: Abrir o formulário de edição de uma vaga e confirmar ausência do seletor; editar o FAQ padrão e ver a mudança refletida em todas as vagas.

**Acceptance Scenarios**:

1. **Given** permissão de editar vagas, **When** o editor abre o formulário de uma vaga (nova ou existente), **Then** o campo de seleção “FAQ da vaga” **não** está disponível.
2. **Given** permissão de editar o FAQ padrão, **When** o editor altera pergunta/resposta e publica, **Then** o detalhe de qualquer vaga publicada passa a refletir a alteração sem editar cada vaga.
3. **Given** valores legados de associação FAQ em vagas antigas, **When** o detalhe público renderiza, **Then** esses valores **não** influenciam o conteúdo exibido (a fonte é sempre o FAQ padrão).

---

### User Story 3 — Seção some só quando o FAQ padrão é inválido (Priority: P1)

Se o FAQ padrão estiver unpublished, inexistente ou sem itens válidos, nenhuma vaga exibe a seção — evitando conteúdo quebrado; assim que o FAQ padrão voltar a ser válido, todas as vagas voltam a exibir.

**Why this priority**: Comportamento previsível e seguro com uma única fonte de verdade.

**Independent Test**: Despublicar (ou esvaziar) o FAQ padrão e abrir uma vaga; republicar e reabrir.

**Acceptance Scenarios**:

1. **Given** FAQ padrão unpublished ou sem itens válidos, **When** o visitante abre qualquer vaga, **Then** a seção “Dúvidas Frequentes” não aparece.
2. **Given** FAQ padrão republicado com itens válidos, **When** o visitante recarrega qualquer vaga, **Then** a seção reaparece com o conteúdo atualizado.

---

### User Story 4 — Deploy automatizado ativa o FAQ padrão (Priority: P1)

A equipe de operação aplica a atualização no destino e, sem passos manuais, o FAQ padrão existe, todas as vagas públicas passam a usá-lo, o formulário de Vagas deixa de oferecer seleção por vaga, e a configuração exportável fica versionada.

**Why this priority**: Regra permanente do projeto — zero passos manuais estruturais em produção.

**Independent Test**: Aplicar a receita de deploy em ambiente com vagas sem FAQ (ou com FAQs distintos) e validar formulário + detalhe público + reexecução idempotente.

**Acceptance Scenarios**:

1. **Given** destino após deploy, **When** o visitante abre vagas que antes não tinham FAQ (ou tinham FAQ diferente), **Then** todas exibem o FAQ padrão válido.
2. **Given** o mesmo deploy, **When** o editor abre o formulário de Vagas, **Then** o seletor “FAQ da vaga” não está presente.
3. **Given** reexecução completa do update, **When** roda de novo, **Then** não duplica a coleção FAQ padrão nem altera conteúdo editorial já divergente do seed (apenas garante existência e a regra de exibição global).
4. **Given** deploy concluído na origem, **When** a configuração estrutural é exportada, **Then** a remoção/ocultação do campo no formulário de Vagas está refletida no versionamento do projeto.

---

### Edge Cases

- FAQ padrão com muitos itens (≥8): Accordion permanece utilizável; comportamento visual igual ao 038.
- Item com só pergunta ou só resposta: omitido na renderização; se nenhum item válido restar, a seção some em **todas** as vagas.
- Vaga em rascunho: no preview/detalhe conforme acesso, a fonte continua sendo o FAQ padrão (não o campo legado por vaga).
- Ambiente sem a coleção seed ainda criada: o deploy cria/garante a coleção padrão de forma idempotente antes de ativar a exibição global.
- Valores antigos em associação por vaga: podem permanecer no banco até limpeza estrutural, mas **nunca** alimentam a página pública após esta feature.
- Visitante anônimo sem permissão de ver FAQ unpublished: seção omitida (mesmo critério de acesso público do CMS).

## Requirements *(obrigatório)*

### Functional Requirements

**Fonte única**

- **FR-001**: O produto MUST possuir exatamente uma coleção FAQ designada como **FAQ padrão das vagas**.
- **FR-002**: O detalhe público de toda vaga MUST obter o conteúdo de “Dúvidas Frequentes” exclusivamente desse FAQ padrão — MUST NOT usar associação escolhida por vaga.
- **FR-003**: Quando o FAQ padrão estiver publicado e contiver ≥1 item válido (pergunta e resposta preenchidas), a seção MUST aparecer em 100% das vagas publicadas acessíveis ao visitante.
- **FR-004**: Quando o FAQ padrão estiver inválido (ausente, unpublished ou sem itens válidos), a seção MUST NOT aparecer em nenhuma vaga.

**Editorial**

- **FR-005**: O formulário de criação/edição de Vagas MUST NOT exibir campo para selecionar FAQ por vaga.
- **FR-006**: Editores MUST continuar podendo editar perguntas e respostas apenas no conteúdo do FAQ padrão (fluxo CMS do tipo FAQ já existente).
- **FR-007**: Alterações publicadas no FAQ padrão MUST refletir automaticamente em todas as vagas sem republicar cada vaga.

**Apresentação**

- **FR-008**: Posição, título (“Dúvidas Frequentes”) e padrão Accordion MUST permanecer alinhados à feature 038 (coluna principal; após “Sobre a Empresa” quando existir; antes do CTA final).
- **FR-009**: Itens inválidos MUST ser omitidos; a seção só renderiza se houver ao menos um item válido no FAQ padrão.

**Deploy e configuração**

- **FR-010**: O deploy MUST garantir de forma idempotente a existência da coleção FAQ padrão (reutilizando a coleção seed canônica quando já existir; criando-a se ausente).
- **FR-011**: O deploy MUST ativar a regra de exibição global e a ausência do seletor no formulário de Vagas sem passos manuais no admin de produção.
- **FR-012**: Reexecução do deploy MUST NOT duplicar a coleção FAQ padrão nem sobrescrever conteúdo editorial já divergente do seed (apenas preenche ausências / garante estrutura e regra).
- **FR-013**: Alterações estruturais exportáveis (ex.: formulário/display de Vagas) MUST ser exportadas e versionadas com a feature (`drush cex` na origem).
- **FR-014**: A documentação de produto (`PRD.md`) MUST refletir que o FAQ das vagas é único/padrão, não selecionável por anúncio.

### Key Entities

- **FAQ padrão das vagas**: Única coleção de perguntas e respostas usada por todos os anúncios de estágio; gerenciada como conteúdo FAQ no CMS.
- **Item FAQ**: Par pergunta + resposta dentro da coleção padrão; cada item válido vira um painel do Accordion.
- **Vaga**: Anúncio de estágio; deixa de carregar FAQ próprio; apenas hospeda a seção no detalhe.
- **Associação legada vaga→FAQ**: Dados históricos por vaga que deixam de alimentar a exibição pública.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em amostragem de ≥3 vagas publicadas distintas após o deploy, 100% exibem o mesmo conjunto de perguntas/respostas do FAQ padrão (quando válido).
- **SC-002**: 0% das páginas de vaga publicadas exibem FAQ diferente do padrão ou FAQ proveniente de seleção individual legada.
- **SC-003**: Em 100% dos formulários de Vagas (criar/editar), o campo de seleção “FAQ da vaga” está ausente.
- **SC-004**: Após editar e publicar o FAQ padrão, a mudança aparece no detalhe de qualquer vaga publicada em até 2 minutos no fluxo normal de CMS (sem editar as vagas).
- **SC-005**: Com FAQ padrão inválido, 100% das vagas amostradas omitem a seção “Dúvidas Frequentes”.
- **SC-006**: Reexecução do deploy resulta em 0 duplicatas da coleção padrão e 0 sobrescritas de conteúdo editorial já divergente do seed.

## Assumptions

- A coleção seed canônica já usada no produto (hoje rotulada editorialmente como exemplo, UUID fixo já adotado nas features 030/038) **é** o FAQ padrão das vagas; não se cria uma segunda coleção “oficial” paralela.
- O conteúdo textual inicial do seed (ex.: “Qual a duração do estágio?”, “Existe auxílio home office?”) permanece até o editorial alterar; esta feature não exige novos textos.
- Remover a seleção por vaga no formulário é obrigatório; remover fisicamente o storage do campo legado pode ocorrer na implementação se simplificar o modelo, desde que o deploy continue idempotente e sem perda de outras configs — a página pública não depende mais desse campo.
- Visual/Accordion da 038 permanece; não há novo layout Figma nesta entrega.
- Receita de destino permanece a do runbook vigente (`cim` → `updb` → `cim` → `cr`), salvo exceção documentada no plano.
- Atualização cirúrgica do `PRD.md` e `drush cex` na origem fazem parte da entrega de implementação, não desta etapa de especificação.

## Dependências

- Accordion e templates do detalhe (`038-vaga-faq-accordion`).
- CT `faq`, paragraph `faq_item_p`, coleção seed canônica e helpers de ensure/seed em `custom_configs`.
- Tema `default` (`node--vagas--full.html.twig` + include do Accordion).
- Runbook de deploy de configurações do projeto.
