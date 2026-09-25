# Contract: Composição `/para-empresas`

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-25  
**Consumidor**: visitante anônimo + editor  
**Produtor**: placements tema `default` + nó shell `para_empresas`

## Objetivo

Ordem vertical estável da landing B2B e regras de exclusividade de rota. Complementa [banner-para-empresas-render.md](banner-para-empresas-render.md).

## Ordem de composição

```text
1. região banner     → views_block banners-block_para_empresas
2. content_full w0   → nossos_diferenciais (instância PE)
3. content_full w1   → nossa_metodologia (instância PE)
4. content_full w2   → o_que_fazemos_bt (instância PE)
5. content_full w3   → diferenciais_quem_somos “Benefícios para Empresas”
6. content_full w4   → cta_v1 “Pronto para contratar os melhores talentos?”
7. rodapé            → inalterado
```

## Exclusividade

| Placement / bloco | `/para-empresas` | Home | Quem Somos | Outras |
|-------------------|------------------|------|------------|--------|
| `block_para_empresas` | sim | não | não | não |
| Instâncias PE (5 blocos) | sim | não | não | não |
| Instâncias home 004–006 | não | sim | não | não |
| `default_ctoparaempresas` | **não** (status false) | — | — | — |
| `banners-block_1` | **não** | — | — | `/para-estudantes` |

## Nó shell

- Alias `/para-empresas` permanece.
- Fields de conteúdo legado vazios após `11024`.
- Twig do node **não** reintroduz “Olá, empresa!” / “Por que anunciar aqui?” nem grade `field_itens_p` se vazia.
- Título Drupal: comportamento atual do tema (bundle `para_empresas` já fora do `page_title` tipicamente restrito a `page`).

## CTA final (`cta_v1`)

| Campo | Seed |
|-------|------|
| Título | Pronto para contratar os melhores talentos? |
| Primário | Cadastrar Empresa → `/cadastro/empresa` |
| Secundário | Abrir Vaga → `/painel/empresa/vagas/nova` |

CSS: library/classes existentes `.block-cta-v1` (015); sem alterar visual de Quem Somos além da convivência de CSS compartilhado do tipo.

## Deploy

Fluxo: `cim` → `updb` (`11024`) → `cim` → `cr`.  
Segunda `updb`: sem duplicar banners/blocos; sem reintroduzir fields limpos se o editor os mantiver vazios (e sem sobrescrever editorial preenchido após seed).
