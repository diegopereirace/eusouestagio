# Contract: render página empresa + detalhe vaga (034)

## Detalhe vaga (`node--vagas--full`)

| Elemento | Fonte | Notas |
|----------|-------|-------|
| Sobre a Vaga | `field_text_long_formatted` | Omitir se vazio |
| Nome empresa (link) | `field_vaga_empresa` → canonical | Só se publicado |
| Sobre a Empresa (seção) | — | **Removido** |

## Página empresa (`node--empresa--full`)

| Elemento | Fonte |
|----------|-------|
| Logo | `field_imagem` |
| Nome | `title` |
| Local | `field_cidade` + `field_estados` |
| Sobre | `field_text_long_formatted` |
| Vagas abertas | View `empresa_vagas`:`block_empresa` arg = NID |

## URL

Pathauto: `/empresa/[node:title]`
