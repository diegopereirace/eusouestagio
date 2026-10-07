# Contract: Render — Header do Detalhe da Vaga (Empresa)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-10-07

## Superfície

| Item | Valor |
|------|--------|
| Entity | Node `vagas`, view mode `full` |
| Template | `node--vagas--full.html.twig` (bloco `<header class="vaga-detalhe__header">`) |
| Root CSS | `.vaga-detalhe` / `.vaga-detalhe__header*` |
| Library | `default/vagas_detalhe` |

## Dados do header

| Slot | Fonte | Omitir se |
|------|--------|-----------|
| Logo | `field_vaga_empresa` → node `empresa` → `field_imagem` | sem empresa / sem imagem / inacessível |
| H1 | `label` / title da vaga | — (sempre) |
| Nome empresa | label do node empresa | sem empresa acessível |
| Badge verificado | markup fixo (ícone) | sem empresa |
| Localização | `field_cidade` + `field_estados` + ícone pin | ambos vazios; omitir `•` órfão |
| Pill regime | label(s) `field_regime_t` | vazio |
| Pill carga | `field_vaga_carga_horaria` | vazio |
| Pill bolsa | `field_text_simple` | vazio |
| Pill postado | preprocess `vaga_postado_ha` (pt-BR) | nunca negativo; &lt;1h = “menos de 1 hora” |

**Proibido no header:** ler `field_empresa_u` / `user_picture` como fonte canônica; usar `field_horarios` como pill de carga; hardcode “EcoConstrutora” no markup.

## Layout visual

| Token | Valor alvo |
|-------|------------|
| Card | fundo branco, border sutil, radius ~16px, padding generoso |
| Logo | 96×96, `object-fit: cover`, radius ~12px |
| Desktop | logo à esquerda; textos + pills à direita / abaixo do título |
| Mobile ≤576px | empilha sem overflow-x; título legível |
| Subtítulo | `Nome` + verificado + `•` + pin + `Cidade, UF` (partes opcionais) |
| Pills | até 4; cantos fully rounded; fundo azul muito claro |

## Isolamento / split Twig (intenção implementada)

| Bloco | Fonte |
|-------|--------|
| `<header class="vaga-detalhe__header">` | **somente** `field_vaga_empresa` → node `empresa` (publicado) + pills regime/carga/bolsa/postado |
| Seção “Sobre a Empresa” | `field_empresa_u` (user legado) até feature de migração |

| Mantém 031 | Não alterar nesta feature |
|------------|---------------------------|
| Grid 8/4, Sobre, Requisitos, Benefícios, FAQ, sidebar, CTA bloco | Listagem `/vagas`, cards laranja, Hero Search |
| Seção “Sobre a Empresa” via `field_empresa_u` | Rota pública `/empresa/*` |

## Acessibilidade mínima

| Regra | Valor |
|-------|--------|
| H1 | um por página (título da vaga) |
| Logo | `alt` descritivo (nome da empresa) ou vazio decorativo se nome já no texto |
| Ícones | `aria-hidden="true"` quando puramente decorativos |
