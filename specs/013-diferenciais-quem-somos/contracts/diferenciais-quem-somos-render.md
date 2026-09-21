# Contract: Renderização do bloco Diferenciais Quem Somos

**Feature**: [spec.md](../spec.md) | **Plan**: [../plan.md](../plan.md) | **Data**: 2026-09-20  
**Consumidor**: tema `default` (Twig)  
**Produtor**: `block_content` bundle `diferenciais_quem_somos` + paragraphs `diferencial_simples_p`

## Objetivo

Contrato de apresentação estável para visitante em `/quem-somos` e para revisão visual. Não é API HTTP.

## Estrutura DOM esperada

```html
<section class="block block-block-content … block-diferenciais-quem-somos" id="diferenciais-quem-somos">
  <div class="content">
    <div class="container py-5">
      <header class="dqs-header text-center">
        <h2 class="dqs-header__title">…field_text_simple…</h2>
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <p class="dqs-header__desc">…field_text_simple_long…</p>
          </div>
        </div>
      </header>

      <div class="row mt-5 justify-content-center dqs-grid">
        <!-- N × paragraph diferencial_simples_p -->
        <div class="col-6 col-md-4 col-lg-3 mb-4">
          <div class="dqs-item text-center d-flex flex-column align-items-center">
            <div class="dqs-item__icon">
              <img class="img-fluid" src="…" alt="…" loading="lazy">
            </div>
            <p class="dqs-item__label mt-3">…field_text_simple…</p>
          </div>
        </div>
        <!-- … -->
      </div>
    </div>
  </div>
</section>
```

Notas:
- Classe raiz obrigatória: `block-diferenciais-quem-somos` (FR-008).
- Omitir `h2` / descrição / ícone / rótulo quando vazios.
- Preservar `attributes` / `content_attributes` / title hooks do template de bloco.
- **Não** reutilizar classes `.block-nossos-diferenciais` / `.nd-*`.

## Contrato visual / CSS

| Propriedade | Regra |
|-------------|--------|
| Escopo | somente seletores sob `.block-diferenciais-quem-somos` |
| Library | `default/diferenciais_quem_somos` |
| Tipografia título | Poppins, negrito/SemiBold, centralizado |
| Tipografia rótulo | Poppins SemiBold (≈600), `.mt-3` |
| Ícone | `.img-fluid` + `max-width: 64px` |
| Espaçamento vertical | equivalente a `.py-5` no container |

## Contrato responsivo

| Viewport | Layout |
|----------|--------|
| ≥992px (`lg`) | até 4 itens/linha (`col-lg-3`) |
| ≥768px (`md`) | até 3 itens/linha (`col-md-4`) |
| ≤575.98px | 2 itens/linha (`col-6`); sem overflow-x |

## Contrato de fallback

| Estado | Comportamento |
|--------|---------------|
| Sem título e sem descrição e zero itens | seção pode renderizar vazia/mínima **sem erro**; preferível omitir grid vazio |
| Zero itens | só cabeçalho (se preenchido) |
| Item sem ícone | rótulo legível |
| Item sem rótulo | só ícone (se houver) |
| &gt; 8 itens | todos renderizam na ordem |

## Contrato de placement / convivência

| Item | Regra |
|------|--------|
| Região | `content_full` do tema `default` |
| Path | somente `/quem-somos` |
| Home | **não** exibe este bloco; `nossos_diferenciais` intacto |
| Banner / Sobre nós / Missão-Visão Node | sem alteração de markup/CSS |

## Dados mínimos para “seção completa” (aceitação)

| Campo | Exemplo |
|-------|---------|
| Título | Nossos Diferenciais |
| Descrição | texto curto institucional |
| Itens | 8 × (ícone + rótulo) |

## Não garantido por este contrato

- Conteúdo editorial final (seed é placeholder)
- Presença de arquivos de ícone em ambientes sem execução do `updb` / sem assets
- Ordem visual relativa a outros futuros blocos em `content_full` além do weight `10`
