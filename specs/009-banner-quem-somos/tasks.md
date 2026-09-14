# Tasks: Banner da página Quem Somos

**Input**: Artefatos de design em `specs/009-banner-quem-somos/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/banner-render.md`, `quickstart.md`  
**Branch**: `dev`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` ausente neste repo; `FEATURE_DIR` = `specs/009-banner-quem-somos`; template alinhado a `specs/008-rodape-redesign/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar View/blocos/Twigs de banners existentes antes de alterar config/tema/seed.

- [X] T001 Confirmar artefatos SDD e feature ativa (`specs/009-banner-quem-somos/`, `.specify/feature.json`)
- [X] T002 [P] Inventariar allowed values atuais de `field_local_exibicao` em `config/sync/field.storage.node.field_local_exibicao.yml`
- [X] T003 [P] Inventariar displays da View `banners` e pages do bloco legado em `config/sync/views.view.banners.yml` e `config/sync/block.block.default_views_block__banners_block_1.yml`
- [X] T004 [P] Inventariar padrão Twig/library do display home em `themes/custom/default/templates/views/views-view--banners--block-home.html.twig`, `themes/custom/default/templates/views/views-view-unformatted--banners--block-home.html.twig` e `themes/custom/default/default.libraries.yml`
- [X] T005 Confirmar último hook `custom_configs_update_11011` e padrão de assets em `modules/custom/custom_configs/custom_configs.install` e `modules/custom/custom_configs/assets/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar allowed value, display View e placements — pré-requisito de todas as user stories. Zero field storage novo.

- [X] T006 Acrescentar allowed value `quem_somos` / rótulo “Quem Somos” em `config/sync/field.storage.node.field_local_exibicao.yml`
- [X] T007 Criar display Block `block_quem_somos` (filtros `status=1`, `type=banners`, `local=quem_somos`; sorts herdados; limite 1; sem empty area) em `config/sync/views.view.banners.yml`
- [X] T008 [P] Criar placement do bloco na região `banner`, tema `default`, pages só `/quem-somos`, weight `0` em `config/sync/block.block.default_views_block__banners_block_quem_somos.yml`
- [X] T009 [P] Remover `/quem-somos` da lista de pages do bloco legado em `config/sync/block.block.default_views_block__banners_block_1.yml` (manter `/para-estudantes`, `/para-empresas`, `/contato`)

**Checkpoint**: após esta fase, US1–US6 podem avançar; Twig/CSS/seed não dependem de schema novo além do allowed value.

---

## Phase 3: User Story 1 — Visitante vê o banner Figma em Quem Somos (Priority: P1)

**Goal**: viewport ≥768px — caixa branca bipartida (copy + CTAs à esquerda, foto à direita com overflow), tag/título/parágrafo Figma, wrapper `.banner-quem-somos-wrapper`.  
**Independent Test Criteria**: abrir `/quem-somos` ≥768px e comparar estrutura, textos, cores, CTAs e overflow com prints Figma (`contracts/banner-render.md`).

- [X] T010 [US1] Registrar library `banner_quem_somos` apontando para o CSS dedicado em `themes/custom/default/default.libraries.yml`
- [X] T011 [P] [US1] Criar CSS base encapsulado (tokens locais `--bqs-orange`/`--bqs-title`; sem mutar `--brand-orange` global) em `themes/custom/default/assets/css/banner-quem-somos.css`
- [X] T012 [US1] Criar template do display que anexa a library e omite markup quando não há rows em `themes/custom/default/templates/views/views-view--banners--block-quem-somos.html.twig`
- [X] T013 [US1] Implementar layout bipartido + copy fixa (tag, título bipartido, parágrafo) + `<picture>` da imagem do primeiro row sob `.banner-quem-somos-wrapper` em `themes/custom/default/templates/views/views-view-unformatted--banners--block-quem-somos.html.twig`
- [X] T014 [P] [US1] Estilizar desktop (caixa branca, tipografia Poppins, cores `#0F172A`/`#FD7B1A`, overflow controlado da coluna de mídia, preservação de alpha PNG) sob `.banner-quem-somos-wrapper` em `themes/custom/default/assets/css/banner-quem-somos.css`
- [X] T015 [US1] Validar SC-001 / cenários US1 em viewport ≥768px conforme `specs/009-banner-quem-somos/quickstart.md`

