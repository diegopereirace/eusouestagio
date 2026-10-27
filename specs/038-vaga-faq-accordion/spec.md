# Feature Specification: Bloco "Dúvidas Frequentes" (FAQ) no Detalhe da Vaga

**Feature Directory**: `specs/038-vaga-faq-accordion`  
**Feature Branch**: `038-vaga-faq-accordion`  
**Created**: 2026-10-27  
**Status**: Draft  
**Predecessor**: `030-vagas-detalhe-layout` / `031-vagas-detalhe-refino` (CT `faq`, paragraph `faq_item_p`, `field_vaga_faq`, accordion no Twig full, CTA `default_ctav1vagas`); `037-vaga-sobre-empresa` (cartão “Sobre a Empresa” imediatamente acima do FAQ)  
**Input do usuário**: Exibir “Dúvidas Frequentes” abaixo de “Sobre a Empresa” e acima de “Pronto para o próximo passo?”, em Accordion (formato visual do layout), dados gerenciáveis e migráveis, preferência por bloco de View com fallback justificado para renderização nativa do campo; deploy 100% automatizado.

## Escopo

### Inclui

- Verificar (e só criar se ausente) a arquitetura de dados já prevista no produto:
  - Content Type `faq` com `field_faq_itens` (referência revisionada ilimitada a paragraphs `faq_item_p`);
  - Paragraph Type `faq_item_p` com pergunta (texto curto) e resposta (texto longo);
  - Campo `field_vaga_faq` no Content Type `vagas` referenciando no máximo uma coleção FAQ.
- Garantir a seção **Dúvidas Frequentes** na coluna principal do detalhe público da vaga, **logo após** “Sobre a Empresa” e **antes** do CTA final “Pronto para o próximo passo?”.
- Apresentar cada item FAQ como Accordion no padrão visual do layout de referência: título da seção “Dúvidas Frequentes”; itens em cartões brancos separados (borda fina, cantos arredondados, espaçamento entre itens); botão com texto escuro em negrito médio; seta escura à direita; sem fundo azul padrão do Accordion nativo agrupado.
- Componentizar a marcação do accordion (include/template focado) para evitar lógica espaguete no template full da vaga.
- Associar, de forma idempotente, a coleção FAQ de exemplo (perguntas “Qual a duração do estágio?” e “Existe auxílio home office?”) à vaga seed **Engenheiro Civil** quando o vínculo estiver vazio.
- Deploy automatizado (ensure estrutural só se faltar + seed/vínculo + estilos/templates) sem passos manuais no admin de produção; exportação das configurações estruturais quando houver alteração exportável; atualização cirúrgica do `PRD.md` se a feature alterar a descrição pública da seção FAQ / Views.

### Fora

- Redesign completo do detalhe da vaga (header, benefícios, sidebar, Sobre a Empresa, CTA).
- FAQ global do site (páginas institucionais) ou reutilização fora do detalhe de vaga.
- Criar View `faq_vaga` / bloco Views como mecanismo principal de renderização **nesta entrega** (ver decisão de arquitetura abaixo e Assumptions).
- Alterar cardinalidade de `field_vaga_faq` de volta para ilimitada (modelo canônico atual: 1 coleção FAQ por vaga).
- Recriar Content Type / paragraphs / fields que já existem em `config/sync` e nos hooks 030/031.
- Alterações em `core/` ou `vendor/`.
- Código de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

### Decisão de arquitetura (View vs. campo nativo)

**Preferência do pedido**: bloco de View (`faq_vaga` / `block_faq_vagas`) com filtro contextual do node da vaga.

**Decisão desta spec**: manter e refinar a **renderização nativa** de `field_vaga_faq` → `field_faq_itens` no template do detalhe da vaga (coluna principal), **sem** View/block placement como fonte de verdade da seção.

**Justificativa (valor de produto + layout)**:

1. A ordem pedida (“abaixo de Sobre a Empresa”, “acima do CTA”) só é confiável **dentro** da coluna principal (`.col-lg-8`) do template full. O CTA final já é um bloco de região (`default_ctav1vagas` em `content_full`) **após** o node; um bloco Views na mesma região ficaria fora da coluna 8/4 e quebraria a composição do Figma.
2. A View com Relationship + Contextual Filter + rewrite de paragraphs para Accordion aumenta complexidade Twig/Views sem ganho editorial (o editor já associa a coleção FAQ no formulário da vaga).
3. A estrutura de dados, o accordion Bootstrap e a posição relativa às seções **já existem** no detalhe; o gap real é visual (cards isolados), componentização e vínculo seed Engenheiro Civil → FAQ.

