# Research: Bloco Nossa Metodologia

**Data**: 2026-09-11 | **Feature**: [spec.md](spec.md)

## R1 — Configuração e deploy

**Decision**: versionar tipo, campos, displays, permissões e placement em `config/sync`; usar `custom_configs_update_11005()` para o conteúdo inicial.

**Rationale**: é o padrão já operacional da feature 004 e separa corretamente configuração de conteúdo editorial.

**Alternatives considered**: configuração manual por ambiente, dump SQL e módulo novo com `config/install` — rejeitados por drift, risco operacional e duplicação.

## R2 — Storages de campos (atualizado 2026-09-11)

**Decision**: reutilizar `field_text_simple`, `field_text_simple_long` e `field_image` (multi) no bloco para as etapas superiores; criar `field_metodologia_passos` (ERR → paragraph) e o tipo `metodologia_passo_p` com `field_image` + `field_text_simple` reutilizados do entity type paragraph. Remover `field_image_desktop` / `field_image_mobile`.

**Rationale**: o fluxograma precisa ser reorganizado no mobile item a item; duas artes monolíticas impedem isso. `field_image` já tem cardinalidade -1 em `block_content`. Passos com ícone+título seguem o padrão de diferenciais/destaque.

**Alternatives considered**: manter desktop/mobile monolíticas; reutilizar `icone_descricao` — rejeitados (layout rígido; pedido explícito de paragraph novo).

## R3 — Conteúdo inicial e UUID

**Decision**: seed idempotente com UUID fixo novo, título e subtítulo da FR-015 e imagens vazias. Se o UUID já existir, não alterar conteúdo editorial.

**Rationale**: `cim` não cria entidades `block_content`; UUID estável permite que o placement versionado referencie a mesma entidade em todos os ambientes.

**Alternatives considered**: versionar binários, adicionar `default_content` ou exigir recriação manual — rejeitados na v1.

## R4 — Placement e ordem da home

**Decision**: região `content_full`, visibilidade `<front>`, weight `-3`.

**Rationale**: “Nossos Diferenciais” já usa a mesma região com weight `-4`; o novo peso o coloca imediatamente depois e antes dos blocos de weight 0.

**Alternatives considered**: região `content` ou reordenação manual — rejeitadas por inconsistência e por não atender FR-008/FR-009.

## R5 — Twig etapas + passos (atualizado 2026-09-11)

**Decision**: `block--block-nossa-metodologia.html.twig` renderiza `.nm-etapas` (loop de `field_image`) e `.nm-passos` (`field_metodologia_passos`). Paragraph template `paragraph--metodologia-passo-p.html.twig` para ícone + título. Setas entre passos via CSS. `loading="lazy"` nas imagens.

**Rationale**: itens independentes permitem flex/coluna no mobile sem arte duplicada.

**Alternatives considered**: `<picture>` monolítica; setas como campo CMS — rejeitados.

## R6 — Layout, tipografia e acessibilidade

**Decision**: `.container`, header `text-center text-md-end`, diagrama fluido abaixo do texto e CSS sob `.block-nossa-metodologia`. Reutilizar Poppins 300–700 já carregada. O `alt` efetivo seguirá desktop → mobile → vazio.

**Rationale**: Bootstrap `md` inicia em 768px, exatamente o breakpoint da especificação; o escopo evita efeitos colaterais.

**Alternatives considered**: grid de duas colunas ou breakpoint customizado — rejeitados por divergirem do mockup e do padrão do tema.

## R7 — Displays e permissões

**Decision**: form display com título, subtítulo, desktop e mobile; view display com labels ocultos e controle final no Twig. Conceder ao papel `moderador` create/edit/delete para `nossa_metodologia`.

**Rationale**: mantém o bloco integralmente gerenciável e segue a feature 004.

**Alternatives considered**: permissão administrativa genérica ou campos sem form display — rejeitados pelo princípio de menor privilégio e SC-001.

## R8 — Performance

**Decision**: usar arquivo original com `img-fluid` na v1 e criar image styles dedicados apenas se a validação demonstrar peso excessivo.

**Rationale**: a arte pode ser SVG/PNG e não deve ser recortada; image styles de banners têm semântica e proporções diferentes.

**Alternatives considered**: reutilizar styles de banner ou criar styles preventivamente — rejeitados por risco de crop e YAGNI.

## R9 — Ordem de deploy

**Decision**: `git pull` → `drush cim -y` → `drush updb -y` → `drush cr`. Em instalação limpa, verificar se a dependência de conteúdo do placement exige um segundo `drush cim -y` após o seed.

**Rationale**: fields precisam existir antes do seed. A dependência do plugin de bloco por UUID é o único ponto que pode exigir segunda passagem em ambiente sem conteúdo prévio.

**Alternatives considered**: `updb` antes de `cim` — rejeitado porque o bundle e os campos ainda não existem.

## R10 — Governança

**Decision**: incluir atualização cirúrgica da seção 3.6 do `PRD.md` na implementação.

**Rationale**: a feature aprova alterações estruturais de block content e placement; a regra de governança exige sincronizar o documento canônico.

**Alternatives considered**: não atualizar o PRD — rejeitado por deixar o inventário de produto incompleto.
