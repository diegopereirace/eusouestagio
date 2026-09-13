# Implementation Plan: Redesign do Rodapé (Footer)

**Branch**: `dev`  
**Date**: 2026-09-13  
**Spec**: [spec.md](spec.md)

## Summary

Refatorar o rodapé existente (`block_content` bundle `footer`, UUID `d0326db5-fc80-4cf5-a0b9-8f779dc4aeba`) para o layout aprovado: fundo azul-marinho, Poppins branca, 5 colunas (marca + 4 ícones sociais circulares | Institucional | Para Estudantes | Para Empresas | Contato), barra inferior centralizada, accordion Bootstrap no mobile e botão flutuante de WhatsApp com `z-index: 1050`. Links das colunas hardcoded no Twig; seed `custom_configs_update_11011` idempotente; Cursor Rule de fluxo e PRD § rodapé atualizados.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Block Content, Bootstrap Barrio 5 / Bootstrap 5.3 (Collapse/Accordion), Font Awesome (já no tema)  
**Storage**: PostgreSQL; config estrutural em `config/sync`; conteúdo via seed `hook_update_N`  
**Testing**: validação manual + quickstart (cim → updb → cr); sem suite automatizada dedicada nesta feature  
**Target Platform**: site público Drupal (desktop ≥992px / mobile &lt;992px)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS encapsulado sob `.site-footer-custom` / `.whatsapp-float-block`; sem assets novos pesados  
**Constraints**: nenhum field storage novo; sem alterar `core/`/`vendor/`; menus legados preservados sem uso; rotas futuras aceitam 404  
**Scale/Scope**: 1 template Twig, CSS do rodapé + float, 1 hook update, PRD + Cursor Rule

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes vindos das regras do projeto (`.cursor/rules/estagio-*.mdc` + PRD):

| Gate | Status |
|------|--------|
| SDD — spec aprovada antes do código | PASS — `spec.md` + checklist OK |
| Sem alteração em `core/` / `vendor/` | PASS — só `themes/custom`, `modules/custom`, `config/sync`, `.cursor/rules`, `PRD.md` |
| Reuso de field storages | PASS — zero storages novos; e-mail social reutiliza `field_email` |
| Deploy: `cim` → `updb` → `cr` + seed idempotente | PASS — `11011` + `drush cex` se houver mudança estrutural |
| Clean URLs / rotas canônicas | PASS — links hardcoded com paths limpos |
| Performance / CSS isolado | PASS — seletores sob wrappers do rodapé/float |

**Post-design**: gates mantidos; sem violação injustificada.

## Design Decisions

1. **Sem novos campos**: manter bundle `footer` e campos atuais; ocultar no Twig `field_text_simple_long` (tagline) e `field_text_simple_2` (endereço).
2. **Links hardcoded**: substituir `drupal_menu(...)` por `<a href>` com rotas canônicas da tabela da spec; menus `rodape---sobre`, `rodape---para-estudantes`, `para-empresas` deixam de ser consumidos (não removidos).
3. **Sociais circulares**: ordem E-mail → Instagram → LinkedIn → WhatsApp; cores de marca via CSS; omitir ícone se campo vazio.
4. **Contato**: `mailto:` e `tel:` (telefone sanitizado); omitir itens vazios.
5. **Mobile accordion**: colunas 2–4 com Bootstrap Collapse abaixo de `lg` (&lt;992px); marca e contato sempre visíveis; desktop sem accordion (`d-none d-lg-*` / espelho).
6. **Barra inferior**: `<hr>` + texto centralizado em 2 linhas; crédito “Desenvolvido por Diego Pereira” sem link; sem links legais na barra.
7. **WhatsApp float**: apenas `z-index: 1050` (hoje `1030`); manter `position: fixed`.
8. **Seed `11011`**: garantir bloco + logo (reutilizar helpers de `11010`); **não** re-seedar menus; preservar conteúdo editorial divergente.
9. **Governança**: confirmar/versionar `.cursor/rules/estagio-fluxo-dev.mdc` (FR-018); atualizar seção do `footer` no `PRD.md` (FR-017).

## Project Structure

```text
specs/008-rodape-redesign/
  plan.md
  research.md
  data-model.md
  quickstart.md
  contracts/footer-render.md
themes/custom/default/
  templates/block/block--default-footer.html.twig
  assets/css/style.css                    # rodapé + .whatsapp-float-block z-index
modules/custom/custom_configs/
  custom_configs.install                  # custom_configs_update_11011
config/sync/                              # cex se displays/placement mudarem
.cursor/rules/estagio-fluxo-dev.mdc
PRD.md
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Twig rodapé (hardcode links, sociais, accordion, barra)
3. CSS (círculos sociais, uppercase, barra, accordion, float `1050`)
4. Seed `11011` + `drush cex` se necessário
5. Cursor Rule + PRD § footer + validação quickstart

## Complexity Tracking

Nenhuma violação de gate a justificar.
