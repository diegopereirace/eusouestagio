# Feature Specification: Bloco "Sobre a Empresa" no Detalhe da Vaga

**Feature Directory**: `specs/037-vaga-sobre-empresa`  
**Feature Branch**: `037-vaga-sobre-empresa`  
**Created**: 2026-10-27  
**Status**: Draft  
**Predecessor**: `034-empresa-pagina-publica` (página pública da empresa + remoção do bloco legado “Sobre a Empresa” baseado em usuário); `032-vaga-header-empresa` (`field_vaga_empresa`); `036-vagas-beneficios-padrao` (ordem atual das seções na coluna principal)  
**Input do usuário**: Adicionar bloco “Sobre a Empresa” no detalhe da vaga (resumo + logo + link “Ver Empresa”), com campo de descrição curta na entidade Empresa, seed na EcoConstrutora e deploy 100% automatizado.

## Escopo

### Inclui

- Exibir, na **coluna principal** do detalhe público da vaga, um bloco de resumo **Sobre a Empresa** quando a vaga tiver empresa publicada vinculada.
- Posicionar o bloco **acima da seção FAQ** (e abaixo das seções de conteúdo da vaga — Sobre a Vaga, Requisitos, Benefícios). Quando não houver FAQ, o bloco permanece no final da coluna principal antes do fechamento da coluna.
- Conteúdo do bloco (layout Figma):
  - Cartão com fundo azul muito claro, borda sutil e cantos arredondados;
  - Logo da empresa à esquerda (quadrado fixo, cantos arredondados, cobertura sem distorção);
  - Título dinâmico no formato “Sobre a {nome da empresa}”;
  - Subtítulo com texto curto de resumo da empresa;
  - Ação “Ver Empresa” à direita, levando à página pública canônica da empresa.
- Garantir na entidade **Empresa** um campo editorial de **resumo curto** (texto simples, até 255 caracteres) usado como subtítulo do bloco — distinto da descrição longa já usada na página da empresa.
- Disponibilizar o campo de resumo nos formulários e displays de visualização da Empresa para editores.
- Popular o resumo da empresa seed **EcoConstrutora** (quando ainda vazio) com o texto do Figma: “Líder regional em construções sustentáveis e infraestrutura urbana de alto impacto.”
- Deploy 100% automatizado (estrutura + displays + seed do resumo) sem passos manuais no admin de produção; exportação das configurações estruturais; atualização cirúrgica do `PRD.md` nas seções de campos da Empresa, se aplicável.

### Fora

- Redesign da página pública completa da empresa (permanece a versão da feature 034).
- Reintrodução do bloco legado baseado em `field_empresa_u` (usuário); este bloco usa **somente** a referência `field_vaga_empresa` → node Empresa.
- Alterar ou truncar automaticamente a descrição longa da empresa no lugar do resumo curto (o resumo é campo próprio, não derivado).
- Seção “Processo de Contratação”, Match, CTA inline ou demais seções ausentes do detalhe atual.
- Migração em massa de empresas existentes para preencher o resumo (apenas seed EcoConstrutora nesta entrega; demais ficam a cargo editorial).
- Alterações em listagens de vagas, header da vaga (além do que já existe), sidebar ou FAQ.
- Alterações em `core/` ou `vendor/`.
- Código de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante conhece a empresa sem sair da vaga (Priority: P1)

Um visitante abre o detalhe de uma vaga com empresa vinculada e, na coluna principal (acima do FAQ), vê um cartão “Sobre a {Empresa}” com logo, resumo curto e um botão para ir à página completa da empresa.

**Why this priority**: É o valor principal da feature — contextualizar a empresa no fluxo de leitura da vaga e oferecer caminho claro para o perfil completo.

**Independent Test**: Abrir a vaga seed “Engenheiro Civil” (EcoConstrutora) e verificar presença do cartão, textos do Figma e navegação do botão “Ver Empresa”.

**Acceptance Scenarios**:

1. **Given** uma vaga publicada com empresa publicada vinculada, logo e resumo preenchidos, **When** o visitante abre o detalhe, **Then** o bloco “Sobre a {nome}” aparece na coluna principal acima do FAQ (ou no final da coluna se não houver FAQ).
2. **Given** o bloco renderizado, **When** o visitante observa o layout, **Then** vê cartão azul claro com borda sutil e cantos arredondados; logo à esquerda; título e subtítulo no meio; botão “Ver Empresa” à direita (em desktop) e empilhamento legível em mobile.
3. **Given** o botão “Ver Empresa”, **When** o visitante clica, **Then** é levado à página pública canônica daquela empresa.

---

### User Story 2 — Visitante não vê bloco órfão sem empresa (Priority: P1)

Quando a vaga não tem empresa associada (ou a empresa não está acessível/publicada), o detalhe não mostra o cartão “Sobre a Empresa”, sem erros ou espaços vazios quebrados.

**Why this priority**: Evita regressão visual e conteúdo inconsistente em vagas legadas ou incompletas.

**Independent Test**: Abrir (ou simular) vaga sem `field_vaga_empresa` e confirmar ausência do bloco; abrir vaga com empresa e confirmar presença.

