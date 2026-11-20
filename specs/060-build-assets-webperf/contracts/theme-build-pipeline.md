# Contract: Pipeline de build do tema `default`

**Feature**: [../spec.md](../spec.md) | **Data**: 2026-11-04

## Escopo

Assets **próprios** do tema em `themes/custom/default` (CSS/JS). Exclui `/libraries/` (Bootstrap, Font Awesome, Popper, jQuery Mask).

## Interface (DX)

| Comando | Diretório | Resultado esperado |
|---------|-----------|-------------------|
| `npm install` | `themes/custom/default` | Dependências em `node_modules/` (não versionado) |
| `npm run dev` ou `npm run watch` | idem | Processo ativo; mudança em `src/` atualiza `dist/` |
| `npm run build` | idem | Exit 0; `dist/css` e `dist/js` minificados; ≤60s após install (SC-002) |

## Contratos de caminho

| Entrada | Saída |
|---------|-------|
| `src/css/<path>.css` ou `.scss` | `dist/css/<path>.css` |
| `src/js/<path>.js` | `dist/js/<path>.js` |

Nomes de arquivo em `dist/` estáveis o suficiente para `default.libraries.yml` (evitar hash obrigatório no MVP).

## Contrato `default.libraries.yml`

Para cada library **própria** listada no plan/spec:

- Path CSS/JS MUST apontar para `dist/...`.
- MUST NOT apontar para `assets/css` ou `assets/js` como canônico pós-migração.
- Libraries de terceiros MUST permanecer com paths `/libraries/...`.

## Falhas

- Erro de sintaxe SCSS/CSS/JS → build falha com mensagem clara (exit ≠ 0); não “sucesso silencioso” com artefato corrompido opaco.
- Commit de mudança em `src/` sem `dist/` regenerado = regressão de produção (checklist de PR).

## Fora deste contrato

- Minificar terceiros.
- Servir `src/` diretamente no Drupal.
- Exigir Node no servidor de produção.
