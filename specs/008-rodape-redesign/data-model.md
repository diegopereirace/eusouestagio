# Data Model: Redesign do Rodapé (Footer)

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-13

## Entidade

### `footer` (`block_content`) — existente, sem alteração de schema

| Campo | Storage | Tipo | Cardinalidade | Exibido no layout? | Notas |
|-------|---------|------|---------------|--------------------|-------|
| Logo | `field_image` | image | 1 | sim | link para `<front>`; fallback asset do módulo |
| Tagline | `field_text_simple_long` | string_long | 1 | **não** | preservado no painel |
| Endereço | `field_text_simple_2` | string | 1 | **não** | preservado no painel |
| E-mail | `field_email` | email | 1 | sim | ícone social + item Contato (`mailto:`) |
| Telefone / WhatsApp | `field_phone_wpp` | string | 1 | sim | ícone social `wa.me` + Contato (`tel:`) |
| Instagram | `field_instagram` | link | 1 | sim | ícone social, `target="_blank"` |
| LinkedIn | `field_linkedin` | link | 1 | sim | ícone social, `target="_blank"` |

**UUID fixo**: `d0326db5-fc80-4cf5-a0b9-8f779dc4aeba`  
**Placement**: `block.block.default_footer` → região `footer_first`, tema `default`

## Relacionamentos

```text
block_content:footer (UUID fixo)
├── field_image                 → File (logo)
├── field_email                 → social + Contato
├── field_phone_wpp             → social WhatsApp + Contato
├── field_instagram / linkedin  → sociais externos
├── field_text_simple_long      (não renderizado)
└── field_text_simple_2         (não renderizado)

block.block.default_footer (config)
└── plugin block_content:<UUID> ──► footer

Twig (fonte de verdade dos links de coluna)
└── paths canônicos hardcoded (sem menu_link_content)

Menus legados (não consumidos pelo template)
├── rodape---sobre
├── rodape---para-estudantes
└── para-empresas
```

## Rotas canônicas (não são entidades CMS)

| Coluna | Rótulo | Path |
|--------|--------|------|
| Institucional | Sobre Nós | `/sobre-nos` *(futura)* |
| Institucional | Política de Privacidade | `/politica-de-privacidade` *(futura)* |
| Institucional | Termos de Uso | `/termos-de-uso` *(futura)* |
| Para Estudantes | Buscar Vagas | `/para-estudantes` |
| Para Estudantes | Cadastro de Estudantes | `/cadastro/candidato` |
| Para Estudantes | Blog | `/blog` *(futura)* |
| Para Empresas | Cadastre sua Empresa | `/cadastro/empresa` |
| Para Empresas | Abrir Vaga | `/painel/empresa/vagas/nova` |

## Regras de validação / fallback

| Estado | Resultado público |
|--------|-------------------|
| `field_email` vazio | omitir ícone e-mail e item Contato e-mail |
| `field_phone_wpp` vazio | omitir ícone WhatsApp e item `tel:` |
| Instagram/LinkedIn vazios | omitir ícones; manter gap dos restantes |
| Logo vazia | fallback `modules/custom/custom_configs/assets/footer/logo-rodape.png` |
| Rota futura inexistente | link renderiza; 404 controlada aceita |
| Ano copyright | dinâmico (`Y` do servidor) |

## Seed lógico (`custom_configs_update_11011`)

- Garantir existência do bloco UUID acima (helper de create-if-missing).
- Garantir logo anexada somente se campo vazio / arte seed anterior.
- **Não** criar/atualizar `menu_link_content` dos menus legados.
- No-op na reexecução (`updb` 2×).

## Configuração a versionar

**Reutilizar (já em sync):** `block_content.type.footer`, field instances, form/view displays, `block.block.default_footer`.

**Criar/exportar só se mudar:** qualquer ajuste real de display/placement → `drush cex`.

## Fora do modelo

- Novos field storages
- Entidades de página para rotas futuras
- Remoção dos menus legados
- Schema do bloco flutuante WhatsApp (só CSS `z-index`)
