# Research: Restaurar Benefícios Estudantes — Layout Figma

**Data**: 2026-11-03 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **conformidade com o Figma**, **reuso do block type / fields existentes**, **recuperação após regressão `11079`/`11080`**, **deploy repetível** (`cim` → `updb` → `cim` → `cr`) e **preservação de copy editorial divergente**.

---

## R1 — Restore in-place (sem segundo bloco)

**Decision**: Manter o bundle `beneficios_estudantes`, o paragraph `card_icon_text_p`, o UUID `a8b9c0d1-e2f3-4456-a789-0bcdef123456` e o placement `default_beneficiosestudantes` (`content_full`, weight `0`, `/para-estudantes`). Alterar apresentação (Twig/CSS), restaurar seed canônico de 6 itens e garantir recriação se instância/placement sumirem.

**Rationale**: FR-007 / FR-009 / SC-008. A “grade de 4 cards” visível hoje **é** este bloco (Assumption); criar paralelo quebraria a ordem Banner → Hero → Benefícios.

**Alternatives considered**:
- Novo block type / segundo placement “depois de si mesmo” — rejeitado (duplicação + confusão editorial).
- Só CSS sem tocar seed — rejeitado (canônico ainda são 4 cards pós-`11079`).

---

## R2 — Fields: reuso total (zero instance nova)

**Decision**:

| Papel UI | Storage / instance | Notas |
|----------|--------------------|--------|
| Badge | `field_text_simple_2` | já no bundle (054) |
| Título | `field_text_simple` | já no bundle |
| Subtítulo | `field_text_simple_long` | já no bundle |
| Itens | `field_itens_lista` → `card_icon_text_p` | seed → 6 |
| CTA | `field_link` | já no bundle |
| Colagem (centro) | `field_image` | já no bundle; satélites fora do CMS |

Ensure de structure/displays no hook permanece defensivo (idempotente), mas **não** cria storage paralelo.

**Rationale**: Assumptions da spec + gate de reuso. YAMLs já em `config/sync`.

**Alternatives considered**: Storage novo para órbita multi-imagem — rejeitado (paralelo + CMS foto-a-foto fora de escopo).

---

## R3 — Itens como cards brancos (não lista flat da 054)

**Decision**:

- Twig do paragraph: layout horizontal (ícone à esquerda, título+descrição à direita); classes de coluna `col-12` + `col-md-6` (sem `col-lg-3`).
- CSS: fundo branco, `border-radius` generoso, padding interno, gap ~16px entre cards; sem min-height 310px da era 022.
- Ícone ~40–48px; `loading="lazy"`.
- Classe preferencial: manter `.be-item` com chrome de card **ou** `.be-card` local sob `.block-beneficios-estudantes` (não reutilizar `.dqs-*` / `.nd-*`).

**Rationale**: FR-004 / SC-001. O Figma desta feature trata itens como cards claros sobre `#eef5ff`, diferentemente da lista flat da 054.

**Alternatives considered**: Manter lista flat 054 — rejeitado (diverge do frame atual). Paragraph type novo — rejeitado (mesmo payload).

---

## R4 — Título bicolor no Twig

**Decision**: Quando o título renderizado (após strip) for exatamente `Muito mais do que encontrar um estágio.`, o Twig emite:

```html
<span class="be-header__title-part be-header__title-part--dark">Muito mais do que</span>
<span class="be-header__title-part be-header__title-part--blue"> encontrar um estágio.</span>
```

Cores: `#07132E` (dark) e `#005B9F` (blue). Qualquer outro título = um único texto na cor dark (preserva editorial).

**Rationale**: FR-001 + Assumption de divisão. Evita partir frases editoriais no meio.

**Alternatives considered**: Dois fields de título — rejeitado (storage paralelo / UX pior). Hardcode no Twig ignorando o field — rejeitado (US3).

---

## R5 — Colagem circular estruturada

**Decision**:

- Markup: `.be-collage` com anel pontilhado (CSS/`border` dashed ou SVG leve), `.be-collage__center` (`field_image` ou asset `collage-center.png`) e `.be-collage__sat` × 5 posicionados em órbita (`position` + ângulos fixos).
- Assets versionados: `collage-center.png`, `collage-sat-1.png`…`collage-sat-5.png` em `modules/custom/custom_configs/assets/beneficios-estudantes/`; hook copia → `public://beneficios-estudantes/`.
- Satélites **não** são fields editáveis; paths públicos fixos no Twig/preprocess após seed.
- Fallback: se faces de órbita ausentes, renderizar `collage.png` composta (arte 054) como imagem única — coluna de texto intacta.
- Acento thumbs-up (se no Figma) pode ser elemento decorativo CSS/SVG estático, não field.

