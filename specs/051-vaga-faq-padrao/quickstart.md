# Quickstart: 051-vaga-faq-padrao

Validação ponta a ponta após implementação. Detalhes: [data-model.md](data-model.md), [contracts/](contracts/).

## Pré-requisitos

- Feature `051-vaga-faq-padrao` aplicada localmente.
- Drupal + Drush (Docker do projeto ou equivalente).
- ≥2 vagas publicadas distintas (com ou sem `field_vaga_faq` legado preenchido).
- Usuário editor/moderador para form de Vagas e edição do FAQ.

## Setup (origem)

```bash
drush updb -y
drush cex -y
drush cr
```

Destino / espelho completo:

```bash
drush cim -y && drush updb -y && drush cim -y && drush cr
```

## Cenários de validação

### 1. Mesmo FAQ em qualquer vaga (US1 / SC-001 / SC-002)

1. Abrir o detalhe de ≥2 vagas publicadas.
2. **Esperado**: seção “Dúvidas Frequentes” com as mesmas perguntas, respostas e ordem.
3. Expandir um item → Accordion como na 038 (cards isolados).
4. Se alguma vaga tinha FAQ legado diferente em `field_vaga_faq`, o público **não** mostra esse conteúdo.

### 2. Form sem seletor (US2 / SC-003)

1. Criar/editar uma vaga como editor.
2. **Esperado**: campo “FAQ da vaga” **ausente**.
3. Editar o node FAQ padrão (coleção seed / UUID canônico), alterar uma pergunta, publicar.
4. Recarregar qualquer vaga → mudança refletida (SC-004, fluxo normal de CMS).

### 3. FAQ inválido omite seção em todas (US3 / SC-005)

1. Despublicar o FAQ padrão (ou esvaziar itens válidos).
2. Abrir qualquer vaga → seção ausente.
3. Republicar com ≥1 item válido → seção volta em todas.

### 4. Deploy idempotente (US4 / SC-006)

1. `drush updb -y` de novo.
2. **Esperado**: sem duplicar a coleção FAQ; sem sobrescrever editorial já divergente; form continua sem seletor.
3. Conferir `config/sync/core.entity_form_display.node.vagas.default.yml` com `field_vaga_faq` em `hidden` (após cex na origem).

### 5. Regressões rápidas

- Posição: após “Sobre a Empresa” (se existir), antes do CTA de região.
- Visual Accordion 038 inalterado.
- Benefícios / sidebar / CTA candidato (050) intocados.

## Resultado da validação (implementação 2026-11-02)

| Cenário | Resultado |
|---------|-----------|
| 1. Mesmo FAQ (≥3 vagas 82/83/84) | PASS — mesmas 2 perguntas do FAQ seed |
| 2. Form sem seletor | PASS — `field_vaga_faq` em `hidden` no form display |
| 3. FAQ unpublished omite | PASS — seção ausente; republicar restaura |
| 3b. Mix parcial / zero válidos | PASS — inválidos filtrados; zero → seção omitida |
| 4. `updb` idempotente | PASS — “No pending updates”; seed no-op |
| 5. Accordion/CSS 038 | PASS — arquivos intocados |

Scripts auxiliares: `modules/custom/custom_configs/scripts/verify_051_faq_*.php`.

## Resultado da validação (implementação 2026-11-02)

| Cenário | Resultado |
|---------|-----------|
| 1. Mesmo FAQ (≥3 vagas 82/83/84) | PASS — mesmas 2 perguntas do FAQ seed |
| 2. Form sem seletor | PASS — `field_vaga_faq` em `hidden` no form display |
| 3. FAQ unpublished omite | PASS — seção ausente; republicar restaura |
| 3b. Mix parcial / zero válidos | PASS — inválidos filtrados; zero → seção omitida |
| 4. `updb` idempotente | PASS — “No pending updates”; seed no-op |
| 5. Accordion/CSS 038 | PASS — arquivos intocados |

Scripts auxiliares: `modules/custom/custom_configs/scripts/verify_051_faq_*.php`.
