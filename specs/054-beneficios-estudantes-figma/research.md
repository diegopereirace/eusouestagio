# Research: Benefícios Estudantes — Layout Figma

**Data**: 2026-11-03 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **conformidade com o Figma**, **reuso do block type / paragraph existentes**, **deploy repetível** (`cim` → `updb` → `cim` → `cr`) e **preservação de copy editorial divergente**.

---

## R1 — Redesenho in-place (sem segundo bloco)

**Decision**: Manter o bundle `beneficios_estudantes`, o paragraph `card_icon_text_p`, o UUID `a8b9c0d1-e2f3-4456-a789-0bcdef123456` e o placement `default_beneficiosestudantes` (`content_full`, weight `0`, `/para-estudantes`). Alterar apresentação (Twig/CSS), fields adicionais no mesmo bundle e seed/migração via hook.

**Rationale**: FR-006 / FR-007. A spec proíbe segunda seção de benefícios na mesma rota e exige que a grade antiga de 4 cards deixe de ser a UI pública.

**Alternatives considered**:
- Novo block type “beneficios_estudantes_v2” — rejeitado (duplicação + placement paralelo).
- Só CSS sem mudar markup/fields — rejeitado (badge, CTA, colagem e 6 itens não cabem no modelo atual).

---

## R2 — Mapeamento de fields (storages canônicos)

**Decision**:

| Papel UI | Storage / instance | Notas |
|----------|--------------------|--------|
| Badge | `field_text_simple_2` | Storage já existe; rótulo form “Badge” |
| Título | `field_text_simple` | já no bundle |
| Subtítulo | `field_text_simple_long` | já no bundle |
| Itens | `field_itens_lista` → `card_icon_text_p` | já no bundle; seed passa a 6 |
| CTA | `field_link` | instance nova; title = rótulo, uri = destino |
| Colagem | `field_image` | instance nova; 1 arquivo composto |

Zero field **storage** novo. Displays form/view do bloco atualizados para incluir badge (acima do título), link e imagem; paragraph displays inalterados em schema (só Twig/CSS).

**Rationale**: Assumptions da spec + gate de reuso. `field_text_simple` já é o título; o segundo texto curto canônico do projeto é `field_text_simple_2` (footer/destaque). CTA e ilustração seguem o padrão de `perfil_destaque_estudante` / `cta_v1` / `missao_visao`.

**Alternatives considered**:
- Hardcode do badge no Twig — rejeitado (US3 pede editável quando houver campo).
- Storage novo `field_badge` — rejeitado (paralelo proibido).
- CTA hardcoded `/cadastro/candidato` — rejeitado (US3 / FR-005 seed editável via `field_link`).

---

## R3 — Apresentação dos itens (lista 2 colunas, sem card-box)

**Decision**:

- Twig do paragraph: layout **horizontal** (ícone à esquerda, título+descrição à direita); classes de coluna `col-12 col-md-6` (**sem** `col-lg-3`).
- CSS: remover/neutralizar chrome de card (borda, fundo `#F8F9FF`, `min-height: 310px`, padding de card). Manter tipografia Poppins; título do item peso 600–700; descrição cinza (`#45464D` ou token alinhado ao Figma).
- Ícone: caixa ~40–48px (proporção lista Figma; não forçar 64px de card). `loading="lazy"`.
- Grade no bloco: `.be-grid` em 2 colunas no desktop dentro da coluna de conteúdo (não 4 cards centrados).

**Rationale**: FR-001 / FR-003 / FR-007 / SC-001. O Figma trata itens como lista, não cards centrados de `022`/`053`.

**Alternatives considered**: Manter `col-lg-3` e só “achatar” o card — rejeitado (ainda lê como grade de cards). Paragraph type novo — rejeitado (mesmo payload ícone+título+texto).

---

## R4 — Layout split + responsivo da colagem

**Decision**:

- Desktop **≥992px**: `.be-split` em duas colunas — esquerda: badge, título, subtítulo, grid de itens, CTA; direita: `.be-collage` com `field_image` (omit empty).
- **&lt;992px**: coluna de colagem **ocultada** (`display: none` ou não renderizar visualmente); conteúdo textual empilha em largura plena; itens 1 coluna &lt;768 e 2 colunas ≥768.
- Sem scroll horizontal causado pela seção (`overflow-x: hidden` permanece no wrapper).
- Asset faltando: omitir `.be-collage` sem quebrar a coluna de texto; hook recopia de `assets/beneficios-estudantes/`.

**Rationale**: FR-003 / FR-004 / SC-003. Spec permite empilhar **ou** ocultar; ocultar em viewport estreita preserva leitura e conversão sem arte decorativa competindo por espaço.

**Alternatives considered**:
- Sempre empilhar colagem abaixo — rejeitado nesta versão (altura mobile sem ganho de conversão).
- Montagem CSS com 7 files + órbita — rejeitado (Assumption: arte versionada; CMS foto-a-foto fora de escopo).

---

## R5 — Migração de copy e itens (legado conhecido → canônico)

**Decision**: O hook `11078` opera no UUID `a8b9…` com regras:

**Badge / título / subtítulo / CTA / colagem** — preencher se vazio; **substituir** somente se o valor atual for string legado conhecida:

