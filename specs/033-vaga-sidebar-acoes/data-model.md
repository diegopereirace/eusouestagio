# Data Model: Sidebar — Ações e Resumo

**Feature**: `033-vaga-sidebar-acoes`  
**Date**: 2026-10-18

## Campos novos (bundle `vagas`)

| Campo | Tipo | Cardinalidade | Obrigatório | Label | Exemplo |
|-------|------|---------------|-------------|-------|---------|
| `field_vaga_periodo` | string (`max_length` 255) | 1 | não | Período | `6 meses +` |
| `field_vaga_quantidade` | integer (`unsigned: true`, `size: normal`) | 1 | não | Vagas | `2` (UI: `02`) |

Storage novo nos dois casos (R3). Sem valor default no field config — o seed preenche a vaga de demonstração.

## Campos reutilizados

| Uso no resumo / ações | Campo | Notas |
|-----------------------|-------|--------|
| Bolsa Auxílio | `field_text_simple` | já no bundle |
| Modelo | `field_regime_t` | termo `regime`, ex. Presencial |
| Localização do seed | `field_cidade`, `field_estados` | Curitiba + `PR`. A lista legada não tinha a sigla `PR` (Paraná estava rotulada na sigla `PA`). O hook acrescenta `PR` = Paraná e, se o rótulo de `PA` ainda for “Paraná”, corrige para “Pará”. |
| Carga (header, não é linha do resumo) | `field_vaga_carga_horaria` | `30h semanais` |
| Empresa | `field_vaga_empresa` | node `empresa` |
| Turno | `field_horarios` | permanece; **fora** do resumo |

## Displays

| Display | Componentes |
|---------|-------------|
| `core.entity_form_display.node.vagas.default` | `field_vaga_periodo` → `string_textfield` (weight 18); `field_vaga_quantidade` → `number` (weight 19). Só adiciona se o componente ainda não existe. |
| `core.entity_view_display.node.vagas.default` | ambos em `hidden` (Twig full consome a entity). |

## Seeds (UUIDs fixos — feature 032)

| UUID | Bundle | Título | Papel nesta feature |
|------|--------|--------|---------------------|
| `f6a7b8c9-d0e1-4234-e567-89abcdef0123` | `empresa` | EcoConstrutora | reutilizado; sem campo novo |
| `a7b8c9d0-e1f2-4345-f678-9abcdef01234` | `vagas` | Engenheiro Civil | período, quantidade, e alinhamento do trio legado |

### Valores-alvo da vaga seed

| Campo | Valor |
|-------|--------|
| `field_vaga_empresa` | EcoConstrutora |
| `field_cidade` | Curitiba |
| `field_estados` | PR |
| `field_regime_t` | termo Presencial |
| `field_vaga_carga_horaria` | 30h semanais |
| `field_text_simple` | R$ 1.500/mês |
| `field_vaga_periodo` | 6 meses + |
| `field_vaga_quantidade` | 2 |

### Regra de escrita

1. Node ausente → cria (helpers 032, defaults atualizados para o layout desta feature quando o campo já existe).
2. Campo vazio → preenche o valor-alvo.
3. Trio ainda igual ao seed 032 (`São Paulo`, `SP`, `R$ 1.800,00`) → substitui por Curitiba / PR / `R$ 1.500/mês`.
4. Qualquer outro valor já gravado → não altera.

## Rotas (somente leitura — sem config nova)

| Nome | Path | Método | Parâmetro |
|------|------|--------|-----------|
| `custom_panel.vagas_candidatar` | `/vaga/apply/{node}` | POST | `node` = ID da vaga |
| `custom_panel.vagas_salvas_toggle` | `/painel/estudante/vagas-salvas/toggle` | POST | corpo `nid` |
