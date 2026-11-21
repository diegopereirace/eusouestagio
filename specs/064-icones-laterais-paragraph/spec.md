# Spec: Ícones laterais para Adicionar/Remover itens (Paragraph)

**Feature**: 064-icones-laterais-paragraph
**Escopo**: `/cadastro/candidato` (instituição, curso extracurricular, experiência) e edição de candidato no painel.

## Problema

Os botões "Incluir ..." e "Remover última ..." dos campos Paragraph aparecem como botões laranja de largura total (Bootstrap Barrio injeta `btn-primary` + `form-control` em submits e o CSS do cadastro pinta `btn-primary` de laranja). Ficam chamativos e competem com o CTA principal.

## Requisitos

- **FR-001**: Adicionar/Remover são ícones pequenos (+ e −) numa coluna lateral à direita da lista de itens.
- **FR-002**: Legenda no hover e no foco de teclado: "Adicionar <item>" / "Remover <item>".
- **FR-003**: Alinhamento estável: o "+" nunca muda de posição quando o "−" aparece/some, inclusive após respostas AJAX (sem throbber inline).
- **FR-004**: Mobile (< 576px): ações em linha abaixo da lista, alinhadas à direita, "+" sempre na ponta direita.
- **FR-005**: Comportamento funcional inalterado (callbacks, wrappers, regras de exibição do "Remover": > 1 instituição; > 0 curso/experiência).
- **FR-006**: Acessibilidade: nome acessível preservado (`#value` mantido, oculto visualmente).

## Fora de escopo

Mudanças de banco, config, `hook_update_N`, `drush cex/cim`.

## Deploy

`npm run build` no tema, versionar `dist/`, `drush cr`.
