# Feature Specification: Build de assets do tema e agregação CSS/JS (WebPerf)

**Feature Directory**: `specs/060-build-assets-webperf`  
**Created**: 2026-11-04  
**Status**: Draft  
**Predecessor**: `046-breakpoints-tema`, `001-banners-busca-home`  
**Input do usuário**: PageSpeed Insights mobile ~58, LCP ~8,0s e FCP ~3,7s. Especificar e preparar implementação de **minificação automatizada + pipeline de build** no tema `default`, **agregação nativa** de CSS/JS via configuração de performance do Drupal (deploy automatizado), e diretriz de LCP no banner hero (`loading="eager"` + `fetchpriority="high"`). Preferência por Vite; DX com `watch`; libraries do tema apontando para artefatos gerados; `hook_update_N` + `drush cex`.

## Problema

Visitantes mobile enfrentam carregamento lento da home (e páginas com o mesmo padrão de assets): muitos arquivos CSS/JS do tema sem minificação sistemática, agregação de CSS/JS **desligada** em `system.performance` (`css.preprocess` / `js.preprocess` = false no sync atual) e risco de a imagem LCP do hero não ser priorizada pelo navegador. Isso degrada LCP/FCP e a nota PageSpeed.

## Solução (visão de produto)

- Desenvolvedores passam a editar fontes do tema em uma pasta de origem; um processo de build gera CSS/JS **minificados** de forma automática (incluindo modo observação em desenvolvimento).
- Em produção, o Drupal **agrupa** CSS e JS habilitados para preprocess, reduzindo requisições HTTP.
- A imagem candidata a LCP do banner hero continua (ou passa a ser) carregada com prioridade alta e sem lazy-load.

## Escopo

### Inclui

- Pipeline de frontend no tema customizado `themes/custom/default`:
  - ferramenta de build moderna (**Vite**, escolha preferencial do pedido; Mix/Gulp só se a implementação descobrir bloqueio incontornável documentado);
  - compilação de SCSS → CSS quando houver fontes SCSS;
  - minificação de CSS (PostCSS/cssnano ou equivalente do toolchain escolhido);
  - minificação de JavaScript customizado do tema (ESBuild/Terser ou equivalente);
  - scripts npm `dev`/`watch` e `build` documentados;
  - leitura a partir de `src/` (ou `scss/` + `js/` equivalentes) e saída em `dist/` (ou `build/`);
  - atualização de `default.libraries.yml` para apontar entradas do tema aos artefatos gerados;
- Migração das folhas e scripts **próprios do tema** hoje em `assets/css/**` e `assets/js/**` para o fluxo fonte → build → libraries (mantendo paridade visual/comportamental);
- `hook_update_N` idempotente em `custom_configs.install` que garante `system.performance` com `css.preprocess` e `js.preprocess` = TRUE;
- Exportação da config via `drush cex` na origem para versionar `config/sync/system.performance.yml`;
- Guia rápido de uso (`npm install`, `npm run dev` / `npm run build`) na feature (quickstart na fase de plan/implement);
- Diretriz e verificação de LCP: imagem principal do banner hero da home (`custom_banners_hero_search` e/ou View de Banners da home) com `loading="eager"` e `fetchpriority="high"` no slide/imagem LCP (primeiro slide visível).

### Fora

