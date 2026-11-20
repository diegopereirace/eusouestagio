# Feature Specification: Busca da Home — Autocomplete de Cidades

**Feature Directory**: `specs/040-home-busca-cidades`  
**Created**: 2026-11-01  
**Status**: Draft  
**Input do usuário**: Ajustar o formulário de pesquisa da Home ao Figma (caixa ~896×86 no desktop); transformar o campo “Cidade ou Remoto” em autocomplete exclusivo de cidades (sem acentos/case-sensitive, só cidades com vagas ativas, formato `Cidade - UF - (N)`); remover a tag “Remoto” das pills de categoria; garantir deploy 100% automatizado (Git + Configuration Management / `hook_update_N` + `drush cr`), sem cliques no admin de produção; artefatos Basecamp/QA no final.

## Escopo

### Inclui

- Ajuste visual/responsivo do formulário de busca principal da **Home** (bloco hero `custom_banners_hero_search`), alinhado ao Figma (proporção global desktop ~896×86; select/curso e autocomplete fluidos em desktop e mobile).
- Substituição do campo atual de **regime** (select “Cidade ou Remoto”) por um **autocomplete de cidades** que alimenta o filtro de localização da listagem de vagas.
- Endpoint de sugestões de cidades: pesquisa facilitada (ignora maiúsculas/minúsculas e acentos); dropdown no formato `Cidade - Sigla do Estado - (número de vagas)`; **somente cidades com pelo menos uma vaga ativa**.
- Remoção da pill/tag **“Remoto”** da lista de atalhos abaixo do formulário da Home, mantendo as pills de curso (TI, Administração, Design, Marketing).
- Declaração de CSS/JS em libraries versionadas; alterações de formulário/rota em módulo custom; remoção da pill via código (lista hardcoded no bloco) — deploy sem passos manuais no admin.
- Atualização cirúrgica do `PRD.md` na seção da busca da Home (campo de localização e pills), se a mudança estrutural/comportamental for confirmada no plano.
- Bloco de texto copiável para Basecamp (branch, título, descrição, roteiro de QA) ao final desta spec.

### Fora