**Acceptance Scenarios**:

1. **Given** vaga sem empresa vinculada, **When** o detalhe renderiza, **Then** o bloco “Sobre a Empresa” não aparece.
2. **Given** vaga cuja referência de empresa aponta para conteúdo não publicado/inacessível, **When** o detalhe renderiza, **Then** o bloco não aparece.

---

### User Story 3 — Editor gerencia o resumo curto da empresa (Priority: P1)

Um editor edita o node Empresa, preenche o campo de resumo curto (até 255 caracteres) e o subtítulo do bloco no detalhe da vaga reflete esse texto.

**Why this priority**: Sem o campo editorial, o bloco não tem subtítulo sustentável e distinto da descrição longa da página da empresa.

**Independent Test**: Editar EcoConstrutora (ou empresa de teste), alterar o resumo, salvar e recarregar o detalhe de uma vaga vinculada.

**Acceptance Scenarios**:

1. **Given** permissão de editar Empresa, **When** o editor abre o formulário, **Then** encontra o campo de resumo curto e consegue salvar um texto de até 255 caracteres.
2. **Given** resumo salvo na empresa, **When** o visitante abre uma vaga vinculada a essa empresa, **Then** o subtítulo do bloco exibe exatamente esse resumo.
3. **Given** empresa com logo e nome, mas sem resumo, **When** o detalhe renderiza, **Then** o bloco ainda aparece (logo, título “Sobre a {nome}” e “Ver Empresa”), omitindo apenas a linha de subtítulo vazia.

---

### User Story 4 — Deploy automatizado leva campo, displays e seed (Priority: P1)

Após o deploy padrão do projeto, o campo de resumo existe na Empresa, aparece nos displays CMS, a EcoConstrutora tem o texto do Figma (se estava vazio) e o bloco renderiza na vaga seed — sem configuração manual em produção.

**Why this priority**: Regra permanente do projeto — zero passos manuais estruturais em produção.

**Independent Test**: Em ambiente limpo ou após reexecução idempotente, aplicar a receita de deploy da feature e abrir a vaga seed + formulário da empresa.

**Acceptance Scenarios**:

1. **Given** destino sem o campo de resumo na Empresa, **When** a receita de deploy da feature conclui, **Then** o campo existe, está nos displays de formulário/visualização e a EcoConstrutora exibe o resumo do Figma se antes estava vazio.
2. **Given** EcoConstrutora cujo resumo já foi alterado pelo editorial, **When** o deploy reexecuta, **Then** o texto editorial **não** é sobrescrito.
3. **Given** reexecução completa do deploy, **When** roda de novo, **Then** não duplica campos nem quebra displays (idempotência).

---

### Edge Cases

- Vaga com empresa vinculada sem logo: o bloco renderiza sem a imagem (ou com espaço omitido), mantendo título, subtítulo (se houver) e botão.
- Empresa com resumo exatamente no limite de 255 caracteres: texto completo é exibido sem truncamento visual forçado que corte no meio da palavra no cartão (quebra de linha natural).
- Título muito longo (“Sobre a {nome}”): permanece legível sem overflow horizontal no mobile.
- FAQ ausente: o bloco fica após Benefícios (ou após a última seção de conteúdo presente) e antes do fechamento da coluna principal.
- Visitante em viewport estreito: layout empilha (logo → textos → botão) com espaçamento consistente; botão não quebra linha de forma ilegível.
- Relação com a feature 034: o detalhe **volta a ter** um bloco intitulado no espírito “Sobre a Empresa”, porém como **cartão de resumo + CTA**, não como seção de descrição longa; a descrição longa permanece na página da empresa.

## Requirements *(obrigatório)*

### Functional Requirements

- **FR-001**: O detalhe público da vaga MUST exibir o bloco de resumo da empresa quando existir empresa publicada e acessível vinculada à vaga.
- **FR-002**: O detalhe público da vaga MUST NOT exibir o bloco quando não houver empresa vinculada válida/publicada.
- **FR-003**: O bloco MUST ocupar a coluna principal, posicionado acima da seção FAQ quando ela existir; caso contrário, ao final do conteúdo da coluna principal.
- **FR-004**: O título do bloco MUST ser a concatenação legível de “Sobre a ” + nome da empresa.
- **FR-005**: O subtítulo do bloco MUST exibir o resumo curto da empresa quando preenchido; MUST omitir a linha de subtítulo quando o resumo estiver vazio (sem placeholder genérico).
- **FR-006**: O bloco MUST exibir o logo da empresa quando disponível, em formato quadrado fixo (~80×80), com cantos arredondados e cobertura sem distorção.
- **FR-007**: O bloco MUST oferecer a ação “Ver Empresa” que leva o visitante à página pública canônica da empresa vinculada.
- **FR-008**: A apresentação MUST seguir o layout Figma do cartão: fundo azul muito claro, borda fina harmonizada ao fundo, cantos arredondados (~12px), padding generoso; em telas médias/largas, organização horizontal (logo | textos | botão); em mobile, empilhamento vertical centrado/legível.
- **FR-009**: A entidade Empresa MUST possuir um campo editorial de resumo curto (texto simples, limite 255 caracteres), distinto da descrição longa já existente.
- **FR-010**: O campo de resumo MUST estar disponível nos displays de formulário e de visualização da Empresa para perfis com permissão de edição.
- **FR-011**: No deploy, se a empresa seed EcoConstrutora existir e o resumo estiver vazio, o sistema MUST preenchê-lo com: “Líder regional em construções sustentáveis e infraestrutura urbana de alto impacto.”
- **FR-012**: A atualização de deploy MUST ser idempotente e MUST NOT sobrescrever resumo editorial já divergente do seed.
- **FR-013**: A configuração estrutural resultante (campo + displays) MUST ser exportável e versionada junto ao código da feature.
- **FR-014**: O bloco MUST usar a empresa vinculada à vaga (referência de conteúdo Empresa), e MUST NOT depender do vínculo legado por usuário da empresa.

