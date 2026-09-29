# Contract: Renderização — Benefícios Para Estudantes

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-29  
**Consumidor**: tema `default` (Twig)  
**Produtor**: `block_content` bundle `beneficios_estudantes` + paragraphs `card_icon_text_p`

## Objetivo

Contrato de apresentação estável para visitante em `/para-estudantes` (seção abaixo do hero) e para revisão visual. Não é API HTTP.

## Estrutura DOM esperada

```html
<section class="block block-block-content … block-beneficios-estudantes" id="beneficios-estudantes">
  <div class="content">
    <div class="container py-5">
      <header class="be-header text-center">
        <h2 class="be-header__title mx-auto">…field_text_simple…</h2>
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <p class="be-header__subtitle mx-auto">…field_text_simple_long…</p>
          </div>
        </div>
      </header>

      <div class="row mt-5 justify-content-center be-grid">
        <!-- N × paragraph card_icon_text_p -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
          <div class="be-card text-center d-flex flex-column align-items-center h-100">
            <div class="be-card__icon">
              <img class="img-fluid" src="…" alt="…" loading="lazy">
            </div>
            <h3 class="be-card__title">…field_text_simple…</h3>
            <p class="be-card__text">…field_text_simple_long…</p>
          </div>
        </div>
        <!-- … -->
      </div>
    </div>
  </div>
</section>
```

Notas:
- Classe raiz obrigatória: `block-beneficios-estudantes` (FR-008).
- Omitir `h2` / subtítulo / ícone / título do card / texto quando vazios.
- Preservar `attributes` / `content_attributes` / title hooks do template de bloco.
- **Não** reutilizar classes `.block-diferenciais-quem-somos` / `.dqs-*` / `.block-nossos-diferenciais` / `.nd-*`.
- Heading do card: preferir `h3` (ou elemento semântico equivalente) — não `h2` duplicado.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente seletores sob `.block-beneficios-estudantes` |
| Library | `default/beneficios_estudantes` → `assets/css/beneficios-estudantes.css` |
| Tipografia | Poppins; cor principal `#0F172A` |
| Título seção | centrado; `max-width: 404px` |
| Subtítulo | centrado; `max-width: 624px` |
| Card | `min-height: 310px`; borda suave / sombra leve (`.shadow-sm` ou equivalente) |
| Ícone | `.img-fluid` + `max-height`/`max-width` ≈ 64px |
| Espaçamento vertical | equivalente a `.py-5` / `.py-lg-5` no container |

**Proibido**: alterar estilos do hero estudantes, PE benefícios, home diferenciais, Quem Somos ou rodapé via seletores globais desta feature.

## Contrato responsivo

| Viewport | Layout |
|----------|--------|
| ≥992px (`lg`) | 4 cards/linha (`col-lg-3`) |
| ≥768px (`md`) | 2 cards/linha (`col-md-6`) |
| ≤575.98px | 1 card/linha (`col-12`); sem overflow-x causado pelo bloco |

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Sem título e sem subtítulo e zero cards | seção mínima **sem erro**; preferível omitir grid vazio |
| Zero cards | só cabeçalho (se preenchido) |
| Card sem ícone | título + texto legíveis |
| Card sem título | ícone + texto (sem heading vazio) |
| Card sem texto | ícone + título |
| &gt; 4 cards | todos renderizam na ordem |

## Contrato de placement / convivência

| Item | Regra |
|------|--------|
| Região | `content_full` do tema `default` |
| Pages | somente `/para-estudantes` |
| Ordem vertical | hero (`banner`, feature `021`) **acima**; esta seção em `content_full` |
| Home / PE / QS / Contato | bloco **ausente** |
| View `vagas` | inalterada |
| Instância PE `diferenciais_quem_somos` | inalterada |
