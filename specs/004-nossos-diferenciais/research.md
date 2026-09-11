# Research: Bloco Nossos Diferenciais

**Data**: 2026-09-11 | **Feature**: [spec.md](spec.md)

Todas as decisões abaixo priorizam **deploy fácil e repetível** entre ambientes (local → staging → prod) sem dump de banco.

---

## R1 — Veículo das mudanças estruturais

**Decision**: Toda estrutura (block type, paragraph type, field instances, form/view displays, permissões de role) vive em `config/sync` e sobe com `drush cim -y` após `git pull`. Desenvolvimento preferencial: criar no UI local → `drush cex -y` → commit dos YAMLs; ou escrever YAMLs espelhando bundles existentes (`cto`, `icone_titulo_descricao`).

**Rationale**: `config/sync` já está operacional; `settings.php.prod` aponta para `config/sync`. É o mesmo padrão das features 001/003. Evita drift de schema entre ambientes.

**Alternatives considered**:
- Criar fields manualmente em cada ambiente — rejeitado (erro humano, deploy lento).
- `config/install` em módulo novo — rejeitado (dupla fonte de verdade vs sync).
- Dump SQL de schema — rejeitado (viola PRD / FR-006).

---

## R2 — Conteúdo editorial vs config (facilitar deploy sem mentir sobre o `cim`)

**Decision**:
1. **Estrutura** → sempre `cim`.
2. **Seed inicial** (título/subtítulo/3 itens de referência da spec) → `custom_configs_update_N()` **idempotente** (cria só se não existir bloco com UUID fixo ou info hash conhecida). Textos vão no update; imagens ficam vazias na v1 (Twig placeholder) para não depender de `files/` entre ambientes.
3. **Placement** do bloco na região da home: (a) incluir `block.block.*.yml` no sync **somente** se o seed criar a entidade com o **mesmo UUID** referenciado no placement; ou (b) placement manual único pós-deploy no ambiente — documentado no quickstart. Preferir (a) na implementação para zero clique.

**Rationale**: `cim` não cria `block_content` entities. Placements no sync já usam UUID (ex.: CTO). Seed com UUID fixo + placement YAML = ambiente destino “já nasce” com a seção, sem dump.

**Alternatives considered**:
- Módulo `default_content` / `content_sync` — dependência nova; YAGNI.
- Exigir editor recriar o bloco em cada ambiente — funciona, mas piora o deploy (rejeitado pelo pedido “facilite o deploy”).
- Versionar binários de imagem no Git e copiar no update — possível follow-up; v1 usa placeholder.

---

## R3 — Ordem de comandos no destino

**Decision**: `git pull` → `drush cim -y` → `drush updb -y` → `drush cr`.

**Rationale**: tipos/fields precisam existir antes do seed Entity API. Diferente de migrações destrutivas da 001 (lá `updb`/`cim` tinham restrições de 2 releases); aqui só há **criação**, então `cim` primeiro é seguro.

**Alternatives considered**: `updb` antes de `cim` — falharia o seed (fields inexistentes).

---

## R4 — Reuso de field storage (`field_text_simple_small`)

**Decision**: Usar `field_text_simple` (storage existente) para títulos curtos do bloco e do paragraph. Não criar `field_text_simple_small`.

**Rationale**: Spec FR-004; storages já em `config/sync` para `block_content` e `paragraph`.

**Alternatives considered**: Novo storage `field_text_simple_small` — viola reutilização e gera YAMLs extras sem ganho.

---

## R5 — Storage novo só para a lista

**Decision**: Criar `field.storage.block_content.field_diferenciais_lista` (entity_reference_revisions → paragraph, cardinality ilimitada ou ≥3). Espelhar padrões de `field_icon_title_text_p`, porém **sem** travar cardinality em 3 (spec permite N itens; cores ciclam).

**Rationale**: não há storage reutilizável com handler restrito a `diferencial_item_p`.

---

## R6 — Templates Twig e naming

**Decision**:
- Bloco: `themes/custom/default/templates/block/block--nossos-diferenciais.html.twig` + garantir suggestion estável (ID de bloco `default_nossosdiferenciais` **ou** `hook_theme_suggestions_block_alter` / template `block--block-content--nossos-diferenciais.html.twig` conforme suggestions reais do Barrio).
- Paragraph: `paragraph--diferencial-item-p.html.twig`.
- Markup: `.container` > header (H2 + P) > `.row` > `.col-lg-6` imagem | `.col-lg-6` lista; items `d-flex align-items-start gap-3`.

**Rationale**: pedido do usuário + padrão Bootstrap 5 do tema.

---

## R7 — Cores dos títulos e geometria

**Decision**: Cores via CSS `:nth-child` no escopo do bloco (1 verde, 2 azul, 3 laranja, ciclo). Geometria (círculo azul, anel verde, anéis cinza) via `::before`/`::after` + elementos decorativos no wrapper da imagem — sem campos CMS.

**Rationale**: Spec FR-009/FR-012; zero config extra no deploy.

---

## R8 — Poppins weight 300

**Decision**: Atualizar o `@import` Google Fonts em `style.css` para incluir `wght@300;400;500;600;700`. Escopo tipográfico da seção com `font-family: 'Poppins', …` no wrapper do bloco (já global no tema; reforço local ok).

**Rationale**: subtítulo do Figma usa 300; import atual começa em 400.

---

## R9 — Permissões

**Decision**: Após criar o bundle, ajustar roles que já editam block content (`moderador` etc.) para incluir create/edit/delete do tipo `nossos_diferenciais`, exportar via `cex` e subir no mesmo PR.

**Rationale**: senão o `cim` sobe a estrutura mas o editor não consegue usar o bloco no destino.
