# Implementation Plan: Detalhe da Vaga — Layout Duas Colunas

**Branch**: `feature-vaga` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo; setup via `.specify/feature.json` → `specs/030-vagas-detalhe-layout`)  
**Date**: 2026-10-04  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/030-vagas-detalhe-layout/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Refatorar o detalhe full do node `vagas` para o layout Figma em duas colunas (8/4), com Content Type `faq` + ER `field_vaga_faq`, campos de etapas/requisitos/benefícios, paragraph `beneficio_vaga_p`, seções (header, sobre, requisitos com checks, benefícios grid, stepper, empresa, FAQ accordion, CTA) e sidebar (ações existentes, resumo, perfil estático). Excluir Match. Deploy: `custom_configs_update_11047` + `drush cex`; PRD §3.1 cirúrgico. Listagem `/vagas` (`028`/`029`) intocada.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3, Bootstrap 5.3 (Barrio)  
**Primary Dependencies**: Drupal core (Node, Field, Text, Image); Paragraphs + Entity Reference Revisions; tema `default`; módulos `custom_configs`, `custom_panel`, `custom_candidaturas`. **Nenhuma dependência Composer nova**.  
**Storage**: PostgreSQL; tipos/fields/displays em `config/sync`; seeds FAQ + ensures via Entity API no hook `11047`  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile &lt;992px / `lg+`)  
**Project Type**: Drupal theme + custom modules (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS em library dedicada; accordion BS5 nativo; seções omitidas quando vazias; cache de node inalterado em essência  
**Constraints**: sem `core/`/`vendor/`; **não** alterar Twigs/CSS da listagem `/vagas` nem cards laranja; sem Match; deploy `cim` → `updb` → `cim` → `cr`; pt-BR  
**Scale/Scope**: 1 CT `faq` + 1 paragraph + 4 fields em `vagas` + storages novos justificados; 1 Twig full + library/CSS; 1 hook `11047`; migração opcional legado→novos; PRD cirúrgico

**Estado atual verificado (2026-10-04):**

- Template: `themes/custom/default/templates/content/node--vagas.html.twig` — já tem grid 8/4 básico; requisitos/benefícios via `field_text_simple_multiple` / `_2` (listas); sidebar com `js-candidatar-vaga` / `js-salvar-vaga` / share; **sem** stepper, FAQ accordion, CTA final Figma, resumo, card perfil, checks/benefícios grid.
- Preprocess: `default_preprocess_node__vagas` expõe `vaga_salva` / `vaga_candidatada` (candidato).
- CSS detalhe: estilos `.vaga-*` em `assets/css/style.css` (não library dedicada).
- CT `faq`: **inexistente**; paragraphs `beneficio_vaga_p`: **inexistente**.
- Campos novos da spec: **inexistentes** em `config/sync`.
- Storages multi string em `node` já usados no bundle `vagas`: `field_text_simple_multiple`, `field_text_simple_multiple_2` → **não** reutilizáveis para novas instances no mesmo bundle.
- Paragraph storages reutilizáveis: `paragraph.field_image`, `paragraph.field_text_simple`.
- Rota pública de empresa: **não existe** (só `/painel/empresa/perfil` autenticado).
- Completude de perfil %: **não existe** serviço/capa no código custom.
- Último hook: `custom_configs_update_11045` (028). `11046` reservado/opcional pela 029 (ausente no install). Esta feature: **`11047`**.
- Library JS candidatura: `default/script-painel` já attachada no Twig atual.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storage | PASS | paragraph: reuso `field_image`/`field_text_simple`; novos storages node só onde o bundle já esgota o storage equivalente ou o machine name é contrato (`field_resposta`, `field_vaga_*`) |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | `11047` + `drush cex` |
| Clean URLs | PASS | canonical do node inalterada; FAQ sem path público obrigatório |
| Performance / CSS isolado | PASS | library `vagas_detalhe`; escopo `.vaga-detalhe` |
| Contrib first | PASS | Node/Field/Paragraphs/BS5; sem Composer novo |
| Sem dump / Entity API | PASS | ensures + seeds UUID no hook |
| Isolamento listagem / cards laranja | PASS | só Twig full + CSS detalhe; Views `page_1`/`block_*` intocados |

**Post-design**: gates mantidos. Storages novos e paragraph dedicado justificados em Complexity Tracking. Sem violação injustificada.

## Design Decisions

1. **Template full**: criar `node--vagas--full.html.twig` com o markup Figma (raiz `.vaga-detalhe`). Manter `node--vagas.html.twig` apenas como fallback genérico mínimo **ou** redirecionar visualmente só se algum modo não-full ainda o usar — preferência: full exclusivo no `--full`; não alterar Twigs de Views.
2. **CSS / library**: `default/vagas_detalhe` → `assets/css/components/vagas-detalhe.css`; attach no Twig full (`attach_library`) + opcional preprocess. Escopo sob `.vaga-detalhe` / `.node--type-vagas.node--view-mode-full`. **Não** reutilizar seletores da listagem (`.item-vaga--lista`, `.vagas-lista-layout`).
3. **CT `faq`**: label “FAQ”; `title` = pergunta; `field_resposta` = `text_long` (storage novo `node.field_resposta`); form/view displays `default` com title + resposta; sem menu.
4. **Seeds FAQ**: 2 nodes UUID fixos (ver [data-model.md](data-model.md)); perguntas do Figma; respostas placeholder pt-BR; reexecução = load por UUID.
5. **Campos `vagas`**:
   - `field_vaga_faq` — ER → `node` bundle `faq`, card. `-1`;
   - `field_vaga_etapas_processo` — string multi `-1` (storage novo);
   - `field_vaga_requisitos` — string multi `-1` (storage novo);
   - `field_vaga_beneficios` — ERR → `beneficio_vaga_p`, card. `-1` (storage novo).
6. **Paragraph `beneficio_vaga_p`**: instances de `field_image` (ícone) + `field_text_simple` (título); form/view displays próprios; Twig opcional `paragraph--beneficio-vaga-p.html.twig` ou render inline no node full.
7. **Preferência de dados no full**: requisitos/benefícios preferem campos novos; se novos vazios e legados (`field_text_simple_multiple` / `_2`) tiverem valor, **migração opcional no hook** (só quando novo vazio) + Twig pode fallback legado só se migração não rodou — preferir migração no hook e Twig só nos novos.
8. **Seções omitidas** se campo vazio (requisitos, benefícios, etapas, FAQ, empresa).
9. **Stepper**: N círculos 1…N; último (e único) com `#FD7B1A`; linha conectora; mobile: wrap/scroll horizontal discreto.
10. **FAQ accordion**: Bootstrap 5 `accordion`; IDs `vaga-faq-{nid}-{faq_nid}`; pergunta = label; resposta = `field_resposta` (formato texto/HTML conforme storage).
11. **Ver Empresa**: **omitir** o botão/link — não há rota pública de perfil de empresa; `/painel/empresa/perfil` exige role empresa. Nome/logo/`field_sobre_empresa` truncado (~160–200 chars / line-clamp) permanecem no card.
12. **CTA final**: markup fixo no Twig (fundo `#023C62`, botão laranja “Candidatar-se Agora”) reutilizando as mesmas classes/estado de candidatura da sidebar (`js-candidatar-vaga` / disabled / login).
13. **Sidebar ações**: preservar fluxos atuais (candidato / anônimo / estados salva/candidatada) + bloco Compartilhar existente.
14. **Resumo**: Período ← `field_horarios` (labels unidos); Bolsa ← `field_text_simple`; Modelo ← `field_regime_t`; Vagas ← “Não informado” (sem campo novo).
15. **Seu Perfil**: markup estático `%` = 75 (ou var preprocess `vaga_perfil_completo` se setada); CTA → `/painel/estudante/perfil` (só sentido para candidato; para anônimo/outros roles: link login com destination ou omitir card — **mostrar para candidato autenticado**; anônimo vê CTA login no card ou omite — decisão: mostrar card com barra estática + “Completar agora” → login com destination do perfil se anônimo).
16. **Match**: zero markup/CSS/campos.
17. **Hook `11047`**: ensure CT faq + field_resposta + displays; seed 2 FAQs; ensure paragraph + fields + displays; ensure 4 fields em vagas + form/view displays; migração opcional legado→novos; **não** associar FAQs a todas as vagas (opcional só seeds demo se campo vazio — YAGNI: não associar automaticamente nesta entrega).
18. **PRD**: §3.1 — CT `faq`, paragraph `beneficio_vaga_p`, campos novos em `vagas`; menção hook `11047` no detalhe full.
19. **Fora**: redesign `/vagas`; novos fluxos candidatura/favorito/share; motor de match; FAQ institucional global.

## Project Structure

### Documentation (this feature)

```text
specs/030-vagas-detalhe-layout/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── vagas-detalhe-render.md
│   └── deploy-vagas-detalhe.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
  templates/content/node--vagas--full.html.twig          # NOVO — layout Figma
  templates/paragraphs/paragraph--beneficio-vaga-p.html.twig  # opcional
  assets/css/components/vagas-detalhe.css                # NOVO
  default.libraries.yml                                  # + vagas_detalhe
  default.theme                                          # preprocess: perfil % opcional; library se não attach no Twig
  templates/content/node--vagas.html.twig                # fallback; não regressar listagem

modules/custom/custom_configs/
  custom_configs.install   # custom_configs_update_11047 + helpers ensure/seed/migrate

config/sync/  # via drush cex (não inventar à mão)
  node.type.faq.yml
  field.storage.node.field_resposta.yml
  field.field.node.faq.field_resposta.yml
  core.entity_form_display.node.faq.default.yml
  core.entity_view_display.node.faq.default.yml
  paragraphs.paragraphs_type.beneficio_vaga_p.yml
  field.field.paragraph.beneficio_vaga_p.field_image.yml
  field.field.paragraph.beneficio_vaga_p.field_text_simple.yml
  core.entity_*_display.paragraph.beneficio_vaga_p.default.yml
  field.storage.node.field_vaga_faq.yml
  field.storage.node.field_vaga_etapas_processo.yml
  field.storage.node.field_vaga_requisitos.yml
  field.storage.node.field_vaga_beneficios.yml
  field.field.node.vagas.field_vaga_*.yml
  core.entity_form_display.node.vagas.default.yml
  core.entity_view_display.node.vagas.default.yml  # e/ou full se existir display dedicado

PRD.md   # §3.1 cirúrgico

# NÃO alterar:
#   views-view-field--vagas--page-1|block-1|block-2|block-3--nothing.html.twig
#   vagas-lista-vertical.css / library vagas_lista_vertical
#   Hero Search 027
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. CT `faq` + `field_resposta` + displays + seed 2 FAQs (origem + ensure hook)
3. Paragraph `beneficio_vaga_p` + 4 campos em `vagas` + displays + migração opcional
4. Twig `node--vagas--full` (seções + sidebar + CTA) + preprocess leve
5. Library/CSS escopada (checks, stepper, grids, cards, tokens)
6. Origem: `drush cex` → versionar configs
7. PRD §3.1 + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Storages novos `field_vaga_etapas_processo` / `field_vaga_requisitos` | Multi-string existentes já estão no bundle `vagas` (legado); Drupal não permite 2ª instance do mesmo storage no mesmo bundle | Reusar `field_text_simple_multiple` para etapas — impossível no mesmo bundle |
| Storage novo `field_vaga_faq` / `field_vaga_beneficios` | Machine names + cardinality/handler específicos (FAQ node; ERR ilimitado) | Reusar `field_numeros_lista` (card 4, outro propósito) |
| Storage `field_resposta` | Contrato FR-001; `field_text_simple_long` é plain string e já semanticamente “cargo/texto curto” em outros bundles | Reusar `field_text_long_formatted` com outro label — nome divergente do FR |
| Paragraph `beneficio_vaga_p` vs `card_icon_text_p` | Spec + padrão do projeto (paragraph por seção); evita campo `field_text_simple_long` obrigatório do card 022 | Reusar `card_icon_text_p` — colisão editorial/CSS |
| Omitir “Ver Empresa” | Sem rota pública de empresa | Link para `/painel/empresa/perfil` — 403/confuso para candidatos |
| `%` perfil estático | Sem serviço de completude; FR permite | Inventar cálculo de perfil nesta feature — fora de escopo |
| Template `--full` novo | Isola detalhe de outros view modes | Reescrever só `node--vagas.html.twig` — ok, mas `--full` é mais preciso |
