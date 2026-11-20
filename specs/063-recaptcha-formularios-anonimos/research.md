# Research: reCAPTCHA nos formulários anônimos

## R1 — Módulo

**Decision**: Manter `captcha` + `recaptcha` + `recaptcha_v3`.  
**Rationale**: Releases estáveis com advisory no Drupal.org (captcha 2.x, recaptcha 8.x-3.5, recaptcha_v3 2.0.5). `simple_recaptcha` está em beta sem release estável coberta.  
**Alternatives**: `simple_recaptcha` (rejeitado); `enable_globally` (rejeitado: cache e busca).

## R2 — Experiência

**Decision**: v3 invisível no envio; checkbox v2 só se o score for menor que 0.5 ou o token falhar.  
**Rationale**: O módulo v3 já tem ação, limiar e `default_challenge`. O widget v2 do 8.x-3.5 só oferece tamanho normal e compacto, não invisível.  
**Alternatives**: Checkbox v2 em todo envio (atrito); esconder o selo (viola a marca do Google após a política de 2026).

## R3 — Onde colocar

**Decision**: Pontos CAPTCHA com tipo `default`, não elemento hardcoded no PHP.  
**Rationale**: Um `default_challenge` muda todos. O módulo insere o elemento antes do botão. `$settings['disable_captcha']` continua valendo no módulo CAPTCHA.  
**Alternatives**: Manter o `#type => captcha` nos cadastros (duplicaria o ponto).

## R4 — Contato

**Decision**: Ponto com id `webform_submission_contato_form`.  
**Rationale**: `WebformSubmissionForm::getBaseFormId()` devolve `webform_submission_{id}_form`. O CAPTCHA cai no id base quando o id completo inclui o node.  
**Alternatives**: Elemento CAPTCHA dentro do YAML do webform (funciona, mas foge da lista única de pontos).

## R5 — Segredos

**Decision**: `$config` override em `settings.php`, nunca no sync.  
**Rationale**: Os YAML atuais têm `secret_key: ''`. Um `cim` apaga segredo que exista só no banco se o override não existir antes.  
**Alternatives**: Colar segredo no YAML (proibido pelo PRD §6.3).