- Redesign do hero de busca da página `/vagas` (`custom_banners_vagas_hero_search` / feature `027`) — salvo se o plano identificar texto residual “Cidade ou Remoto” a alinhar; fora do escopo visual desta task.
- Autocomplete de cursos (já existente / comportamento atual do campo “Qual o seu curso?”).
- Remoção do vocabulário/termo de taxonomia `regime` “Remoto” do sistema (apenas some da UI de pills da Home).
- Filtro por regime na Home (o select de regime deixa de existir neste formulário; regime continua disponível nos filtros da listagem, se já existirem).
- Alterações em `core/` ou `vendor/`.
- Código de implementação nesta etapa (segue `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê a barra de busca alinhada ao Figma (Priority: P1)

Visitante abre a Home e encontra a barra de pesquisa com proporções e estilo do layout aprovado: caixa global larga no desktop, campos e botão “Buscar” bem proporcionados, sem quebra visual em mobile.

**Why this priority**: É o requisito visual explícito do Figma e o primeiro contato com a busca.

**Independent Test**: Abrir a Home em desktop (≥992px) e mobile (≤575.98px) e comparar com o frame do Figma (dimensões ~896×86 no desktop; ausência de overflow horizontal).

**Acceptance Scenarios**:

1. **Given** a Home publicada com o hero de busca, **When** o visitante abre em desktop, **Then** a caixa global da busca aproxima-se de 896×86 e os campos/botão respeitam o layout do Figma.
2. **Given** viewport mobile, **When** a página carrega, **Then** select/curso, autocomplete de cidade e botão permanecem usáveis, sem scroll horizontal causado pela barra.
3. **Given** CSS/JS novos, **When** o deploy sobe apenas via Git + limpeza de cache, **Then** os estilos/scripts entram em vigor sem injeção manual no admin.

---

### User Story 2 — Visitante busca por cidade via autocomplete (Priority: P1)

Visitante digita no campo de localização (ex.: “sao paulo” ou “FORTALEZA”) e recebe sugestões de cidades com vagas ativas no formato `Cidade - UF - (N)`; ao escolher e buscar, chega à listagem filtrada por aquela cidade.

**Why this priority**: Substitui o comportamento legado (regime) pelo valor de negócio pedido — descoberta por cidade com vagas reais.

**Independent Test**: Digitar variantes sem acento/caixa mista; confirmar sugestões e submissão para a listagem com filtro de cidade.

**Acceptance Scenarios**:

1. **Given** o campo de localização na Home, **When** o visitante observa o placeholder/label, **Then** o texto **não** contém a palavra “Remoto” (ex.: “Cidade”).
2. **Given** vagas ativas em Fortaleza/CE, **When** digita “fortaleza” ou “FORTALEZA”, **Then** aparece sugestão no formato `Fortaleza - CE - (N)` com N ≥ 1.
3. **Given** vagas ativas em São Paulo/SP, **When** digita “sao paulo” (sem acento), **Then** a sugestão correspondente a “São Paulo” é listada.
4. **Given** uma sugestão selecionada, **When** submete “Buscar”, **Then** a listagem de vagas aplica o filtro de cidade correspondente.
5. **Given** uma cidade sem nenhuma vaga ativa, **When** o visitante digita o nome dessa cidade, **Then** ela **não** aparece no dropdown.

---

### User Story 3 — Visitante não vê mais a tag “Remoto” (Priority: P1)

Abaixo do formulário, as pills rápidas mostram apenas categorias de curso; “Remoto” não aparece.

**Why this priority**: Regime não deve misturar-se com tags de curso; requisito explícito de limpeza de UI.

**Independent Test**: Inspecionar a fileira de pills na Home e confirmar ausência de “Remoto”.

**Acceptance Scenarios**:

1. **Given** a Home renderizada, **When** o visitante olha as pills sob a busca, **Then** vê TI, Administração, Design e Marketing (quando os termos existirem) e **não** vê “Remoto”.
2. **Given** deploy em ambiente limpo via receita automatizada, **When** a Home carrega, **Then** a ausência de “Remoto” já está efetiva **sem** edição manual de bloco/View/taxonomia no admin de produção.

---

### User Story 4 — Deploy 100% automatizado (Priority: P1)

Após `git pull` e a receita padrão do projeto (`drush cim` / `drush updb` conforme artefatos / `drush cr`), layout, autocomplete e remoção da pill estão ativos sem cliques no painel.

**Why this priority**: Requisito crítico do pedido e regra permanente de deploy do projeto.

**Independent Test**: Ambiente de destino desatualizado executa só comandos versionados e valida as três mudanças (visual, autocomplete, sem pill Remoto).

**Acceptance Scenarios**:

1. **Given** CSS/JS alterados e declarados em libraries do tema/módulo, **When** sobe o código e roda limpeza de cache, **Then** a Home serve os assets novos sem CSS/JS colados no admin.
2. **Given** rota/callback de autocomplete e alteração do formulário no módulo custom, **When** o código é deployado, **Then** o autocomplete responde sem configuração manual de rotas no UI.
3. **Given** a pill “Remoto” removida da lista definida em código do bloco (ou, se no plano se descobrir origem em config, exportada via `drush cex` / removida via `hook_update_N` idempotente), **When** roda a receita de destino, **Then** a Home já reflete a limpeza sem intervenção editorial.

---

### Edge Cases

- Nenhuma cidade com vagas ativas corresponde ao texto digitado → dropdown vazio ou mensagem neutra; formulário não quebra.
- Digitação só com espaços / string vazia → sem sugestões; submissão vazia leva à listagem padrão (comportamento atual de busca sem filtros).
- Cidade com mesmo nome em estados diferentes → cada par cidade+UF aparece como linha distinta com sua contagem.
- Contagem de vagas cai a zero após publicação expirar/despublicar → cidade deixa de ser sugerida na próxima consulta (sem cache stale indevido além das regras normais de cache do site).
- Visitante cola texto com acentos misturados e caixa aleatória → normalização trata como o nome canônico da cidade.
- JS desabilitado → campo de cidade permanece utilizável como texto livre submetido ao filtro de cidade (degradação aceitável); layout não depende de JS para legibilidade básica.
- Termo de curso de uma pill inexistente na taxonomia → pill omitida (comportamento já adotado no bloco).

## Requirements *(obrigatório)*

### Functional Requirements

**Layout e assets**

- **FR-001**: A barra de busca da Home MUST alinhar-se visualmente ao Figma no desktop (caixa global com proporção alvo ~896×86) e permanecer responsiva em mobile para o campo de curso e o de cidade.
- **FR-002**: Todo CSS e JS desta feature MUST ser versionado no repositório e declarado em libraries (`.libraries.yml`) do tema e/ou módulo responsável; PROIBIDO depender de CSS/JS injetado pelo admin para ativar a feature em produção.
- **FR-003**: Após deploy de código, limpeza de cache (`drush cr`) MUST ser suficiente para os assets da busca entrarem em vigor (junto com a receita cim/updb quando houver config/update).

**Campo de localização (autocomplete de cidades)**

- **FR-004**: O campo atualmente rotulado/placeholder “Cidade ou Remoto” MUST passar a ser um campo de autocomplete **exclusivo de cidades**; o placeholder/label MUST remover a palavra “Remoto” (rótulo padrão assumido: “Cidade”).
- **FR-005**: O select de regime MUST deixar de ser o controle desse slot na Home; a submissão do campo de localização MUST mapear para o filtro de **cidade** da listagem de vagas (não para regime).
- **FR-006**: As sugestões MUST ignorar diferenças de maiúsculas/minúsculas e de acentos na comparação com os nomes de cidade (ex.: “sao paulo” equivale a “São Paulo”).
- **FR-007**: Cada item do dropdown MUST exibir exatamente o formato `Cidade - Sigla do Estado - (número de vagas)` (ex.: `Fortaleza - CE - (12)`).
- **FR-008**: A consulta de sugestões MUST considerar apenas cidades que possuam **pelo menos uma vaga ativa** no momento (vaga publicada/aberta conforme definição canônica do projeto); cidades com zero vagas ativas MUST NOT aparecer.
- **FR-009**: A lógica do autocomplete (rota/callback) e a alteração do formulário/bloco da Home MUST viver em módulo(s) custom versionados, de modo que o Git sozinho leve a capacidade ao destino (sem configurar rota ou form no UI de produção).

**Pills / tags**

- **FR-010**: A tag/pill “Remoto” MUST ser removida da lista de categorias sugeridas abaixo do formulário de busca da Home.
- **FR-011**: As pills restantes MUST continuar a representar **cursos/áreas** (TI, Administração, Design, Marketing), sem misturar regime.
- **FR-012 — Estratégia de deploy da remoção**:
  - **Origem atual conhecida**: a lista de pills da Home é definida em código no bloco hero (`Remoto` + cursos). Nesse caso, a remoção MUST ser feita no código versionado; deploy = Git + `drush cr` (sem `cim`/`updb` só por causa da pill).
  - **Se** no planejamento se confirmar origem em Bloco/View/config exportável: remover localmente e exportar com `drush cex` → `config/sync`, importar em produção com `drush cim`.
  - **Se** for necessário apagar/desativar termo de taxonomia ou menulink no banco: criar `hook_update_N` **idempotente** em `custom_configs.install` e aplicar em destino com `drush updb`.
  - Em todos os casos: **zero** passos manuais no admin de produção.

**Deploy e governança**

- **FR-013**: Nenhuma ativação desta feature em produção MUST exigir cliques no painel administrativo.
- **FR-014**: Alterações estruturais exportáveis (se houver) MUST seguir Configuration Management (`drush cex` na origem → `drush cim` no destino); seeds/remoções de dados MUST usar `hook_update_N` idempotente quando aplicável.
- **FR-015**: Receita de destino MUST documentar-se no quickstart da feature (padrão do projeto: `cim` → `updb` → `cim` → `cr`, ajustada se só houver código/assets).

### Key Entities

- **Vaga (anúncio de estágio)**: conteúdo publicado que possui cidade, estado (UF) e status ativo/aberto; alimenta contagem e elegibilidade no autocomplete.
- **Cidade (valor de localização da vaga)**: nome de município associado às vagas; chave de agrupamento junto com a UF para sugestão e filtro.
- **Estado (UF)**: sigla exibida no dropdown ao lado do nome da cidade.
- **Pill de curso**: atalho visual abaixo da busca que aplica filtro de curso/área; distinto de regime.
- **Hero de busca da Home**: bloco de formulário na Home com campo de curso, campo de cidade e botão Buscar.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em desktop, a barra de busca da Home corresponde visualmente ao frame Figma (proporção alvo ~896×86) em revisão lado a lado; em mobile, 100% dos revisores de QA confirmam usabilidade sem scroll horizontal causado pela barra.
- **SC-002**: Em 100% dos testes com nomes sem acento e com caixa mista (mín. 5 cidades conhecidas com vagas), o autocomplete devolve a cidade canônica esperada em até 1 segundo sob carga normal.
- **SC-003**: 100% das sugestões exibidas seguem o formato `Cidade - UF - (N)` com N ≥ 1; auditoria manual não encontra cidade com zero vagas ativas no dropdown.
- **SC-004**: Após deploy só com comandos automatizados (sem admin), a Home **não** exibe a pill “Remoto” e o campo de localização **não** menciona “Remoto” no placeholder/label.
- **SC-005**: Um visitante consegue ir da Home a uma listagem filtrada por cidade escolhida no autocomplete em no máximo 3 interações (digitar → selecionar → Buscar).

## Assumptions

- “Vaga ativa” = vaga publicada e elegível à listagem pública atual (mesmo critério da View de vagas pública); não se cria novo workflow de status só para o autocomplete.
- Placeholder/label do campo de localização = **“Cidade”** (remove “Remoto”; copy final pode ser refinada no plano se o Figma trouxer texto exato diferente).
- O parâmetro GET de cidade já exposto na listagem de vagas continua sendo o destino da submissão; não se inventa um segundo filtro paralelo.
- Contagem `(N)` = número de vagas ativas daquele par cidade+UF no momento da consulta.
- Escopo UI desta feature = **Home** (`custom_banners_hero_search`); o hero de `/vagas` não é redesenhado aqui.
- Remoção da pill “Remoto” **não** apaga o termo de taxonomia `regime` do sistema — apenas a deixa de oferecer como atalho na Home.
- Assets compilados (se o tema usar pipeline SCSS→CSS) entram no mesmo commit/PR da feature e são referenciados pelas libraries.
- Padrão de autocomplete de cursos existente no projeto pode ser espelhado conceitualmente (rota dedicada + library), adaptado a cidades/vagas.

## Dependencies

- Bloco hero da Home e library `default/hero_search` (feature `001` / módulo `custom_banners`).
- Campos de localização das vagas (`field_cidade`, `field_estados`) e filtro exposto `cidade` na View `vagas`.
- Regras de deploy do projeto (`config/sync`, `custom_configs.install`, runbook de produção).

## Artefatos Basecamp / QA *(copiar e colar)*

```text
Sugestão de Branch:
feat/home-busca-autocomplete-cidades

Título da Task:
Home: alinhar busca ao Figma, autocomplete de cidades e remover tag Remoto

Descrição da Task:
Ajustar o formulário de pesquisa da Home ao novo layout do Figma (proporções da barra ~896×86 no desktop e responsividade dos campos).

Substituir o campo “Cidade ou Remoto” por um autocomplete exclusivo de cidades: pesquisa sem diferenciar maiúsculas/minúsculas nem acentos (ex.: “sao paulo” encontra “São Paulo”); dropdown no formato “Cidade - UF - (número de vagas)”; listar somente cidades que tenham pelo menos uma vaga ativa no momento.

Remover a tag/pill “Remoto” das categorias sugeridas abaixo da busca, mantendo apenas atalhos de curso (TI, Administração, Design, Marketing), para não misturar regime com área de estudo.

Deploy pensado para ser 100% automatizado: CSS/JS via libraries versionadas; autocomplete e alteração do formulário em módulo custom (Git); remoção da pill via código (ou, se origem for config/termo, via drush cex/cim ou hook_update_N idempotente). Zero cliques no admin de produção — receita com Git + drush cim/updb/cr conforme artefatos gerados.

Roteiro de Validação Manual (QA):
1. Desktop: abrir a Home e conferir a barra de busca com o Figma (caixa ~896×86, botão Buscar, campos alinhados).
2. Mobile: repetir a checagem; confirmar que curso, cidade e Buscar são usáveis e que não há scroll horizontal causado pela barra.
3. Confirmar que a pill “Remoto” NÃO aparece abaixo do formulário; confirmar presença das pills de curso esperadas.
4. No campo de cidade, digitar “sao paulo” (minúsculas, sem acento) e “FORTALEZA” (maiúsculas); verificar sugestões no formato “Cidade - UF - (N)” com N ≥ 1.
5. Selecionar uma sugestão e clicar em Buscar; confirmar listagem filtrada pela cidade escolhida.
6. Tentar uma cidade conhecida sem vagas ativas (ou criar condição de teste); confirmar que ela NÃO aparece no autocomplete.
7. Após deploy apenas com Git + comandos Drush (sem painel), repetir os passos 1–6 no ambiente de destino.
```
