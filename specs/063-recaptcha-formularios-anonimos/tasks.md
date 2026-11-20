# Tasks: reCAPTCHA nos formulários anônimos

**Input**: `specs/063-recaptcha-formularios-anonimos/`  
**Hook**: nenhum  
**Config estrutural**: pontos CAPTCHA, ação v3, papel moderador, uninstall `image_captcha`

## Phase 1: Setup

- [X] T001 Criar artefatos SDD e apontar feature ativa em `.specify/feature.json` e `.cursor/rules/specify-rules.mdc`

## Phase 2: Foundational

- [X] T002 Documentar override dos `secret_key` v2 e v3 em `sites/default/settings.php` e `sites/default/settings.php.prod`, sem valor real no Git
- [X] T003 Garantir `enable_globally: 0` e não proteger busca nem filtros de Views

## Phase 3: US1 e US2 — Verificação invisível com reserva

- [X] T004 [US1] Criar ação `formulario_publico` (limiar 0.5, reserva `default`) e apontar `captcha.settings` `default_challenge` para `recaptcha_v3/formulario_publico`
- [X] T005 [US2] Apontar `recaptcha_v3.settings` `default_challenge` para `recaptcha/reCAPTCHA` e mensagem de erro em pt-BR; manter selo visível, hostname e cache desligado
- [X] T006 [US1] Ativar pontos `default` em login, senha, registro, cadastro estudante, cadastro empresa e `webform_submission_contato_form`
- [X] T007 [US1] Remover o elemento CAPTCHA hardcoded de `CandidatoRegistrationForm` e `EmpresaRegistrationForm`

## Phase 4: US3 — Operação

- [X] T008 [US3] Conceder `skip CAPTCHA` ao papel `moderador`
- [X] T009 [US3] Desinstalar `image_captcha` do `core.extension` e remover `image_captcha.settings.yml`

## Phase 5: US4 — Renderização

- [X] T010 [US4] Ajustar `.captcha` e `.grecaptcha-badge` em `themes/custom/default/src/css/style.css` e gerar `dist/css/style.css`

## Phase 6: Verificação e PRD

- [X] T011 Importar config (`drush cim`) e reconstruir cache
- [X] T012 Atualizar PRD §1.4, §4.2, §5.2 e §6.4
- [X] T013 Validar os cinco formulários e a listagem de vagas no browser

## Dependencies

T001 → T002–T010 → T011 → T013. T012 pode seguir T006. T007 depende de T006 para não ficar formulário sem proteção.