| Campo | Legados conhecidos (substituíveis) | Alvo canônico |
|-------|-------------------------------------|---------------|
| Badge | (vazio) | `BENEFÍCIOS PARA ESTUDANTES` |
| Título | `Encontre oportunidades que combinam com você.` | `Muito mais do que encontrar um estágio.` |
| Subtítulo | frase seed `11035`; frase Figma `053` | `Na Eu Sou Estágio você se desenvolve, recebe apoio e constrói uma carreira com mais segurança e propósito.` |
| CTA | vazio | title `Cadastre-se gratuitamente`, uri `internal:/cadastro/candidato` (ou equivalente limpo) |
| Colagem | vazio / arquivo seed ausente | copiar `collage.png` (nome final na implementação) |

**Itens (`field_itens_lista`)**:

1. Vazio → criar 6 paragraphs canônicos + ícones.
2. Conjunto legado das **4** cards (títulos exatos do seed `022`/`053`, incluindo `Filtros Inteligentes` / `Filtros inteligentes`) **e** contagem 4 → **substituir** a lista pelos 6 canônicos (novos paragraphs; órfãos antigos podem permanecer sem referência — aceitável; não apagar em massa entidades não relacionadas).
3. Qualquer outra composição (contagem ≠ 4, ou títulos diferentes do set legado) → **não** substituir títulos/textos; apenas preencher ícones vazios quando o delta casar com slot de seed (best-effort) e não truncar itens extras.

Seis itens canônicos (ordem):

1. Encontrar propósito — Descubra oportunidades que fazem sentido para você.
2. Desenvolver competências — Acesse conteúdos e recursos para a sua evolução.
3. Receber acompanhamento — Conte com orientação durante toda a sua jornada.
4. Aprender mais — Tenha acesso a dicas, cursos e materiais exclusivos.
5. Construir carreira — Dê os primeiros passos para um futuro promissor.
6. Ter uma experiência de estágio de verdade — Conecte-se com empresas que valorizam seu potencial.

**Rationale**: FR-002 / FR-008 / SC-005 / SC-006. Sem essa migração controlada, ambientes com seed `022` ficariam eternamente nos 4 cards.

**Alternatives considered**:
- Nunca substituir lista se `!isEmpty()` — rejeitado (FR-007 falharia em quase todos os destinos).
- Sempre overwrite — rejeitado (gate editorial).

---

## R6 — Assets versionados

**Decision**:

- Ícones: `modules/custom/custom_configs/assets/beneficios-estudantes/icon-1.png` … `icon-6.png` (substituir/estender os 4 atuais; implementação pode redesenhar arte alinhada ao Figma).
- Colagem: `modules/custom/custom_configs/assets/beneficios-estudantes/collage.png` (composição circular pronta).
- Destino público: `public://beneficios-estudantes/…` via helper `_custom_configs_seed_file_from_module_asset` (padrão do módulo).
- Tema **não** versiona a colagem em `themes/.../images` como fonte de verdade do seed (evitar dualismo); CSS só referencia URI do field.

**Rationale**: Gate permanente de deploy (assets no módulo + hook). Espelha `022` / diferenciais QS.

**Alternatives considered**: Colagem só no tema como CSS `background-image` — rejeitado (piora deploy idempotente e FR de field).

---

## R7 — Hook `11078` e receita

**Decision**: `custom_configs_update_11078` orquestra:

1. Ensure structure: instances `field_text_simple_2`, `field_link`, `field_image` no bundle + form/view displays (defensivo; espelha ensure de `11035`).
2. Apply seed/migração (R5) + ícones/colagem (R6).
3. Ensure placement `default_beneficiosestudantes` ativo na região/visibility corretas (reativar se `status: false` indevido).

Origem: implementar → `drush updb -y` → `drush cex -y` → `drush cr`.  
Destino: `git pull` → `cim` → `updb` → `cim` → `cr`.

**Rationale**: Gate `drupal-deploy-configs.mdc`. Instances novas precisam estar no sync; seed não vive só em YAML.

**Alternatives considered**: Só cex sem hook — rejeitado (conteúdo/migração 4→6 não exporta). Só hook sem cex — rejeitado (`cim` sem instances quebra o bundle no destino limpo).

---

## R8 — CTA visual

**Decision**: Botão sob `.be-cta` / `.be-cta__btn` com fundo laranja da marca (`var(--brand-orange)` / `#ff8a22`), texto branco, raio alinhado aos CTAs de banner estudantes; omitir se `field_link` vazio. Convivência com CTA final `026` é intencional (dois CTAs na página).

**Rationale**: FR-005 / Assumptions. Tokens já no tema; não mutar variável global além do uso local.

**Alternatives considered**: Reusar classes `.cta-v1__btn--primary` — rejeitado (acoplamento visual a outro bloco). Navy como perfil destaque — rejeitado (Figma pede laranja).

---

## R9 — `mattpocock/skills`

**Decision**: Não instalar nem bloquear. Padrões do repo (Twig + CSS library + hook + cex) cobrem a feature.

**Alternatives considered**: Instalar pacote durante o plan — rejeitado (dependência não pedida).
