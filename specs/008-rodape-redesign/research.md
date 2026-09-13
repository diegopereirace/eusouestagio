# Research: Redesign do Rodapé (Footer)

**Data**: 2026-09-13 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

## R1 — Fonte de verdade dos links

**Decision**: hardcodar no Twig as rotas canônicas das colunas Institucional, Para Estudantes e Para Empresas; parar de renderizar `drupal_menu` para os três menus do rodapé.

**Rationale**: `menu_link_content` é conteúdo (não exporta via `config/sync`). Seeds de menu (`11010`) resolvem parcialmente o deploy, mas hardcode no Twig garante paridade total entre ambientes sem novos seeds de links e antecipa URLs futuras (`/sobre-nos`, `/politica-de-privacidade`, `/termos-de-uso`, `/blog`).

**Alternatives considered**: manter menus + seed de links; criar config entities de menu links — rejeitados (drift editorial / complexidade sem ganho; `menu_link_content` não é config).

## R2 — Campos e reuso (sem storage novo)

**Decision**: zero field storages novos. Ícone social de e-mail e `mailto:` da coluna Contato usam `field_email`; WhatsApp social e `tel:`/`wa.me` usam `field_phone_wpp`. Tagline e endereço permanecem no bundle, fora do markup.

**Rationale**: FR-001/FR-002 e regra de reuso do projeto; a estrutura atual já cobre o layout aprovado.

**Alternatives considered**: campo `field_social_email` ou link field dedicado — rejeitado (duplicação).

## R3 — Hook `custom_configs_update_11011`

**Decision**: novo update idempotente que (re)garante bloco `footer` + logo via helpers existentes (`_custom_configs_seed_footer_block`, `_custom_configs_seed_footer_logo`); **não** chama `_custom_configs_seed_footer_menu_links`. Preserva conteúdo editorial divergente do seed.

**Rationale**: o redesign é majoritariamente apresentacional (Twig/CSS). O hook fecha o gap de ambientes que ainda não têm o bloco/logo e documenta o corte com seeds de menu. Idempotência = no-op se UUID/logo já ok.

**Alternatives considered**: só alterar Twig sem hook; re-seedar menus “por garantia” — rejeitados (deploy incompleto em ambiente limpo; trabalho morto nos menus legados).

## R4 — Accordion mobile (Bootstrap Collapse)

**Decision**: abaixo do breakpoint `lg` (&lt;992px), colunas 2–4 usam Collapse/Accordion Bootstrap; colunas 1 e 5 sempre visíveis. Em `lg+`, grid de 5 colunas sem accordion (markup desktop/mobile espelhado com utilitários `d-lg-*` ou equivalente sem JS no desktop).

**Rationale**: Bootstrap já está no tema (Barrio 5); alinhado às Assumptions da spec; evita CSS custom de accordion e regressão em outros componentes.

**Alternatives considered**: `<details>`/`<summary>`; accordion só com CSS puro; breakpoint `md` (768px) — rejeitados (menos consistente com BS5; diverge da spec).

## R5 — Ícones sociais circulares e cores de marca

**Decision**: botões circulares (`.footer-social-link`) com ícones FA; cores fiéis às marcas (Instagram / LinkedIn / WhatsApp / e-mail). Ordem fixa: E-mail, Instagram, LinkedIn, WhatsApp. Omitir ícone se o campo correspondente estiver vazio.

**Rationale**: design anexo 2; FA já carregado; omissão evita href quebrado (edge cases da spec).

**Alternatives considered**: ícones monocromáticos brancos atuais; SVGs versionados no módulo — rejeitados (divergem do design; YAGNI se FA cobre).

## R6 — Barra inferior e crédito

**Decision**: barra centralizada com `© {ano dinâmico} Eu Sou Estágio. Todos os direitos reservados.` e linha `Desenvolvido por Diego Pereira` **sem** `<a>`. Remover links legais da barra (já na coluna Institucional).

**Rationale**: design aprovado; ano via `"now"|date("Y")` (já no template atual).

**Alternatives considered**: manter link para diegopereirace.com.br; links legais na barra — rejeitados pela spec.

## R7 — Contato `mailto:` / `tel:`

**Decision**: e-mail com `mailto:`; telefone com `tel:` usando dígitos sanitizados (mesmo padrão do `wa.me` atual); exibir rótulo formatado do campo.

**Rationale**: FR-012 / US3; o template atual mostra telefone só como `<span>` — gap a fechar.

**Alternatives considered**: só texto sem protocolo — rejeitado (pior UX mobile).

## R8 — Botão flutuante WhatsApp

**Decision**: alterar apenas `.whatsapp-float-block { z-index: 1050 }` (de `1030`); manter `position: fixed` e demais estilos.

**Rationale**: escopo explícito FR-015; LGPD banner usa z-index alto (≈2000+) — aceitável o float abaixo de modais/cookies se SC-006/SC-007 confirmarem no aceite.

**Alternatives considered**: subir para acima do LGPD; refactor do bloco WhatsApp — fora de escopo.

## R9 — Config sync e displays

**Decision**: se form/view display ou placement não mudarem semanticamente, não forçar YAMLs novos; qualquer alteração estrutural real → `drush cex` para `config/sync`.

**Rationale**: redesign é Twig/CSS; fields já exportados. Evita diff cosmético.

**Alternatives considered**: reexportar tudo preventivamente — rejeitado (ruído no git).

## R10 — Cursor Rule e PRD

**Decision**: versionar `.cursor/rules/estagio-fluxo-dev.mdc` com `globs` em `modules/custom/**/*`, `themes/custom/**/*`, `config/sync/**/*` (sem `alwaysApply: true`); atualizar cirurgicamente a seção do bloco `footer` no `PRD.md`.

**Rationale**: FR-017/FR-018; rascunho da rule já existe no working tree — implementação confirma conteúdo e versionamento.

**Alternatives considered**: `alwaysApply: true`; rule só em docs markdown — rejeitados (ruído global; menor aderência ao agente).

## R11 — Cor de fundo

**Decision**: ponto de partida `#023c62` (CSS atual); na implementação, extrair hex exato do anexo 2 e ajustar se divergir.

**Rationale**: Assumption da spec; evita inventar token sem artefato visual.

**Alternatives considered**: introduzir CSS variable global de brand agora — possível melhoria futura; não bloqueante.
