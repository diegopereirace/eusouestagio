# Research: Página Para Empresas

**Data**: 2026-09-25 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **deploy repetível** (`cim` → `updb` → `cim` → `cr`), **reuso de tipos/View existentes**, **Clean URL `/para-empresas`**, e **isolamento CSS** sob wrappers da feature.

---

## R1 — Display `block_para_empresas` + valor `para_empresas`

**Decision**: acrescentar allowed value `para_empresas` em `field_local_exibicao`; criar display Block `block_para_empresas` filtrado por esse valor; placement na região `banner` só em `/para-empresas`; remover `/para-empresas` de `default_views_block__banners_block_1` (fica só `/para-estudantes`).

**Rationale**: FR-005–008; padrão `block_home` / `block_quem_somos` (001/009); evita dois banners na mesma rota.

**Alternatives considered**:
- Reusar `internas` + só path visibility — rejeitado (editor não distingue destino; mistura com `/para-estudantes`).
- Filtrar por NID — rejeitado (frágil entre ambientes).

---

## R2 — Copy/CTAs do hero no Twig vs. fields em `banners`

**Decision**: rótulo “SOLUÇÕES CORPORATIVAS”, título “Encontre os melhores talentos para sua empresa.” e CTAs vivem no Twig do display; os 2 nodes `banners` alimentam **somente imagens** (desktop/mobile) + peso + local. Carrossel na coluna de mídia; copy fixa à esquerda (mesma em todos os slides).

**Rationale**: content type `banners` hoje só tem `field_imagem_desktop` / `field_imagem_mobile` / `field_peso` / `field_local_exibicao` (igual 009); Assumptions US4 — aceite de banner cobre troca de imagem; copy Figma estável.

**Alternatives considered**:
- Novos fields de texto/link em `banners` — rejeitado nesta feature (YAGNI + reuso storage + risco de drift).
- Copy por slide distinta — spec lista um único conjunto de strings.

**URLs canônicas dos CTAs**:

| Rótulo | URI |
|--------|-----|
| Cadastrar Empresa | `/cadastro/empresa` |
| Contrate o Estágio Certo | `/painel/empresa/vagas/nova` |

---

## R3 — Layout Twig do hero (duas colunas + carrossel)

**Decision**:
- `views-view--banners--block-para-empresas.html.twig` — attach library; omit markup se zero slides.
- `views-view-unformatted--…` (ou preprocess montando `banner_slides` como no home) — wrapper `.banner-para-empresas-wrapper` > inner `max-width: 1200px` > row: copy | carousel.
- Gap ~48px; padding horizontal ~24px; stack em &lt;992px (copy acima).
- Tokens locais: laranja `#FD7B1A`, título escuro alinhado ao Figma; **não** alterar `--brand-orange` global.

**Rationale**: FR-010–011; SC-001/002; espelha 009 + carousel da home.

**Alternatives considered**: carrossel full-bleed estilo home — rejeitado (Figma pede duas colunas contidas); Layout Builder — fora do escopo.

---

## R4 — Benefícios: reuso `diferenciais_quem_somos`

**Decision**: **não** criar `beneficios_empresas`. Nova instância do tipo `diferenciais_quem_somos` (paragraphs `diferencial_simples_p`: ícone + rótulo), título seed “Benefícios para Empresas”, placement dedicado só `/para-empresas`. Ajuste cirúrgico no Twig: `id` da `<section>` derivado do título/label (slug) em vez de hardcode `#diferenciais-quem-somos`.

**Rationale**: FR-016 critério “só muda título/copy e o grid cabe”; tipo já tem título + descrição + lista ícone+texto Bootstrap; zero storage/paragraph type novos.

**Alternatives considered**:
- `nossos_diferenciais` — layout bipartido (imagem + itens com descrição); não é grade de benefícios.
- `o_que_fazemos_bt` — cards com listas, **sem** ícone por item.
- Tipo novo `beneficios_empresas` — só se o Figma exigir markup/CSS incompatível; reavaliar na implementação visual; default = reuso.

