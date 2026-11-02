# Quickstart: Como funciona — imagens

**Feature**: [spec.md](spec.md) | **Data**: 2026-11-02

```bash
# Local
docker compose exec -T drupal vendor/bin/drush cim -y
docker compose exec -T drupal vendor/bin/drush updb -y
docker compose exec -T drupal vendor/bin/drush cim -y
docker compose exec -T drupal vendor/bin/drush cr

# Produção (/var/www/html) — follow-up form display (11067)
git pull
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php updb -y
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr
```

## Esperado

- Campos “Imagem desktop” (`field_image`) e “Imagem mobile” (`field_imagem_mobile`) no bloco **e no form admin**
- Seed `11062` anexa WebPs de `assets/como-funciona/` (`como-funciona-desktop-hd.webp` ~2347x274, `como-funciona-mobile-v2.webp` ~1020x1980) se os campos estiverem vazios
- Hook `11063` troca a arte seed antiga (desktop 1024px / mobile 2 colunas) pelas novas; upload editorial diferente é preservado
- Hook `11067` (follow-up): garante form/view display das imagens quando o front já tem seed mas o admin omite os campos
- `.cf-flow-media` sem margens (centralização via flex)
- Home: `<picture class="cf-flow-media">` com `media` de `default.mobile` (Breakpoints API)
- HTML único no cache (sem variação por UA); browser escolhe a fonte
- Regenerar as duas artes (opcional; requer Pillow e fontes Poppins): `python modules/custom/custom_configs/assets/como-funciona/generate_images.py <dir-fontes>`

## Aceite

1. Desktop (≥768px): arte horizontal sem distorção
2. Mobile (<768px): arte adaptada em fluxo vertical (7 chevrons para baixo); Network mostra só o WebP mobile
3. Editar bloco Como funciona → vê Imagem desktop e Imagem mobile
4. Re-executar `updb` → imagens preservadas
