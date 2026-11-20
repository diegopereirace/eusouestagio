# Research: 038-vaga-faq-accordion

## R1 — View `faq_vaga` vs. renderização nativa de `field_vaga_faq`

**Decision**: manter e refinar a renderização nativa no Twig do detalhe (`field_vaga_faq` → `field_faq_itens`). Não criar View nem bloco Views nesta entrega.  
**Rationale**: a ordem pedida (após “Sobre a Empresa”, antes do CTA `default_ctav1vagas`) só é confiável **dentro** de `.col-lg-8` do template full. O CTA já é placement em `content_full` **depois** do node; um bloco Views na mesma região sairia da coluna 8/4 e quebraria o Figma. A estrutura de dados e o Accordion BS5 já existem (030/031); o gap é visual + componentização + vínculo seed. Não há `faq_vaga` / `block_faq` em `config/sync`.  
**Alternatives considered**: View com Relationship + Contextual Filter + rewrite (rejeitado: complexidade sem ganho editorial e risco de layout); bloco Views na região lateral (rejeitado: FR-007/FR-009).

## R2 — O que já existe vs. o que falta

**Decision**: tratar CT `faq`, paragraph `faq_item_p`, `field_faq_itens`, `field_vaga_faq` (card. 1) e o Accordion inline no Twig full como **baseline**; a feature só garante (ensure no-op se presente), não recria. Trabalho novo = (1) vínculo idempotente Engenheiro Civil → coleção seed, (2) include Twig dedicado, (3) CSS de cards isolados.  
**Rationale**: inventário 2026-10-27 confirma YAMLs em `config/sync`, helpers `_custom_configs_ensure_faq_*` / `_custom_configs_seed_faq_collection_example()` e seção FAQ nas linhas ~175–213 de `node--vagas--full.html.twig`. `_custom_configs_seed_vaga_engenheiro_civil()` **não** preenche `field_vaga_faq`.  
**Alternatives considered**: recriar tipos/fields no hook (rejeitado: FR-004 e risco de duplicata); alterar cardinalidade de volta a ilimitada (rejeitado: escopo “Fora”).

## R3 — Componentização Twig

**Decision**: extrair a seção FAQ para `themes/custom/default/templates/content/includes/vaga-faq-accordion.html.twig` e chamá-la no full **depois** do include “Sobre a Empresa” e **antes** do fechamento da coluna 8. O pai continua responsável por montar a lista de paragraphs válidos (ou o include recebe `faq_paras` já filtrados).  
**Rationale**: FR-014; padrão já usado por sidebar (033) e cartão empresa (037). Evita ~40 linhas de lógica no full.  
**Alternatives considered**: deixar inline no full (rejeitado: FR-014); preprocess PHP só para filtrar itens (opcional — Twig já filtra; não é necessário se a lista for montada no pai ou no include).

## R4 — Critério de item válido

**Decision**: só renderizar painel se pergunta **e** resposta, após trim, forem não vazias. Seção omitida se a lista filtrada ficar vazia. Coleção unpublished ou sem `field_faq_itens` → omitir.  
**Rationale**: US2/US3 e edge cases; campos CMS já são `required: true`, mas o Twig atual usa `pergunta or resposta` e ainda poderia mostrar painel incompleto se dados legados existirem.  
**Alternatives considered**: manter `or` (rejeitado: painel sem pergunta ou sem corpo); confiar só na validação do form (rejeitado: seed/legado pode divergir).

## R5 — Comportamento de abertura (parent vs. independente)

**Decision**: painéis **independentes** — remover `data-bs-parent` do markup. Cada cartão abre/fecha sem forçar o fechamento dos demais.  
**Rationale**: cartões visualmente isolados (FR-012) leem como unidades; o parent agrupado reforça o “bloco único” do Accordion nativo que a feature quer abandonar. IDs únicos por item (`vaga-faq-{nid}-{pid|delta}`) permanecem.  
**Alternatives considered**: manter `data-bs-parent` (aceitável funcionalmente, mas reforça o agrupamento visual rejeitado pelo Figma).

## R6 — Visual dos cards (CSS sob `.vaga-detalhe__faq`)

**Decision**: reescrever o bloco “FAQ accordion” em `vagas-detalhe.css` para cards flutuantes: cada `.accordion-item` com fundo branco, `border: 1px solid` fina (`var(--vd-border)` ou equivalente), `border-radius: 10px` (faixa 8–12), `margin-bottom: 1rem` (~16px), sem bordas contínuas entre itens; botão sempre fundo branco (incluindo `:not(.collapsed)`), texto escuro/navy `font-weight: 600`, sem fundo azul/cinza ativo do BS5; seta escura via override das variáveis/ícones do Accordion (`--bs-accordion-btn-icon` / active, ou filter no `::after`). Título da seção reusa `.vaga-detalhe__section-title` (já `var(--vd-navy)` / `#023C62`).  
**Rationale**: SC-004 / FR-011–013; o CSS atual só troca `border-color` e deixa o item agrupado + fundo `#f7fafc` no ativo.  
**Alternatives considered**: Accordion flush nativo (rejeitado: continua agrupado); JS custom de accordion (rejeitado: BS5 já carrega no tema).

## R7 — Hook e vínculo seed

**Decision**: `custom_configs_update_11059` chama, em ordem: ensures estruturais FAQ já existentes (no-op se presentes) → `_custom_configs_seed_faq_collection_example()` → helper novo `_custom_configs_link_vaga_engenheiro_civil_faq()` que, se a vaga UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234` existir, tiver `field_vaga_faq` e estiver **vazio**, e a coleção UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0` existir, grava a referência. Não sobrescreve vínculo preenchido.  
**Rationale**: último update = `11058`; Engenheiro Civil hoje não recebe FAQ no seed; FR-015/016/018. Reusar helpers 031 evita duplicar criação de tipos.  
**Alternatives considered**: embutir o vínculo em `_custom_configs_seed_vaga_engenheiro_civil()` e pedir reinstall (rejeitado: update antigo não reexecuta); criar segunda coleção FAQ (rejeitado: Assumption manda reutilizar o UUID canônico).

## R8 — Deploy / cex / PRD

**Decision**: receita destino `cim` → `updb` → `cim` → `cr`. Nesta feature o típico é **só tema + hook de vínculo** — `drush cex` na origem tende a no-op estrutural; rodar mesmo assim para confirmar. PRD: atualização cirúrgica no parágrafo do detalhe full (§3.1 área vagas) mencionando Accordion em cards + vínculo seed, sem inventar View.  
**Rationale**: `drupal-deploy-configs.mdc` + Assumption da spec; guardião PRD só quando a descrição pública muda.  
**Alternatives considered**: pular cex (rejeitado: checklist permanente); criar View só para “aparecer no PRD” (rejeitado: R1).

## R9 — Cache / preprocess

**Decision**: sem bolha de cache nova e sem preprocess obrigatório. O include lê o mesmo `node` / paragraphs já carregados no render do detalhe.  
**Rationale**: sem View, bloco ou lazy builder; padrão do 031.  
**Alternatives considered**: preprocess para filtrar itens válidos (desnecessário se o Twig filtra).
