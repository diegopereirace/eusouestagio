# Research: Layout “Sobre nós” (wrap texto + imagem)

**Data**: 2026-09-14 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

## R1 — Reuso de fields vs. storages paralelos

**Decision**: usar `field_titulo`, `field_text_long_formatted` e `field_imagem` já anexados ao bundle `quem_somos`.

**Rationale**: FR-001; regra de reuso do projeto; instances e form groups “Primeiro Bloco” já existem em `config/sync`. O pedido verbal `field_text_formatted_long` / `field_image` mapeia para esses machine names.

**Alternatives considered**: criar storages/instances com os nomes verbais — rejeitado (duplicação proibida; risco de migração destrutiva).

## R2 — Um campo de texto vs. dois blocos “lado / abaixo”

**Decision**: manter um único `field_text_long_formatted`; o wrap visual é responsabilidade do CSS/float, não de dois campos editoriais.

**Rationale**: FR-002; evita drift de copy e complexidade de CMS; Figma trata um fluxo contínuo de texto.

**Alternatives considered**: dividir em dois Text long — rejeitado pela spec (Fora / FR-002).

## R3 — View mode `default` vs. criar `full`

**Decision**: continuar com `entity_view_display` `default` (único existente); não criar modo `full` nesta feature.

**Rationale**: Assumptions da spec; a rota de página já consome `default`; criar `full` exigiria config/display duplicados sem ganho.

**Alternatives considered**: espelhar `default` em `full` — YAGNI; pode ser feature futura se outros modos forem necessários.

## R4 — Float Bootstrap vs. CSS Grid / duas colunas

**Decision**: 1ª seção com wrapper da imagem `float-md-end` (+ `ms-md-*` / `mb-*`) e container `clearfix`; texto em fluxo normal após a imagem no DOM.

**Rationale**: FR-007/008/012; é o mecanismo nativo para “texto contorna a imagem”; Bootstrap 5 já no tema; mobile desliga float abaixo de `md`.

**Alternatives considered**: manter `row`/`col-md-*` (estado atual) — não produz wrap; CSS Grid com áreas — não envolve texto ao redor da imagem como float; shape-outside — mais frágil e sem necessidade no Figma.

## R5 — Ordem no DOM (imagem antes do texto)

**Decision**: renderizar o wrapper da imagem **antes** do corpo formatado no Twig da 1ª seção; título permanece acima de ambos.

**Rationale**: FR-007; float à direita com imagem primeiro no fluxo é o padrão estável para wrap.

**Alternatives considered**: texto antes + float na imagem — funciona em alguns casos, mas a spec exige imagem no início do fluxo do texto.

## R6 — Decorativos geométricos

**Decision**: pseudo-elementos CSS (`::before` anel verde vazado ~185×185; `::after` círculo laranja sólido) no wrapper `position-relative` da imagem; `pointer-events: none`; `z-index` atrás/à frente conforme Figma sem cobrir texto no mobile.

**Rationale**: FR-010; zero assets; isolados ao wrapper (não vazam se o wrapper for omitido sem imagem).

**Alternatives considered**: SVGs/Twig extras — mais markup; background no container da seção — risco de vazamento para a 2ª seção.

## R7 — Tokens de cor

**Decision**: sob `.sobre-nos` (ou equivalente), `--sobre-nos-green: var(--brand-green)` (`#5eb344`) e `--sobre-nos-orange: #FD7B1A` (Figma / alinhado ao banner 009). Não alterar `--brand-orange` global (`#ff8a22`).

**Rationale**: fidelidade visual + SC-007 (sem regressão em outros tipos); mesmo padrão de tokens locais da feature 009.

**Alternatives considered**: usar só `--brand-orange` global — aceitável, mas diverge do laranja já adotado no banner Quem Somos.

## R8 — Escopo de CSS / library

**Decision**: library `default/layout_sobre_nos` → `assets/css/layout-sobre-nos.css`; attach no Twig do node; seletores sob `.node--type-quem-somos .sobre-nos` (e filhos). Classe BEM `.sobre-nos` = equivalente prático ao “`.node-quem-somos`” da spec.

**Rationale**: FR-006; evita poluir `style.css` global; espelha `banner_quem_somos`.

**Alternatives considered**: regras soltas em `style.css` — rejeitado (risco de vazamento e diff grande).

## R9 — Segunda seção e clearfix

**Decision**: manter markup atual da 2ª seção (`row-custom-2` duas colunas); garantir `clearfix` no fim da 1ª seção / início do bloco seguinte.

**Rationale**: FR-013; fora de escopo redesign Missão/Visão; só não quebrar.

**Alternatives considered**: redesenhar 2ª seção no Figma — feature aparte (Fora).

## R10 — Hook `custom_configs_update_11013`

**Decision**: update idempotente que:
1. Verifica field instances `field_titulo`, `field_text_long_formatted`, `field_imagem` no bundle `quem_somos`; reanexa via API de Field se ausentes (storages já existem).
2. Garante form display (campos no content / grupo Primeiro Bloco) e view display `default` com esses campos não ocultos de forma a impedir o Twig.
3. No-op se já estiverem corretos; **nunca** sobrescreve valores editoriais do node.

Estrutura primária continua em `config/sync` (`cim` antes do `updb`).

**Rationale**: FR-014/015/016; ambientes desatualizados; padrão 11001+.

**Alternatives considered**: só theme sem hook — rejeitado (requisito explícito de deploy zero-manual); seed de conteúdo — fora de escopo (editor dono do copy).

## R11 — `field_imagem` required vs. edge case sem imagem

**Decision**: manter `required: true` no field config (estado atual); o Twig trata ausência de entidade de arquivo de forma defensiva (omitir wrapper) para SC-008 / conteúdo legado inconsistente.

**Rationale**: formulário editorial exige imagem no caminho feliz; template robusto não quebra se o arquivo sumir do disco.

**Alternatives considered**: tornar a imagem opcional no form — possível, mas muda governança editorial sem pedido explícito; adiar.

## R12 — PRD

**Decision**: atualizar cirurgicamente §3.1 (machine name `quem_somos`) e adicionar subseção de campos/layout wrap da página institucional.

**Rationale**: FR-017; mudança de apresentação relevante da página; guardião PRD para alteração estrutural/governança.

**Alternatives considered**: só Twig sem PRD — incompleto frente à FR-017 e ao padrão das features anteriores.
