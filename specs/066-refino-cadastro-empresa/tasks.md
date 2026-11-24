# Tasks: 066-refino-cadastro-empresa

- [x] T001 Spec/plan/tasks + feature.json + specify-rules
- [x] T002 (superseded) Hero estático 1280×356 — substituído por banner CMS
- [x] T003 page--cadastro--empresa.html.twig + cadastro-empresa.css + library + body class
- [x] T004 Refatorar EmpresaRegistrationForm (cartões, ícones, attach library)
- [x] T005 Ativar banners block_3 + filtro título “Banner - Cadastro Empresa” + drush cex
- [x] T006 page.banner no Twig + CSS do hero CMS (sem overlay estático)
- [x] T007 drush cr + smoke /cadastro/empresa (banner do node/39) e regressão candidato
- [x] T008 Artes reais no node 39: `assets/banner-cadastro-empresa/desktop.jpg` (1800×500) e `mobile.jpg` (828×460, recorte/texto próprios p/ tela pequena)
- [x] T009 `custom_configs_update_11096` idempotente aplica desktop+mobile no node (ordem explícita ⇒ `$replace = TRUE`); CSS do hero com altura natural
