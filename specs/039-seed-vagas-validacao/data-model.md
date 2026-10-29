# Data model — Seed 15 vagas + empresas

## Entidades

| Papel | Tipo | Identidade |
|-------|------|------------|
| Perfil público | node `empresa` | UUID `b039e001-0001-4000-8000-0000000000NN` (NN=01..15) |
| Conta B2B | user role `empresa` | username `empresa-seed-NN` |
| Vaga | node `vagas` | UUID `b039v001-0001-4000-8000-0000000000NN` |
| FAQ compartilhada | node `faq` | UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0` (existente) |

## Catálogo (NN → empresa / vaga / local)

| NN | Empresa (title / fantasia) | Vaga (title) | Cidade/UF | Destaque | Curso | Regime |
|----|----------------------------|--------------|-----------|----------|-------|--------|
| 01 | EcoTech Soluções | Estágio em Desenvolvimento Web | São Paulo / SP | sim | Ciência da Computação | Remoto |
| 02 | Nova Saúde Clínica | Estágio em Enfermagem | Rio de Janeiro / RJ | não | Enfermagem | Presencial |
| 03 | Atlas Engenharia | Estágio em Engenharia Civil | Curitiba / PR | não | Engenharia Civil | Presencial |
| 04 | Horizonte Educacional | Estágio em Pedagogia | Belo Horizonte / MG | não | Pedagogia | Híbrido |
| 05 | Finança Clara | Estágio em Administração | São Paulo / SP | sim | Administração | Remoto |
| 06 | Verde Agro | Estágio em Agronomia | Goiânia / GO | não | Agronomia | Presencial |
| 07 | Pixel Design Studio | Estágio em Design Gráfico | Florianópolis / SC | não | Design | Híbrido |
| 08 | Lex Advogados | Estágio em Direito | Brasília / DF | não | Direito | Presencial |
| 09 | NutriVida | Estágio em Nutrição | Porto Alegre / RS | não | Nutrição | Presencial |
| 10 | DataFlow Analytics | Estágio em Ciência de Dados | Campinas / SP | sim | Ciência da Computação | Remoto |
| 11 | ConstruMais | Estágio em Arquitetura | Salvador / BA | não | Arquitetura | Presencial |
| 12 | SoftNorth | Estágio em QA | Fortaleza / CE | não | Sistemas de Informação | Híbrido |
| 13 | Mercado Local | Estágio em Marketing | Recife / PE | não | Marketing | Remoto |
| 14 | BioLab Pesquisa | Estágio em Biomedicina | Londrina / PR | não | Biomedicina | Presencial |
| 15 | LogiMove | Estágio em Logística | Manaus / AM | não | Logística | Presencial |

## Campos preenchidos

### node `empresa`

`title`, `field_imagem`, `field_text_simple`, `field_text_long_formatted`, `field_cidade`, `field_estados`, `status=1`.

### user `empresa`

`name`, `mail`, `pass` (`SeedEmpresa!2026`), `field_cnpj`, `field_razao_social`, `field_nome_fantasia`, `field_inscricao_municipal`, `field_sobre_empresa`, endereço (`field_cep`…`field_estado`), `field_responsavel_*`, `field_termo`, `user_picture`, role `empresa`, `status=1`.

### node `vagas`

`title`, `field_text_long_formatted`, `field_vaga_requisitos`, `field_vaga_etapas_processo`, `field_vaga_beneficios` (4 padrão), `field_vaga_faq`, `field_vaga_empresa`, `field_empresa_u`, `field_regime_t`, `field_cursos_t`, `field_text_simple` (salário), `field_vaga_carga_horaria`, `field_vaga_periodo`, `field_vaga_quantidade`, `field_vaga_destaque`, `field_cidade`, `field_bairro`, `field_estados`, `field_escolaridade`, `field_horarios`, `field_auxilio_transporte`, `status=1`.

## Assets

`modules/custom/custom_configs/assets/seed-vagas-validacao/logo-NN.png` → `public://empresa/seed/logo-NN.png` (node) e `public://pictures/seed/logo-NN.png` (user).
