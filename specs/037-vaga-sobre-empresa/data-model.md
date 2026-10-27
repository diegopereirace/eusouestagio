# Data Model: 037-vaga-sobre-empresa

## Resumo

Nenhum storage novo. A feature cria uma **instância** do storage de texto curto que já existe e grava um valor seed em um único node.

- **Storage** `field.storage.node.field_text_simple` — `type: string`, `max_length: 255`, cardinalidade 1. Inalterado.
- **Instância nova** `field.field.node.empresa.field_text_simple` — rótulo “Resumo curto”, `required: false`, `translatable: false`.
- **Instâncias existentes** do mesmo storage (`vagas` = Salário, `contato`, `depoimento`) permanecem como estão.

## CT `empresa` — alteração

| Campo | Storage | Bundle | Antes | Depois |
|-------|---------|--------|-------|--------|
| `field_text_simple` | `node.field_text_simple` (string 255) | `empresa` | ausente | instância “Resumo curto”, opcional |

Demais campos do bundle não mudam de tipo: `field_imagem` (logo), `field_text_long_formatted` (descrição longa da página), `field_cidade`, `field_estados`.

### Validação

- Máximo 255 caracteres: imposto pelo storage. O form widget `string_textfield` recusa valor maior.
- Vazio é válido (`required: false`). No cartão, subtítulo vazio não renderiza a linha (FR-005).
- O valor é texto simples. Não aceita HTML (diferente de `field_text_long_formatted`).

### Displays

| Display | Componente | Formatter / widget | Notas |
|---------|------------|--------------------|-------|
| `core.entity_form_display.node.empresa.default` | `field_text_simple` | `string_textfield` | Weight 2 na primeira inserção; descrição longa/cidade/estado sobem um peso se ainda estiverem colados |
| `core.entity_view_display.node.empresa.default` | `field_text_simple` | `string`, label acima | Mesma lógica de peso. A página pública full **não** imprime esse componente |

Não há view mode `full` exportado para `empresa`. O template `node--empresa--full.html.twig` é markup próprio e continua lendo só logo, título, local e `field_text_long_formatted`.

## Relação vaga → empresa

Sem campo novo na vaga.

| De | Para | Campo | Uso nesta feature |
|----|------|-------|-------------------|
| `vagas` | `empresa` | `field_vaga_empresa` (ER, cardinalidade 1) | Fonte única do cartão. Ignora `field_empresa_u` |

O cartão aparece quando a referência aponta para um node `empresa` publicado e com `access('view')`.

## Seed

| Node | UUID | Campo | Valor seed | Condição |
|------|------|-------|------------|----------|
| EcoConstrutora | `f6a7b8c9-d0e1-4234-e567-89abcdef0123` | `field_text_simple` | Líder regional em construções sustentáveis e infraestrutura urbana de alto impacto. | Só se o campo existir e estiver vazio |

Vaga de conferência (não é alterada por esta feature): Engenheiro Civil, UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234`, já vinculada à EcoConstrutora.

## Estado / transições

- **Empresa sem a instância** → após `updb`/`cim`, a instância existe e o form a exibe.
- **EcoConstrutora com resumo vazio** → texto do Figma.
- **EcoConstrutora com resumo editorial** → inalterada na reexecução.
- **Outras empresas** → campo disponível e vazio; nenhum backfill.
- **Vaga com empresa publicada e acessível** → cartão na coluna principal.
- **Vaga sem referência, ou empresa despublicada/inacessível** → sem cartão.
- **Reexecução do hook** → no-op de instância, de display e de seed.

## O que não muda

- Storage e instância `node.vagas.field_text_simple` (salário no resumo da sidebar).
- `field_text_long_formatted` da EcoConstrutora (descrição longa da página 034).
- Pathauto `/empresa/[node:title]`.
- View `empresa_vagas`.