---

## R5 — Instâncias dedicadas dos blocos da home

**Decision**: criar **novas** entidades `block_content` (UUIDs fixos) dos tipos `nossos_diferenciais`, `nossa_metodologia`, `o_que_fazemos_bt` + placements novos. **Não** alterar `default_nossosdiferenciais` / `default_nossametodologia` / `default_oquefazemos` (`<front>`).

**Rationale**: FR-013–015; SC-003/009; evita side-effect na home se o editor mudar copy B2B.

**Alternatives considered**: reutilizar a mesma instância da home com multi-path visibility — rejeitado (copy B2B vs home compartilhada).

---

## R6 — CTA final `cta_v1` vs. CTO legado

**Decision**: nova instância `cta_v1` (título “Pronto para contratar os melhores talentos?”; links seed `/cadastro/empresa` + `/painel/empresa/vagas/nova`); placement weight 4; **`default_ctoparaempresas.status = false`**.

**Rationale**: FR-018–020; Edge Case convivência; tipo `cta_v1` já tem Twig/CSS (015).

**Alternatives considered**: manter CTO + cta_v1 — rejeitado (dois CTAs); apagar entidade CTO — desnecessário (só desativar placement).

---

## R7 — Limpeza do nó `para_empresas`

**Decision**: resolver alias `/para-empresas` → node bundle `para_empresas`; esvaziar fields de conteúdo (`field_titulo`, `field_text_simple`, `field_text_simple_long`, `field_text_simple_long_2`, `field_imagem`, `field_itens_p`) se não vazios; preservar nó + alias; Twig `node--para-empresas.html.twig` passa a omitir blocos vazios (sem “casca” com grade vazia). Não há field `body` clássico neste bundle — o “HTML legado” vive nesses fields / render atual.

**Rationale**: FR-003–004; SC-004; conteúdo visual migra para View + blocos em `content_full`.

**Alternatives considered**: apagar o nó — rejeitado (alias/UC institucional); só esconder no Twig sem limpar DB — deixa lixo editorial e confunde editores.

---

## R8 — Ordem e weights em `content_full`

**Decision**:

| Seção | Weight | Placement ID (planejado) |
|-------|--------|--------------------------|
| Nossos Diferenciais | 0 | `default_nossosdiferenciaisparaempresas` |
| Nossa Metodologia | 1 | `default_nossametodologiaparaempresas` |
| O Que Fazemos | 2 | `default_oquefazemosparaempresas` |
| Benefícios | 3 | `default_beneficiosparaempresas` |
| CTA v1 | 4 | `default_ctav1paraempresas` |

Banner na região `banner` (fora dessa sequência).

**Rationale**: FR-014; US2.

---

## R9 — Hook `11024` e assets

**Decision**: `custom_configs_update_11024` orquestra helpers idempotentes: limpeza nó; ensure allowed value; seed 2 banners (`local=para_empresas`) + copy assets → `public://`; seed 5 blocos; ensure/disable placements; nunca duplicar; nunca sobrescrever editorial divergente. Assets versionados em `modules/custom/custom_configs/assets/banner-para-empresas/`. Ícones de Benefícios: reutilizar assets `diferenciais-quem-somos/` ou espelhar subset se o seed precisar de arte B2B distinta.

**Rationale**: FR-023–024; `drupal-deploy-configs.mdc`; último hook = `11023`.

**Alternatives considered**: só `cim` sem hook — rejeitado (conteúdo não vai em config); dump SQL — proibido.

---

## R10 — Governança Cursor (FR-001)

**Decision**: a regra `.cursor/rules/drupal-deploy-configs.mdc` **já exige** `hook_update_N` + `drush cex` sem pedir ao usuário. Na implementação, confirmar alinhamento; editar só se houver lacuna factual vs. FR-001/FR-002.

**Rationale**: YAGNI em reescrita; spec pede fixação permanente — já cumprida.

**Alternatives considered**: reescrever o arquivo inteiro — ruído desnecessário.
