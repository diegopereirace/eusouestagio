# Data Model: Refatorar UI — Nossos Diferenciais

**Feature**: [spec.md](spec.md) | **Data**: 2026-11-01

Sem CT/fields/placements novos. Reusa o modelo da **004**.

## Entidades (somente leitura / render)

| Entidade | Campos usados | Papel nesta feature |
|----------|---------------|---------------------|
| `block_content` `nossos_diferenciais` | `field_text_simple`, `field_text_simple_long`, `field_image`, `field_diferenciais_lista` | Título, subtítulo, colagem completa (CMS), lista |
| `paragraph` `diferencial_item_p` | `field_image`, `field_text_simple`, `field_text_simple_long` | Ícone, título, descrição do item |

UUID seed de referência (inalterado): `b0a1c2d3-e4f5-4678-9abc-def012345678`.

## Camada de apresentação (não é entidade Drupal)

| Peça | Fonte | Persistência |
|------|-------|--------------|
| Colagem completa ~1324×718 | `field_image` do bloco (âncora + fatias no asset) | CMS / `public://` |
| Derivada web | image style `wide` (1090w) | derivada Drupal |
| Círculo verde / ponto navy / ring | CSS (spans `.nd-media__deco-*`) | código do tema |
| Cores de título de item | CSS `.field__item:nth-child(3n+k)` | código do tema |

## Regras de validação (render)

| Condição | Comportamento |
|----------|---------------|
| Título vazio | Omitir `h2` |
| Subtítulo vazio | Omitir `p` |
| Sem `field_image` | Placeholder `.nd-media__placeholder` preserva coluna |
| Lista vazia | Cabeçalho + colagem ainda renderizam |
| N > 3 itens | Renderizar todos; cores ciclam verde → navy → laranja via `.field__item` |
| Imagem CMS oversized | Contida via style `wide` + `object-fit` / `aspect-ratio` |

## Relacionamentos

```text
nossos_diferenciais 1──* diferencial_item_p  (via field_diferenciais_lista)
         │
         └── field_image → File (colagem completa)
```

Placements (inalterados): Home `<front>` e `/para-empresas` apontam para a mesma entidade.
