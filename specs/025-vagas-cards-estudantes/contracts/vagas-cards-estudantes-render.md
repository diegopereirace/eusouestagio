# Contract: Renderização — Cards Laranja em `/para-estudantes`

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-30  
**Consumidor**: tema `default` (Twig + CSS)  
**Produtor**: View `vagas` display `page_1` (Fields + Custom Text `nothing`)

## Objetivo

Contrato de apresentação estável para a listagem pública em `/para-estudantes`, alinhado ao card laranja da Home (`block_1`), sem redesenhar a Home.

## Estrutura DOM esperada (por vaga)

```html
<!-- wrapper da view (já existente) -->
<div class="view … css-vagas-page container">
  <div class="view-header">
    <!-- título da seção; SEM link “Ver todas as vagas” -->
    <div …>
      <h3 class="text-title …">Vagas de Destaque</h3>
    </div>
  </div>
  <div class="view-filters">…filtros…</div>

  <div class="view-content row g-4">
    <div class="views-row col-12 col-md-6 col-lg-4">
      <div class="item-vaga item-vaga--destaque">
        <div class="item-inner">
          <div class="vaga-destaque__top">
            <span class="vaga-destaque__icon" aria-hidden="true">
              <i class="fa-solid fa-…"></i>
            </span>
            <span class="item-regime">…</span>
            <!-- ou item-regime--empty se sem regime -->
          </div>
          <div class="vaga-destaque__body">
            <h3 class="vaga-destaque__title">…</h3>
            <p class="vaga-destaque__meta">Empresa • Cidade, UF</p>
          </div>
          <hr class="vaga-destaque__divider" aria-hidden="true">
          <div class="vaga-destaque__salary-row">
            <span class="dv-salario">…</span>
            <a class="vaga-destaque__ver-mais" href="/node/N">Ver Mais</a>
          </div>
          <div class="vaga-destaque__actions">
            <a class="ui-btn ui-btn--destaque" href="/node/N">Inscreva-se</a>
            <!-- SEM “Buscar mais vagas” -->
          </div>
        </div>
      </div>
    </div>
    <!-- … -->
  </div>
  <!-- pager -->
</div>
```

## Regras de apresentação

| Elemento | Obrigatório | Notas |
|----------|-------------|-------|
| Fundo card `#FD761A` | sim | via CSS `.item-vaga--destaque` sob `.css-vagas-page` |
| Ícone FA | sim | fallback `briefcase` |
| Badge regime | se houver | senão placeholder empty |
| Título | sim | truncamento alinhado à Home |
| Meta empresa • local | parcial | omitir partes vazias; nbsp se totalmente vazio |
| Salário | se houver | Ver Mais permanece |
| Ver Mais / Inscreva-se | sim | ambos → canonical da vaga |
| Buscar mais vagas | **não** | omitido em `page_1` |
| Ver todas as vagas (header) | **não** | só na Home |

## Isolamento / regressão

- Classes do card legado (`.row-custom`, “Ver Detalhes”, pills de curso no card antigo) **não** devem aparecer nos resultados de `page_1` após a feature.
- Home: header com “Ver todas as vagas” e botão “Buscar mais vagas” no card **permanecem**.
- Filtros / pager / empty state: markup funcional preservado; resultados filtrados continuam em cards laranja.

### Checklist de preservação funcional `page_1` (US4 / US5)

| Aspecto | Valor esperado (inalterado) | Validar em |
|---------|-----------------------------|------------|
| `filters` | nid, cursos, estado, cidade, escolaridade, regime | US4 / quickstart E |
| `exposed_form` | presente / funcional | US4 |
| `pager` | full, 12 itens/página | US4 |
| `empty` | “Nenhum resultado encontrado” | US4 |
| `path` | `para-estudantes` | US4 / US5 |
| `use_ajax` | `true` | US4 |
| `block_1` / `block_2` | fora de redesign | US3 / US5 / regressão G |

## Tokens

| Token | Valor |
|-------|-------|
| Fundo card | `#FD761A` (família Figma `#FD7B1A`) |
| Tipografia | Poppins (tema) |
| Grid | `.row.g-4` + `.col-12.col-md-6.col-lg-4` |
