# Tasks: Redesign do Rodapé (Footer)

**Input**: Artefatos de design em `specs/008-rodape-redesign/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/footer-render.md`, `quickstart.md`  
**Branch**: `dev`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar o rodapé/float atuais antes de alterar Twig/CSS/seed.

- [X] T001 Confirmar artefatos SDD e feature ativa (`specs/008-rodape-redesign/`, `.specify/feature.json`)
- [X] T002 [P] Inventariar template atual e contrato DOM em `themes/custom/default/templates/block/block--default-footer.html.twig` vs `specs/008-rodape-redesign/contracts/footer-render.md`
- [X] T003 [P] Inventariar CSS atual do rodapé e float em `themes/custom/default/assets/css/style.css` (blocos `.site-footer-custom` e `.whatsapp-float-block`)
- [X] T004 [P] Confirmar helpers de seed existentes (`_custom_configs_seed_footer_block`, `_custom_configs_seed_footer_logo`) em `modules/custom/custom_configs/custom_configs.install`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: garantir que schema/placement já cobrem o layout — zero field storage novo — e que o caminho de deploy está claro.

- [X] T005 Confirmar bundle `footer` + fields (`field_image`, `field_email`, `field_phone_wpp`, `field_instagram`, `field_linkedin`, `field_text_simple_long`, `field_text_simple_2`) sem criar storages novos em `config/sync/field.field.block_content.footer.*.yml`
- [X] T006 Confirmar placement `block.block.default_footer` (região `footer_first`, UUID `d0326db5-fc80-4cf5-a0b9-8f779dc4aeba`) em `config/sync/block.block.default_footer.yml`
- [X] T007 Extrair/confirmar hex do fundo azul-marinho (base `#023c62` / anexo 2) para uso sob `.site-footer-custom` em `themes/custom/default/assets/css/style.css`

**Checkpoint**: após esta fase, US1–US5 podem avançar; Twig/CSS do rodapé não dependem de schema novo.

---

## Phase 3: User Story 1 — Visitante vê o novo rodapé no desktop (Priority: P1)

**Goal**: viewport ≥992px — 5 colunas (marca + sociais circulares | Institucional | Estudantes | Empresas | Contato), tipografia Poppins branca, fundo azul-marinho, barra inferior centralizada.  
**Independent Test Criteria**: página pública ≥992px; comparar com anexo 2 (ordem, textos, ícones, barra); logo → `<front>`; tagline/endereço ocultos.

- [X] T008 [US1] Refatorar markup desktop do rodapé (5 colunas Bootstrap, ocultar tagline/endereço, preservar `attributes`/`title_*`/`content_attributes`) em `themes/custom/default/templates/block/block--default-footer.html.twig`
- [X] T009 [US1] Implementar coluna marca: logo → `<front>`, fallback `modules/custom/custom_configs/assets/footer/logo-rodape.png`, linha `.footer-social.d-flex.gap-2` com 4 ícones circulares (ordem E-mail → Instagram → LinkedIn → WhatsApp; omitir se vazio) em `themes/custom/default/templates/block/block--default-footer.html.twig`
- [X] T010 [US1] Implementar barra inferior (`<hr class="footer-divider">` + copyright dinâmico `"now"|date("Y")` + “Desenvolvido por Diego Pereira” sem link; sem links legais) em `themes/custom/default/templates/block/block--default-footer.html.twig`
- [X] T011 [P] [US1] Atualizar CSS desktop encapsulado (fundo, Poppins branca, títulos uppercase, círculos sociais com cores de marca, divisória, barra centralizada) sob `.site-footer-custom` em `themes/custom/default/assets/css/style.css`
- [X] T012 [US1] Validar SC-001 / cenários US1 em viewport ≥992px conforme `specs/008-rodape-redesign/quickstart.md`

---

## Phase 4: User Story 2 — Accordion mobile (Priority: P1)

**Goal**: viewport &lt;992px — marca e contato sempre visíveis; colunas 2–4 como Collapse Bootstrap; desktop sem accordion.  
**Independent Test Criteria**: &lt;992px expandir/recolher Institucional, Estudantes, Empresas; ≥992px grid sem accordion.

- [X] T013 [US2] Adicionar markup accordion/Collapse Bootstrap nas seções Institucional, Para Estudantes e Para Empresas (utilitários `d-lg-*` / espelho desktop) em `themes/custom/default/templates/block/block--default-footer.html.twig`
- [X] T014 [P] [US2] Ajustar CSS mobile do accordion (títulos clicáveis, espaçamento empilhado) sob `.site-footer-custom` em `themes/custom/default/assets/css/style.css` (media / utilitários &lt; `lg`)
- [X] T015 [US2] Garantir `aria-expanded` / heading acessível nos toggles (padrão Bootstrap) em `themes/custom/default/templates/block/block--default-footer.html.twig`
- [X] T016 [US2] Validar SC-002 / cenários US2 em viewport &lt;992px e ≥992px conforme `specs/008-rodape-redesign/quickstart.md`

---

## Phase 5: User Story 3 — Links canônicos e protocolos (Priority: P1)

**Goal**: `href`s das colunas 2–4 = tabela canônica; Contato `mailto:`/`tel:`; sociais externos `target="_blank"` + `rel="noopener noreferrer"`; sem `drupal_menu`.  
**Independent Test Criteria**: inspecionar cada `href` vs `data-model.md`; rotas futuras podem 404.

