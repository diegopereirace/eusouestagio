# Research: Refino Visual "Quem Somos" — Conformidade com Figma

**Data**: 2026-11-02 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **fidelidade ao Figma**, **performance (sem request inútil no mobile)** e **deploy repetível** (`cim` → `updb` → `cim` → `cr`), sem regressão das features `009`–`015`.

---

## R1 — Banner mobile: zero request de imagem

**Decision**: Combinar (a) classe Bootstrap `d-none d-md-block` na coluna `.banner-quem-somos__media` e (b) `<picture>` com `<source media="(min-width: 768px)" srcset="{{ media.desktop }}">` + `<img src="data:image/gif;base64,…1x1…" alt="…" loading="eager" fetchpriority="high">`. Remover o `<source media="(max-width: 767.98px)">` atual (que força download da arte mobile).

**Rationale**: FR-004 / SC-001. `display: none` sozinho **não** impede o browser de baixar um `<img loading="eager">`. Com source só a partir de `768px`, viewports menores caem no placeholder data-URI (bytes negligíveis) e a coluna não ocupa layout. Em desktop o source casa → arte LCP preservada.

**Alternatives considered**:
- Só `d-none d-md-block` — rejeitado (ainda gera request; falha SC-001).
- Detecção de UA no servidor — rejeitado (frágil, cache, bots).
- Carregar imagem via JS após `matchMedia` — rejeitado (atrasa LCP desktop; JS novo fora de escopo).
- Manter source mobile + hide — rejeitado (contradiz “nenhuma requisição”).

---

## R2 — Image Styles dedicados (3)

**Decision**: Criar três estilos exportáveis:

| Machine name | Efeito | Dimensão | Uso |
|--------------|--------|----------|-----|
| `quem_somos_sobre_nos` | `image_scale_and_crop` | 864×538 | foto `field_imagem` (~432×269 @2x) |
| `quem_somos_missao_visao` | `image_scale` (width) | 1920 × auto | fundo `field_imagem_desktop` |
| `diferencial_simples_icon` | `image_scale` | 160×160 | ícones `field_image` (80×80 @2x) |

**Rationale**: FR-002 / FR-006 / FR-012 / FR-014. Estilos core genéricos (`large`/`wide`) não batem com as caixas do Figma e acoplariam outros displays.

**Alternatives considered**: Responsive Image com múltiplos breakpoints — overkill para esta página; reusar `cto`/`banner_*` — dimensões e crops errados.

---

## R3 — Aplicar Image Style no Twig/preprocess (não só no formatter)

**Decision**: Em `node--quem-somos.html.twig` (e/ou helper em `default.theme`), gerar URL com `\Drupal\image\Entity\ImageStyle::load(...)->buildUrl($uri)` (ou equivalente já usado no tema) para a foto Sobre nós e o `background-image` de Missão/Visão. Parallelamente, setar `image_style` nos view displays via Config API/`cex` para consistência admin e futuros renders via `content.*`.

**Rationale**: O Twig atual bypassa o formatter (`file_url(uri)` / inline style). Só alterar o YAML do display **não** muda a URL renderizada hoje.

**Alternatives considered**: Refatorar para `{{ content.field_imagem }}` — possível, mas risco maior de markup/CSS; fora do mínimo para fechar a lacuna. Manter URI cru — viola FR-002.

---

## R4 — Parallax: `fixed` desktop / `scroll` mobile

**Decision**: Em `quem-somos-missao-visao.css`:

```css
.quem-somos-missao-visao { background-attachment: scroll; }
@media (min-width: 768px) {
  .quem-somos-missao-visao { background-attachment: fixed; }
}
```

Manter `background-size: cover`, `background-position: center`, overlay `::before`, `min-height: 380px`.

**Rationale**: FR-009 / FR-010 / SC-003. Safari iOS e vários Android tratam `background-attachment: fixed` com jank ou scroll travado — fallback `scroll` é prática padrão, não regressão.

**Alternatives considered**: Parallax JS (`transform` on scroll) — rejeitado (JS novo, complexidade). `fixed` em todos os viewports — rejeitado (risco mobile).

---

## R5 — Ícones finais + caixa 80/64 + display

**Decision**:
- Versionar PNGs finais em `modules/custom/custom_configs/assets/diferenciais-quem-somos/` (substituir `icon-1.png`…`icon-8.png` quando disponíveis).
- Hook `11069` copia → `public://` e atualiza `field_image` dos 8 paragraphs **somente se** vazios ou URI contém o nome provisório `icon-N.png`.
- View display: `image_style: diferencial_simples_icon`.
- CSS: caixa `80×80` em `≥768px`, `64×64` em `<768px`.

**Rationale**: FR-013–016 / SC-004. Idempotência e preservação editorial alinhadas a `drupal-deploy-configs`.

**Alternatives considered**: Forçar overwrite sempre — rejeitado (quebra editorial). Manter 64px fixo — rejeitado (Figma admite até 80 no desktop). Se assets finais ausentes — Assumption: só Image Style + tamanho CSS.

---

## R6 — CTA secundário outline isolado

**Decision**: Em `cta-v1.css` sob `.block-cta-v1 .cta-v1__btn--secondary`: `border: 1px solid #0F172A` (manter fundo `#FFFFFF` e texto `#0F172A`). Não alterar `cta-v1-vagas.css` nem `cta-v1-para-estudantes.css`.

**Rationale**: FR-018 / FR-019 / SC-006 / SC-009. O card Quem Somos já está correto desde `015`; só falta o contorno.

**Alternatives considered**: Reusar `.ui-btn--outline` / `.btn-outline-azul-escuro` — risco de tokens/hover divergentes e vazamento visual. Outline em todos os CTA v1 do site — fora de escopo.

---

## R7 — Hook `11069` e receita de deploy

**Decision**: `custom_configs_update_11069` idempotente: (1) ensure Image Styles; (2) ensure `image_style` nos displays alvo; (3) sync ícones assets→public + paragraphs condicionais; (4) preencher textos vazios do node `quem_somos` apenas. Origem: `drush cex`. Destino: `cim` → `updb` → `cim` → `cr`.

**Rationale**: FR-020–023; regra permanente `drupal-deploy-configs` (2ª passada de `cim` após `updb` para displays/UUIDs).

**Alternatives considered**: Só `cex` sem hook — ícones/arquivos em `public://` e conteúdo não viajam. Só hook sem `cex` — Image Styles não versionados. Admin manual em prod — proibido.

---

## R8 — Sem pipeline SCSS

**Decision**: Continuar CSS plano em `assets/css/*.css` com custom properties locais por seção.

**Rationale**: Assumption da spec; tema não tem Sass/Gulp/Webpack. “SCSS limpo” do pedido verbal = CSS organizado, baixo aninhamento, tokens locais.

**Alternatives considered**: Introduzir Sass — mudança de infraestrutura fora do essencial desta correção visual.

---

## Resolução de clarificações

Nenhum `[NEEDS CLARIFICATION]` na spec. Itens abertos de implementação (fornecimento dos PNGs finais) tratados como Assumption com fallback seguro — não bloqueiam o plan.