Se, em `/speckit-plan`, surgir requisito editorial forte de reuso do mesmo Accordion fora do node (ex.: painel), a View poderá ser adicionada como entrega posterior sem mudar o modelo de dados.

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante tira dúvidas sem sair da vaga (Priority: P1)

Um visitante abre o detalhe de uma vaga com FAQ associado e, na coluna principal (após “Sobre a Empresa”, antes do CTA “Pronto para o próximo passo?”), vê “Dúvidas Frequentes” em Accordion; ao tocar em uma pergunta, a resposta abre sem sair da página.

**Why this priority**: É o valor principal — reduzir atrito e dúvidas comuns no funil de candidatura.

**Independent Test**: Abrir a vaga seed “Engenheiro Civil” e expandir/colapsar os dois itens de exemplo.

**Acceptance Scenarios**:

1. **Given** uma vaga publicada com coleção FAQ publicada contendo ≥1 item válido, **When** o visitante abre o detalhe, **Then** a seção “Dúvidas Frequentes” aparece na coluna principal após “Sobre a Empresa” (quando o cartão da empresa existir) e antes do CTA final.
2. **Given** a seção renderizada, **When** o visitante observa o layout, **Then** vê título da seção em azul escuro; cada pergunta em cartão branco com borda fina, cantos arredondados e espaço entre cartões; seta à direita.
3. **Given** dois ou mais itens, **When** o visitante abre o segundo painel, **Then** o comportamento de Accordion permanece coerente (painéis identificados de forma única; abertura/fechamento independente conforme o padrão adotado no markup).

---

### User Story 2 — Visitante não vê FAQ vazio ou órfão (Priority: P1)

Quando a vaga não tem FAQ, a coleção está vazia/unpublished ou os itens não têm pergunta/resposta, a seção não aparece — sem erros nem espaços quebrados.

**Why this priority**: Evita regressão visual e conteúdo inconsistente.

**Independent Test**: Abrir (ou simular) vaga sem `field_vaga_faq` e confirmar ausência da seção; abrir vaga com FAQ e confirmar presença.

**Acceptance Scenarios**:

1. **Given** vaga sem coleção FAQ vinculada, **When** o detalhe renderiza, **Then** a seção “Dúvidas Frequentes” não aparece.
2. **Given** coleção FAQ unpublished ou sem itens válidos, **When** o detalhe renderiza para visitante anônimo, **Then** a seção não aparece.

---

### User Story 3 — Editor gerencia perguntas da vaga (Priority: P1)

Um editor cria/edita um node FAQ com itens (pergunta + resposta), associa à vaga pelo campo “FAQ da vaga” e vê o Accordion refletir a alteração na página pública.

**Why this priority**: Sem operabilidade editorial o bloco fica engessado no seed.

**Independent Test**: Editar a coleção FAQ associada à Engenheiro Civil (ou criar nova), salvar e recarregar o detalhe.

**Acceptance Scenarios**:

1. **Given** permissão de editar FAQ e vagas, **When** o editor abre o formulário do FAQ, **Then** consegue adicionar/remover itens com pergunta e resposta.
2. **Given** vaga com campo “FAQ da vaga”, **When** o editor associa a coleção e publica, **Then** o Accordion da página pública lista esses itens.
3. **Given** item com pergunta preenchida e resposta vazia (ou o inverso, se a validação permitir), **When** a página renderiza, **Then** o item inválido é omitido ou a validação do CMS impede salvar — sem quebrar a seção.

---

### User Story 4 — Deploy automatizado vincula seed e preserva editorial (Priority: P1)

Após o deploy padrão, a estrutura FAQ (se ainda faltasse em algum ambiente) está garantida, a coleção de exemplo com as duas perguntas do layout existe, a vaga Engenheiro Civil referencia essa coleção quando o vínculo estava vazio, e o Accordion visual alinhado ao layout está ativo — sem configuração manual em produção.

**Why this priority**: Regra permanente do projeto — zero passos manuais estruturais em produção.

**Independent Test**: Em ambiente limpo ou após reexecução idempotente, aplicar a receita de deploy e abrir a vaga seed.

**Acceptance Scenarios**:

