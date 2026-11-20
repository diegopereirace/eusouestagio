# Feature Specification: Oportunidades Estudantes — bloco próprio (fim do flip-flop)

**Feature Directory**: `specs/056-oportunidades-estudantes-bloco`  
**Created**: 2026-11-03  
**Status**: Implemented  
**Hook**: `custom_configs_update_11085`

## Problema (causa raiz)

As seções **“Encontre oportunidades que combinam com você.”** (grade 4 cards, 022/053) e **“Benefícios para Estudantes”** (split Figma, 054/055) eram a **mesma** instância `beneficios_estudantes`. Cada restore sobrescrevia a outra — daí o “sumiu novamente”.

Pedido explícito do produto: a página deve mostrar **banner → Encontre oportunidades… → Benefícios para estudantes** (ambas presentes).

## Solução definitiva

- Novo block type `oportunidades_estudantes` (UUID `c1d2e3f4-a5b6-4789-c012-3456789abcde`) com título, subtítulo e 4 cards.
- `beneficios_estudantes` permanece o layout Figma (6 itens + CTA + colagem).
- Ordem `content_full`: oportunidades **w0** → benefícios **w1** → jornada **w2** → …
- Twig/CSS/library isolados; paragraph `card_icon_text_p` com suggestion por bloco pai.
- Assets em `modules/custom/custom_configs/assets/oportunidades-estudantes/`.
- Deploy: `cim` → `updb` (`11085`) → `cim` → `cr`.

## Fora

- Remerge das duas seções no mesmo bundle.
- Alterar banner / Hero Search / jornada / CTA final além dos weights.
