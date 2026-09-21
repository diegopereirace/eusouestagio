# Contract: Renderização Impact in Numbers (Quem Somos)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-21  
**Consumidor**: tema `default` (Twig)  
**Produtor**: `node` bundle `quem_somos` + paragraphs `numero_destaque_p`

## Objetivo

Contrato de apresentação estável para visitante em `/quem-somos` e para revisão visual frente ao Figma (Section 6). Não é API HTTP.

## Estrutura DOM esperada

```html
<section class="section-impact-numbers">
  <div class="section-impact-numbers__inner">
    <div class="row section-impact-numbers__grid">
      <!-- até 4 × paragraph numero_destaque_p -->
      <div class="col-6 col-md-3">
        <div class="section-impact-numbers__item d-flex flex-column align-items-center">
          <div class="section-impact-numbers__stat">20k+</div>
          <div class="section-impact-numbers__label">ESTUDANTES ATIVOS</div>
        </div>
      </div>
      <!-- … -->
    </div>
  </div>
</section>
```

Notas:
- Classe raiz obrigatória: `section-impact-numbers` (FR-007).
- Renderizar a seção **somente** se houver ≥1 item com conteúdo utilizável.
- Omitir `.section-impact-numbers__stat` / `__label` quando o campo correspondente estiver vazio.
- Omitir a coluna do item se destaque e subtexto estiverem ambos vazios.
- Preservar ordem dos paragraphs conforme cadastrado no Node.
- Posição no Node: após Missão/Visão; antes do conteúdo de regiões posteriores (`content_full` / Diferenciais 013).

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente seletores sob `.section-impact-numbers` |
| Library | `default/impact_numbers` → `assets/css/impact-numbers.css` |
| Fundo seção | `#0F172A` |
| Padding | vertical `64px`, horizontal `40px` |
| Conteúdo interno | max-width `1280px`, centralizado |
| Full-bleed | breakout para escapar `#main.container` (padrão Missão/Visão) |
| Destaque | Poppins, `font-weight: 700`, cor `#FD7B1A`, caixa alvo `56px` |
| Subtexto | cor `#FFFFFF`, `opacity: 0.8`, `text-transform: uppercase`, caixa alvo `20px` |
| Gap item | `8px` entre destaque e subtexto |
| Alinhamento item | coluna flex, centralizado |

## Contrato responsivo

| Viewport | Layout |
|----------|--------|
| ≥768px (`md+`) | 4 itens em uma linha (`.col-md-3`) |
| &lt;768px | 2 itens por linha (`.col-6`); sem overflow-x causado pela seção |

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Lista vazia | **não** renderizar a seção |
| 1–3 itens | grid com colunas disponíveis; sem placeholders |
| Item parcialmente vazio | renderizar só o preenchido |
| Item totalmente vazio | não renderizar a célula |
| Erro Twig | proibido — página deve permanecer estável |

## Contrato de convivência

| Item | Regra |
|------|--------|
| Banner 009 | sem alteração |
| Sobre nós 010 | sem alteração de markup/CSS |
| Missão/Visão 012 | sem alteração; Impact in Numbers é seção **posterior** |
| Diferenciais 013 | bloco em `content_full` intacto; aparece após o Node |
| Home / outras rotas | seção **não** aparece fora de `/quem-somos` |

## Dados mínimos para “seção completa” (aceitação)

| # | Destaque | Subtexto |
|---|----------|----------|
| 1 | 20k+ | ESTUDANTES ATIVOS |
| 2 | 1.2k+ | EMPRESAS PARCEIRAS |
| 3 | 8k+ | ESTÁGIOS INICIADOS |
| 4 | 95% | SATISFAÇÃO GLOBAL |

## Não garantido por este contrato

- Conteúdo editorial final após o editor alterar o seed
- Presença da seção em ambientes sem `cim`/`updb` aplicados
- Título/subtítulo de cabeçalho da faixa (fora de escopo nesta fase)
