# Feature Specification: Home — Busca Inteligente (curso × cidade)

**Feature Directory**: `specs/041-home-busca-inteligente`  
**Created**: 2026-11-01  
**Status**: Draft  
**Input do usuário**: A busca da Home deve ser inteligente. Existem dois inputs (curso e cidade); a pesquisa deve funcionar com um input ou os dois. Ao escolher/digitar em um campo, o outro deve restringir-se a opções com vagas reais na combinação; quando não fizer sentido deixar um input ativo (zero opções), desativá-lo. Latência do autocomplete é requisito. Deploy 100% automatizado.

## Escopo

### Inclui

- Cruzamento entre os campos **curso** e **cidade** do hero de busca da Home (`custom_banners_hero_search`).
- Autocomplete de curso restringido pela cidade (e UF, quando selecionada); autocomplete de cidade restringido pelo curso.
- Desabilitar o campo oposto (com limpeza de valor e aviso) quando a combinação zerar opções; reabilitar ao esvaziar a origem.
- Preservar busca com **um** ou **dois** campos preenchidos (comportamento atual).
- Latência percebida do autocomplete ≤ 1s (meta operacional ≤ 300ms em cache hit); debounce do probe cruzado ~300ms.
- Deploy só com Git + limpeza de cache (`drush cr`); sem passos manuais no admin.

### Fora

- Redesign visual do hero da Home (feature **040**) ou do hero `/vagas` (**027**).
- Alteração de filtros/schema da View `vagas`.
- Spinner obrigatório no MVP.
- Alterações em `core/` ou `vendor/`.
- `hook_update_N` / exportação de config estrutural (esta feature é código + assets).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante busca só por curso ou só por cidade (Priority: P1)

Visitante preenche apenas um dos campos e clica em Buscar; chega à listagem filtrada por aquele critério.

**Why this priority**: Regressão crítica — o cruzamento não pode tornar os campos obrigatórios em conjunto.

**Independent Test**: Preencher só curso → Buscar; preencher só cidade → Buscar; ambos vazios → listagem padrão.

**Acceptance Scenarios**:

1. **Given** a Home com o hero de busca, **When** o visitante preenche só o curso e busca, **Then** a listagem aplica o filtro de curso.
2. **Given** a Home, **When** preenche só a cidade (e UF se selecionada) e busca, **Then** a listagem aplica o filtro de localização.
3. **Given** ambos os campos vazios, **When** busca, **Then** a listagem padrão é exibida (comportamento atual).

---

### User Story 2 — Cruzamento restringe sugestões (Priority: P1)

Ao escolher ou digitar uma cidade, o campo curso só sugere cursos com vagas ativas naquela localização; ao escolher/digitar um curso, o campo cidade só sugere cidades com vagas daquele curso.

**Why this priority**: Valor de negócio da “busca inteligente”.

**Independent Test**: Selecionar cidade seedada → digitar no curso; selecionar curso → digitar na cidade; conferir sugestões e contagens.

**Acceptance Scenarios**:

1. **Given** uma cidade com vagas só em certos cursos, **When** o visitante seleciona essa cidade, **Then** o autocomplete de curso lista apenas esses cursos.
2. **Given** um curso com vagas só em certas cidades, **When** seleciona esse curso, **Then** o autocomplete de cidade lista apenas essas cidades no formato `Cidade - UF - (N)` com N da combinação.
3. **Given** texto digitado livre (≥2 chars) em um campo, **When** o outro campo pede sugestões, **Then** a restrição usa o texto como constraint (match facilitado, sem acento/case).

---

### User Story 3 — Campo sem opções é desativado (Priority: P1)

Quando a escolha em um campo deixa o outro sem nenhuma opção com vagas, o campo oposto é desabilitado, limpo e exibe um aviso; ao limpar a origem, o oposto volta a ficar ativo.

**Why this priority**: “Quando não fizer sentido deixar um input ativo, desative.”

**Independent Test**: Forçar combinação sem vagas (ou seed/cenário conhecido); observar disable/hint; limpar origem e reabilitar.

**Acceptance Scenarios**:

1. **Given** uma cidade sem cursos com vagas (ou constraint que zera), **When** a checagem conclui, **Then** o campo curso fica desabilitado, valor limpo e com aviso visível.
2. **Given** um curso sem cidades com vagas, **When** a checagem conclui, **Then** o campo cidade fica desabilitado, valor e UF limpos e com aviso.
3. **Given** um campo desabilitado por cruzamento, **When** o visitante esvazia o campo origem, **Then** o oposto é reabilitado e o aviso some.

---

### User Story 4 — Autocomplete continua rápido (Priority: P1)

Digitação nos campos continua responsiva; o probe de cruzamento não trava a digitação.

**Why this priority**: Latência explícita no pedido.

**Independent Test**: Digitar 2+ chars com e sem constraint; medir percepção ≤1s; digitar enquanto o probe do outro campo ocorre.

**Acceptance Scenarios**:

1. **Given** carga normal, **When** o visitante digita no autocomplete (com ou sem constraint), **Then** as sugestões aparecem em até 1 segundo.
2. **Given** o visitante digita no campo origem, **When** o probe do oposto está em andamento, **Then** a digitação no origem não é bloqueada.

---

### User Story 5 — Deploy automatizado (Priority: P1)

Após Git + `drush cr`, o cruzamento e os avisos estão ativos sem cliques no admin.

