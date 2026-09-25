# Implementation Plan: Página Para Empresas

**Branch**: `feature-para-empresas` (scaffolding Spec Kit — `.specify/scripts` ausente neste repo; setup executado manualmente)  
**Date**: 2026-09-25  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/019-para-empresas-page/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Refatorar `/para-empresas`: limpar conteúdo editorial legado do nó `para_empresas`; hero via View `banners` display `block_para_empresas` (carrossel 2 slides, layout duas colunas, container ≤1200px); instâncias dedicadas de `nossos_diferenciais`, `nossa_metodologia`, `o_que_fazemos_bt` + Benefícios via reuso de `diferenciais_quem_somos` + CTA `cta_v1` em `content_full` só nessa URL; desativar `default_ctoparaempresas` e retirar path do `banners-block_1`; hook `custom_configs_update_11024` idempotente + `drush cex` → `config/sync`; PRD cirúrgico. Regra `drupal-deploy-configs.mdc` já cobre FR-001 (manter/reforçar se necessário).

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3 / SCSS do tema  
**Primary Dependencies**: Drupal core (Node, Views, Block, Image, Options, Path alias); Paragraphs; tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3). **Nenhuma dependência Composer nova.**  
**Storage**: PostgreSQL; estrutura em `config/sync`; seeds via `hook_update_N` + Entity API  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile &lt;992px / lg+ ≥992px — breakpoints Bootstrap)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS encapsulado (libraries dedicadas); omit empty; carousel Bootstrap já presente no tema; sem JS novo além do carousel BS  
**Constraints**: sem `core/`/`vendor/`; zero storages novos se Benefícios reusar tipo existente; deploy `cim` → `updb` → (2ª `cim`) → `cr`; pt-BR; não alterar placements da home; não redesenhar header/footer  
**Scale/Scope**: 1 allowed value + 1 display View + ~6 placements + 2 Twigs banner + CSS; 5 block_content seeds + 2 banner nodes; 1 hook `11024`; limpeza campos do nó; PRD §3.6 / rota

**Estado atual verificado (2026-09-25):**

- Último hook: `custom_configs_update_11023` → próximo livre **`11024`**.
- `field_local_exibicao`: `home` | `internas` | `quem_somos` — falta `para_empresas`.
- View `banners`: displays `block_1`/`block_2`/`block_3`/`block_home`/`block_quem_somos` — sem `block_para_empresas`.
- `default_views_block__banners_block_1`: pages `/para-estudantes` + `/para-empresas` → remover `/para-empresas`.
- `default_ctoparaempresas` (tipo `cto`, UUID conteúdo `f37cafe0-…`): `content_full` weight 0, só `/para-empresas` → desativar.
- Home: `default_nossosdiferenciais` / `default_nossametodologia` / `default_oquefazemos` só `<front>` — **não tocar**.
- Nó canônico: bundle `para_empresas` + Twig `node--para-empresas.html.twig` (fields `field_titulo`, textos, `field_imagem`, `field_itens_p`) — legado a limpar/omitir.
- Tipo banners: só mídia + peso + local (sem fields de copy) — copy/CTAs do hero no Twig (padrão 009).
- `drupal-deploy-configs.mdc`: diretriz permanente de `hook_update_N` + `cex` **já presente**.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md`, regra Cursor |
| Reuso de field storages / tipos | PASS | reuso View `banners`, tipos 004–006/013/015; zero storage novo |
| Deploy `cim` → `updb` → `cr` (+ 2ª `cim`) + hook idempotente | PASS | `11024` + `drush cex` estrutural |
| Clean URLs | PASS | CTAs `/cadastro/empresa`, `/painel/empresa/vagas/nova`; visibilidade `/para-empresas` |
| Performance / CSS isolado | PASS | wrappers `.banner-para-empresas-*` / classes dos blocos; sem vazar para home/QS |
| Contrib first / custom mínimo | PASS | sem módulo novo; Benefícios = reuso `diferenciais_quem_somos` |
| Sem dump / Entity API | PASS | seeds + limpeza via Entity API |
| Convivência home / Quem Somos | PASS | instâncias e placements novos; home intocada |

**Post-design**: gates mantidos; decisão Benefícios justificada em research R4. Sem violação injustificada.

## Design Decisions

1. **Allowed value `para_empresas`**: acrescentar em `field.storage.node.field_local_exibicao` (rótulo “Para Empresas”); exportar via `cex`.
2. **Display `block_para_empresas`**: filtro `status=1` + tipo `banners` + `local=para_empresas`; pager `none` (carrossel 2 itens); css_class dedicado; herda sorts (`field_peso` ASC + `created` DESC).
3. **Placement banner**: `views_block:banners-block_para_empresas` → região `banner`, tema `default`, pages só `/para-empresas`, weight 0.
4. **Legado `block_1`**: pages = somente `/para-estudantes` (remover `/para-empresas`).
5. **Hero Twig**: copy/CTAs **fixos no Twig** (padrão 009); carrossel Bootstrap na coluna de mídia com 2 imagens seed; container interno `max-width: 1200px`; gap ~48px; padding horizontal ~24px; mobile empilha copy acima da imagem.
6. **CTAs hero**: “Cadastrar Empresa” → `/cadastro/empresa`; “Contrate o Estágio Certo” → `/painel/empresa/vagas/nova` (rota canônica “Abrir Vaga” do rodapé).
7. **Limpeza do nó**: hook carrega nó pelo alias `/para-empresas` (bundle `para_empresas`); esvazia fields de conteúdo legado (`field_titulo`, `field_text_simple`, `field_text_simple_long`, `field_text_simple_long_2`, `field_imagem`, `field_itens_p`) **somente se** ainda contiverem valores (idempotente); **não** apaga o nó nem o alias; Twig omite seções vazias (omit empty).
8. **Instâncias dedicadas** (UUIDs fixos distintos da home): `nossos_diferenciais`, `nossa_metodologia`, `o_que_fazemos_bt` — placements `content_full` weights **0 / 1 / 2**, só `/para-empresas`. Copy seed: espelhar home ou tom B2B leve (pt-BR); nunca sobrescrever editorial divergente.
9. **Benefícios**: **reusar** `diferenciais_quem_somos` + paragraphs `diferencial_simples_p` (ícone + rótulo); nova instância título “Benefícios para Empresas”; placement weight **3**; Twig existente serve (ajuste mínimo: `id` da section derivado do título/label para não forçar `#diferenciais-quem-somos` nesta rota).
10. **CTA `cta_v1`**: nova instância título “Pronto para contratar os melhores talentos?”; botões seed: primário “Cadastrar Empresa” → `/cadastro/empresa`; secundário “Abrir Vaga” → `/painel/empresa/vagas/nova`; placement weight **4**; desativar `default_ctoparaempresas` (`status: false`).
11. **Hook `11024`**: ensure allowed value + (defensivo) display/placement paths; limpar fields do nó; seed 2 banners + 5 blocos; ensure placements; disable CTO; assets em `modules/custom/custom_configs/assets/banner-para-empresas/` (+ ícones benefícios se seed usar assets novos).
12. **PRD**: §3.1.1b / §3.6 / UC-16 / rotas — composição `/para-empresas`, display, hook `11024`.
13. **Regra Cursor**: `drupal-deploy-configs.mdc` já atende FR-001; na implementação, só reforçar se houver lacuna vs. spec (não reescrever por esporte).

