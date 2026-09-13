# Contract: Renderização do Rodapé (Footer)

**Feature**: [spec.md](../spec.md)  
**Consumidor**: tema `default` (região `footer_first` + float WhatsApp)  
**Produtor**: `block_content` bundle `footer` + Twig hardcoded

## Objetivo

Definir DOM estável, contratos de link, responsividade (accordion), fallbacks e empilhamento do botão flutuante.

## Estrutura DOM (desktop / mobile)

```html
<footer class="site-footer-custom">
  <div class="block-default-footer">
    <div class="row footer-main">
      <!-- Col 1: marca — sempre visível -->
      <div class="footer-brand">
        <a class="footer-logo-link" href="/"><img class="footer-logo-img" alt="…"></a>
        <div class="footer-social d-flex gap-2">
          <!-- mailto / Instagram / LinkedIn / wa.me — omitir se vazio -->
          <a class="footer-social-link footer-social-link--email" …></a>
          …
        </div>
      </div>

      <!-- Cols 2–4: desktop grid | mobile accordion (Collapse) -->
      <section class="footer-menu-section" data-footer-section="institucional">…</section>
      <section class="footer-menu-section" data-footer-section="estudantes">…</section>
      <section class="footer-menu-section" data-footer-section="empresas">…</section>

      <!-- Col 5: contato — sempre visível -->
      <div class="footer-contact-col">
        <h3 class="footer-menu-title">CONTATO</h3>
        <ul class="footer-contact">
          <li><a href="mailto:…">…</a></li>
          <li><a href="tel:…">…</a></li>
        </ul>
      </div>
    </div>

    <hr class="footer-divider">
    <div class="footer-bottom text-center">
      <div class="footer-copy">© {YEAR} Eu Sou Estágio. Todos os direitos reservados.</div>
      <div class="footer-dev">Desenvolvido por Diego Pereira</div>
    </div>
  </div>
</footer>

<!-- Componente separado -->
<div class="whatsapp-float-block" style="/* position:fixed; z-index:1050 */">…</div>
```

## Contrato de links

| Origem | `href` | Atributos |
|--------|--------|-----------|
| Logo | `path('<front>')` | — |
| Social e-mail | `mailto:{field_email}` | — |
| Social Instagram/LinkedIn | URI do campo | `target="_blank"` `rel="noopener noreferrer"` |
| Social WhatsApp | `https://wa.me/{digits}` | `target="_blank"` `rel="noopener noreferrer"` |
| Colunas 2–4 | paths da tabela em [data-model.md](../data-model.md) | URLs limpas; futuras podem 404 |
| Contato e-mail | `mailto:` | — |
| Contato telefone | `tel:{digits}` | rótulo = valor editorial |

## Contrato responsivo

| Viewport | Comportamento |
|----------|---------------|
| ≥992px (`lg+`) | 5 colunas no grid; títulos uppercase; sem accordion |
| &lt;992px | marca + contato visíveis; seções 2–4 Collapse (expandir/recolher pelo título) |

## Contrato visual

| Elemento | Regra |
|----------|-------|
| Fundo | azul-marinho escuro (base `#023c62`; hex final do anexo 2) |
| Tipografia | Poppins, texto branco |
| Títulos colunas | uppercase |
| Sociais | círculos com cores de marca |
| Barra inferior | centralizada; sem links legais; crédito sem âncora |
| CSS | seletores sob `.site-footer-custom` / `.whatsapp-float-block` |

## Contrato de fallback

Ver tabela em [data-model.md](../data-model.md). Sem `|raw` em conteúdo editorial.

## Contrato de placement / seed

- Placement: `default_footer` → `footer_first`
- UUID: `d0326db5-fc80-4cf5-a0b9-8f779dc4aeba`
- Deploy: `drush cim -y` → `drush updb -y` (`11011`) → `drush cr`
- Float: `position: fixed`; `z-index: 1050`

## Acessibilidade

- Títulos de seção como heading (`h3`) / botões de accordion com `aria-expanded` (padrão Bootstrap).
- Ícones sociais com `title`/`aria-label` e `aria-hidden` no `<i>`.
- Preservar `title_prefix`, `title_suffix`, `attributes`, `content_attributes`.
