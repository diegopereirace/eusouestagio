# Contract: Renderização CTA v1 (Quem Somos)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-22  
**Consumidor**: tema `default` (Twig)  
**Produtor**: `block_content` bundle `cta_v1` + placement `default_ctav1quemsomos`

## Objetivo

Contrato de apresentação estável para visitante em `/quem-somos` e para revisão visual frente ao Figma (CTA card). Não é API HTTP.

## Estrutura DOM esperada

```html
<div class="block block-cta-v1 ...">
  <div class="cta-v1">
    <div class="cta-v1__copy">
      <h2 class="cta-v1__title">Seu próximo estágio começa aqui.</h2>
      <p class="cta-v1__subtitle">Junte-se a milhares de estudantes…</p>
    </div>
    <div class="cta-v1__actions d-grid gap-2 d-md-flex justify-content-md-center gap-md-3">
      <a class="cta-v1__btn cta-v1__btn--primary" href="/vagas">Buscar vagas</a>
      <a class="cta-v1__btn cta-v1__btn--secondary" href="/cadastro/candidato">Cadastrar gratuitamente</a>
    </div>
  </div>
</div>
```

Notas:
- Classe de escopo obrigatória: `block-cta-v1` (FR-007).
- Card contido (max-width ~1200px), conteúdo centralizado.
- Gap visual entre grupo de textos e grupo de botões: `24px`.
- Omitir título/subtítulo/botão quando o campo correspondente estiver vazio (ou botão sem URI).
- Se não houver nenhum conteúdo utilizável → **não** renderizar o card.
- Labels de bloco Drupal ocultos (`label_display: 0`).
- Posição na página: após Impact in Numbers (weight 11); placement weight 12.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente seletores sob `.block-cta-v1` |
| Library | `default/cta_v1` → `assets/css/cta-v1.css` |
| Fundo card | `#D3E4FE` |
| Border-radius | `32px` |
| Padding | `64px` (todos os lados) |
| Max-width | `1200px`, centralizado (não full-bleed) |
| Título | Poppins, semibold/bold, `#0F172A` |
| Subtítulo | Poppins, `#45464D`, hierarquia inferior ao título |
| Primário | fundo `#FD7B1A`, texto branco |
| Secundário | fundo `#FFFFFF`, texto escuro, borda ausente/sutil |
| Gap copy↔actions | `24px` |

**Proibido**: depender de `.ui-btn--primary` global (`#e55a24`) para o primário deste bloco.

## Contrato responsivo

| Viewport | Layout |
|----------|--------|
| ≥768px (`md+`) | botões lado a lado, centralizados |
| ≤575.98px | botões empilhados; sem overflow-x causado pelo bloco |

## Contrato de navegação (seed)

| Botão | Destino |
|-------|---------|
| Buscar vagas | `/vagas` |
| Cadastrar gratuitamente | `/cadastro/candidato` |

URLs limpas; texto/URI editáveis pelo editor após o seed.

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Título vazio | omitir título |
| Subtítulo vazio | omitir subtítulo |
| Botão sem URI | omitir esse botão |
| Sem botões | omitir `.cta-v1__actions` |
| Sem textos e sem botões | **não** renderizar o card |
| Erro Twig | proibido — página deve permanecer estável |

## Contrato de convivência

| Item | Regra |
|------|--------|
| Banner 009 | sem alteração |
| Sobre nós 010 | sem alteração |
| Missão/Visão 012 | sem alteração |
| Diferenciais 013 | sem alteração (weight 10) |
| Impact in Numbers 014 | sem alteração (weight 11); CTA aparece **depois** |
| Bloco CTO (home) | sem alteração de markup/CSS/campos |
| Outras rotas | bloco **não** aparece fora de `/quem-somos` |

## Dados mínimos para “seção completa” (aceitação)

| Campo | Valor |
|-------|--------|
| Título | Seu próximo estágio começa aqui. |
| Subtítulo | Junte-se a milhares de estudantes e encontre a oportunidade que vai mudar sua carreira. |
| Primário | Buscar vagas → /vagas |
| Secundário | Cadastrar gratuitamente → /cadastro/candidato |

## Não garantido por este contrato

- Conteúdo editorial final após o editor alterar o seed
- Presença do bloco em ambientes sem `cim`/`updb` aplicados
- Paridade pixel-perfect fora dos tokens listados (±2px de arredondamento no gap)
