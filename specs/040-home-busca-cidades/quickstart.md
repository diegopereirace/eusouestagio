# Quickstart: Home — autocomplete de cidades

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-01

Comandos Drush locais: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).  
Produção: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

## Pré-requisitos

1. Stack local no ar; seed de vagas com cidades/UFs conhecidas (ex. feature `039` ou dados equivalentes).
2. Branch com alterações em `custom_banners` + tema `default` (+ PRD).
3. **Nenhum** dump necessário; **nenhum** `hook_update_N` obrigatório.

Contratos: [home-search-cidade.md](contracts/home-search-cidade.md), [cidades-autocomplete.md](contracts/cidades-autocomplete.md), [deploy-home-busca-cidades.md](contracts/deploy-home-busca-cidades.md).  
Modelo: [data-model.md](data-model.md).

---

## A) Deploy / cache (obrigatório)

### Destino / local após pull

```bash
docker compose exec drupal drush cr
```

(Esteira completa `cim` → `updb` → `cim` → `cr` é aceitável e deve ser no-op para config/hooks desta feature.)

**Gates:** assets `hero_search` servidos; rota `/api/cidades/autocomplete` responde 200; zero admin.

---

## B) Layout Figma (US1 → SC-001)

1. Abrir a Home em desktop (≥992px); comparar barra de busca com o frame (~896×86).
2. Mobile (≤575.98px): curso, cidade e Buscar usáveis; sem scroll horizontal causado pela barra.

---

## C) Autocomplete de cidades (US2 → SC-002, SC-003, SC-005)

1. Placeholder/label do campo = **“Cidade”** (sem “Remoto”).
2. Digitar `sao paulo` e `FORTALEZA` (ou cidades seedadas); sugestões no formato `Cidade - UF - (N)` com N ≥ 1.
3. Selecionar sugestão → Buscar → URL com `cidade=` (e `estado=` quando selecionado) e listagem filtrada.
4. Cidade sem vagas ativas → não aparece no dropdown.
5. Endpoint direto: `GET /api/cidades/autocomplete?q=sao` → JSON conforme contrato.

---

## D) Pill Remoto ausente (US3 → SC-004)

1. Abaixo do form: pills de curso esperadas; **sem** “Remoto”.
2. Clique em pill de curso → `/vagas?cursos=…`.

---

## E) Deploy automatizado (US4 → SC-004)

1. Em ambiente “frio”, aplicar só Git + `drush cr` (ou receita completa).
2. Repetir B–D sem abrir o admin.

---

## F) Isolamento

1. Filtro `regime` na listagem/sidebar (se existir) continua disponível — não foi removido do sistema.
2. Hero `/vagas`: copy do campo localização = “Cidade”; layout 027 intacto.
3. Autocomplete de cursos da Home permanece funcional.

---

## Checklist SC

| SC | Como verificar |
|----|----------------|
| SC-001 | B |
| SC-002 | C (latência percebida) |
| SC-003 | C (formato + N ≥ 1) |
| SC-004 | D + E |
| SC-005 | C passo 3 (≤3 interações) |