### Key Entities

- **Vaga**: Anúncio de estágio; referencia no máximo uma Empresa; página de detalhe que hospeda o bloco.
- **Empresa**: Conteúdo público com nome (título), logo, descrição longa (página própria) e, nesta feature, **resumo curto** para o cartão no detalhe da vaga.
- **Resumo curto da empresa**: Texto editorial de até 255 caracteres usado como subtítulo do cartão; independente da descrição longa.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em 100% das vagas publicadas com empresa válida + resumo + logo (amostra seed incluída), o visitante vê o cartão completo (logo, título “Sobre a {nome}”, subtítulo, “Ver Empresa”) acima do FAQ quando o FAQ existir.
- **SC-002**: Em 100% das vagas sem empresa válida, o cartão não aparece (0 blocos órfãos).
- **SC-003**: Clique em “Ver Empresa” na vaga seed leva à página pública da EcoConstrutora em 100% das tentativas de validação.
- **SC-004**: Após o deploy, a EcoConstrutora com resumo vazio passa a exibir o texto do Figma; reexecução do deploy em ambiente já preenchido/editorial resulta em 0 sobrescritas indevidas.
- **SC-005**: Em validação desktop e mobile, 100% das amostragens do cartão mantêm legibilidade (sem overflow horizontal, botão acionável, hierarquia logo/textos/ação reconhecível).
- **SC-006**: Editor consegue criar/atualizar o resumo curto (≤255 caracteres) no formulário da Empresa e ver o resultado no detalhe da vaga em menos de 2 minutos no fluxo normal de CMS.

## Assumptions

- A referência vaga→empresa (`field_vaga_empresa`) e a página pública da empresa já existem (features 032/034); esta feature só adiciona o cartão de resumo no detalhe da vaga e o campo de subtítulo.
- A seção “Processo de Contratação” citada no pedido **não está presente** no detalhe atual; o ponto de âncora acordado é **acima do FAQ** (após Benefícios / última seção de conteúdo da vaga).
- O machine name canônico do resumo curto na implementação será `field_sobre_empresa_resumo` (texto simples, 255), salvo se a regra de reuso de storage do projeto exigir reutilizar um storage de texto curto já existente com nova instância no bundle `empresa` — o rótulo editorial e o limite de 255 permanecem os mesmos.
- O texto seed da EcoConstrutora só é aplicado se o campo estiver vazio; conteúdo editorial divergente é preservado.
- Cores e medidas do Figma de referência: fundo ~`#EFF4FF`, título ~`#023C62`, subtítulo ~`#45464D`, logo 80×80, radius ~12px; o tema pode mapear isso para tokens/classes existentes desde que o resultado visual seja equivalente.
- A feature 034 removeu o bloco legado “Sobre a Empresa” (descrição via usuário); esta feature **reintroduz** um bloco com o mesmo propósito de marca, porém como cartão de resumo + CTA para a página da empresa — sem restaurar o markup/legado `field_empresa_u`.
- Práticas de qualidade na implementação (Twig componentizado/include quando fizer sentido, CSS focado no componente, hook idempotente) seguem as regras do projeto. Skill `mattpocock/skills` (ex.: revisão de código) pode ser aplicada na fase de implementação/revisão se disponível no ambiente do agente; não há skill mattpocock específica para Drupal/Twig no inventário do projeto — o padrão local de hooks e templates permanece a referência.
- Atualização cirúrgica do `PRD.md` (§ campos da Empresa) e execução de `drush cex` no ambiente de origem fazem parte da entrega de implementação, não desta etapa de especificação.

## Dependências

- Content Type `empresa`, vínculo `field_vaga_empresa` e página canônica da empresa (032/034).
- Template de detalhe full da vaga e estilos do componente de detalhe já existentes (030/031/032/033/036).
- Empresa seed EcoConstrutora e vaga seed Engenheiro Civil.
- Módulo de configuração customizada (`custom_configs`) e receita de destino do projeto (`cim` → `updb` → `cim` → `cr`, conforme runbook vigente).
- Exportação final de configuração (`drush cex`) no ambiente de origem após criar o campo e displays.
