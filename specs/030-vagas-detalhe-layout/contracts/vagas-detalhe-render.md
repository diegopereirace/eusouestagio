# Contract: Renderização Detalhe da Vaga (view mode full)

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-10-04  
**Consumidor**: visitante na URL canônica do node `vagas`  
**Produtor**: Twig `node--vagas--full.html.twig` + library `default/vagas_detalhe` + preprocess `default_preprocess_node__vagas`

## Objetivo

Contrato de apresentação do detalhe Figma em duas colunas. **Não** se aplica a `/vagas` listagem nem cards laranja Home/PE/similares.

## Estrutura DOM esperada

```html
<article class="node node--type-vagas node--view-mode-full vaga-detalhe …">
  <div class="container py-5">
    <div class="row g-4">
      <div class="col-12 col-lg-8">
        <header class="vaga-detalhe__header">…</header>
        <section class="vaga-detalhe__sobre">…</section>
        <section class="vaga-detalhe__requisitos">…</section><!-- omitir se vazio -->
        <section class="vaga-detalhe__beneficios">…</section><!-- omitir se vazio -->
        <section class="vaga-detalhe__processo">…</section><!-- omitir se vazio -->
        <section class="vaga-detalhe__empresa">…</section><!-- omitir se sem empresa -->
        <section class="vaga-detalhe__faq">…</section><!-- omitir se vazio -->
        <section class="vaga-detalhe__cta">…</section><!-- markup fixo -->
      </div>
      <aside class="col-12 col-lg-4">
        <div class="vaga-detalhe__acoes">…</div>
        <div class="vaga-detalhe__resumo">…</div>
        <div class="vaga-detalhe__perfil">…</div>
      </aside>
    </div>
  </div>
</article>
```

## Proibições

- Seções “Seu Match com a vaga” / “Por que combina com você?”
- Alterar markup/CSS da listagem `.item-vaga--lista` / `.vagas-lista-layout`
- Classes `.item-vaga--destaque` (cards laranja) neste template

## Contrato de dados — coluna principal

| Região UI | Fonte | Fallback / omitir |
|-----------|-------|-------------------|
| Logo | `field_empresa_u.entity.user_picture` | omitir / placeholder |
| Título | `title` (h1) | — |
| Empresa | `field_nome_fantasia` | “Anônima” |
| Local | `field_cidade` + `field_estados` | omitir trecho |
| Badge regime | `field_regime_t` label | omitir |
| Badge carga | `field_horarios` | omitir |
| Badge bolsa | `field_text_simple` | omitir |
| Badge postado | tempo relativo `created` | omitir se indisponível |
| Sobre a Vaga | `field_text_long_formatted` | omitir seção |
| Requisitos | `field_vaga_requisitos[]` + check azul | omitir seção |
| Benefícios | `field_vaga_beneficios` → ícone + título; grid 2/4 | omitir seção; sem ícone → placeholder/só título |
| Processo | `field_vaga_etapas_processo[]` stepper; último `#FD7B1A` | omitir seção |
| Sobre a Empresa | logo + nome + `field_sobre_empresa` truncado | omitir seção se sem empresa; **sem** botão Ver Empresa |
| FAQ | `field_vaga_faq` → Accordion BS5 | omitir seção; IDs `vaga-faq-{vaga}-{faq}` |
| CTA final | markup fixo `#023C62` + “Candidatar-se Agora” | mesmo fluxo da sidebar |

## Contrato de dados — sidebar

| Região UI | Fonte | Notas |
|-----------|-------|--------|
| Candidatar-se | `js-candidatar-vaga` / disabled se `vaga_candidatada` | anônimo → login |
| Salvar | `js-salvar-vaga` + `vaga_salva` | candidato |
| Compartilhar | WhatsApp / Facebook / LinkedIn (URLs atuais) | — |
| Resumo Período | `field_horarios` | unidos |
| Resumo Bolsa | `field_text_simple` | — |
| Resumo Modelo | `field_regime_t` | — |
| Resumo Vagas | literal “Não informado” | sem field |
| Seu Perfil | barra + % (default 75) + “Completar agora” | `/painel/estudante/perfil` ou login |

## Responsividade

| Viewport | Comportamento |
|----------|---------------|
| `≥992px` (`lg`) | col-8 + col-4 lado a lado |
| `<992px` | empilhados; sidebar abaixo; sem overflow-x |

## Isolamento

| Superfície | Esperado |
|------------|----------|
| `/vagas` listagem | intacta |
| Home / PE / similares cards | intactos |
| View modes ≠ full | não usam o novo markup Figma |
