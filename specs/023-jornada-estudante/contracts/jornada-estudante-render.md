# Contract: Renderização — Jornada do Estudante

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-30  
**Consumidor**: tema `default` (Twig)  
**Produtor**: `block_content` bundle `jornada_estudante` + paragraphs `passo_jornada_p`

## Objetivo

Contrato de apresentação estável para visitante em `/para-estudantes` (Section 03, após Benefícios) e para revisão visual. Não é API HTTP.

## Estrutura DOM esperada

```html
<section class="block block-block-content … block-jornada-estudante" id="jornada-estudante">
  <div class="content">
    <div class="container je-container"><!-- max-width 1280px; padding ~64/40 -->
      <header class="je-header text-center">
        <h2 class="je-header__title">…field_text_simple…</h2>
      </header>

      <div class="row justify-content-center g-4 mt-4 je-grid">
        <!-- N × passo (loop Twig: loop.index / loop.last) -->
        <div class="col-12 col-md-6 col-lg-3">
          <div class="je-card text-center d-flex flex-column align-items-center">
            <span class="je-badge"><!-- ou je-badge--last se loop.last -->1</span>
            <h3 class="je-card__title">…passo field_text_simple…</h3>
            <p class="je-card__text">…passo field_text_simple_long…</p>
          </div>
        </div>
        <!-- … último com .je-badge--last / fundo laranja -->
      </div>
    </div>
  </div>
</section>
```

Notas:
- Classe raiz obrigatória: `block-jornada-estudante` (FR-009).
- Número da badge = posição na lista (`loop.index`); **não** vem do banco.
- Último item da lista recebe estilo laranja (`loop.last` / `.je-badge--last` / `:last-child`).
- Omitir `h2` / título do passo / descrição quando vazios.
- Preservar `attributes` / `content_attributes` / title hooks do template de bloco.
- **Não** reutilizar classes `.block-beneficios-estudantes` / `.be-*`.
- Heading do passo: preferir `h3` — não `h2` duplicado.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente seletores sob `.block-jornada-estudante` |
| Library | `default/jornada_estudante` → `assets/css/jornada-estudante.css` |
| Seção | fundo full-bleed `#EFF4FF`; `width: 100%`; `margin: 0` |
| Container | `max-width: 1280px`; paddings ~`64px` / `40px` (Top/Bottom / Left/Right) |
| Tipografia | Poppins; título seção `#000000` ou `#0F172A` (~38px visual); descrição `#45464D` ~14px |
| Card | fundo `#FFFFFF`; `border-radius: 16px`; padding ~`24px`; sombra sutil; `min-height` ~`202px` |
| Badge | `48×48`; `border-radius: 50%`; número branco bold ~18px; flex centrado |
| Badge padrão | fundo `#023C62` |
| Badge último | fundo `#FD7B1A` (token local; **não** mutar `--brand-orange` global) |

**Proibido**: alterar estilos do hero estudantes, benefícios estudantes, PE, home ou rodapé via seletores globais desta feature.

## Contrato responsivo

| Viewport | Layout |
|----------|--------|
| ≥992px (`lg`) | 4 cards/linha (`col-lg-3`) |
| ≥768px (`md`) | 2 cards/linha (`col-md-6`) |
| ≤575.98px | 1 card/linha (`col-12`); sem overflow-x causado pelo bloco |

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Sem título e zero passos | seção mínima **sem erro**; preferível omitir grid vazio |
| Zero passos | só cabeçalho (se preenchido) |
| &lt; 4 passos | badges 1…N; **último** item da lista é laranja |
| Passo sem título | badge + descrição (sem heading vazio) |
| Passo sem descrição | badge + título |
| Tentativa &gt; 4 no CMS | impedida (form/validação) |

## Contrato de placement / convivência

| Item | Regra |
|------|--------|
| Região | `content_full` do tema `default` |
| Weight | `1` (após `default_beneficiosestudantes` weight `0`) |
| Pages | somente `/para-estudantes` |
| Ordem vertical | hero (`banner`, `021`) → benefícios (`022`) → **esta seção** |
| Home / PE / QS / Contato | bloco **ausente** |
| View `vagas` | inalterada |
| Bloco `beneficios_estudantes` | inalterado |
