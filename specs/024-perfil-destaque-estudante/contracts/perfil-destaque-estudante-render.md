# Contract: Renderização — Perfil em Destaque (Estudante)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-30  
**Consumidor**: tema `default` (Twig)  
**Produtor**: `block_content` bundle `perfil_destaque_estudante` + paragraphs `item_lista_icone_p`

## Objetivo

Contrato de apresentação estável para visitante em `/para-estudantes` (Section 04, após Jornada) e para revisão visual. Não é API HTTP.

## Estrutura DOM esperada

```html
<section class="block block-block-content … section-perfil-destaque block-perfil-destaque-estudante" id="perfil-destaque-estudante">
  <div class="content">
    <div class="container pd-container"><!-- max-width 1280px; padding 64/40; fundo #FFFFFF -->
      <div class="row align-items-center g-5">
        <div class="col-12 col-lg-6">
          <div class="pd-visual" aria-hidden="true"><!-- mock HTML (não é field_image) -->
            <div class="pd-visual__stage"><!-- gradiente #4E535E → #181A1F -->
              <div class="pd-profile-card"><!-- card Habilidades + Formação -->
                …
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="pd-content"><!-- max-width texto ~528px no desktop -->
            <h2 class="pd-title">…field_text_simple…</h2>

            <div class="pd-list">
              <!-- N × item_lista_icone_p (≤3) -->
              <div class="pd-item d-flex gap-3 mb-4">
                <div class="pd-item__icon flex-shrink-0"><!-- 20×20 -->
                  <img src="…" alt="" width="20" height="20" loading="lazy">
                </div>
                <div class="pd-item__body">
                  <h3 class="pd-item__title">…titulo…</h3><!-- cor #9D4300 -->
                  <p class="pd-item__text">…descricao…</p><!-- cor #45464D -->
                </div>
              </div>
              <!-- … -->
            </div>

            <a class="pd-cta btn …" href="/painel/estudante/perfil">Completar meu perfil</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
```

Notas:
- Classes raiz obrigatórias: `section-perfil-destaque` e/ou `block-perfil-destaque-estudante` (FR-008).
- Coluna esquerda é sempre renderizada (mock HTML/CSS); não depende de `field_image` no bloco.
- Omitir `h2` / ícone / título do item / descrição / CTA quando vazios.
- Preservar `attributes` / `content_attributes` / title hooks do template de bloco.
- **Não** reutilizar classes `.block-beneficios-estudantes` / `.be-*` / `.block-jornada-estudante` / `.je-*`.
- Heading do item: preferir `h3` — não `h2` duplicado.
- CTA: rótulo = título do `field_link`; href = URI do link.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente seletores sob `.section-perfil-destaque` / `.block-perfil-destaque-estudante` |
| Library | `default/perfil_destaque_estudante` → `assets/css/perfil-destaque-estudante.css` |
| Seção | fundo `#FFFFFF`; container `max-width: 1280px`; paddings `64px` / `40px` |
| Tipografia | Poppins; título seção max-width `528px` (desktop) |
| Ícone item | `20px × 20px`; `flex-shrink-0` |
| Título item | negrito/semibold; `#9D4300` (ou tom exato do check Figma) |
| Descrição item | `#45464D` |
| CTA | ~`208×44`; fundo `#023C62`; texto `#FFFFFF`; raio alinhado ao design system |

**Proibido**: alterar estilos do hero estudantes, benefícios, jornada, PE, home ou rodapé via seletores globais desta feature.

## Contrato responsivo

| Viewport | Layout |
|----------|--------|
| ≥992px (`lg`) | duas colunas lado a lado (`.col-lg-6` + `.col-lg-6`), alinhadas ao centro vertical |
| &lt;992px | colunas empilham (`.col-12`); mock visual acima, conteúdo abaixo; sem overflow-x causado pelo bloco |
| ≤575.98px | mesma pilha; `.pd-visual__stage` escala sem estourar container |

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Sem título e zero itens e sem CTA | seção mínima **sem erro** |
| Ilustração ausente | N/A — coluna esquerda é mock HTML fixo |
| Zero itens | mock visual + cabeçalho/CTA quando preenchidos |
| &lt; 3 itens | lista com disponíveis |
| Item sem ícone / título / descrição | omitir só o ausente; flex permanece |
| CTA vazio | omitir botão |
| Tentativa &gt; 3 no CMS | impedida (form/validação) |

## Fora do contrato

- Markup/CSS do hero `021`, benefícios `022`, jornada `023`, View `vagas`
- Ajuste do fluxo de login do painel
- Layout Builder
