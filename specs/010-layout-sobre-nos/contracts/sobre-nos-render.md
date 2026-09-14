# Contract: Renderização da seção “Sobre nós”

**Feature**: [spec.md](../spec.md)  
**Consumidor**: visitante em `/quem-somos` (tema `default`)  
**Produtor**: node `quem_somos` + Twig `node--quem-somos.html.twig` + library `layout_sobre_nos`

## Objetivo

Definir DOM estável da **primeira seção**, comportamento de wrap/float, decorativos, tipografia, responsividade, fallbacks e isolamento CSS. A segunda seção permanece no contrato legado de duas colunas (documentado só pelo isolamento).

## Estrutura DOM (1ª seção)

```html
<article class="node node--type-quem-somos …">
  <div class="node__content clearfix container">
    <section class="sobre-nos clearfix">
      <h3 class="sobre-nos__title …">Sobre nós</h3>
      <!-- imagem ANTES do corpo no fluxo -->
      <div class="sobre-nos__media position-relative float-md-end …">
        <img class="sobre-nos__img img-fluid" src="…" alt="…" loading="lazy">
        <!-- decorativos via ::before / ::after no .sobre-nos__media -->
      </div>
      <div class="sobre-nos__body">
        <!-- HTML do field_text_long_formatted -->
        <p>…</p>
      </div>
    </section>

    <div class="row row-custom-2 …">
      <!-- seção 2 inalterada em estrutura de colunas -->
    </div>
  </div>
</article>
```

Notas:

- Classe Drupal `node--type-quem-somos` sempre presente; BEM `.sobre-nos` marca a seção wrap.
- Heading da seção vem de `field_titulo` (não do `title` do node).
- Wrapper `.sobre-nos__media` **somente** se houver entidade de arquivo válida.

## Contrato de campos → UI

| Campo | Elemento | Regra |
|-------|----------|-------|
| `field_titulo` | `.sobre-nos__title` | Visível; tipografia Poppins / título de seção |
| `field_imagem` | `.sobre-nos__img` | Fluida; `max-width` ~432px em desktop; alt do field |
| `field_text_long_formatted` | `.sobre-nos__body` | HTML preservado; flui ao redor do float em `md+` |

## Contrato responsivo

| Viewport | Comportamento |
|----------|---------------|
| ≥768px (`md+`) | `.sobre-nos__media` com `float-md-end` + margem start/bottom; texto contorna à esquerda e abaixo |
| &lt;768px | float desativado; empilhamento legível; sem overflow-x causado pela seção ou decorativos |

Clearfix em `.sobre-nos` (e/ou no `node__content`) impede vazamento para `.row-custom-2`.

## Contrato visual

| Elemento | Regra |
|----------|-------|
| Proporção imagem | reconhecível ~432×269; não estourar container |
| Anel verde | ~185×185 vazado; topo direito do wrapper da imagem |
| Círculo laranja | sólido; canto inferior direito do wrapper |
| Tipografia | Poppins (já no tema) |
| Tokens locais | `--sobre-nos-green`, `--sobre-nos-orange` (#FD7B1A); **não** mutar `--brand-orange` global |
| CSS | **apenas** sob `.node--type-quem-somos` / `.sobre-nos` |

## Contrato de fallback

| Caso | Resultado |
|------|-----------|
| Sem imagem | sem `.sobre-nos__media`; corpo em largura total |
| Sem texto | sem `.sobre-nos__body` (ou vazio); imagem+título sem float quebrado |
| Sem título | seção ainda renderiza corpo/imagem se presentes |
| Zero resultados N/A | é página de node, não View |

## Contrato de isolation

| Escopo | Regra |
|--------|-------|
| Home / outras internas | sem anel/círculo “fantasma” |
| Banner 009 | intacto (região `banner`) |
| Seção 2 | não herda float; layout colunas preservado |

## Contrato de deploy

| Item | Valor |
|------|-------|
| Hook | `custom_configs_update_11013` (idempotente; ensure fields/displays) |
| Fluxo | `git pull` → `drush cim -y` → `drush updb -y` → `drush cr` |
| Config | `drush cex` se form/view/instances mudarem na origem |
| Conteúdo | sem seed; editorial permanece |

## Fora deste contrato

- Markup/copy do banner Quem Somos
- Redesign Missão/Visão / campos `*_2`
- Novas rotas ou renomeação de storages
