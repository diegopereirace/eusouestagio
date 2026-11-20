# Research: Refino Figma Contato (bleed + cards) e tipografia Contato

**Data**: 2026-11-04 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **fidelidade ao Figma de Contato**, **token tipográfico só no H2 Contato**, **CSS limpo sem `100vw`**, e **zero regressão do webform**, reusando Node/fields/alter da 018.

---

## R1 — Bleed direito assimétrico (sem `100vw`)

**Decision**: Refatorar o markup/CSS de Contato para layout assimétrico em ≥992px:

1. Remover a restrição de `.container` Bootstrap que corta **ambas** as colunas (hoje o painel fica “dentro da caixa” e deixa faixa branca à direita).
2. Usar CSS Grid ou flex full-width sob `.layout-contato-node` (ou wrapper dedicado), com:
   - **coluna esquerda**: padding/margin-left alinhado ao gutter do container do tema (mesma referência do header), largura ~7/12 ou equivalente para caber o form;
   - **coluna direita**: fundo `#E5EEFF` estendido até a borda direita útil da viewport (técnica breakout: `margin-right`/`width` relativo ao parent liberado, ou coluna que ocupa o restante do grid até a edge).
3. Liberar `#main.container` **só** em `body.path-contato` / `:has(.node--contato)` no mesmo espírito de Missão/Visão e Impact Numbers (`max-width: none; padding laterais 0` no shell), para o bleed não ser clipado pelo Barrio.
4. **Proibido** `width: 100vw` / `margin-right: calc(50% - 50vw)` cego — gera overflow-x com scrollbar (já documentado no tema).

**Rationale**: FR-002 / FR-003 / SC-001 / SC-002. O gap atual é estrutural (container simétrico), não só cor.

**Alternatives considered**:
- Só `border-radius: 0` e `width: 100%` mantendo `.container` — rejeitado (não alcança a borda da viewport).
- Absolute positioning do painel — rejeitado (altura das colunas e mobile ficam frágeis).
- Duplicar region full-width Barrio — overkill para um node.

---

## R2 — Breakpoint 992px e mobile

**Decision**: Empilhamento e regras mobile em `@media (max-width: 991.98px)`:

- Ordem: formulário → painel.
- Painel: 100% da área de conteúdo (sem exigir bleed só à direita).
- `.form-actions`: wrap; submit `width: 100%`.
- Cards: podem quebrar de linha; sem `nowrap` forçado que cause scroll horizontal (revisar regra atual `flex-wrap: nowrap` em ≤575.98px).

**Rationale**: FR-011 / SC-004; Assumption da spec (Bootstrap `lg`).

**Alternatives considered**: Cortar em 768px — rejeitado (spec e Twig já usam `col-lg-*`).

---

## R3 — Linha de ações: botão + cards (só apresentação)

**Decision**: Manter a injeção PHP existente (`custom_configs_webform_submission_form_alter`) e ajustar CSS:

| Elemento | Alvo desktop | Notas |
|----------|--------------|--------|
| Submit | ~180×56; `#FD7B1A`; texto branco; sem borda aparente | vencer `.btn.btn-primary.form-control` |
| Card E-mail | ~280×70; borda `#C6C6CD`; valor `#45464D` | `mailto:` já ok |
| Card WhatsApp | ~214×70; mesma linguagem | `wa.me` + DDI 55 já ok |
| Gap | consistente (flex `gap`) | fileira única no desktop se couber |

Omit empty permanece no alter. Se markup mínimo precisar de BEM extra (`--email` / `--whatsapp` já existem), ajustar o inline_template sem mudar a origem dos fields.

**Rationale**: FR-006–009 / FR-015 / SC-003. Estrutura DOM já correta; gap é visual.

**Alternatives considered**:
- Mover cards para o Twig do node fora do form — rejeitado (quebra a fileira única com o submit do webform).
- Recriar webform elements — fora de escopo.

---

## R4 — Painel “Time de especialistas”

