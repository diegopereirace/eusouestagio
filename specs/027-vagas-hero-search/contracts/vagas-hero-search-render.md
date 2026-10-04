# Contract: Renderização Hero Search — /vagas

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-10-04  
**Consumidor**: tema `default` (library/CSS) + visitante em `/vagas`  
**Produtor**: Block Plugin `custom_banners_vagas_hero_search` + placement `default_custom_banners_vagas_hero_search`

## Objetivo

Contrato de apresentação e de query estável para o Hero Search no topo de `/vagas`. Não é API HTTP. **Isolamento**: este contrato **não** se aplica ao hero da home (`#hero-search` / `.hero-search`).

## Estrutura DOM esperada

```html
<div id="block-default-custom-banners-vagas-hero-search" class="block ...">
  <div class="content">
    <section class="vagas-hero-search" id="vagas-hero-search">
      <div class="vagas-hero-search__inner">
        <h1 class="vagas-hero-search__title">Encontre a oportunidade ideal para sua carreira.</h1>
        <p class="vagas-hero-search__subtitle">Explore milhares de vagas de estágio em empresas parceiras e dê o próximo passo na sua jornada profissional.</p>

        <form class="vagas-hero-search__form" method="get" action="/vagas" role="search">
          <div class="vagas-hero-search__fields">
            <div class="vagas-hero-search__field vagas-hero-search__field--title">
              <!-- ícone lupa -->
              <label class="visually-hidden" for="vagas-hero-title">Cargo ou palavra-chave</label>
              <input id="vagas-hero-title" name="title" type="text" placeholder="Cargo ou palavra-chave" value="…">
            </div>
            <div class="vagas-hero-search__divider" aria-hidden="true"></div>
            <div class="vagas-hero-search__field vagas-hero-search__field--cidade">
              <!-- ícone pino -->
              <label class="visually-hidden" for="vagas-hero-cidade">Cidade ou Remoto</label>
              <input id="vagas-hero-cidade" name="cidade" type="text" placeholder="Cidade ou Remoto" value="…">
            </div>
            <div class="vagas-hero-search__divider" aria-hidden="true"></div>
            <div class="vagas-hero-search__field vagas-hero-search__field--curso">
              <!-- ícone capelo -->
              <label class="visually-hidden" for="vagas-hero-cursos">Seu curso</label>
              <input id="vagas-hero-cursos" name="cursos" type="text" placeholder="Seu curso" value="…">
            </div>
            <div class="vagas-hero-search__submit-wrap">
              <button type="submit" class="vagas-hero-search__submit">Buscar Vagas</button>
            </div>
          </div>
        </form>

        <ul class="vagas-hero-search__pills" role="list">
          <li><a class="vagas-hero-search__pill" href="/vagas?cursos=Marketing">Marketing</a></li>
          <!-- … demais pills resolvidas; sem “Ver todas” -->
        </ul>
      </div>
    </section>
  </div>
</div>
```

Notas:
- Classe de escopo obrigatória: `.vagas-hero-search` (nunca `.hero-search`).
- Labels de bloco Drupal ocultos (`label_display: 0`).
- Posição: primeiro conteúdo abaixo do header em `/vagas` (região `highlighted`, weight `-50`).
- **Proibido**: link/botão “Ver todas” associado às pills.
- Pills cujo termo não existe no vocabulário `curso` **não** aparecem.

## Contrato de query (GET)

| Name | Mapeamento View | Exemplo |
|------|-----------------|---------|
| `title` | filtro exposto `title` (contains) | `/vagas?title=estagio` |
| `cidade` | filtro exposto `cidade` | `/vagas?cidade=Fortaleza` |
| `cursos` | filtro exposto `cursos` (nome do termo) | `/vagas?cursos=Engenharia` |

- `action` canônico: `/vagas` (rota `view.vagas.page_1`).
- Combinação AND quando múltiplos params presentes.
- Submit com todos vazios → `/vagas` utilizável sem erro.
- Inputs DEVEM refletir valores atuais da query quando a página é `/vagas`.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente sob `.vagas-hero-search` |
| Library | `default/vagas_hero_search` → `assets/css/components/vagas-hero-search.css` |
| Library home | **não** carregar `default/hero_search` neste bloco |
| Fundo seção | branco |
| Wrapper | `max-width: 1280px` |
| Padding seção | top `48px`, bottom `32px`, left/right `40px` (±2px) |
| Título | `h1`, Poppins bold; max-width ~`1062px` |
| Subtítulo | max-width ~`715px`; cor `#45464D` (ou token equivalente) |
| Barra | pílula (border-radius alto, sombra sutil, borda clara); inputs sem chrome Bootstrap |
| Botão | fundo `#58A83C`, texto branco, sem borda; altura ~`56px` |
| Pills | flex wrap + gap; outline azul claro; cantos arredondados |

**Proibido**:
- Alterar tokens/seletores em `hero-search.css` de forma que mude a home
- Depender de classes `.hero-search*` neste bloco

## Contrato responsivo

| Viewport | Layout |
|----------|--------|
| Desktop (md+) | três inputs + botão em uma linha; divisórias verticais entre inputs |
| ≤575.98px | campos empilhados; pills com wrap; **sem** overflow-x causado pelo hero |

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Termo de pill ausente | omitir pill |
| Lista de pills vazia | omitir o `<ul>` (ou renderizar vazio sem erro) |
| Placement ausente | página `/vagas` só com a View |
| Rota ≠ `/vagas` | bloco ausente |

## Convivência

| Rota | Esperado |
|------|----------|
| `/vagas` | este hero presente e primeiro |
| `<front>` | somente hero home (`custom_banners_hero_search`) |
| `/para-estudantes`, outras | este hero ausente |
