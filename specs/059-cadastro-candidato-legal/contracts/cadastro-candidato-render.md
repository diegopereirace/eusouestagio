# Contract: Render `/cadastro/candidato`

**Feature**: [../spec.md](../spec.md) | **Data**: 2026-11-04

## Escopo

Contrato de apresentação da rota de cadastro de candidato. Não define regras de negócio de campos/CPF.

## Body / page class

- Body recebe classe `page-cadastro-candidato` via `default_preprocess_html()` quando a rota é `custom_configs_users.candidato_registration` (cache context `route`). Necessária para o escopo do CSS da library.
- Template: `page--cadastro--candidato.html.twig`.

## Banner

| Elemento | Contrato |
|----------|----------|
| Posição | Topo do `<main>`, acima do formulário |
| Eyebrow | Texto: `Cadastro de candidato` |
| Título | Texto exato: `Crie sua conta e encontre seu estágio ideal` |
| Alinhamento | Conteúdo à esquerda no `.container` (área limpa da arte) |
| Mídia | `<picture>` com source mobile e img desktop (arte temática de estágio/carreira) |
| Overlay | Gradiente lateral escuro suficiente para contraste do título (branco/claro) |
| LCP | `loading="eager"` (e `fetchpriority="high"` se suportado no markup) |
| Fallback | Fundo sólido da marca + título se imagem falhar |
| Overflow | Banner/img não causam scroll horizontal (`overflow-x: clip`, img `max-width: 100%` + `object-fit: cover`) |
| Concorrência | View `banners` `block_2` **não** renderiza nesta rota |

## Layout do formulário

| Elemento | Contrato |
|----------|----------|
| Largura desktop | Centralizada em `col-12 col-xl-10 col-xxl-9` |
| Empilhamento | Cada seção do Form API em cartão próprio (`.cadastro-section`); sem cartão único envolvendo todo o form |
| Visual do cartão | Fundo branco, borda suave, sombra leve, `border-radius` ~1rem, padding generoso |
| Conteúdo | Intro + mensagens + seções + cartão final de ações |
| Mobile | Margens laterais fluidas (`clamp`); **sem** scroll horizontal do layout |

## Form (Form API)

| Elemento | Classes / comportamento alvo |
|----------|------------------------------|
| Inputs text/email/password | `form-control form-control-lg` (+ radius suave opcional) |
| Labels | aparência `form-label fw-bold text-dark` |
| Seções | `.cadastro-section` com chip de ícone Font Awesome (`fa-solid`), título `h2` e subtítulo; badge “Opcional” quando couber |
| Subcards | Instituição/curso/experiência em `.cadastro-subcard` (inset), sem competir com o cartão da seção |
| Submit | No cartão final `.cadastro-section--actions`; `btn btn-primary btn-lg w-100`; cor visual `#FD7B1A`; texto branco |
| Aceite | `form-check` / `form-check-input`; links `/termos-de-uso` e `/politica-de-privacidade` com `target="_blank"` |
| CAPTCHA | Dentro do cartão final; margem vertical no wrapper `.captcha` |
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