- [X] T017 [US3] Substituir `drupal_menu(...)` por links hardcoded Institucional (`/sobre-nos`, `/politica-de-privacidade`, `/termos-de-uso`) em `themes/custom/default/templates/block/block--default-footer.html.twig`
- [X] T018 [P] [US3] Hardcodar links Para Estudantes (`/para-estudantes`, `/cadastro/candidato`, `/blog`) em `themes/custom/default/templates/block/block--default-footer.html.twig`
- [X] T019 [P] [US3] Hardcodar links Para Empresas (`/cadastro/empresa`, `/painel/empresa/vagas/nova`) em `themes/custom/default/templates/block/block--default-footer.html.twig`
- [X] T020 [US3] Implementar Contato com `mailto:` e `tel:` (dígitos sanitizados) + omissão se campos vazios em `themes/custom/default/templates/block/block--default-footer.html.twig`
- [X] T021 [US3] Completar contratos sociais (e-mail `mailto:`; Instagram/LinkedIn/WhatsApp `wa.me` com `target="_blank"` `rel="noopener noreferrer"`; `title`/`aria-label`; omitir vazios) em `themes/custom/default/templates/block/block--default-footer.html.twig`
- [X] T022 [US3] Validar SC-003 / tabela de rotas vs `specs/008-rodape-redesign/contracts/footer-render.md` e `specs/008-rodape-redesign/data-model.md`

---

## Phase 6: User Story 4 — Deploy reproduzível (Priority: P1)

**Goal**: `custom_configs_update_11011` idempotente garante bloco+logo; sem re-seed de menus; `cex` só se houver mudança estrutural.  
**Independent Test Criteria**: `cim → updb → cr`; segunda `updb` no-op; conteúdo editorial preservado.

- [X] T023 [US4] Implementar `custom_configs_update_11011` idempotente (bloco UUID + logo via helpers; **não** chamar `_custom_configs_seed_footer_menu_links`) em `modules/custom/custom_configs/custom_configs.install`
- [X] T024 [US4] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cr` e reexecutar `updb` (SC-005) conforme `specs/008-rodape-redesign/quickstart.md`
- [X] T025 [US4] Se displays/placement mudarem, exportar com `drush cex -y` para `config/sync/`; senão documentar no-op estrutural em `specs/008-rodape-redesign/quickstart.md`

---

## Phase 7: User Story 5 — Botão flutuante WhatsApp (Priority: P2)

**Goal**: `.whatsapp-float-block` com `position: fixed` e `z-index: 1050`, acima do rodapé ao rolar.  
**Independent Test Criteria**: rolar páginas públicas; inspecionar `z-index: 1050`; float não coberto pelo rodapé.

- [X] T026 [US5] Atualizar `.whatsapp-float-block` para `z-index: 1050` (manter `position: fixed`) em `themes/custom/default/assets/css/style.css`
- [X] T027 [US5] Validar SC-006 (float fixo, empilhamento vs rodapé) em amostra home/vagas/cadastro conforme `specs/008-rodape-redesign/quickstart.md`

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: governança (Cursor Rule + PRD), limpeza de CSS legado e aceite final sem regressão.

- [X] T028 [P] Confirmar/versionar Cursor Rule de fluxo (FR-018: SDD, CM, reuso de campos, hooks idempotentes; `globs` em `modules/custom/**/*`, `themes/custom/**/*`, `config/sync/**/*`) em `.cursor/rules/estagio-fluxo-dev.mdc`
- [X] T029 [P] Atualizar seção do bloco `footer` no `PRD.md` (5 colunas, links hardcoded, menus legados sem uso, hook `11011`)
- [X] T030 Remover/ajustar CSS legado morto do rodapé (tagline visível, menus, links legais na barra, crédito com `<a>`) sob `.site-footer-custom` em `themes/custom/default/assets/css/style.css`
- [X] T031 Executar validação final completa (SC-001–SC-008, amostragem sem regressão) com `specs/008-rodape-redesign/quickstart.md` e `specs/008-rodape-redesign/checklists/requirements.md`
- [X] T032 Limpar cache Drupal após alterações Twig/CSS (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → Polish (Phase 8)
- US2 depende do markup base de US1 (mesmo Twig)
- US3 pode avançar em paralelo parcial com US2 após T008–T010 (mesmas seções de coluna)
- US4 independente de accordion/float, mas deve rodar após Twig estável o bastante para validar layout pós-`updb`
- US5 só toca CSS do float — paralelo a US4 após Foundational
- Polish após US1–US5

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1)
                    ├→ US2 (P1)
                    ├→ US3 (P1)
                    ├→ US4 (P1) ──┐
                    └→ US5 (P2) ──┴→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002, T003, T004 em paralelo após T001
- **Foundational**: T005 e T006 em paralelo; T007 após inventário CSS (T003)
- **US1**: T011 (CSS) em paralelo após T008 estruturar classes
- **US2**: T014 (CSS) em paralelo com T015 após T013
- **US3**: T018 e T019 em paralelo após T017 (ou junto se um único edit no Twig)
- **US4 ∥ US5**: T023–T025 e T026–T027 após US1 visual mínimo
- **Polish**: T028 e T029 em paralelo

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (desktop fiel ao design) + trechos essenciais de US3 (hardcode + contato)
2. **Incremento mobile**: US2 (accordion)
3. **Deploy**: US4 (`11011` + cim/updb/cr)
4. **Float + governança**: US5 + Polish (rule, PRD, aceite)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T032`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
