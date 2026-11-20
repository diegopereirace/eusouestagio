# Implementation Plan: Bloco CTA Final — Para Estudantes

**Branch**: `feature-para-estudantes` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo, setup executado manualmente)  
**Date**: 2026-09-30  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/026-cta-final-estudantes/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar a **última faixa de conversão** em `/para-estudantes` reutilizando o Custom Block Type **`cta_v1`** (sem novos tipos/campos): nova instância seedada, placement `default_ctav1paraestudantes` em `content_full` weight **`4`** (após `default_views_block__vagas_block_3` weight `3`), visível só nessa rota, com **visual escuro full-bleed isolado** (gradiente `#023C62`→`#011A2B`, textos brancos, secundário outline branco) via Twig suggestion do placement + library/CSS dedicada — **sem** alterar o CSS/Twig global do CTA claro de Quem Somos / Para Empresas. Automação: `custom_configs_update_11043` idempotente + `drush cex`; PRD §3.6 cirúrgico.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Block Content, Field, Link, System visibility); tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3). **Nenhuma dependência Composer nova.**  
**Storage**: PostgreSQL; estrutura em `config/sync`; seed via `hook_update_N` + Entity API  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile ≤575.98px / md+ — breakpoints Bootstrap)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS encapsulado em library dedicada da instância; sem JS novo; omit empty no Twig  
**Constraints**: sem `core/`/`vendor/`; **zero** novos block types / field storages / instances; **não** alterar visual global `.block-cta-v1` / `cta-v1.css` / `block--block-cta-v1.html.twig` de forma regressiva; deploy `cim` → `updb` → `cim` → `cr`; pt-BR; features `021`–`025` e CTAs QS/PE intactos  
**Scale/Scope**: 1 instância `block_content`, 1 placement, 1 Twig suggestion, 1 library/CSS escopada, 1 hook `11043`, PRD cirúrgico

**Estado atual verificado (2026-09-30):**

- Hook desta feature: `custom_configs_update_11043` (`11042` já usado para título `/vagas`).
- Bundle `cta_v1` + campos (`field_text_simple`, `field_text_simple_long`, `field_link`, `field_link_2`) **existem** (015 / 019).
- Instâncias CTA existentes: Quem Somos `e6f7a8b9-…` / `default_ctav1quemsomos` w12; Para Empresas `a7b8c9d0-…` / `default_ctav1paraempresas` w5.
- Twig global: `block--block-cta-v1.html.twig` + library `default/cta_v1` → `cta-v1.css` (card claro `#D3E4FE`) — **não modificar de forma regressiva**.
- Composição `/para-estudantes` em `content_full`: benefícios `0` → jornada `1` → perfil `2` → vagas `block_3` `3` → **(CTA ausente)**.
- Já existem suggestions por placement ID no tema (ex.: `block--default-footer.html.twig`) — padrão a reusar.
- Helpers de seed CTA PE (`_custom_configs_seed_cta_v1_para_empresas`) são o template de idempotência a espelhar.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storages / sem tipo novo | PASS | reusa `cta_v1` e quatro campos; zero storage/instance novos |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | `11043` + `drush cex` estrutural; 2ª `cim` para placement pós-seed |
| Clean URLs | PASS | seed `/cadastro/candidato`, `/vagas`; rota `/para-estudantes` inalterada |
| Performance / CSS isolado | PASS | library dedicada + seletores sob ID/classe da instância |
| Contrib first / custom mínimo | PASS | sem módulo novo; Block Content + Link core |
| Sem dump / Entity API | PASS | seed via `BlockContent::create()` / entityTypeManager |
| Convivência 021–025 + CTAs QS/PE | PASS | só adiciona placement w4; não altera blocos/fields/CSS global alheios |

**Post-design**: gates mantidos. Isolamento visual via suggestion Twig + CSS escopado (não alterar `cta-v1.css` global) justifica Complexity Tracking abaixo. Sem violação injustificada.

## Design Decisions

