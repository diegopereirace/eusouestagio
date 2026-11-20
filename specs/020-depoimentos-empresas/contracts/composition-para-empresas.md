# Contract: Composição `/para-empresas` (com Depoimentos)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-26  
**Consumidor**: visitante anônimo + editor  
**Produtor**: placements tema `default` + View `depoimentos_carousel`  
**Supersede parcial**: ordem `content_full` documentada em `specs/019-para-empresas-page/contracts/composition-para-empresas.md` (weights 0–4) — esta feature **insere** Depoimentos e **reindexa** o CTA.

## Objetivo

Ordem vertical estável da landing B2B após a feature 020, com exclusividade de rota.

## Ordem de composição

```text
1. região banner     → views_block banners-block_para_empresas
2. content_full w0   → nossos_diferenciais (mesma instância da home)
3. content_full w1   → nossa_metodologia (mesma instância da home)
4. content_full w2   → o_que_fazemos_bt (instância PE)
5. content_full w3   → diferenciais_quem_somos “Benefícios para Empresas”
6. content_full w4   → views_block depoimentos_carousel-block_depoimentos_empresas  ← NOVO
7. content_full w5   → cta_v1 “Pronto para contratar…” (weight atualizado 4→5)
8. rodapé            → inalterado
```

## Exclusividade

| Placement / bloco | `/para-empresas` | Home | Quem Somos | Outras |
|-------------------|------------------|------|------------|--------|
| `block_para_empresas` (banner) | sim | não | não | não |
| Placements PE w0–w3 (019) | sim | via home p/ Diferenciais/Metodologia | não | não |
| `depoimentos_carousel` block PE | **sim** | **não** | **não** | **não** |
| `default_ctav1paraempresas` | sim (w5) | não | não | não |
| Instâncias home 004–006 | não* | sim | não | não |

\*Exceto entidades Diferenciais/Metodologia compartilhadas (placements distintos).

## CTA final

Inalterado em copy/links; apenas **weight 5**. CSS `.block-cta-v1` intocado.

## Deploy

Fluxo: `cim` → `updb` (`11032`) → `cim` → `cr`.  
Segunda `updb`: sem duplicar nodes seed nem placement; sem resetar weight do CTA se já for 5.
