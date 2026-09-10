# Especificação de Feature: Vagas de Destaque (Home) — anexo1 + ícones

**Feature Directory**: `specs/003-vagas-destaque-home`
**Criada em**: 2026-09-10
**Status**: Active
**Input do usuário**: Aplicar estilo Figma anexo1 no bloco Vagas de Destaque da Home; ícone por curso via select amigável (Font Awesome já instalado).

## Escopo

### Inclui

- Visual anexo1 no card da View `vagas` display `block_1` (Home).
- Campo `field_icone_fa` (list_string) no taxonomy `curso`.
- Header da seção: título “Vagas de Destaque” + link “Ver todas as vagas” → `/para-estudantes`.
- Correção da empresa no card (`field_empresa_u` / `field_nome_fantasia`).

### Fora

- Redesign dos cards em `/para-estudantes` (`page_1`) e vagas similares (`block_2`).
- Nova biblioteca de ícones (Lucide/Phosphor).
- Ícone cadastrado por vaga individual.
- Upload de imagem/SVG no termo.

## User Scenarios & Testing

### Cenário 1 — Visitante vê cards laranja no estilo anexo1 (P1)

1. Visitante acessa a home.
2. Vê seção “Vagas de Destaque” com até 3 cards laranja: ícone, badge de regime, título, empresa•local, salário, “Ver Mais”, botões “INSCREVA-SE” e “BUSCAR MAIS VAGAS”.

### Cenário 2 — Editor associa ícone ao curso (P1)

1. Editor edita um termo do vocabulário Curso.
2. Escolhe ícone em select com rótulos amigáveis (ex. “Tecnologia / TI”).
3. Vagas daquele curso passam a exibir o ícone correspondente no card da Home.

### Cenário 3 — Links de navegação (P1)

1. “Ver todas as vagas” / “BUSCAR MAIS VAGAS” levam a `/para-estudantes`.
2. “INSCREVA-SE” e “Ver Mais” levam à página da vaga.

### Edge Cases

- Curso sem `field_icone_fa` → ícone fallback `briefcase`.
- Vaga sem empresa → meta só com local (quando houver).
- Vaga sem salário → linha de salário omitida; “Ver Mais” permanece.

## Requirements

- **FR-1**: Cards da Home DEVEM usar o visual anexo1 escopado em `.css-vagas-home` (não alterar listing/similares).
- **FR-2**: Ícone DEVE vir do 1º termo `field_cursos_t` via `field_icone_fa`; fallback `briefcase`.
- **FR-3**: Reutilizar Font Awesome do tema; sem nova dependência.
- **FR-4**: Empresa DEVE usar `field_empresa_u.entity.field_nome_fantasia`.
- **FR-5**: Header/footer da View Home DEVEM apontar para `/para-estudantes`.

## Success Criteria

- **SC-1**: Home reconhecível frente ao anexo1.
- **SC-2**: `/para-estudantes` e similares inalterados visualmente.
- **SC-3**: Editor escolhe ícone sem saber classe FA.
