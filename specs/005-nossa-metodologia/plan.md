# Implementation Plan: Bloco Nossa Metodologia

**Branch**: `dev` (bootstrap manual: `.specify/scripts` e constituição ausentes)  
**Date**: 2026-09-11  
**Spec**: [spec.md](spec.md)

## Summary

Entregar o bloco gerenciável **Nossa Metodologia** (`nossa_metodologia`) com título, subtítulo, **etapas superiores como imagens multi-valor** (`field_image`) e **passos inferiores como paragraphs** (`metodologia_passo_p`: ícone + título). Isso permite reflow responsivo item a item (sem artes desktop/mobile monolíticas).

Estrutura e placement são versionados em `config/sync`; seed/migração via update hooks idempotentes em `custom_configs`. O tema `default` renderiza etapas e passos com Twig + Bootstrap 5 + CSS escopado (setas decorativas entre passos).

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Block Content, Field, File, Image), Drush 13, Bootstrap Barrio 5.5; nenhuma dependência nova  
**Storage**: PostgreSQL 16; schema/configuração via Configuration API; arquivos em `public://`; conteúdo editorial separado de config  
**Testing**: validação funcional, responsiva e de deploy descrita em [quickstart.md](quickstart.md)  
**Target Platform**: Docker Compose local e VPS Drupal  
**Project Type**: CMS Drupal SSR; webroot na raiz do repositório  
**Performance Goals**: uma única imagem transferida por viewport via `<picture>`, `loading="lazy"` e zero JavaScript novo  
**Constraints**: não alterar `core/` ou `vendor/`; reutilizar storages de texto; Clean URLs preservadas; placement apenas em `<front>`; arte sem overflow  
**Scale/Scope**: 1 block type, 2 storages novos de imagem, 4 field instances, 2 displays, 1 placement, 1 Twig, CSS escopado, 1 seed idempotente e atualização cirúrgica do PRD

**Estado verificado (2026-09-11):**

- Feature ativa em `.specify/feature.json`: `specs/005-nossa-metodologia`.
- `field_text_simple` e `field_text_simple_long` já existem em `block_content`.
- `field_image_desktop` e `field_image_mobile` ainda não existem em `block_content`.
- Poppins 300–700 já está carregada em `themes/custom/default/assets/css/style.css`.
- “Nossos Diferenciais” está em `content_full`, `<front>`, weight `-4`; o novo bloco usará weight `-3`.
- O próximo update hook disponível em `custom_configs.install` é `custom_configs_update_11005`.

## Constitution Check

*GATE: aprovado antes da Fase 0 e revalidado após a Fase 1.*

Não existe `.specify/memory/constitution.md`; os gates aplicáveis vêm das regras do projeto e da especificação.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD antes de código | PASS | spec → plan → tasks → implement |
| Não alterar `core/`/`vendor/` | PASS | somente config, tema, módulo custom e documentação |
| Configuration Management | PASS | estrutura e placement em `config/sync` |
| Reuso de storages | PASS | textos reutilizados; apenas duas imagens novas exigidas por FR-004 |
| Performance | PASS | `<picture>`, uma arte por viewport, lazy loading, sem JS |
| Segurança | PASS | sem endpoint novo; renderização Twig escapada e Entity API no seed |
| Clean URLs | PASS | sem rota nova; visibilidade pelo token `<front>` |
| Código custom mínimo | PASS | reutiliza tema e módulo existentes; nenhuma dependência nova |
| Governança do PRD | PASS | atualização limitada à seção 3.6 durante a implementação |

**Resultado**: sem violações; nenhuma justificativa de complexidade necessária.

## Project Structure

### Documentation

```text
specs/005-nossa-metodologia/
├── spec.md
├── checklists/requirements.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/
│   └── block-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
├── block_content.type.nossa_metodologia.yml
├── field.storage.block_content.field_image_desktop.yml
├── field.storage.block_content.field_image_mobile.yml
├── field.field.block_content.nossa_metodologia.field_text_simple.yml
├── field.field.block_content.nossa_metodologia.field_text_simple_long.yml
├── field.field.block_content.nossa_metodologia.field_image_desktop.yml
├── field.field.block_content.nossa_metodologia.field_image_mobile.yml
├── core.entity_form_display.block_content.nossa_metodologia.default.yml
├── core.entity_view_display.block_content.nossa_metodologia.default.yml
├── block.block.default_nossametodologia.yml
└── user.role.moderador.yml

themes/custom/default/
├── templates/block/block--block-nossa-metodologia.html.twig
└── assets/css/style.css

modules/custom/custom_configs/
└── custom_configs.install

PRD.md
```

**Structure Decision**: manter a mesma arquitetura da feature 004: config sync para estrutura, tema para apresentação e `custom_configs` para seed. Não criar módulo, biblioteca, paragraph ou dependência adicional.

## Design Decisions

1. **Modelo**: título e subtítulo reutilizam storages existentes; desktop/mobile usam storages novos, cardinalidade 1.
2. **Renderização**: Twig resolve a arte disponível e usa `<picture>` com breakpoint `767.98px`; se uma arte faltar, a outra atende ambos os viewports.
3. **Layout**: `.container`, header `text-center text-md-end` e diagrama em largura útil abaixo; Poppins no escopo do bloco.
4. **Placement**: região `content_full`, weight `-3`, visibilidade `request_path: <front>`, UUID igual ao seed.
5. **Conteúdo inicial**: `custom_configs_update_11005()` cria somente título/subtítulo; imagens permanecem editoriais. O hook não sobrescreve uma entidade já existente.
6. **Deploy**: `cim` cria estrutura, `updb` cria o conteúdo seed e `cr` atualiza caches. Em ambiente totalmente limpo, validar a dependência do placement e repetir `cim` após o seed se o plugin do UUID não estiver disponível no primeiro import.

## Phases

### Phase 0 — Research

Concluída em [research.md](research.md): Configuration Management, storages, Twig `<picture>`, fallbacks, placement, seed, permissões, performance e governança.

### Phase 1 — Design & Contracts

- [data-model.md](data-model.md): entidades, campos, regras e estados.
- [contracts/block-render.md](contracts/block-render.md): contrato DOM, responsividade e fallbacks.
- [quickstart.md](quickstart.md): cenários executáveis de deploy e aceite.

### Phase 2 — Tasks

Fora deste comando. `/speckit-tasks` deve decompor, nesta ordem:

1. Criar/exportar tipo, storages, instances e displays.
2. Implementar template Twig e CSS.
3. Criar seed `11005`, placement e permissões.
4. Atualizar a seção 3.6 do `PRD.md`.
5. Executar exportação limpa e validar todos os cenários do quickstart.

## Constitution Check (pós-design)

Revalidado após modelo, contrato e quickstart: todos os gates permanecem **PASS**. O desenho não introduz dependências, rotas, SQL direto, alterações de core/vendor ou complexidade não prevista.