**Decision**: No desktop, o fundo `#E5EEFF` é a **coluna** (bleed), não um card com radius 24px flutuando no meio. Conteúdo (imagem, título, texto) centralizado com flex (`align-items` / `justify-content: center`). Mobile: painel full-width; radius opcional se melhorar o frame estreito. Imagem: `loading="lazy"` + `decoding="async"` + Image Style `contato_painel` (scale width 640, `upscale: false`, convert WebP) — não servir URI crua.

**Rationale**: FR-010 / FR-010a / US3. Padding Figma ~318px = intenção de centralização, não valor fixo. Style 640 cobre o painel sem `srcset`; WebP reduz bytes vs PNG/JPEG original.

**Alternatives considered**: Manter `border-radius: 24px` + container — rejeitado (conflita com bleed na edge). URI crua — rejeitado (FR-010a / performance). `eager` no desktop (LCP) — rejeitado (requisito pede lazy; no mobile a imagem fica abaixo do form).

---

## R5 — Token tipográfico canônico

**Decision**: Introduzir utilitário CSS `.ese-section-title` (nome curto, escopo projeto):

```css
font-family: Inter, "Segoe UI", sans-serif;
font-weight: 600;
font-size: 30px;
line-height: 38px;
letter-spacing: -0.3px;
color: #000000;
```

Aplicar no H2 “Envie sua mensagem” (Twig + CSS Contato). Carregar via library global leve ou dependência anexada no tema base / `style` para que blocos públicos herdem sem library por página. Inter já está disponível no tema.

**Rationale**: FR-016 / FR-017 / SC-007. Anexo Contato = fonte de verdade do H2.

**Alternatives considered**:
- Só CSS em `.layout-contato-node__title` sem classe canônica — rejeitado (FR-017 pede `.ese-section-title`).
- Variáveis CSS `--ese-section-*` sem classe — útil como complemento, mas a classe dá aplicabilidade imediata.
- Migrar tudo para Poppins 600 — rejeitado (spec manda Inter).

---

## R6 — Escopo tipográfico: só Contato (override de produto)

**Decision**: O token aplica-se **somente** a `/contato` (`.layout-contato-node__title` + `.ese-section-title` + legado `.block-layout-contato .layout-contato__title`). Library: `layout_contato` (não `global-styling`).

**Não** aplicar aliases a `.nd-header__title`, `.nm-header__title`, `.oqf-header__title`, `.cf-header__title`, `.be-header__title`, `.oe-header__title`, `.je-header__title`, `.dqs-header__title`, heroes, cards, CTAs, nav, footer, labels.

**Rationale**: FR-018 / SC-008 (revisados): evitar regressão tipográfica em blocos já refinados (042–056).

**Alternatives considered**: Padronização site-wide via aliases — rejeitada por decisão de produto pós-implementação. Trocar `h2` global — rejeitado (colateral amplo).

---

## R7 — Deploy / hook / config

**Decision**: Tema + **Image Style** `contato_painel` em `config/sync`. Sem `hook_update_N` salvo display incompleto. Assets Contato **não** são reescritos (057). Origem: `drush cex -y` + `drush cr`. Destino: **`cim` + `cr`** (obrigatório); se `11089` → `cim` → `updb` → `cim` → `cr`.

**Rationale**: FR-010a + gates deploy; “só `cr`” quebraria a imagem do painel sem o style importado.

**Alternatives considered**: Hook cosmético vazio — rejeitado. Reseed Node — rejeitado (editorial / 057). Deploy só tema — rejeitado após T038.

---

## R8 — Inputs do webform

**Decision**: CSS sob `.layout-contato-node__webform`: altura ~57px nos inputs de linha; border fino; radius ~8px; pares lado a lado no desktop (comportamento atual do webform) e stack no mobile. Não alterar YAML do webform.

**Rationale**: FR-012 / US7.

**Alternatives considered**: Alterar elementos do webform no admin — fora de escopo.
