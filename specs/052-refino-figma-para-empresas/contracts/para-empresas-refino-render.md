# Contract: Renderização Refino `/para-empresas`

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-11-03  
**Consumidor**: tema `default` (Twig + CSS + JS)  
**Produtor**: View banners PE, bloco `nossos_diferenciais`, View depoimentos, bloco `cta_v1` PE + role do usuário

## Objetivo

Contrato de apresentação estável para visitante em `/para-empresas`. Não é API HTTP. Documenta as **lacunas** desta feature; composição completa permanece sob `019` / `020` / `042`.

---

## 1. Banner (`#banner-carousel-para-empresas`)

### Markup (essencial)

```html
<div id="banner-carousel-para-empresas"
     class="carousel slide banner-carousel banner-carousel--cinematic banner-para-empresas__carousel …"
     data-bs-ride="carousel" data-bs-interval="7000"
     data-bs-touch="true" data-bs-pause="hover">
  … slides <picture> + img …
  … controls + indicators se multi …
</div>
<div class="banner-para-empresas__ctas d-flex gap-3 …">
  <a class="…__btn--primary" href="/cadastro/empresa">Cadastrar Empresa</a>
  <a class="…__btn--secondary" href="/painel/empresa/vagas/nova">…</a>
</div>
```

| Regra | Valor |
|-------|--------|
| Library | `default/banner_para_empresas` **depende** de `banner_carousel_js` + `banner_carousel_cinematic` (sem CSS layout da home) |
| Classe `banner-carousel` | Presente no `.carousel` multi-slide para o behavior |
| Classe `banner-carousel--cinematic` | Mesma transição da home (máscara + ken burns) |
| Setas | Ícones PNG sem disco/fundo laranja (paridade home) |
| Transição / setas / indicators | Paridade perceptível com home |
| `<768px` CTAs | Larguras iguais (±2px); lado a lado com gap ou empilhados com gap uniforme |
| `≥768px` CTAs | Overlay/posição desktop preservados |
| Imagens | URI do conteúdo; **proibido** placeholder `500×600` |
| 1º slide | `loading="eager"` + `fetchpriority="high"`; demais `lazy` |
| Escopo CSS região | `body.path-para-empresas` (não `node--type-para-empresas`) |

**Regressão proibida na home**: classes/CSS de `.home-hero` / `banner-carousel.css` inalterados em comportamento visual.

---

## 2. Nossos Diferenciais (`.block-nossos-diferenciais`)

| Propriedade | Regra |
|-------------|--------|
| `.nd-header__subtitle` color | `#4E4E4E` |
| `.nd-item__desc` color | `#4E4E4E` |
| Títulos itens | Cores cicláveis brand (verde/navy/laranja) — **inalteradas** |
| Desktop camadas | Anel verde `z-index` atrás da foto; dot navy na frente |
| Alinhamento desktop | Topo imagem ↔ topo texto/lista (±4px) |
| `<768px` | Decos absolutos ocultos; imagem acima dos textos; **0** scroll-x causado pela seção |

---

## 3. Depoimentos (`.depoimentos-empresas`)

| Propriedade | Regra |
|-------------|--------|
| Clique bullet N | Card central = depoimento N |
| Estado ativo | Classe `is-active` + `aria-current` no bullet N |
| Cor ativo | `var(--brand-orange)` (distinto de `#c5cbd3` inativo) |
| Swipe | Após gesto, bullet ativo sincroniza com índice lógico |
| N = 1 | Sem dots ou único ativo; sem erro JS |

---

## 4. CTA v1 PE (`default_ctav1paraempresas`)

Template: `block--default-ctav1paraempresas.html.twig`  
Preprocess: `is_empresa` boolean.

| Perfil | Botão 1 (esq.) | Botão 2 (dir.) |
|--------|----------------|----------------|
| Anônimo | Link `field_link` (cadastro) | **Ausente do DOM** |
| Auth sem `empresa` | Link cadastro | **Ausente do DOM** |
| Auth com `empresa` | Controle desabilitado “Empresa Cadastrada” (estilo cinza-alaranjado fosco, não navegável) | Link `field_link_2` se URI não vazia (“Abrir Vaga” → rota canônica) |
| `field_link_2` vazio | — | Ausente mesmo para empresa (fail-safe) |

| Propriedade | Regra |
|-------------|--------|
| `<img>` no CTA (se existir) | `loading="lazy"` |
| Escopo CSS disabled | Só instância/override PE — não alterar CTA Quem Somos / vagas / estudantes |

---

## 5. Seções fora de contrato desta feature

Metodologia, O Que Fazemos, Benefícios PE, header, footer: **regressão funcional/visual proibida**; sem mudança de contrato.
