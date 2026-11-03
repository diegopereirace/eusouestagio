# Contract: Renderização — Benefícios Estudantes (Figma restaurado)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-11-03  
**Consumidor**: tema `default` (Twig + CSS)  
**Produtor**: `block_content` bundle `beneficios_estudantes` + paragraphs `card_icon_text_p`

## Objetivo

Contrato de apresentação estável para a seção Benefícios em `/para-estudantes` após o restore Figma (feature 055). Substitui a UI pública de 4 cards centrados pós-`11079` **nesta seção**. Não é API HTTP.

## Estrutura DOM esperada

```html
<section class="block … block-beneficios-estudantes" id="beneficios-estudantes">
  <div class="content">
    <div class="container py-5">
      <div class="be-split row …">
        <div class="be-split__content col-…">
          <header class="be-header text-start">
            <!-- omit empty -->
            <p class="be-header__badge">…field_text_simple_2…</p>
            <h2 class="be-header__title">
              <!-- se título canônico: -->
              <span class="be-header__title-part--dark">Muito mais do que</span>
              <span class="be-header__title-part--blue"> encontrar um estágio.</span>
              <!-- senão: texto único -->
            </h2>
            <p class="be-header__subtitle">…field_text_simple_long…</p>
          </header>

          <div class="row be-grid">
            <!-- N × card_icon_text_p -->
            <div class="col-12 col-md-6 …">
              <div class="be-item …"> <!-- card branco -->
                <div class="be-item__icon">…img lazy…</div>
                <div class="be-item__body">
                  <h3 class="be-item__title">…</h3>
                  <p class="be-item__text">…</p>
                </div>
              </div>
            </div>
          </div>

          <!-- omit se field_link vazio -->
          <div class="be-cta">
            <a class="be-cta__btn" href="/cadastro/candidato">
              Cadastre-se gratuitamente
              <!-- indicação visual de continuidade (seta) -->
            </a>
          </div>
        </div>

        <!-- ≥576; omit empty / oculto &lt;576 -->
        <div class="be-split__visual col-…">
          <div class="be-collage" aria-hidden="false">
            <div class="be-collage__ring"></div>
            <div class="be-collage__center">…img centro…</div>
            <div class="be-collage__sat be-collage__sat--1">…</div>
            <!-- … sat-2 … sat-5 -->
            <!-- fallback: <img class="be-collage__img"> se órbita indisponível -->
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
```

Notas:
- Classe raiz obrigatória: `block-beneficios-estudantes`.
- Cabeçalho **alinhado à esquerda**.
- Itens como **cards claros** (fundo branco + radius), ícone à esquerda do texto.
- Preservar `attributes` / `content_attributes` / title hooks do bloco.
- **Não** reutilizar classes `.block-diferenciais-quem-somos` / `.dqs-*` / `.block-nossos-diferenciais` / `.nd-*` / `.pd-*` / `.hero-estudantes-wrapper`.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente seletores sob `.block-beneficios-estudantes` |
| Library | `default/beneficios_estudantes` → `assets/css/beneficios-estudantes.css` |
| Fundo seção | `#eef5ff` |
| Tipografia | Poppins; título bicolor canônico `#07132E` / `#005B9F` |
| Badge | rótulo pequeno; uppercase via CSS se necessário; cor marca/laranja |
| Itens | cards brancos; gap ~16px; grade 2 colunas no desktop |
| CTA | botão laranja (`var(--brand-orange)`), texto branco, seta de continuidade; omit empty |
| Colagem | órbita (centro + 5 satélites + anel pontilhado); lazy; não sobrepor texto |

**Proibido**: alterar estilos de banner, Hero, jornada, CTA final, PE, QS ou home via seletores desta feature.

## Contrato responsivo

| Viewport | Conteúdo | Itens | Colagem |
|----------|----------|-------|---------|
| ≥992px | coluna esquerda do split | 2 colunas | órbita à direita |
| ≥576px e &lt;992px | largura plena | 1 coluna (&lt;768) / até 2 (≥768) | abaixo do conteúdo, redimensionada |
| &lt;576px | largura plena | 1 coluna | omitida |

Sem scroll horizontal causado pela seção.

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Sem badge/título/subtítulo/itens/CTA/colagem | seção mínima sem erro |
| Zero itens | cabeçalho (+ CTA/colagem se existirem) |
| Item sem ícone | título + texto legíveis no card |
| CTA vazio | omitir botão |
| Faces da órbita ausentes | fallback `collage.png` composta **ou** omitir visual; texto intacto |
| &gt;6 itens editoriais | renderizar todos na ordem |
| Título não canônico | cor única (sem split forçado) |

## Fora deste contrato

Banner, Hero Search, jornada, `block_3`, CTA final `026`, PE, QS, home, CMS foto-a-foto da órbita.