1. **Given** destino com estrutura já importada de `config/sync`, **When** o update da feature roda, **Then** não recria tipos/fields duplicados; apenas garante vínculo Engenheiro Civil → FAQ seed se vazio e aplica entregáveis de tema.
2. **Given** Engenheiro Civil sem `field_vaga_faq`, **When** o deploy conclui, **Then** a vaga passa a referenciar a coleção seed com “Qual a duração do estágio?” e “Existe auxílio home office?”.
3. **Given** Engenheiro Civil cujo FAQ editorial já diverge do seed, **When** o deploy reexecuta, **Then** o vínculo editorial **não** é sobrescrito.
4. **Given** reexecução completa, **When** roda de novo, **Then** não duplica nodes FAQ, paragraphs nem vínculos (idempotência).

---

### Edge Cases

- Vaga com “Sobre a Empresa” ausente: o FAQ permanece na coluna principal após a última seção de conteúdo presente (Benefícios / Requisitos / Sobre a Vaga) e antes do CTA.
- Coleção FAQ com muitos itens (≥8): Accordion permanece utilizável; scroll da página natural; sem overflow horizontal.
- Pergunta muito longa: quebra de linha no botão sem empurrar a seta para fora da área clicável.
- Resposta com HTML básico permitido pelo formato do campo: renderiza de forma segura conforme o formato já configurado no CMS.
- Visitante em viewport estreito: cartões ocupam a largura da coluna; toque nos botões permanece confortável.
- Ambiente que ainda não importou configs 030/031: o hook de ensure pode recriar estrutura mínima; em ambientes já sincronizados, ensure é no-op.

## Requirements *(obrigatório)*

### Functional Requirements

**Dados e verificação**

- **FR-001**: O sistema MUST possuir (ou garantir de forma idempotente se ausente) o Content Type de coleção FAQ com campo de itens de cardinalidade ilimitada referenciando paragraphs de item FAQ.
- **FR-002**: O sistema MUST possuir (ou garantir) o paragraph de item FAQ com campo de pergunta (texto curto) e campo de resposta (texto longo).
- **FR-003**: O Content Type de vagas MUST possuir (ou garantir) um campo de referência à coleção FAQ (no máximo uma coleção por vaga).
- **FR-004**: A implementação MUST verificar a presença dessas estruturas antes de criar; MUST NOT duplicar tipos, storages ou instances já existentes no ambiente / `config/sync`.

**Exibição e posição**

- **FR-005**: O detalhe público da vaga MUST exibir a seção “Dúvidas Frequentes” quando houver coleção FAQ publicada com pelo menos um item válido.
- **FR-006**: O detalhe público da vaga MUST NOT exibir a seção quando não houver FAQ válido.
- **FR-007**: A seção MUST ocupar a coluna principal, imediatamente após o bloco “Sobre a Empresa” quando este existir, e antes do CTA final “Pronto para o próximo passo?”.
- **FR-008**: A renderização MUST usar a associação vaga → coleção FAQ → itens (não uma lista global desvinculada da vaga).
- **FR-009**: A entrega MUST documentar e seguir a decisão de **não** usar bloco Views como fonte principal da seção nesta feature (ver Escopo); MUST NOT posicionar a seção via placement de região que saia da coluna principal.

**Accordion e apresentação**

- **FR-010**: Cada item MUST renderizar no padrão Accordion (container + item + cabeçalho/botão + painel colapsável), com identificadores únicos por item para abertura/fechamento corretos.
- **FR-011**: O título da seção MUST ser “Dúvidas Frequentes”, em hierarquia de seção (nível 2 ou 3 coerente com o restante do detalhe) e cor azul escura alinhada ao layout (~`#023C62`).
- **FR-012**: Cada item MUST parecer um cartão isolado: fundo branco, borda fina, cantos arredondados (~8–12px), margem inferior entre itens (~16px); MUST NOT parecer um único bloco agrupado com bordas laterais/superiores contínuas do Accordion padrão.
- **FR-013**: O botão do item MUST ter fundo branco/transparente (também no estado ativo), texto escuro em negrito médio; a seta MUST ser escura e à direita.
- **FR-014**: A marcação do Accordion MUST estar componentizada (include ou template dedicado), sem lógica espaguete no template full da vaga.

**Seed, deploy e migração**