## Project Structure

### Documentation (this feature)

```text
specs/019-para-empresas-page/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── banner-para-empresas-render.md
│   └── composition-para-empresas.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  field.storage.node.field_local_exibicao.yml          # + para_empresas
  views.view.banners.yml                               # + display block_para_empresas
  block.block.default_views_block__banners_block_para_empresas.yml  # NOVO
  block.block.default_views_block__banners_block_1.yml # - /para-empresas
  block.block.default_ctoparaempresas.yml              # status: false
  block.block.default_nossosdiferenciaisparaempresas.yml
  block.block.default_nossametodologiaparaempresas.yml
  block.block.default_oquefazemosparaempresas.yml
  block.block.default_beneficiosparaempresas.yml       # plugin diferenciais_quem_somos
  block.block.default_ctav1paraempresas.yml

themes/custom/default/
  templates/views/views-view--banners--block-para-empresas.html.twig
  templates/views/views-view-unformatted--banners--block-para-empresas.html.twig
  templates/content/node--para-empresas.html.twig      # omit empty / shell mínimo
  templates/block/block--block-diferenciais-quem-somos.html.twig  # id dinâmico (cirúrgico)
  assets/css/banner-para-empresas.css
  default.libraries.yml                                # library banner_para_empresas
  default.theme                                        # preprocess slides (opcional)

modules/custom/custom_configs/
  custom_configs.install                               # custom_configs_update_11024 + helpers
  assets/banner-para-empresas/                         # PNGs seed (2 slides)

PRD.md                                                 # §3.x + §3.6 + rota
.cursor/rules/drupal-deploy-configs.mdc                # só se precisar reforço FR-001
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. Config: allowed value + display View + placements (+ disable CTO) → `drush cex`
3. Twig/CSS hero + omit empty no node + ajuste id Twig Benefícios
4. Hook `11024` + assets seed
5. PRD + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Reuso de `diferenciais_quem_somos` com nome “Quem Somos” no machine name | FR-016: grade ícone+rótulo idêntica; evita tipo/paragraph/storage novos | Criar `beneficios_empresas` — YAGNI se o Figma couber; `nossos_diferenciais` tem layout bipartido + descrição por item |
| Copy do hero no Twig (não no node banners) | Schema `banners` só tem mídia; padrão 009; US4 cobre imagem + blocos gerenciáveis | Novos fields de texto/link em `banners` — escopo e risco de drift |
