# Plan: 064 Ícones laterais Paragraph

- Wrapper AJAX (`#instituicoes-wrapper`, `#cursos-extracurriculares-wrapper`, `#experiencias-wrapper`) vira grid 2 colunas: `.cadastro-paragraph-items` + `.cadastro-paragraph-rail`.
- Rail com `align-self: end`, `column-reverse` (+ embaixo fixo, − acima).
- Submit continua `<input type="submit">`; ícone via `background-image` (SVG data URI); texto oculto.
- Tooltip via `<span class="cadastro-icon-action" data-tooltip>` (`#prefix`/`#suffix`) + `::after`.
- `#ajax.progress.type = none`; estado `:disabled` estilizado.
- Helper `paragraphActions()` em `CandidatoRegistrationForm` e `CandidatoEditForm`.
- CSS: `cadastro-candidato.css` (cadastro) e `style.css` (painel).
- Sem mudança estrutural/DB.
