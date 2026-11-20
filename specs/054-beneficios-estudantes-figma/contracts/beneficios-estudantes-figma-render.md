# Contract: Renderização — Benefícios Estudantes (Figma split)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-11-03  
**Consumidor**: tema `default` (Twig + CSS)  
**Produtor**: `block_content` bundle `beneficios_estudantes` + paragraphs `card_icon_text_p`

## Objetivo

Contrato de apresentação estável para a seção Benefícios em `/para-estudantes` após o layout Figma. Substitui o contrato visual de 4 cards centrados de `022`/`053` **nesta seção**. Não é API HTTP.

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
            <h2 class="be-header__title">…field_text_simple…</h2>
            <p class="be-header__subtitle">…field_text_simple_long…</p>
          </header>

          <div class="row be-grid">
            <!-- N × card_icon_text_p -->
            <div class="col-12 col-md-6 …">
              <div class="be-item d-flex …">
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
            <a class="be-cta__btn" href="/cadastro/candidato">Cadastre-se gratuitamente</a>
          </div>
        </div>

        <!-- desktop ≥992; omit empty / oculto &lt;992 -->
        <div class="be-split__visual col-…">
          <div class="be-collage">
            <img … loading="lazy" alt="…">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
```

Notas:
- Classe raiz obrigatória: `block-beneficios-estudantes`.
- Cabeçalho **alinhado à esquerda** (não `text-center` da era 022/053).
- Preferir classes `.be-item*` (lista); não depender de `.be-card` com chrome de card.
- Preservar `attributes` / `content_attributes` / title hooks do bloco.
- **Não** reutilizar classes `.block-diferenciais-quem-somos` / `.dqs-*` / `.block-nossos-diferenciais` / `.nd-*` / `.pd-*`.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente seletores sob `.block-beneficios-estudantes` |
| Library | `default/beneficios_estudantes` → `assets/css/beneficios-estudantes.css` |
| Tipografia | Poppins; título seção alto contraste; subtítulo/itens cinza legível |
| Badge | rótulo pequeno / uppercase ou peso conforme Figma; sem card |
| Itens | ícone à esquerda do texto; sem borda/fundo/min-height de card |
| CTA | botão laranja (`var(--brand-orange)`), texto branco; omit empty |
| Colagem | arte composta; não sobrepor texto; lazy |

**Proibido**: alterar estilos de banner, Hero, jornada, CTA final, PE, QS ou home via seletores desta feature.

## Contrato responsivo

| Viewport | Conteúdo | Itens | Colagem |
|----------|----------|-------|---------|
| ≥992px | coluna esquerda do split | 2 colunas | visível à direita |
| ≥768px e &lt;992px | largura plena | 2 colunas | ocultada |
| &lt;768px | largura plena | 1 coluna | ocultada |

Sem scroll horizontal causado pela seção.

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Sem badge/título/subtítulo/itens/CTA/colagem | seção mínima sem erro |
| Zero itens | cabeçalho (+ CTA/colagem se existirem) |
| Item sem ícone | título + texto legíveis |
| CTA vazio | omitir botão |
| Colagem vazia / arquivo missing | omitir visual; texto intacto |
| &gt;6 itens editoriais | renderizar todos na ordem |

## Fora deste contrato

- Banner, Hero Search, jornada, `block_3`, CTA final `cta_v1`.
- Edição individual das fotos da órbita no painel.
- Markup legado obrigatório de 4 cards centrados / `.be-card` com chrome.