**Acceptance Scenarios**:

1. **Given** código e assets versionados, **When** sobe o deploy e limpa cache, **Then** a Home já aplica o cruzamento sem configuração manual.

## Edge Cases

- Digitação só com espaços → sem constraint efetiva; ambos os campos ativos.
- JS desabilitado → campos permanecem editáveis; busca GET continua; sem disable dinâmico (degradação aceitável).
- Homônimos de cidade: UF do hidden continua a restringir o cruzamento quando presente.
- Contagem `(N)` com constraint de curso reflete só vagas daquele curso.
- Cache stale: tags de lista de vagas/cursos invalidam agregações após publicação/despublicação (dentro das regras normais de cache).

## Requirements *(obrigatório)*

### Functional Requirements

- **FR-001**: A busca da Home MUST aceitar submissão com apenas curso, apenas cidade, ambos ou nenhum (listagem padrão).
- **FR-002**: O autocomplete de curso MUST listar somente cursos com pelo menos uma vaga publicada. Com cidade (e estado, se selecionado) preenchidos, MUST restringir ainda àquela localização.
- **FR-003**: Com curso preenchido, o autocomplete de cidade MUST listar somente cidades (par cidade+UF) com pelo menos uma vaga publicada daquele curso; `(N)` MUST refletir essa contagem filtrada.
- **FR-004**: Quando a constraint de um campo resultar em zero opções para o outro, o outro campo MUST ser desabilitado, ter o valor limpo (e UF limpa, se cidade) e exibir aviso traduzível.
- **FR-005**: Ao esvaziar o campo que gerou a constraint, o campo oposto MUST ser reabilitado e o aviso removido.
- **FR-006**: Match de texto MUST ignorar maiúsculas/minúsculas e acentos (mesmo padrão da feature 040).
- **FR-007**: Latência percebida do dropdown MUST ser ≤ 1s sob carga normal; o probe de habilitação MUST usar debounce ~300ms e NÃO bloquear a digitação no campo origem.
- **FR-008**: CSS/JS MUST viver em libraries versionadas; lógica de endpoints MUST viver em módulo custom — deploy sem admin.
- **FR-009**: Escopo UI MUST ser o hero da Home; hero `/vagas` MUST permanecer intacto nesta feature.
- **FR-010**: Nenhuma ativação em produção MUST exigir cliques no painel; sem `hook_update_N` obrigatório e sem YAML novo em `config/sync` só por esta feature.

### Key Entities

- **Vaga publicada**: node `vagas` com `status=1`, cidade, UF e curso(s).
- **Constraint de localização**: texto de cidade (+ UF opcional) que restringe cursos.
- **Constraint de curso**: texto de curso que restringe cidades.
- **Hint de campo desabilitado**: aviso sob o campo quando zerado pelo cruzamento.

## Success Criteria *(obrigatório)*

- **SC-001**: 100% dos testes com um único campo preenchido chegam à listagem filtrada corretamente.
- **SC-002**: Em 100% dos cenários seedados de cruzamento, as sugestões do campo oposto contêm apenas opções com vagas na combinação.
- **SC-003**: Combinações sem vagas desabilitam o campo oposto com aviso em ≤ 1 interação após a seleção/debounce; limpar a origem reabilita em 100% dos casos.
- **SC-004**: Em 100% das digitacões de QA (mín. 5), o dropdown aparece em até 1 segundo sob carga normal.
- **SC-005**: Após deploy só com Git + `drush cr`, cruzamento e avisos estão ativos sem admin.

## Assumptions

- “Vaga ativa” = publicada (`status=1`), mesmo critério da 040 / listagem pública.
- Copy dos hints: curso → “Sem cursos com vagas nesta cidade.”; cidade → “Sem cidades com vagas para este curso.”
- Sem constraint de cidade, o autocomplete de curso lista só termos com ≥1 vaga publicada (não todos os termos do vocabulário); cidades continuam só com vagas.
- Botão Buscar permanece sempre ativo.
- Parâmetros GET da View (`cursos`, `cidade`, `estado`) inalterados.

## Dependencies

- Feature **040** (autocomplete de cidades, form Home, library `hero_search`).
- Feature **039** (seed de vagas para QA).
- View `vagas` `page_1` e campos `field_cidade`, `field_estados`, `field_cursos_t`.

## Artefatos Basecamp / QA *(copiar e colar)*

```text
Sugestão de Branch:
feat/home-busca-inteligente

Título da Task:
Home: busca inteligente cruzando curso e cidade

Descrição da Task:
Tornar a busca da Home inteligente: os campos curso e cidade se influenciam (sugestões só com vagas na combinação). A pesquisa continua funcionando com um ou dois campos. Quando a combinação zerar opções do outro campo, desabilitar com aviso. Autocomplete deve permanecer rápido (≤1s). Deploy = Git + drush cr.

Roteiro de Validação Manual (QA):
1. Buscar só com curso; só com cidade; com ambos; com nenhum.
2. Selecionar cidade → conferir cursos sugeridos só daquela localização.
3. Selecionar curso → conferir cidades sugeridas só daquele curso (formato Cidade - UF - (N)).
4. Forçar combinação sem opções → campo oposto desabilitado + aviso; limpar origem → reabilita.
5. Digitar 2+ chars e confirmar dropdown ≤1s; digitar enquanto o outro campo é verificado (não trava).
6. Após Git + drush cr, repetir sem abrir o admin.
```
