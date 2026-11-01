# Data Model: Refatorar UI — Nossa Metodologia

**Feature**: [spec.md](spec.md) | **Data**: 2026-11-01

Sem CT/fields/placements novos. Reusa o modelo da **005**.

## Entidades (somente leitura / render)

| Entidade | Campos usados | Papel nesta feature |
|----------|---------------|---------------------|
| `block_content` `nossa_metodologia` | `field_text_simple`, `field_text_simple_long`, `field_image`, `field_metodologia_passos` | Título, subtítulo, imagens de etapas (chevrons), lista de passos |
| `paragraph` `metodologia_passo_p` | `field_image`, `field_text_simple` | Ícone circular + título do passo |

UUID seed de referência (inalterado): `1f9b40ca-aa77-4e7e-a450-5aa94888e274`.

## Camada de apresentação (não é entidade Drupal)

| Peça | Fonte | Persistência |
|------|-------|--------------|
| Linha superior (chevrons) | `field_image` multi-valor do bloco | CMS / `public://` |
| Wrapper max-width ~770px | CSS `.nm-etapas` (≥992px) | código do tema |
| Linha inferior (ícones + setas) | paragraphs + CSS `::after` | CMS + código do tema |
| Wrapper max-width ~1160px | CSS `.nm-passos` (≥992px) | código do tema |
| Conectores entre etapas | CSS `::after` (cores existentes) | código do tema |
| Setas verdes entre passos | CSS `::after` (SVG data-URI) | código do tema |

## Regras de validação (render)

| Condição | Comportamento |
|----------|---------------|
| Título vazio | Omitir `h2` |
| Subtítulo vazio | Omitir `p` |
| Sem etapas e sem passos | Cabeçalho (se preenchido) ainda renderiza; sem erro Twig |
| Sem etapas | Omitir `.nm-etapas` |
| Sem passos | Omitir `.nm-passos` |
| N > 3 etapas ou passos | Renderizar todos; wrappers mantêm max-width; wrap/compressão conforme breakpoint |
| Imagem oversized | Contida com `object-fit: contain` no item |
| Alt vazio | `alt=""` (etapa); ícone pode usar título do passo ou `alt=""` |

## Relacionamentos

```text
nossa_metodologia 1──* metodologia_passo_p  (via field_metodologia_passos)
         │
         └── field_image → File[] (etapas / chevrons)
```

Placements (inalterados): Home `<front>` e `/para-empresas` apontam para a mesma entidade.
