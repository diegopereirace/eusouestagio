# Contract: Renderização CTA Final — Para Estudantes

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-30  
**Consumidor**: tema `default` (Twig suggestion + CSS)  
**Produtor**: `block_content` bundle `cta_v1` (UUID `e1f2a3b4-…`) + placement `default_ctav1paraestudantes`

## Objetivo

Contrato de apresentação estável para visitante em `/para-estudantes` e revisão visual frente ao Figma (faixa CTA escura final). Não é API HTTP. **Isolamento**: este contrato **não** se aplica a `/quem-somos` nem `/para-empresas`.

## Estrutura DOM esperada

```html
<div id="block-default-ctav1paraestudantes" class="block block-cta-v1--para-estudantes ...">
  <div class="content">
    <section class="cta-v1 cta-v1--dark"> <!-- wrapper full-bleed -->
      <div class="container">
        <div class="cta-v1__copy">
          <h2 class="cta-v1__title">Pronto para dar o próximo passo?</h2>
          <p class="cta-v1__subtitle">Junte-se a milhares de estudantes…</p>
        </div>
        <div class="cta-v1__actions d-grid gap-2 d-md-flex justify-content-md-center gap-md-3">
          <a class="cta-v1__btn cta-v1__btn--primary" href="/cadastro/candidato">Cadastre-se Gratuitamente</a>
          <a class="cta-v1__btn cta-v1__btn--secondary" href="/vagas">Explorar Vagas</a>
        </div>
      </div>
    </section>
  </div>
</div>
```

Notas:
- Classe de escopo obrigatória: `block-cta-v1--para-estudantes` (e/ou ID `#block-default-ctav1paraestudantes`).
- Fundo full-bleed (100% viewport); conteúdo interno em `.container` (~1280px).
- Omitir título/corpo/botão quando o campo correspondente estiver vazio (ou botão sem URI).
- Se não houver nenhum conteúdo utilizável → **não** renderizar a faixa.
- Labels de bloco Drupal ocultos (`label_display: 0`).
- Posição: último bloco de `content_full` antes do footer (weight `4`, após vagas `block_3` weight `3`).

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente sob `.block-cta-v1--para-estudantes` / `#block-default-ctav1paraestudantes` |
| Library | `default/cta_v1_para_estudantes` → `assets/css/cta-v1-para-estudantes.css` |
| Library clara | **não** carregar `default/cta_v1` nesta instância |
| Fundo | linear gradient `#023C62` → `#011A2B`, full-bleed |
| Padding seção | top/bottom `64px`, left/right `40px` (±2px) |
| Textos | centralizados, cor branca |
| Título | Poppins semibold/bold, `h2` |
| Primário | fundo `#FD7B1A`, texto branco, sem borda |
| Secundário | fundo transparente, `1px solid #FFFFFF`, texto branco |

**Proibido**:
- Alterar tokens/seletores em `cta-v1.css` de forma que mude Quem Somos / Para Empresas
- Depender de `.ui-btn--primary` global para o primário

## Contrato responsivo

| Viewport | Layout |
|----------|--------|
| ≥768px (`md+`) | botões lado a lado, centralizados, gap visível |
| ≤575.98px | botões empilhados; sem overflow-x causado pelo bloco |

## Contrato de navegação (seed)

| Botão | Destino |
|-------|---------|
| Cadastre-se Gratuitamente | `/cadastro/candidato` |
| Explorar Vagas | `/vagas` |

URLs limpas; texto/URI editáveis pelo editor após o seed.

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Título vazio | omitir título |
| Corpo vazio | omitir corpo |
| Botão sem URI | omitir esse botão |
| Sem botões | omitir `.cta-v1__actions` |
| Sem textos e sem botões | **não** renderizar a faixa |
| Erro Twig | proibido — página deve permanecer estável |

## Contrato de convivência

| Item | Regra |
|------|--------|
| Hero `021` | sem alteração |
| Benefícios `022` (w0) | sem alteração |
| Jornada `023` (w1) | sem alteração |
| Perfil `024` (w2) | sem alteração |
| Vagas `block_3` `025` (w3) | sem alteração; CTA aparece **depois** |
| CTA Quem Somos | visual claro preservado (sem gradiente escuro / outline branco) |
| CTA Para Empresas | visual anterior preservado |
| Home / outras rotas | placement **não** aparece |

## Dados mínimos para “seção completa” (aceitação)

| Campo | Valor |
|-------|--------|
| Título | Pronto para dar o próximo passo? |
| Corpo | Junte-se a milhares de estudantes que já encontraram a oportunidade ideal através da nossa plataforma. |
| Primário | Cadastre-se Gratuitamente → /cadastro/candidato |
| Secundário | Explorar Vagas → /vagas |

## Não garantido por este contrato

- Conteúdo editorial final após o editor alterar o seed
- Presença do bloco em ambientes sem `cim`/`updb` aplicados
- Paridade pixel-perfect fora dos tokens listados (±2px)
