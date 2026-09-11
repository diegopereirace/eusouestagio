# Contract: Renderização do bloco Nossos Diferenciais

**Feature**: [spec.md](spec.md) | **Data**: 2026-09-11  
**Consumidor**: tema `default` (Twig)  
**Produtor**: `block_content` bundle `nossos_diferenciais` + paragraphs `diferencial_item_p`

## Objetivo

Contrato de apresentação estável para visitante e para revisão visual frente ao Figma. Não é API HTTP.

## Estrutura DOM esperada

```html
<section class="block-nossos-diferenciais" …>
  <div class="container">
    <header class="…">
      <h2>…field_text_simple…</h2>
      <p>…field_text_simple_long…</p>
    </header>
    <div class="row align-items-center">
      <div class="col-lg-6">
        <div class="nd-media">
          <!-- pseudo-elementos: círculo azul, anel verde, anéis cinza -->
          <img class="img-fluid" src="…" alt="…" loading="lazy">
          <!-- ou placeholder se sem imagem -->
        </div>
      </div>
      <div class="col-lg-6">
        <div class="nd-list">
          <!-- N × paragraph -->
          <div class="nd-item d-flex align-items-start gap-3">
            <div class="nd-item__icon">…img…</div>
            <div class="nd-item__body">
              <h3 class="nd-item__title">…</h3>
              <p class="nd-item__desc">…</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
```

## Tokens de estilo (contrato visual)

| Token | Uso |
|-------|-----|
| Font | Poppins (incl. weight 300 no subtítulo) |
| Título item 1,4,7… | verde (marca) |
| Título item 2,5,8… | azul-escuro |
| Título item 3,6,9… | laranja/terracota |
| Breakpoint colunas | `col-lg-6` (≥992px lado a lado) |

## Dados mínimos para “seção completa” (aceitação)

| Campo | Exemplo de referência |
|-------|------------------------|
| Título | Nossos Diferenciais |
| Subtítulo | Não conectamos apenas estudantes e empresas. … |
| Itens ≥1 | Encontramos / Gerenciamos / Desenvolvemos |

## Não garantido por este contrato

- URL canônica da página (placement é editorial/config de bloco)
- Presença de arquivos de imagem em todos os ambientes após o primeiro `cim` (seed v1 sem binários)