**Rationale**: FR-014 exige comunicação visual de órbita; Assumption permite múltiplos arquivos desde que o resultado bata com o Figma; CMS foto-a-foto fora.

**Alternatives considered**:
- Só `collage.png` opaca — rejeitado como solução primária (spec pede estrutura).
- 7 imagens no CMS — rejeitado (escopo US3).

---

## R6 — Responsivo (colagem abaixo, não sempre oculta)

**Decision**:

| Viewport | Conteúdo | Itens | Colagem |
|----------|----------|-------|---------|
| ≥992px | coluna esquerda (~6/6 ou 7/5) | 2 colunas | órbita à direita |
| 576–991.98px | 100% | 1 coluna (&lt;768) / até 2 (≥768 se estável) | abaixo do conteúdo, redimensionada |
| &lt;576px | 100% | 1 coluna | **omitida** |

Sem scroll horizontal (`overflow-x: hidden` no wrapper). CTA full-width no mobile estreito.

**Rationale**: FR-005 / Assumptions. Diferente da 054 (ocultar &lt;992): brand art ainda aparece em tablet.

**Alternatives considered**: Sempre ocultar &lt;992 — rejeitado nesta feature. Sempre mostrar órbita em 375px — rejeitado (poluição / overflow).

---

## R7 — Seed canônico e migração pós-`11079`

**Decision**:

1. Restaurar `_custom_configs_beneficios_estudantes_canonical_items()` para os **6** benefícios Figma (ícones `icon-1`…`icon-6`).
2. Badge seed: `Benefícios para Estudantes` (CSS `text-transform: uppercase` se o Figma pedir caixa alta). Aceitar badge legado `BENEFÍCIOS PARA ESTUDANTES` como já canônico (não reescrever).
3. Título/subtítulo/CTA/colagem: fill-if-empty; migrar títulos/subtítulos legados das 4 cards **e** garantir que título Figma já aplicado pela 054 seja preservado.
4. Lista: vazia → 6 canônicos; exatamente 4 itens com títulos ∈ legado 022/053 → substituir pelos 6; qualquer outra lista → preservar (+ fill ícones vazios quando aplicável).
5. Bloco ausente → recriar UUID com seed completo + colagem centro.

**Rationale**: FR-002 / FR-008 / FR-009 / SC-005 / SC-006 / SC-008. O canônico de 4 itens atual é a regressão a desfazer.

**Alternatives considered**: Overwrite sempre — rejeitado (editorial). Novo UUID — rejeitado (quebra placement exportado).

---

## R8 — Hook `11081` e deploy

**Decision**: `custom_configs_update_11081()` chama, em ordem:

1. `_custom_configs_ensure_beneficios_estudantes_structure()` (defensivo)
2. `_custom_configs_seed_beneficios_estudantes()` (com canônico 6 + recreate)
3. Ensure/cópia dos assets da órbita se introduzidos
4. `_custom_configs_ensure_beneficios_estudantes_placement()` (reativa se desabilitado/ausente)

Receita destino: `cim` → `updb` → `cim` → `cr`. Origem: `updb` → `cex` (se display) → `cr`.

**Rationale**: Governança permanente; FR-009 / FR-010 / SC-007.

**Alternatives considered**: Só mexer Twig/CSS sem hook — rejeitado (4 cards no banco permanecem; bloco deletado não volta).

---

## R9 — Fundo, tipografia, CTA

**Decision**:

- Fundo da seção: `#eef5ff` sob `.block-beneficios-estudantes`.
- Tipografia: Poppins; badge laranja marca; subtítulo cinza legível.
- CTA: botão laranja `var(--brand-orange)`, texto branco; indicação visual de continuidade (seta CSS/`::after` ou caractere) sem JS; omit empty.

**Rationale**: FR-001 / FR-003 / FR-006.

**Alternatives considered**: Hardcode CTA sem `field_link` — rejeitado (US3).

---

## R10 — Skills / toolkit CSS

**Decision**: `mattpocock/skills` **não** está instalado neste ambiente. Implementação segue o padrão do tema (`default.libraries.yml` + CSS escopado + Twig Barrio), alinhado a blocos vizinhos (`nossa_metodologia`, `cta_v1`).

**Rationale**: Assumption da spec; não bloqueia a feature.

**Alternatives considered**: Bloquear até instalar skills — rejeitado (atraso sem ganho).
