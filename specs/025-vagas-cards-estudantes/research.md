# Research: Cards Laranja — Vagas em Para Estudantes

**Data**: 2026-09-30 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **paridade visual com Home (`003`)**, **deploy repetível** (`cim` → `updb` → `cim` → `cr`), **zero regressão em `block_1`**, e **preservação de filtros/paginação** de `/para-estudantes`.

---

## R1 — Reuso do card Home sem View Mode novo

**Decision**: Manter display `page_1` em modo **Fields** + campo Custom Text (`nothing`). Reescrever apenas o Twig `views-view-field--vagas--page-1--nothing.html.twig` com o mesmo markup/classes `.item-vaga--destaque` da Home. Não criar view mode `card_home` / `teaser` / display Content.

**Rationale**: Spec Assumptions + descoberta do checklist: Home e `page_1` já usam Fields + Custom Text. View Mode novo aumentaria escopo (form/view displays de node, migrations de display) sem ganho de produto (FR-001/002).

**Alternatives considered**:
- View Mode + Content row — rejeitado (escopo, YAGNI).
- Paragraph/block intermediário — rejeitado (listagem já é View).

---

## R2 — Duplicar Twig vs. partial compartilhado

**Decision**: **Duplicar** o markup adaptado em `page-1--nothing.html.twig` (omitindo “Buscar mais vagas”). **Não** refatorar `block-1--nothing.html.twig` nesta feature.

**Rationale**: Spec coloca redesign de `block_1` fora de escopo; qualquer include compartilhado exige tocar o Twig da Home. Duplicação ~90 linhas é aceitável para isolamento e diff mínimo (ponytail / YAGNI). Drift futuro pode ser consolidado em feature dedicada se necessário.

**Alternatives considered**:
- `{% include %}` + flag `show_buscar_mais` — rejeitado (mexe na Home).
- Copiar e manter os dois botões — rejeitado (FR-007 / link circular).

---

## R3 — Escopo CSS: expandir seletores, não library nova

**Decision**: Em `themes/custom/default/assets/css/style.css`, expandir regras existentes `.css-vagas-home .item-vaga--destaque…` para o grupo:

```css
.css-vagas-home .item-vaga--destaque,
.css-vagas-page .item-vaga--destaque { … }
```

(e equivalentes para descendentes / media queries). Ajustes finos de header da listagem sob `.css-vagas-page .view-header` / título. **Sem** library nova.

**Rationale**: FR-002; CSS do card já vive em `style.css` (003). Library isolada só faria sentido com arquivo novo — desnecessário. Token de fundo permanece `#FD761A` (Home atual); `#FD7B1A` do Figma tratado como mesma família.

**Alternatives considered**:
- Arquivo `vagas-cards-estudantes.css` + library — YAGNI.
- Trocar wrapper `page_1` para `css-vagas-home` — rejeitado (colisões com padding/header Home e filtros da página).

---

## R4 — Cabeçalho: “Vagas de Destaque” sem link circular

**Decision**: Atualizar o `header` area do display `page_1` para título **“Vagas de Destaque”** (ex. `h3.text-title` alinhado à esquerda). **Não** incluir “Ver todas as vagas”. Manter header/footer de `block_1` intactos (link → `/para-estudantes`).

**Rationale**: FR-004 / US3 / SC-005. Header atual de `page_1` é “Vagas Disponíveis” — fora do Figma. Link “Ver todas” na própria listagem seria circular.

**Alternatives considered**:
- Reusar HTML idêntico ao Home (com link) — rejeitado (US3).
- Remover header e depender só do title da View (“Para Estudantes”) — rejeitado (Figma pede o título da seção de cards).

---

## R5 — Grid responsivo 3 / 2 / 1 só em `page_1`

**Decision**: Override do `style` do display `page_1` com `row_class: 'col-12 col-md-6 col-lg-4'`. O Twig `views-view--vagas--page-1.html.twig` já envolve rows em `.view-content.row.g-4`. Home continua com default `col-md-4 col-12`.

**Rationale**: FR-003 / SC-002. Hoje `page_1` herda `row_class` do display default (`col-md-4 col-12` → 3 colunas a partir de `md`), o que **não** entrega 2 colunas no tablet. Spec exige `md` = 2, `lg+` = 3.

**Alternatives considered**:
- Alinhar Home ao mesmo grid — fora de escopo (003).
- CSS Grid custom sem classes Bootstrap — YAGNI / foge do padrão do projeto.

---

## R6 — Empresa, ícone e CTAs (paridade de dados com Home)

**Decision**:
- Empresa: `field_empresa_u.entity.field_nome_fantasia` (não `field_text_simple_2`).
- Ícone: 1º termo de `field_cursos_t` → `field_icone_fa`, fallback `briefcase`.
- Local: `field_cidade` + `field_estados` no formato `Cidade, UF` (como Home; bairro fica fora do subtítulo do card destaque).
- CTAs: “Ver Mais” e “Inscreva-se” → `path('entity.node.canonical', {node: id})`.
- Truncamentos: mesmos limites da Home (título 42, meta 48, salário 28, regime 14).

**Rationale**: FR-005/006/008; SC-001/003. Template atual de `page_1` usa campos/layout legado incompatíveis com o card laranja.

**Alternatives considered**: manter `field_text_simple_2` — rejeitado (dados incorretos vs. Home). Incluir bairro na meta — rejeitado (quebraria paridade Home).

---

## R7 — Automação deploy (`11040`) + cex

**Decision**: `custom_configs_update_11040` idempotente garante no destino:
1. Header de `page_1` = “Vagas de Destaque” (sem link “Ver todas”);
2. `row_class` = `col-12 col-md-6 col-lg-4` (style override no display);
3. `css_class` permanece `css-vagas-page container` (defensivo).

Twig/CSS via código do tema (git pull). Origem: `drush cex` após ajustes admin/API da View. Destino: `cim` → `updb` → `cim` → `cr`.

**Rationale**: FR-010 / US5 / regra permanente `drupal-deploy-configs.mdc`. Último hook verificado: `11039` → próximo **`11040`**.

**Alternatives considered**:
- Só `cex` sem hook — rejeitado (destino com DB legado pode manter header/row antigos se config parcial divergir; hook fecha o gap).
- Só hook sem cex — rejeitado (config estrutural deve viver em `config/sync`).

---

## R8 — Filtros, pager e empty state

**Decision**: Não alterar `filters`, `exposed_form`, `pager`, `empty`, `path` nem `use_ajax` de `page_1`. Cards laranja devem renderizar após filtro/paginação sem mudança de query.

**Rationale**: FR-009; fora de escopo explícito da spec.

**Alternatives considered**: redesenhar chrome dos filtros — fora de escopo.

---

## Resolução de NEEDS CLARIFICATION

Nenhum item “NEEDS CLARIFICATION” no Technical Context do plan. Spec checklist sem marcadores pendentes.
