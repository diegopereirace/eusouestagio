# Contrato de UI — Hero de busca de `/vagas`

**Feature**: 048 · **Escopo CSS/JS**: exclusivamente `.vagas-hero-search*` (nada em `.hero-search` da Home)

## 1. Estrutura Twig (`block--vagas-hero-search.html.twig`)

```text
section#vagas-hero-search.vagas-hero-search
└─ div.vagas-hero-search__inner
   ├─ h1.vagas-hero-search__title            (inalterado)
   ├─ p.vagas-hero-search__subtitle          (inalterado)
   ├─ form.vagas-hero-search__form[method=get][action=/vagas][role=search]
   │  └─ div.vagas-hero-search__fields
   │     ├─ div.vagas-hero-search__field.--title    (ícone lupa + label.visually-hidden + input[name=title])
   │     ├─ div.vagas-hero-search__divider.--lead[aria-hidden]
   │     ├─ div.vagas-hero-search__rest             (agrupa cidade + curso quando Input 1 tem texto)
   │     │  ├─ div.vagas-hero-search__field.--cidade   (ícone pin   + input[name=cidade], placeholder "Cidade ou Remoto")
   │     │  ├─ div.vagas-hero-search__divider[aria-hidden]
   │     │  └─ div.vagas-hero-search__field.--curso    (ícone capelo + input[name=cursos])
   │     └─ div.vagas-hero-search__submit-wrap > button.vagas-hero-search__submit "Buscar Vagas"
   ├─ ul.vagas-hero-search__pills[role=list] > li > a.vagas-hero-search__pill[href=/vagas?cursos=<norm>]
   └─ p.vagas-hero-search__view-all > a.vagas-hero-search__view-all-link[href=/vagas] "Ver todas"   (NOVO)
```

### Atributos adicionados aos inputs

| Atributo | Input 1 (`title`) | Input 2 (`cidade`) | Input 3 (`cursos`) |
|----------|-------------------|--------------------|--------------------|
| `data-ac-endpoint` | `Url::fromRoute('custom_banners.vagas_autocomplete')` | `…cidades_autocomplete` | `…cursos_autocomplete` |
| `data-ac-kind` | `vagas` | `cidades` | `cursos` |
| `autocomplete` | `off` | `off` | `off` |
| `placeholder` | Cargo ou palavra-chave | **Cidade ou Remoto** | Seu curso |
| `label` (visually-hidden) | Cargo ou palavra-chave | Cidade ou Remoto | Seu curso |

Os atributos ARIA (`role="combobox"`, `aria-autocomplete="list"`, `aria-expanded`, `aria-controls`, `aria-activedescendant`) são aplicados pelo JS (enhancement progressivo; sem JS o form GET nativo funciona).

## 2. Dropdown (criado pelo JS, irmão do input dentro de `.vagas-hero-search__field`)

```html
<ul class="vagas-hero-search__suggestions" id="vagas-hero-ac-title" role="listbox" hidden>
  <li class="vagas-hero-search__suggestion is-active" id="vagas-hero-ac-title-0" role="option" aria-selected="true">
    <span class="vagas-hero-search__suggestion-text">Es<mark>tág</mark>io …</span>
    <span class="vagas-hero-search__suggestion-badge">Vaga</span>   <!-- só Input 1 -->
  </li>
</ul>
<span class="visually-hidden" role="status" aria-live="polite"></span>
```

- Todo conteúdo inserido via `textContent`/nós DOM (nunca `innerHTML`).
- `<mark>` envolve o trecho digitado (comparação sem acento/caixa, índices mapeados para o texto original).
- Classes de estado: `.is-active` (hover/teclado). `hidden` fecha.
- Zero itens: um `<li class="vagas-hero-search__suggestion--empty" role="presentation">Nenhum resultado encontrado</li>` (não selecionável); o listbox permanece aberto.

## 3. Comportamento (JS `vagas-hero-autocomplete.js`)

