# Contract: CTA final da vaga — audiência e candidatura

**Feature**: `050-vaga-beneficios-cta`  
**Placement**: `default_ctav1vagas`  
**Twig**: `themes/custom/default/templates/block/block--default-ctav1vagas.html.twig`  
**JS**: `themes/custom/default/assets/js/script-painel.js` (library `default/script-painel`)  
**Referência sidebar**: [033 vaga-sidebar-render](../../033-vaga-sidebar-acoes/contracts/vaga-sidebar-render.md)

## Visibility (config exportável)

| Condição | Valor |
|----------|--------|
| `entity_bundle:node` | `vagas` |
| `user_role` | `candidato` (negate false) |

Resultado: anônimo, empresa, moderador/admin **sem** role `candidato` → bloco **não** renderiza. Usuário com role `candidato` (+ outros papéis) → bloco elegível.

## Ação do botão primário

Mesmo contrato da sidebar para candidato ainda não candidatada:

```html
<a href="{{ path('custom_panel.vagas_candidatar', {node: vaga_nid}) }}"
   class="cta-v1__btn cta-v1__btn--primary js-candidatar-vaga"
   data-node-id="…"
   data-apply-url="{{ path('custom_panel.vagas_candidatar', {node: vaga_nid}) }}"
   data-method="POST">Candidatar-se Agora</a>
```

- `{{ attach_library('default/script-painel') }}` no Twig do bloco (ou preprocess).
- Listener existente: `preventDefault`, CSRF, POST; sucesso → texto “Candidatura Enviada”, remove `href`.
- **Não** navegar para `internal:/cadastro/candidato`.

## Estado já candidatada

```html
<button type="button" class="cta-v1__btn cta-v1__btn--primary" disabled
  aria-disabled="true">Candidatura Enviada</button>
```

- `default_block_access()` **não** deve mais retornar `forbidden()` só porque já há candidatura (o Twig cobre o estado).
- Cache contexts: `user`, `route`; tags de candidatura quando aplicável.

## Preprocess (obrigatório)

Variáveis mínimas no bloco:

| Variável | Origem |
|----------|--------|
| `vaga_nid` / node id | `routeMatch` → node bundle `vagas` |
| `vaga_candidatada` | `_default_user_has_candidatura_vaga()` |
| `vaga_apply_url` | `Url::fromRoute('custom_panel.vagas_candidatar', …)` |

Se a rota não for detalhe de `vagas`, o bloco já falha na visibility de bundle.

## Fora de escopo

- Outras instâncias `cta_v1` (quem-somos, para-empresas, para-estudantes).
- Alterar controller `VagasAplicadasController`.
- Novo endpoint de candidatura.
