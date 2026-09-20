# Research: Quem Somos — Seção Missão e Visão (no Node)

**Data**: 2026-09-20 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

## R1 — Node fields vs. Custom Block + Paragraphs (011)

**Decision**: Missão/Visão no Node `quem_somos` via três field instances + Field Group; **sem** Paragraphs.

**Rationale**: FR-005 e Input do usuário; conteúdo institucional vive no mesmo formulário da página; elimina duplicidade editorial Node vs. bloco; remove dependência de `content_full` + placement path.

**Alternatives considered**: manter/aprofundar `011` (bloco + paragraphs) — rejeitado pelo product owner; Layout Builder — fora de escopo.

## R2 — Reuso de storages (sem paralelos)

**Decision**: anexar ao bundle `quem_somos`:
- `field_imagem_desktop` (image) — fundo
- `field_text_simple_long` (string_long) — texto Missão
- `field_text_simple_long_2` (string_long) — texto Visão

**Rationale**: FR-005; storages já existem (`banners` / `para_empresas`); pedido verbal `field_image_desktop` mapeia para o canônico `field_imagem_desktop`.

**Alternatives considered**: criar `field_bg_imagem` / `field_image` paralelo — proibido; reusar `field_imagem` do Sobre nós — rejeitado (FR: `field_imagem` exclusivo da 1ª seção).

## R3 — Remoção do legado Anexo 2

**Decision**: remover instances `field_titulo_2`, `field_text_long_formatted_2`, `field_imagem_2` + `group_segundo_bloco` + markup Twig da 2ª seção; em seguida excluir storages órfãos (hoje só usados por `quem_somos`).

**Rationale**: FR-001–004; checklist visual exige ausência do layout Anexo 2; storages órfãos aumentam ruído no Configuration Management.

**Alternatives considered**: ocultar campos no display sem deletar — rejeitado (editor ainda veria “Segundo Bloco”); esvaziar conteúdo e manter fields — rejeitado (débito editorial).

## R4 — Full-width dentro de `#main.container`

**Decision**: (1) no Twig, `.container` só envolve Sobre nós; seção Missão/Visão é irmã fora desse container interno; (2) CSS breakout sob `.quem-somos-missao-visao` (`width: 100vw; margin-left: calc(50% - 50vw);` ou equivalente seguro) para escapar `#main.container` da `page.html.twig`.

**Rationale**: FR-010; o bloco 011 usava `content_full` (fora de `#main`); o Node renderiza em `page.content` dentro do container Barrio — breakout é a técnica equivalente pedida na spec.

**Alternatives considered**: preprocess removendo `container` de `#main` em `/quem-somos` — risco de regressão no Sobre nós e no layout da página; mover seção de volta para bloco — contradiz a feature.

## R5 — Markup e tipografia (espelho visual da 011)

**Decision**: reutilizar o contrato visual já validado em `block-missao-visao.css` (overlay, `min-height: 380px`, Poppins, `col-md-6`, `border-right` no 1º col), sob classe raiz **`.quem-somos-missao-visao`** e library nova — **não** reutilizar a library do bloco (evita acoplar Node ao CSS do bloco aposentado na rota).

**Rationale**: SC-001 alinhado ao Anexo 1; FR-016 isolamento; YAGNI de reinventar tokens.

**Alternatives considered**: attach `default/missao_visao` no Node — rejeitado (nome/seletores `.block-missao-visao` ficariam semânticamente errados e acoplados à 011).

## R6 — Títulos fixos no Twig

**Decision**: H2 “Nossa Missão” / “Nossa Visão” hardcoded (pt-BR); campos armazenam só o corpo.

**Rationale**: FR-008 / Assumptions; simplifica modelo vs. paragraphs com `field_text_simple`.

**Alternatives considered**: fields de título editáveis — fora do escopo desta feature.

## R7 — Desabilitar bloco `default_missaovisao` (não deletar tipos)

**Decision**: `block.block.default_missaovisao` → `status: false` via `cex` + ensure no hook `11015`. Manter `block_content.type.missao_visao` / paragraphs / conteúdo seed no sistema.

**Rationale**: FR-017 / US4 — uma faixa só; apagar tipos seria destrutivo e fora do mínimo; disable é reversível e idempotente.

**Alternatives considered**: apagar placement YAML — também ok, mas `status: false` preserva histórico de config; restringir pages a path inexistente — mais frágil; deletar block type — YAGNI / risco a conteúdo.

## R8 — Hook `custom_configs_update_11015`

**Decision**: update idempotente que:
1. Remove instances/grupo legado e limpa displays (se ainda presentes).
2. Anexa os três fields + `group_missao_visao` + widgets nos displays (defensivo pós-`cim`).
3. Seed: textos Assumptions em `field_text_simple_long` / `_2` **somente se vazios**; imagem de fundo a partir do asset `modules/custom/custom_configs/assets/missao-visao/fundo-missao-visao.jpg` (ou File já usado pelo bloco UUID `b2c3d4e5-…`) **somente se** `field_imagem_desktop` vazio.
4. Migração opcional: se `field_text_long_formatted_2` ainda existir e novos textos vazios, strip tags → Missão (uma vez); Visão fica com seed Assumptions se ainda vazia.
5. Garante `default_missaovisao` desabilitado.
6. **Nunca** sobrescreve editorial já preenchido.

**Rationale**: FR-017–018; padrão `11012`–`11014`.

**Alternatives considered**: só config sem seed — página fica sem copy visual no aceite; migrar HTML legado fielmente para ambos campos — impossível (um campo legado → dois textos).

## R9 — Ordem de deploy e export

**Decision**: desenvolvimento: alterar estrutura → `drush cex`; ambientes: `cim` → `updb` → `cr`. Hook é rede de segurança se `cim` parcial.

**Rationale**: FR-018; `estagio-fluxo-dev.mdc`.

## R10 — PRD

**Decision**: atualização cirúrgica:
- §3.1.0: remover `*_2`; documentar `group_missao_visao` + três fields; apontar Twig/library/`11015`.
- §3.6: marcar `missao_visao` como não exibido em `/quem-somos` (substituído pelo Node); referenciar `11015`.

**Rationale**: FR-019; Guardião do Escopo.

## R11 — Convivência com Sobre nós (010) e banner (009)

**Decision**: zero mudança em `group_primeiro_bloco`, `layout_sobre_nos`, banner `block_quem_somos`.

**Rationale**: Fora de escopo; SC-009.

## R12 — Omitir seção vazia

**Decision**: no Twig, se Missão e Visão vazios **e** sem imagem → não renderizar `<section>`; se só um texto → uma coluna; sem imagem → fallback de cor escura + overlay.

**Rationale**: Edge cases / SC-008.
