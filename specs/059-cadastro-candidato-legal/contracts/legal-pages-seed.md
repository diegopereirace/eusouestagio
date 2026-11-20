# Contract: Seed Páginas Legais

**Feature**: [../spec.md](../spec.md) | **Data**: 2026-11-04

## Objetivo

Garantir que `/termos-de-uso` e `/politica-de-privacidade` respondam 200 com Nodes `page` publicados após `drush updb` (hook `11089`), sem passos manuais no admin.

## Entradas canônicas

| Título | UUID | Alias |
|--------|------|-------|
| Termos de Uso | `a1b2c3d4-e5f6-4789-a012-3456789abc01` | `/termos-de-uso` |
| Política de Privacidade | `a1b2c3d4-e5f6-4789-a012-3456789abc02` | `/politica-de-privacidade` |

## Pré-condição

Bundle `node` type `page` disponível (via `cim` se necessário). Se ausente → mensagem orientando `drush cim -y` e retorno seguro (sem fatal).

## Comportamento idempotente

Para cada página:

1. **Load por UUID** → se existir e for `page`, usar.
2. **Senão, load por alias** → se alias aponta para `page` publicado, **reusar** (não criar segundo Node; não deletar o alvo).
3. **Senão criar** Node com UUID (se livre), título, `status=1`, `uid=1`.
4. **Body**: se field `body` vazio → gravar HTML placeholder (≥1 subtítulo + ≥2 parágrafos). Se body não vazio → **no-op**.
5. **Alias**: se path do Node não tiver o alias estrito → criar `path_alias` (`langcode: pt-br`). Se alias já aponta para este Node → no-op.
6. Mensagens Drush: created / reused / skipped / alias-ok.

## Placeholder (mínimo)

```html
<h2>1. Introdução</h2>
<p>Conteúdo placeholder. Substitua pelo texto jurídico definitivo.</p>
<p>Esta página é editável pelo time editorial sem novo deploy de copy.</p>
```

(estrutura equivalente aceitável; format `full_html` ou formato já usado em `page` no projeto.)

## Fora do contrato

- Redação jurídica definitiva.
- Traduções adicionais além de `pt-br`.
- Remoção de Nodes editoriais existentes com outro UUID (exceto não criar duplicata no mesmo alias).
- Alteração de menus legados do rodapé (Twig já hardcoda as URLs).

## Verificação

```text
GET /termos-de-uso → 200 + título Termos de Uso
GET /politica-de-privacidade → 200 + título Política de Privacidade
drush updb -y (2ª vez) → sem novos nids para os mesmos UUIDs; body editorial intacto
```
