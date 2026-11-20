# Contract: render seção Benefícios no detalhe da vaga (036)

## Detalhe vaga (`node--vagas--full.html.twig`, seção `.vaga-detalhe__beneficios`)

| Elemento | Fonte | Notas |
|----------|-------|-------|
| Título da seção | `'Benefícios'|t` | `<h2 class="vaga-detalhe__section-title">` — só renderiza se houver ao menos 1 benefício válido |
| Grade de cards | `node.field_vaga_beneficios` | `<div class="vaga-detalhe__beneficios-grid row g-3">` — `col-6 col-lg-3` por card (2 cols mobile / 4 desktop) |
| Card | paragraph `beneficio_vaga_p` | `<div class="vaga-detalhe__beneficio-card">` — fundo branco, borda `--vd-border`, `border-radius: 12px`, padding `1.25rem 1rem`, flex column centered |
| Ícone | `ben.field_image.entity.uri.value` | `<img>` 48×48 `object-fit: contain` no container `.vaga-detalhe__beneficio-icon` (3rem); fallback `<i class="fa-solid fa-gift">` se vazio |
| Título | `ben.field_text_simple.value` | `<p class="vaga-detalhe__beneficio-title">` — `0.9rem` semibold `var(--vd-navy)` |
| Renderização condicional | `has_beneficios` (Twig) | Seção só aparece se `node.field_vaga_beneficios` não for vazio; card só aparece se `ben_title or ben_icon` |

## Ordem dos 4 cards padrão (após backfill)

1. Plano de Carreira — ícone mala de couro (`beneficios-01.png`)
2. Vale Transporte — ícone maleta azul (`beneficios-02.png`)
3. Vale Refeição — ícone talheres (`beneficios-03.png`)
4. Horário Flexível — ícone relógio (`beneficios-04.png`)

## Comportamento de edge cases

- Vaga com coleção parcial (1–3 itens editoriais): seção renderiza apenas os itens existentes; **não** recebe complemento automático.
- Card com ícone ausente mas título presente: renderiza card com fallback `fa-gift`.
- Card com título ausente mas ícone presente: renderiza card só com ícone.
- Card sem ícone nem título: omitido do grid (Twig `if ben_title or ben_icon`).
- Vaga sem nenhum benefício válido: seção inteira omitida (`has_beneficios` falso) — após backfill, este caso não ocorre em vagas existentes; só em novas se o editor tentar salvar sem benefícios (bloqueado por `required: true`).

## CSS (`.vaga-detalhe__beneficio-*` em `vagas-detalhe.css`)

```css
.vaga-detalhe__beneficio-card {
  background: #fff;
  border: 1px solid var(--vd-border);
  border-radius: 12px;
  padding: 1.25rem 1rem;        /* bump de 1rem 0.75rem */
  text-align: center;
  height: 100%;
  min-height: 7rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.vaga-detalhe__beneficio-icon {
  width: 3rem;                  /* bump de 2.5rem */
  height: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--vd-navy);
  font-size: 1.5rem;
}

.vaga-detalhe__beneficio-icon img {
  max-width: 48px;              /* bump de 32px */
  max-height: 48px;
  object-fit: contain;
}

.vaga-detalhe__beneficio-title {
  margin: 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--vd-navy);       /* bump de --vd-text */
}
```

## Twig (ajuste `width/height` dos `<img>`)

Em `node--vagas--full.html.twig` (linha 152) e `paragraph--beneficio-vaga-p.html.twig` (linha 23):

```twig
<img src="{{ file_url(ben_icon) }}" alt="" width="48" height="48" loading="lazy">
```

(bump de `32` para `48` — hint de layout para evitar reflow; CSS continua controlando o tamanho real via `max-width/max-height`.)