---

## Phase 4: User Story 2 — Visitante mobile vê empilhamento legível (Priority: P1)

**Goal**: viewport &lt;768px — texto/CTAs acima da imagem; sem scroll horizontal; overflow reduzido; CTAs tocáveis.  
**Independent Test Criteria**: `/quem-somos` ≤767.98px — ordem, legibilidade e ausência de overflow-x causado pelo banner.

- [X] T016 [US2] Ajustar CSS mobile (empilhamento `col-12`, desligar/reduzir overflow da mídia, `flex-wrap` nos CTAs) sob `.banner-quem-somos-wrapper` em `themes/custom/default/assets/css/banner-quem-somos.css`
- [X] T017 [US2] Confirmar ordem DOM copy→mídia e classes `col-12 col-md-*` no markup em `themes/custom/default/templates/views/views-view-unformatted--banners--block-quem-somos.html.twig`
- [X] T018 [US2] Validar SC-002 / cenários US2 em viewport ≤767.98px conforme `specs/009-banner-quem-somos/quickstart.md`

---

## Phase 5: User Story 3 — CTAs levam às rotas canônicas (Priority: P1)

**Goal**: botões “Conheça nossas vagas” → `/para-estudantes` e “Cadastre-se” → `/cadastro/candidato`, com estilos primário/secundário do contrato.  
**Independent Test Criteria**: clicar cada CTA e confirmar destino; inspecionar `href`s.

- [X] T019 [US3] Implementar CTAs como `<a>` com `href`s canônicos, classes `banner-quem-somos__btn--primary` / `--secondary` e grupo `d-flex gap-3 flex-wrap` em `themes/custom/default/templates/views/views-view-unformatted--banners--block-quem-somos.html.twig`
- [X] T020 [P] [US3] Estilizar botões (primário fundo `#FD7B1A` texto branco; secundário fundo branco, texto escuro, borda) sob `.banner-quem-somos-wrapper` em `themes/custom/default/assets/css/banner-quem-somos.css`
- [X] T021 [US3] Validar SC-003 / contrato de links vs `specs/009-banner-quem-somos/contracts/banner-render.md`

---

## Phase 6: User Story 4 — Banner antigo some; novo aparece só em Quem Somos (Priority: P1)

**Goal**: `/quem-somos` só com o novo banner; `block_1` nas demais internas; `block_home` intacto.  
**Independent Test Criteria**: comparar `/quem-somos`, home, `/para-estudantes` e `/contato` após `cim`/`cr`.

- [X] T022 [US4] Confirmar YAML do novo bloco (plugin `views_block:banners-block_quem_somos`, região `banner`, pages `/quem-somos`) em `config/sync/block.block.default_views_block__banners_block_quem_somos.yml`
- [X] T023 [US4] Confirmar ausência de `/quem-somos` nas pages do legado em `config/sync/block.block.default_views_block__banners_block_1.yml`
- [X] T024 [US4] Validar SC-004 (sem carrossel genérico em Quem Somos; home/`block_1` sem regressão) conforme `specs/009-banner-quem-somos/quickstart.md`

---

## Phase 7: User Story 5 — Editor controla a imagem via banners (Priority: P2)

**Goal**: node `banners` com local Quem Somos alimenta só a mídia; copy fixa no Twig; zero publicados → sem caixa vazia.  
**Independent Test Criteria**: publicar/despublicar banner Quem Somos e recarregar `/quem-somos`.

- [X] T025 [US5] Garantir fallback mobile→desktop e `alt`/`fetchpriority="high"` na `<picture>` em `themes/custom/default/templates/views/views-view-unformatted--banners--block-quem-somos.html.twig`
- [X] T026 [US5] Confirmar omissão total do wrapper quando a View não tem rows em `themes/custom/default/templates/views/views-view--banners--block-quem-somos.html.twig`
- [X] T027 [US5] Validar SC-007 e cenários US5 (imagem editorial / despublicar / textos imutáveis) conforme `specs/009-banner-quem-somos/quickstart.md`

