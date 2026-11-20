# Contract: Renderização Refino `/quem-somos`

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-11-02  
**Consumidor**: tema `default` (Twig + CSS)  
**Produtor**: node `quem_somos`, view banners `block_quem_somos`, blocks `diferenciais_quem_somos` / `cta_v1` / Impact Numbers + Image Styles

## Objetivo

Contrato de apresentação estável para visitante em `/quem-somos` e revisão visual frente ao Figma. Não é API HTTP. Documenta apenas as **lacunas** desta feature; o restante das seções permanece sob os contratos das features `009`–`015`.

---

## 1. Banner

### DOM (trecho mídia)

```html
<div class="col-12 col-md-7 banner-quem-somos__media d-none d-md-block">
  <picture>
    <source media="(min-width: 768px)" srcset="{desktop-url}">
    <img class="banner-quem-somos__img"
         src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
         alt="…"
         loading="eager"
         fetchpriority="high">
  </picture>
</div>
```

| Regra | Valor |
|-------|--------|
| Viewport `<768px` | Coluna oculta; **nenhuma** request de arte do banner (só data-URI) |
| Viewport `≥768px` | Source desktop ativo; LCP `eager` + `fetchpriority="high"` |
| Copy / CTAs | Inalterados |

---

## 2. Sobre nós

| Propriedade | Regra |
|-------------|--------|
| `<img>` `src` | Path `/files/styles/quem_somos_sobre_nos/...` (não URI original) |
| `loading` | `lazy` |
| Decorações | `::before` anel verde + `::after` círculo laranja — CSS puro, sem markup extra |
| Gap texto↔imagem | Conforme régua Figma (±4px); texto não encosta na foto |

---

## 3. Missão / Visão

| Propriedade | Regra |
|-------------|--------|
| `background-image` | URL `/files/styles/quem_somos_missao_visao/...` |
| `background-attachment` | `fixed` se `≥768px`; `scroll` se `<768px` |
| Overlay | `::before` escuro existente; texto branco legível |
| `min-height` | `380px` |

---

## 4. Nossos Diferenciais

| Propriedade | Regra |
|-------------|--------|
| Ícones | Arquivos finais (não provisórios `icon-N.png` quando assets entregues) |
| `src` | `/files/styles/diferencial_simples_icon/...` + `loading="lazy"` |
| Caixa ícone desktop | `80×80px` (`≥768px`) |
| Caixa ícone mobile | `64×64px` (`<768px`) |
| Grid | 2 cols mobile / 4 cols desktop; título+subtexto centralizados |

---

## 5. Impact in Numbers

Sem mudança de contrato visual. Regressão proibida:

| Token | Valor |
|-------|--------|
| max-width | `1280px` |
| padding | `64px 40px` |
| fundo | `#0F172A` |
| número | `#FD7B1A` |
| texto | `#FFFFFF` |

---

## 6. CTA v1 (Quem Somos)

Atualização do contrato `015` para o botão secundário:

| Propriedade | Antes (015) | Depois (047) |
|-------------|-------------|--------------|
| Secundário | fundo `#FFFFFF`, borda ausente/sutil | fundo `#FFFFFF` + **`border: 1px solid #0F172A`** |
| Primário | `#FD7B1A` / branco | **inalterado** |
| Card | `#D3E4FE`, radius `32px`, pad `64px`, max `1200px` | **inalterado** |

Escopo CSS: somente `.block-cta-v1` (não `--vagas` / `--para-estudantes`).

---

## 7. Performance geral

| Imagem | loading | Derivativo |
|--------|---------|------------|
| Banner hero (desktop) | `eager` + `fetchpriority="high"` | (política banner existente; sem novo style nesta feature) |
| Demais `<img>` da página | `lazy` | Image Style obrigatório |

Tipografia: tokens locais por seção (`--bqs-*`, `--sin-*`, hex documentados) — **não** `--brand-orange`.

---

## 8. Fallbacks

| Estado | Comportamento |
|--------|---------------|
| Ícone diferencial vazio | Card sem imagem quebrada; grid intacto |
| `field_imagem` / `field_imagem_desktop` vazios | Seção omite mídia / condicional já existente; sem erro |
| Mobile + parallax | `scroll`; sem jank imposto por `fixed` |
| Hook reexecutado | No-op; editorial divergente preservado |