1. **Reuso total do tipo** `cta_v1`: mesmos quatro campos; **não** criar block type, storage ou instance.
2. **Nova instância** distinta (UUID próprio) com copy Figma PE estudantes; edição independente de QS/PE empresas.
3. **Placement** `default_ctav1paraestudantes`: tema `default`, região `content_full`, weight **`4`**, visibility `request_path` = `/para-estudantes`, `label_display: '0'`.
4. **Isolamento visual (obrigatório)**:
   - Twig: `themes/custom/default/templates/block/block--default-ctav1paraestudantes.html.twig` (suggestion por ID de placement — prioridade sobre `block--block-cta-v1.html.twig`).
   - Classe de escopo: `.block-cta-v1--para-estudantes` (+ ID Drupal `#block-default-ctav1paraestudantes`).
   - Library nova `default/cta_v1_para_estudantes` → `assets/css/cta-v1-para-estudantes.css`.
   - **Não** alterar seletores/tokens em `cta-v1.css` nem o Twig global (exceto se um attach genérico for estritamente necessário — preferir attach só no Twig da instância).
5. **Markup**: reutilizar estrutura semântica do CTA (`cta-v1__title`, `__subtitle`, `__actions`, `__btn--primary/secondary`) com classes de escopo da variante escura; utilitários Bootstrap para stack de botões (`.d-grid.gap-2.d-md-flex…`).
6. **Tokens Figma (só esta instância)**:

   | Token | Valor |
   |-------|--------|
   | Fundo | full-bleed, linear `#023C62` → `#011A2B` |
   | Padding seção | `64px` top/bottom, `40px` left/right |
   | Conteúdo | `.container` (~1280px Bootstrap) |
   | Textos | branco, centralizados; título `h2` Poppins semibold/bold |
   | Primário | bg `#FD7B1A`, texto branco, sem borda |
   | Secundário | bg transparente, `1px solid #FFFFFF`, texto branco |

7. **UUIDs fixos**:
   - `block_content`: `e1f2a3b4-c5d6-4789-d012-3ef012345678`
   - Placement config: `f2a3b4c5-d6e7-4890-e123-4f0123456789`
8. **Hook `11043`**: seed se ausente/campos vazios; ensure placement w4 + visibility; **nunca** sobrescrever editorial divergente; **nunca** tocar placements/CSS de QS/PE empresas nem blocos 021–025.
9. **Seed**: título `Pronto para dar o próximo passo?`; corpo `Junte-se a milhares de estudantes que já encontraram a oportunidade ideal através da nossa plataforma.`; primário `Cadastre-se Gratuitamente` → `internal:/cadastro/candidato`; secundário `Explorar Vagas` → `internal:/vagas`.
10. **PRD** §3.6: documentar instância PE estudantes, placement w4, UUID, hook `11043`, library isolada; atualizar composição `/para-estudantes` (linha shell + bullet `cta_v1`).
11. **Fora**: Layout Builder; novo tipo/campos; redesign 021–025; alteração visual dos CTAs claros.

## Project Structure

### Documentation (this feature)

```text
specs/026-cta-final-estudantes/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── cta-final-estudantes-render.md
│   └── deploy-cta-final-estudantes.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  block.block.default_ctav1paraestudantes.yml           # criar (cex)

themes/custom/default/
  templates/block/block--default-ctav1paraestudantes.html.twig
  assets/css/cta-v1-para-estudantes.css                 # novo
  default.libraries.yml                                 # + cta_v1_para_estudantes
  # NÃO alterar (regressão proibida):
  #   templates/block/block--block-cta-v1.html.twig
  #   assets/css/cta-v1.css

modules/custom/custom_configs/
  custom_configs.install                                # custom_configs_update_11043 + helpers seed/placement

PRD.md                                                  # §3.6 cirúrgico (composição PE + bullet cta_v1)
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Twig suggestion + library/CSS escopada (tokens Figma escuros, Bootstrap stack, omit empty)
3. Hook `11043` (seed + ensure placement)
4. Origem: `drush cex` → versionar placement
5. PRD §3.6 + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Twig/CSS duplicados vs. variante no global | Spec exige isolamento absoluto; alterar `cta-v1.css` / Twig global quebraria SC-004 (QS/PE) | Classes condicionais no Twig global + override no mesmo CSS — risco alto de vazamento |
| Library nova vs. só ID no CSS global | Library dedicada espelha padrão do tema e facilita remoção; attach só na suggestion | Patch em `cta-v1.css` com `#block-…` — ainda toca arquivo compartilhado e aumenta chance de regressão em reviews |