| Regra | Valor |
|-------|-------|
| Mínimo de caracteres | 2 |
| Debounce | 300 ms após a última tecla |
| Cancelamento | `AbortController` por input; resposta obsoleta descartada por token |
| Cache de sessão | `Map` (≤ 50), chave `endpoint|termo normalizado` |
| Teclas | ↓/↑ navegam (↓ abre se fechado e houver itens); Enter seleciona item ativo, senão submete; Esc fecha (mantém texto); Tab fecha |
| Fechamento | seleção, Esc, blur, clique fora, erro de rede, `pageshow` persistido (0 itens **não** fecha — mostra empty) |
| Empty state | "Nenhum resultado encontrado" (mesmo copy da listagem `/vagas`); sem `role="option"` |
| Agrupamento | form `.is-keyword-filled` quando Input 1 tem texto; `.vagas-hero-search__rest` vira cluster compacto (cidade + curso) |
| Seleção | preenche `input.value = item.value`, dispara `input`/`change` sem reabrir, **não** submete |
| Inicialização | `Drupal.behaviors.vagasHeroAutocomplete` + `once('vagas-hero-ac', 'form.vagas-hero-search__form')` |
| Dependências | `core/drupal`, `core/once` (sem jQuery UI) |
| Submit | inalterado em `vagas-hero-search.js` (normaliza NFD/minúsculas → `location.assign`) |

## 4. Tokens visuais (CSS puro, `components/vagas-hero-search.css`)

| Elemento | Especificação |
|----------|---------------|
| Container `.vagas-hero-search__form` | `#fff`, `border:1px solid #e2e8f0`, `border-radius:12px`, `box-shadow:0 4px 12px rgba(0,0,0,.05)`, `padding:4px`, `min-height:56px` |
| Campos (≥ 992 px) | `display:flex; flex-direction:row; align-items:center`; divisórias verticais `1px #e2e8f0` |
| Campos (< 992 px) | `flex-direction:column`; divisórias horizontais; botão 100% |
| Input | `border:0; outline:0; box-shadow:0; background:transparent`; ícone SVG 20×20 à esquerda (`#94a3b8`) |
| Foco | `.vagas-hero-search__field:focus-within` → contorno 2 px azul do tema (`#1e3a5f`), `border-radius:8px` |
| Botão | `#58A83C`, texto `#fff`, `border-radius:8px`, `padding-inline: 1.5rem (px-4)`, `align-self:stretch` (≥ 992 px), hover/focus `#4d9434` + foco visível |
| Pills | `#EFF4FF`, `border:1px solid #bfdbfe`, `border-radius:9999px`, `padding:2px 16px`, altura ≈ 30 px, texto `#1e3a5f` |
| "Ver todas" | `color:var(--brand-orange)` (`#ff8a22`), peso 600, sublinhado no hover/focus, abaixo das pills |
| Dropdown | `position:absolute; top:calc(100% + 8px); left:0; min-width:100%; max-width:min(420px, calc(100vw - 32px)); z-index:1040`; fundo `#fff`, `border-radius:8px`, sombra `0 8px 24px rgba(15,23,42,.12)`, item ativo `#EFF4FF`, itens ≥ 44 px de altura no mobile |
| Empty do AC | `.vagas-hero-search__suggestion--empty`: texto `#64748b`, cursor default, sem hover azul |
| Cluster (Input 1 preenchido) | `.vagas-hero-search__rest` em flex (não `contents`); fundo `#f8fafc`, borda `#e2e8f0`, raio 8 px; Input 1 `flex:1 1 auto`; em ≥ 992 px o cluster não passa de ~28 rem |
| Mobile ≤ 575.98 px | sem overflow horizontal; dropdown na largura do campo |

> Confirmado na implementação (2026-11-02): tokens do frame `196-1186` — container `padding:4px` / `min-height:56px` / raio 12 px; botão `#58A83C` raio 8 px rente (sem `overflow:hidden` no form para não cortar o dropdown).

## 5. Library `default/vagas_hero_search` (v1.2.2)

```yaml
vagas_hero_search:
  version: 1.2.2
  js:
    assets/js/vagas-hero-search.js: {}
    assets/js/vagas-hero-autocomplete.js: {}
  css:
    component:
      assets/css/components/vagas-hero-search.css: {}
  dependencies:
    - default/bootstrap
    - core/drupal
    - core/once
```

## 6. Invariantes / regressão

- Home: `.hero-search`, `hero-search.js`, `block--hero-search.html.twig`, `/api/*` 040/041 — **sem alteração**.
- Query GET resultante: `/vagas?title=<n>&cidade=<n>&cursos=<n>` (apenas preenchidos; `<n>` minúsculas sem acento).
- Pré-preenchimento por URL continua vindo do `VagasHeroSearchBlock` (valores canônicos) sem abrir dropdown.
