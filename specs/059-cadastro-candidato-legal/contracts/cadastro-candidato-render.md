# Contract: Render `/cadastro/candidato`

**Feature**: [../spec.md](../spec.md) | **Data**: 2026-11-04

## Escopo

Contrato de apresentação da rota de cadastro de candidato. Não define regras de negócio de campos/CPF.

## Body / page class

- Body (ou html) recebe classe de path `page-cadastro-candidato` (suggestion nativa).
- Template: `page--cadastro--candidato.html.twig`.

## Banner

| Elemento | Contrato |
|----------|----------|
| Posição | Topo do `<main>`, acima do card |
| Título | Texto exato: `Crie sua conta e encontre seu estágio ideal` |
| Mídia | `<picture>` com source mobile e img desktop |
| Overlay | Escuro suficiente para contraste do título (branco/claro) |
| LCP | `loading="eager"` (e `fetchpriority="high"` se suportado no markup) |
| Fallback | Fundo sólido da marca + título se imagem falhar |
| Concorrência | View `banners` `block_2` **não** renderiza nesta rota |

## Card

| Elemento | Contrato |
|----------|----------|
| Largura desktop | Limitada / centralizada (≈ até 6/12) |
| Visual | Fundo claro, `shadow-sm`, cantos bem arredondados, padding generoso, sem borda pesada |
| Conteúdo | Mensagens + formulário |
| Mobile | Largura confortável; sem overflow-x do layout |

## Form (Form API)

| Elemento | Classes / comportamento alvo |
|----------|------------------------------|
| Inputs text/email/password | `form-control form-control-lg` (+ radius suave opcional) |
| Labels | aparência `form-label fw-bold text-dark` |
| Seções | Headings/agrupadores existentes preservados e visíveis |
| Submit | `btn btn-primary btn-lg w-100 mt-4`; cor visual `#FD7B1A`; texto branco |
| Aceite | `form-check` / `form-check-input`; links `/termos-de-uso` e `/politica-de-privacidade` com `target="_blank"` |
| CAPTCHA | Utilizável; margem vertical no wrapper `.captcha` |
| Library | `default/cadastro_candidato` anexada **somente** neste form |

## Redirect

| Condição | Destino |
|----------|---------|
| Sucesso + conta ativa + login automático | `/painel/estudante/perfil` (`custom_panel.painel_estudante_perfil`) |
| Verify mail / pending approval | Comportamento atual (`<front>`) |

## Não-contratos (fora)

- Markup interno de elementos do core reescrito em Twig.
- Alterações visuais em `/cadastro/empresa` ou `/user/login`.
- Layout Builder.