---

## Phase 8: User Story 6 — Deploy reproduz o banner em outro ambiente (Priority: P1)

**Goal**: `cim` → `updb` → `cr` aplica display, placement, allowed value e seed; hook `11012` idempotente; `cex` versiona estrutural.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; segunda `updb` sem duplicatas.

- [X] T028 [US6] Versionar asset PNG transparente do seed em `modules/custom/custom_configs/assets/banner-quem-somos/quem-somos-img.png`
- [X] T029 [US6] Implementar `custom_configs_update_11012` idempotente (garantir allowed value; seed node UUID `e9f0a1b2-c3d4-4e5f-8690-1234567890ab` + imagem só se ausente; não sobrescrever mídia editorial; defensivo remover `/quem-somos` do legado se ainda listado) em `modules/custom/custom_configs/custom_configs.install`
- [X] T030 [US6] Exportar/confirmar configs estruturais com `drush cex -y` para `config/sync/field.storage.node.field_local_exibicao.yml`, `config/sync/views.view.banners.yml`, `config/sync/block.block.default_views_block__banners_block_quem_somos.yml` e `config/sync/block.block.default_views_block__banners_block_1.yml`
- [X] T031 [US6] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cr` e reexecutar `updb` (SC-005/SC-006) conforme `specs/009-banner-quem-somos/quickstart.md`

---

## Phase 9: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, isolamento CSS confirmado e aceite final sem regressão.

- [X] T032 [P] Atualizar §3.1.1b (`home` \| `internas` \| `quem_somos`) e §3.6 (display `block_quem_somos`, placement, convivência com `block_1`) em `PRD.md`
- [X] T033 Executar validação final completa (SC-001–SC-008, amostragem home/`/para-estudantes`/“Sobre nós”) com `specs/009-banner-quem-somos/quickstart.md` e `specs/009-banner-quem-somos/checklists/requirements.md`
- [X] T034 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → US6 (Phase 8) → Polish (Phase 9)
- US2 depende do markup/CSS base de US1 (mesmo Twig/CSS)
- US3 pode avançar em paralelo parcial com US2 após T013 (mesmo Twig; CSS de botões paralelo ao mobile)
- US4 valida sobretudo o Foundational (T008–T009); pode rodar em paralelo com US1 após Phase 2
- US5 depende de Twig com empty-omit + `<picture>` (T012–T013)
- US6 (seed/hook) independente do polish visual fino, mas precisa do allowed value (T006) e asset; validar pós-Twig mínimo
- Polish após US1–US6

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1)
                    ├→ US2 (P1)
                    ├→ US3 (P1)
                    ├→ US4 (P1) ──┐
                    ├→ US5 (P2) ──┤
                    └→ US6 (P1) ──┴→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002, T003, T004 em paralelo após T001; T005 após T001
- **Foundational**: T008 e T009 em paralelo após T006–T007 (ou junto se um único lote de config)
- **US1**: T011 (CSS base) em paralelo com T012 após T010; T014 após classes estáveis em T013
- **US2 ∥ US3**: T016–T017 com T019–T020 após T013
- **US4 ∥ US1**: T022–T024 após Phase 2, em paralelo ao visual
- **US5**: após T012–T013 (e seed de US6 se ambiente limpo)
- **US6**: T028 em paralelo com Twig; T029 após T006 + T028
- **Polish**: T032 em paralelo com preparo de T033

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (desktop Figma) + US3 (CTAs) + trechos de US4 (placement)
2. **Mobile + editorial**: US2 + US5 (empty omit / fallback imagem)
3. **Deploy**: US6 (`11012` + asset + cim/updb/cr + cex)
4. **Fechamento**: Polish (PRD §3.1.1b/§3.6 + aceite SC-001–SC-008)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T034`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
