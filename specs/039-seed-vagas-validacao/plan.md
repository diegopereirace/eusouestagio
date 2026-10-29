# Implementation Plan: 039-seed-vagas-validacao

## Technical Context

- Drupal 11; seeds via `hook_update_N` em `custom_configs.install`.
- Imagem no cadastro: reuso `user_picture`.
- Helper de arquivo: `_custom_configs_seed_file_from_module_asset()`.
- Benefícios: `_custom_configs_build_vaga_beneficios_padrao_paragraphs()`.
- FAQ: `_custom_configs_seed_faq_collection_example()`.

## Approach

1. Form + perfil logo (`EmpresaRegistrationForm`, `EmpresaPerfilController`, Twig).
2. 15 assets PNG versionados.
3. Catalog helper + purge + seed empresas/users/vagas.
4. `custom_configs_update_11061` orquestra tudo.
5. Quickstart / contract de deploy com aviso destrutivo.

## Deploy

Destino: `cim` → `updb` (`11061`) → `cim` → `cr`.

## Complexity

Média — volume de seed, sem mudança estrutural de CT (exceto UX do form).
