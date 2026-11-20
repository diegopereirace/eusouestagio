<?php

/**
 * @file
 * Cria Image Style contato_painel (scale 640 + WebP) — feature 058 / FR-010a.
 *
 * Uso: docker compose exec -T drupal vendor/bin/drush php:script modules/custom/custom_configs/scripts/create_contato_painel_style.php
 */

use Drupal\image\Entity\ImageStyle;

$id = 'contato_painel';
$existing = ImageStyle::load($id);
if ($existing) {
  echo "EXISTS:{$existing->id()}\n";
  return;
}

$style = ImageStyle::create([
  'name' => $id,
  'label' => 'Contato — Painel (640w WebP)',
]);
$style->addImageEffect([
  'id' => 'image_scale',
  'weight' => 0,
  'data' => [
    'width' => 640,
    'height' => NULL,
    'upscale' => FALSE,
  ],
]);
$style->addImageEffect([
  'id' => 'image_convert_avif',
  'weight' => 2,
  'data' => [
    'extension' => 'webp',
  ],
]);
$style->save();
echo "CREATED:{$style->id()}\n";
