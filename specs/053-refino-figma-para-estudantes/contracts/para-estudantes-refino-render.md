# Contract: Renderização Refino `/para-estudantes`

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-11-03  
**Consumidor**: tema `default` (Twig + CSS + JS)  
**Produtor**: View banners estudantes, plugin Hero de vagas (segundo placement), bloco `beneficios_estudantes`, placement desabilitado do perfil, bloco `cta_v1` estudantes

## Objetivo

Contrato de apresentação estável para o visitante em `/para-estudantes`. Não é API HTTP. Documenta as **lacunas** desta feature; jornada e destaque `block_3` permanecem como em `023` / `025`.

Ordem visual alvo:

1. Banner (região `banner`)
2. Hero Search (região `highlighted`, renderizada entre banner e `content_full` só nesta rota)
3. Benefícios
4. Jornada e destaque de vagas (inalterados)
5. CTA final encostado no footer

---

## 1. Banner (`#banner-carousel-para-estudantes`)

### Markup (essencial)

```html
<div id="banner-carousel-para-estudantes"
     class="carousel slide banner-carousel banner-carousel--cinematic banner-para-estudantes__carousel …"
     data-bs-ride="carousel" data-bs-interval="7000"
     data-bs-touch="true" data-bs-pause="hover">
  … picture + img (1º slide eager / fetchpriority high) …
  … controls + indicators somente se multi …
</div>
<div class="banner-para-estudantes__ctas">
  <a class="…__btn--primary" href="/para-estudantes#main-content">Encontrar minha vaga</a>
  <a class="…__btn--secondary" href="/cadastro/candidato">Criar meu perfil</a>
</div>
```

Um único slide: **sem** setas nem indicators; classe `banner-carousel--single` (o behavior JS ignora esse caso).

### Comportamento

| Viewport | CTAs | Carrossel |
|----------|------|-----------|
| ≥768px | Overlay, canto inferior esquerdo | Transição cinematográfica + pause on hover quando multi |
| &lt;768px | Fora da arte, larguras iguais (±2px), lado a lado; &lt;~380px empilhados com largura plena igual | Setas/indicadores ainda só se multi |

### Fora deste contrato

- Não anexar `banner-carousel.css` da home.
- Não alterar Twig/CSS/library de `block_para_empresas`.
- Não injetar placeholder `500×600`. Slide sem `field_imagem_desktop` continua omitido no preprocess.

---

## 2. Hero Search

Mesmo tema hook `custom_banners_vagas_hero_search` e o mesmo markup de `/vagas` ([specs/027-vagas-hero-search](../../027-vagas-hero-search/contracts/vagas-hero-search-render.md) + autocomplete `048`).

| Item | Contrato |
|------|----------|
| Onde | Dentro de `.highlighted > aside.container.section`, imediatamente após `.banner` |
| Título | `Encontre a oportunidade ideal para sua carreira.` |
| Subtítulo | `Explore milhares de vagas de estágio em empresas parceiras e dê o próximo passo na sua jornada profissional.` |
| Campos | `title`, `cidade`, `cursos` + botão Buscar Vagas + pills de curso |
| Submit | `method="get"` `action` = URL de `view.vagas.page_1` (alias `/vagas`) |
| Placement | `default_custom_banners_vagas_hero_search_estudantes` |
| `/vagas` | Placement antigo intacto; uma página nunca renderiza os dois |

Não há endpoint novo.

---

## 3. Benefícios (`#beneficios-estudantes`)

| Viewport | Grade |
|----------|--------|
| &lt;768px | 1 coluna |
| ≥768px e &lt;992px | 2 colunas |
| ≥992px | 4 colunas, uma linha |

Card:

- Fundo `#F8F9FF`, borda `1px` `#C6C6CD` a 30% de opacidade, `border-radius: 12px`, padding `32px 32px 64px`, gap interno `12px`.
- Ícone no **topo à esquerda**, caixa ~64×64, proporção preservada.
- Título negrito, cor escura (`#0f172a`).
- Descrição `#45464D`.
- Ordem: Vagas alinhadas, Modelos de trabalho, Vários segmentos, Filtros inteligentes.
- Sem scroll horizontal causado pela seção.

Copy canônica quando o campo está vazio ou no legado conhecido: ver [data-model.md](../data-model.md). Texto editorial diferente permanece.

---

## 4. Perfil em destaque

Após o deploy, o HTML público de `/para-estudantes` **não** contém `.block-perfil-destaque-estudante` / `.section-perfil-destaque`. A entidade de conteúdo pode continuar no banco. Jornada e `block_3` continuam.

---

## 5. Junção CTA → footer

Escopo: `body.path-para-estudantes` + `#block-default-ctav1paraestudantes`.

| Medida | Alvo |
|--------|------|
| Distância entre o fim da caixa do CTA (gradiente) e o início da caixa do `.site-footer-custom` | **0px** |
| Borda branca extra | não introduzir |
| `/quem-somos` e `/para-empresas` | aparência de CTA inalterada |
| Mobile e desktop | o mesmo vão 0 |

O padding interno do footer (64px) faz parte do rodapé, não do vão.