- **FR-015**: O deploy MUST garantir de forma idempotente a coleção FAQ de exemplo com as perguntas “Qual a duração do estágio?” e “Existe auxílio home office?” (reutilizando a coleção seed já existente quando presente).
- **FR-016**: Se a vaga seed Engenheiro Civil existir e `field_vaga_faq` estiver vazio, o deploy MUST associá-la à coleção FAQ de exemplo; MUST NOT sobrescrever vínculo editorial já preenchido.
- **FR-017**: Qualquer alteração estrutural exportável MUST ser acompanhada de exportação versionada (`drush cex` no ambiente de origem).
- **FR-018**: A atualização de deploy MUST ser idempotente (reexecução = no-op seguro).

### Key Entities

- **Vaga**: Anúncio de estágio; referencia no máximo uma coleção FAQ; hospeda a seção no detalhe.
- **Coleção FAQ**: Conteúdo que agrupa os itens de perguntas e respostas reutilizáveis/associáveis a uma vaga.
- **Item FAQ**: Par pergunta + resposta dentro da coleção; cada item vira um painel do Accordion.
- **CTA final da vaga**: Bloco já existente “Pronto para o próximo passo?”; âncora inferior da ordenação (FAQ fica acima dele na leitura da página).

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em 100% das vagas publicadas com FAQ válido (amostra seed incluída), o visitante vê “Dúvidas Frequentes” na coluna principal após “Sobre a Empresa” (quando presente) e antes do CTA final.
- **SC-002**: Em 100% das vagas sem FAQ válido, a seção não aparece (0 seções órfãs).
- **SC-003**: Na vaga seed Engenheiro Civil após deploy, as duas perguntas de exemplo estão presentes e expansíveis em 100% das validações.
- **SC-004**: Em validação desktop e mobile, 100% dos cartões do Accordion mantêm aparência de cards isolados (espaço entre itens, cantos arredondados, sem fundo azul ativo padrão) e seta escura à direita.
- **SC-005**: Editor consegue alterar um item FAQ e ver o resultado no detalhe da vaga associada em menos de 2 minutos no fluxo normal de CMS.
- **SC-006**: Reexecução do deploy resulta em 0 duplicatas de coleção/itens e 0 sobrescritas de vínculo FAQ editorial já preenchido.

## Assumptions

- Content Type `faq`, paragraph `faq_item_p` (`field_pergunta` / `field_resposta`), `field_faq_itens` e `field_vaga_faq` (cardinalidade 1) **já existem** em `config/sync` e nos hooks das features 030/031; esta feature prioriza verificação + lacunas (vínculo seed + polish visual + componentização).
- O Accordion Bootstrap já está (ou permanecerá) no tema do detalhe; o trabalho visual desta feature é alinhar ao layout de referência (cards flutuantes), não reinventar o componente.
- A coleção seed canônica usa UUID fixo já adotado no projeto (`c3d4e5f6-a7b8-4901-b234-56789abcdef0`); novos seeds reutilizam essa coleção em vez de criar outra.
- A vaga seed Engenheiro Civil usa UUID fixo já adotado (`a7b8c9d0-e1f2-4345-f678-9abcdef01234`).
- View `faq_vaga` **não** será criada nesta entrega (decisão documentada no Escopo); se o product owner insistir depois, será feature/follow-up sem mudança de modelo de dados.
- Skill `mattpocock/skills` não está instalada/disponível neste ambiente de agente; práticas de código limpo, componentização Twig e deploy automatizado seguem as regras locais do projeto (Spec Kit + `custom_configs` + tema `default`). Na implementação, preferir include Twig dedicado e CSS escopado a `.vaga-detalhe__faq`.
- Atualização cirúrgica do `PRD.md` (se a descrição pública do FAQ no detalhe precisar refletir “cards Accordion + vínculo seed”) e `drush cex` no ambiente de origem fazem parte da entrega de implementação, não desta etapa de especificação.
- Receita de destino permanece a do runbook vigente (`cim` → `updb` → `cim` → `cr`), salvo exceção documentada no plano.

## Dependências

- Estrutura FAQ e template full do detalhe (030/031).
- Cartão “Sobre a Empresa” e ordem atual da coluna principal (037).
- CTA `default_ctav1vagas` (“Pronto para o próximo passo?”) como âncora inferior.
- Vaga seed Engenheiro Civil e coleção FAQ seed.
- Módulo `custom_configs` e tema `default`.
- Exportação final de configuração (`drush cex`) no ambiente de origem se houver mudança estrutural exportável (caso típico desta feature: só tema + hook de vínculo — cex pode ser no-op se nada mudou em config).
