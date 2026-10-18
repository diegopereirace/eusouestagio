# Research: 034-empresa-pagina-publica

## R1 — Página empresa vs manter card na vaga

**Decision**: remover card da vaga; página canônica do CT `empresa`.  
**Rationale**: pedido do produto; 032 já criou CT público.

## R2 — Campos “básicos”

**Decision**: reutilizar `field_text_long_formatted`, `field_cidade`, `field_estados`.  
**Rationale**: regra de reuso; suficiente para versão temporária.

## R3 — Lista de vagas

**Decision**: View dedicada `empresa_vagas` + contextual ER.  
**Rationale**: exportável, aberta a mudanças futuras sem acoplar à listagem `/vagas`.

## R4 — Embed no Twig

**Decision**: `drupal_view()` via Twig Tweak (já no projeto).  
**Rationale**: sem bloco placement por path.

## R5 — Legado `field_empresa_u`

**Decision**: não remover storage; só remover seção Twig.  
**Rationale**: painel/candidaturas ainda dependem do user ER.