- Otimização completa de imagens (WebP AVIF, CDN, image styles novos, lazy de below-the-fold global) — apenas a priorização LCP do hero desta feature;
- Critical CSS / inline do above-the-fold / HTTP/2 push / service worker;
- Minificação/bundling de bibliotecas de terceiros já servidas em `/libraries/` (Bootstrap, Font Awesome, Popper, jQuery Mask) — permanecem como estão;
- Alteração de `core/` ou `vendor/`;
- Redesign visual de páginas ou refatoração funcional de behaviors JS (apenas mover/minificar/preservar);
- Instalar Node.js no servidor de produção como requisito obrigatório (artefatos gerados devem ser versionáveis / reproduzíveis no fluxo de deploy atual);
- Código de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`);
- Meta de “nota PageSpeed X” como único critério de aceite desta feature (agregação + minificação + LCP hero são contribuições; LCP 8s pode exigir trabalho de imagem em features seguintes).

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Desenvolvedor altera CSS/JS e vê build minificado automaticamente (Priority: P1)

Desenvolvedor do tema edita um arquivo-fonte (SCSS/CSS ou JS) sob a pasta de origem do tema, executa o modo observação e obtém artefato minificado atualizado na pasta de saída, referenciado pelas libraries do tema.

**Why this priority**: Elimina minificação manual e garante que o que o site serve reflita o pipeline moderno.

**Independent Test**: Alterar um seletor ou string JS em fonte; rodar `watch`/`dev`; confirmar arquivo em `dist/` (ou equivalente) atualizado e menor/igual em tamanho que a fonte não minificada; library carrega o caminho novo.

**Acceptance Scenarios**:

1. **Given** o tema com pipeline configurado, **When** o desenvolvedor executa a instalação de dependências do tema e o script de observação, **Then** alterações em fontes disparam recompilação sem passo manual de minificar.
2. **Given** um build de produção do tema, **When** inspeciona os artefatos CSS/JS gerados, **Then** estão minificados (sem comentários de desenvolvimento desnecessários; whitespace reduzido) e utilizáveis pelo Drupal.
3. **Given** `default.libraries.yml`, **When** uma página anexa uma library do tema migrada, **Then** o HTML referencia o caminho dos artefatos gerados (não a fonte bruta antiga como caminho canônico).

---

### User Story 2 — Visitante autenticado ou anônimo recebe CSS/JS agregados em produção (Priority: P1)

Após o deploy desta feature, o site em modo de performance de produção serve CSS e JS do Drupal com preprocess/agregação ativos, reduzindo o número de arquivos linkados/scriptados em relação ao estado atual (preprocess false).

**Why this priority**: Agregação nativa ataca diretamente o volume de requisições HTTP, complementar à minificação por arquivo.

**Independent Test**: Em ambiente pós-`updb`/`cim` com cache limpo, abrir a home deslogado; contar links/scripts agregados vs. lista fragmentada anterior; confirmar config `system.performance` com preprocess true.

**Acceptance Scenarios**:

1. **Given** ambiente sem preprocess ativo, **When** o update de deploy desta feature roda, **Then** `css.preprocess` e `js.preprocess` passam a TRUE de forma idempotente.
2. **Given** a config exportada na origem, **When** o time revisa `config/sync`, **Then** `system.performance.yml` reflete preprocess true para CSS e JS.
3. **Given** visitante anônimo na home (produção ou staging espelhando produção), **When** inspeciona o HTML, **Then** CSS/JS elegíveis aparecem agregados (URLs de arquivos combinados do Drupal), sem regressão visual óbvia das seções principais da home.
4. **Given** reexecução do `hook_update_N`, **When** preprocess já está true, **Then** o hook é no-op seguro (não falha, não altera outras chaves de performance sem necessidade).

---

### User Story 3 — Navegador prioriza a imagem LCP do hero da home (Priority: P1)

Visitante abre a home; a imagem principal do banner hero (primeiro slide / LCP) é solicitada com alta prioridade e sem `loading="lazy"`.

**Why this priority**: LCP ~8s é o sintoma crítico citado; hero é o candidato mais provável.

**Independent Test**: Inspecionar o markup da imagem LCP do hero na home; confirmar atributos; opcionalmente comparar Lighthouse LCP element.

**Acceptance Scenarios**:

1. **Given** a home com banner hero ativo, **When** o HTML do primeiro slide/imagem LCP é inspecionado, **Then** contém `loading="eager"` e `fetchpriority="high"`.
2. **Given** slides seguintes do mesmo carrossel (se existirem), **When** inspecionados, **Then** podem permanecer `loading="lazy"` (não competir com o LCP).
3. **Given** templates/views já corretos, **When** a auditoria desta feature roda, **Then** documenta conformidade ou corrige o ponto faltante sem remover a priorização onde já existe.

---

### User Story 4 — Novo contribuinte sobe o pipeline em minutos (Priority: P2)

Desenvolvedor novo clona o repo, lê o quickstart da feature e consegue instalar e gerar build do tema sem conhecimento prévio do histórico do projeto.

**Why this priority**: DX e continuidade; evita regressão de “esqueci de minificar”.

**Independent Test**: Seguir apenas o quickstart em máquina limpa com Node compatível; `build` conclui com exit 0.

**Acceptance Scenarios**:

1. **Given** o quickstart, **When** executa os comandos documentados (`npm install` e `npm run build` no diretório do tema), **Then** o build conclui com sucesso e gera a pasta de saída esperada.
2. **Given** o mesmo guia, **When** executa o modo observação, **Then** o processo permanece ativo e reage a mudanças de fonte.

---

### Edge Cases

- Fonte SCSS com erro de sintaxe: build falha com mensagem clara; artefato antigo não é silenciosamente corrompido de forma opaca (falha visível no CI/local).
- Library do tema ainda apontando para caminho legado após migração parcial: proibido no aceite final — todas as libraries do tema listadas no escopo devem apontar para artefatos gerados.
- Agregação ativa + JS que depende de ordem/closures frágeis: behaviors Drupal existentes devem continuar funcionando na home e páginas-chave (smoke); se algum arquivo precisar de `preprocess: false` pontual, documentar exceção mínima na library.
- Ambiente local de desenvolvimento: preprocess pode permanecer desligado via override local **desde que** produção/staging recebam TRUE via update + sync; o hook garante o destino.
- `node_modules` não versionado; artefatos `dist/` versionados **ou** gerados em etapa obrigatória pré-deploy documentada (ver Assumptions — default: versionar `dist/` para não exigir Node em produção).
- Reexecução de `drush updb` com config já true: mensagem de status sem erro.
- Banner hero ausente na home (bloco desligado): SC de LCP hero N/A nesse ambiente; demais requisitos de build/agregação permanecem.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: O tema `default` MUST possuir um ecossistema de build automatizado (preferência Vite) capaz de produzir CSS e JS minificados a partir de fontes versionadas.
- **FR-002**: O build MUST suportar compilação SCSS → CSS quando houver fontes `.scss`, e MUST minificar o CSS resultante.
- **FR-003**: O build MUST minificar o JavaScript customizado do tema incluído no pipeline.
- **FR-004**: MUST existir script de observação (`watch`/`dev`) que recompila e minifica ao detectar mudanças nas fontes.
- **FR-005**: Fontes MUST viver sob pasta de origem (`src/` ou equivalente documentado); artefatos finais MUST viver sob pasta de saída (`dist/` ou equivalente documentado).
- **FR-006**: `default.libraries.yml` MUST referenciar os artefatos gerados para todas as libraries CSS/JS **próprias do tema** migradas nesta feature.
- **FR-007**: Bibliotecas de terceiros em `/libraries/` (Bootstrap, Font Awesome, etc.) MUST permanecer fora do escopo de minificação do tema.
- **FR-008**: Um `hook_update_N` idempotente em `custom_configs.install` MUST carregar a config `system.performance` e garantir `css.preprocess` = TRUE e `js.preprocess` = TRUE.
- **FR-009**: Na origem, após o update/configuração, MUST-se executar `drush cex` para versionar `config/sync/system.performance.yml` alinhado ao preprocess ativo.
- **FR-010**: A imagem LCP do banner hero da home MUST usar `loading="eager"` e `fetchpriority="high"` (corrigir se ausente; preservar se já conforme).
- **FR-011**: MUST existir documentação rápida de uso do pipeline (`npm install`, `npm run dev`/`watch`, `npm run build`) no quickstart da feature.
- **FR-012**: O pipeline e a agregação MUST NÃO alterar o comportamento visível esperado das seções públicas já existentes (smoke home + pelo menos uma página interna que use libraries migradas).
- **FR-013**: Deploy MUST permanecer sem passo manual no admin de produção para ativar agregação (update hook + config sync).
- **FR-014**: Durante a implementação, o time MUST verificar se alguma skill do pacote `mattpocock/skills` (via `npx skills find` / skills.sh) agrega valor tangível ao DX do pipeline; se nenhuma for adequada, documentar a verificação no quickstart/plan sem bloquear a entrega.

### Key Entities

- **Pipeline do tema (`default`)**: Configuração npm + Vite (ou equivalente justificado); pastas `src/` → `dist/`.
- **Libraries do tema**: Entradas em `default.libraries.yml` que passam a apontar para artefatos minificados.
- **Configuração `system.performance`**: Entidade de config do core com flags `css.preprocess` e `js.preprocess`.
- **Banner hero (LCP)**: Bloco/View da home cuja imagem principal é candidata a Largest Contentful Paint.
- **Update hook de performance**: `custom_configs_update_N` idempotente que garante preprocess true.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Em 100% das libraries CSS/JS próprias do tema no escopo, o caminho publicado aponta para artefato gerado pelo build (auditoria de `default.libraries.yml` + smoke de anexação).
- **SC-002**: O build de produção dos assets do tema conclui com sucesso em ≤ 60 segundos em máquina de desenvolvimento padrão do time (após a instalação inicial de dependências do pipeline).
- **SC-003**: Após deploy da feature, `css.preprocess` e `js.preprocess` estão ativos em 100% dos ambientes de destino que aplicaram `updb`/`cim` desta release.
- **SC-004**: Na home (visitante anônimo, agregação ativa), o número de arquivos CSS/JS **do tema/core elegíveis a preprocess** linkados individualmente cai em relação ao baseline atual com preprocess false (verificado por comparação de network/HTML antes vs. depois no mesmo ambiente).
- **SC-005**: A imagem LCP do hero da home apresenta `loading="eager"` e `fetchpriority="high"` em 100% das inspeções de markup pós-feature.
- **SC-006**: Smoke visual da home (hero, topo, seções principais) e de uma página que use library migrada não apresenta regressão funcional/visual óbvia atribuível à minificação/agregação (checklist binário aprovado pelo implementador).
- **SC-007**: Novo desenvolvedor completa instalação + build seguindo só o quickstart em ≤ 15 minutos (excluindo tempo de download de Node/npm se ausentes).
- **SC-008**: Reexecução do update de performance não gera erro e não desfaz preprocess true.

## Assumptions

- **Ferramenta**: Vite é a escolha padrão; Mix/Gulp só com justificativa escrita no plan se Vite for inviável com libraries Drupal multi-entry.
- **Escopo de migração**: Todos os CSS/JS sob `themes/custom/default/assets/css` e `assets/js` entram no pipeline nesta feature (não só `global-styling`). Imagens em `assets/images` permanecem onde estão.
- **SCSS**: O tema hoje usa CSS plano; o pipeline aceita SCSS e permite migrar fontes como `.css` ou renomear para `.scss` sem reescrever visualmente. Não é obrigatório reescrever toda a arquitetura em partials SCSS nesta feature — o mínimo é build + minify + watch.
- **Artefatos em Git**: Por padrão, a pasta de saída (`dist/`) **é versionada** para produção não depender de Node; `node_modules/` permanece ignorado.
- **Agregação**: Destino de produção/staging deve refletir preprocess true; overrides locais de desenvolvedor (se existirem) não anulam o sync/hook no destino.
- **LCP hero**: Baseline atual já aplica eager/high em vários templates de banners; esta feature **audita e fecha lacunas** no candidato LCP da home, sem expandir para todas as imagens do site.
- **PageSpeed**: Melhora esperada em FCP/LCP e redução de requests; a nota 58→meta maior pode exigir features posteriores (imagens, fontes, TTFB).
- **Skills `mattpocock/skills`**: Busca preliminar (`webperf`, `vite`, `frontend`) não retornou skill madura de WebPerf/Vite útil (≥1k installs); na implementação, revalidar e registrar o resultado (FR-014).
- **Numeração do hook**: Próximo `hook_update_N` disponível na linha atual do módulo (após `11089`) será definido na implementação.
- **PRD**: Alteração de `system.performance` exportada é estrutural de config; na implementação, confirmar com o time se o `PRD.md` precisa de menção cirúrgica (guardião de escopo).

## Dependencies

- Node.js/npm disponíveis nas máquinas de desenvolvimento (e na CI, se o time passar a validar `npm run build`).
- Drupal 11 aggregation nativa (`system.performance`).
- Módulo `custom_configs` para o update hook.
- Tema `themes/custom/default` como único tema customizado alvo.
- Fluxo de deploy existente (`cim` → `updb` → `cim` → `cr`), com preprocess vindo do sync e/ou do hook.

## Out of Scope (resumo)

Ver seção **Escopo → Fora**. Em especial: otimização profunda de imagens, critical CSS, alterar core/vendor, e implementação de código nesta fase de specify.
