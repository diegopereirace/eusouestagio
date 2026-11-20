<?php

/**
 * @file
 * Verifica seed 039. Uso: drush php:script modules/custom/custom_configs/scripts/verify_039_seed.php
 */

$node_storage = \Drupal::entityTypeManager()->getStorage('node');
$user_storage = \Drupal::entityTypeManager()->getStorage('user');

$vagas = $node_storage->loadByProperties(['type' => 'vagas']);
$empresas = $node_storage->loadByProperties(['type' => 'empresa']);
$users = 0;
for ($i = 1; $i <= 15; $i++) {
  $pad = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
  $found = $user_storage->loadByProperties(['name' => 'empresa-seed-' . $pad]);
  if ($found) {
    $users++;
  }
}

$legacy = $node_storage->loadByProperties(['uuid' => 'a7b8c9d0-e1f2-4345-f678-9abcdef01234']);
$eco = $node_storage->loadByProperties(['uuid' => 'f6a7b8c9-d0e1-4234-e567-89abcdef0123']);

$sample = $node_storage->loadByProperties(['uuid' => 'b039v001-0001-4000-8000-000000000001']);
/** @var \Drupal\node\NodeInterface|null $vaga */
$vaga = $sample ? reset($sample) : NULL;

$lines = [
  'vagas=' . count($vagas),
  'empresas=' . count($empresas),
  'users_seed=' . $users,
  'legacy_engenheiro=' . ($legacy ? 'STILL_EXISTS' : 'gone'),
  'legacy_eco=' . ($eco ? 'STILL_EXISTS' : 'gone'),
];

if ($vaga) {
  $emp = $vaga->get('field_vaga_empresa')->entity;
  $usr = $vaga->hasField('field_empresa_u') && !$vaga->get('field_empresa_u')->isEmpty()
    ? $vaga->get('field_empresa_u')->entity
    : NULL;
  $lines[] = 'sample_title=' . $vaga->label();
  $lines[] = 'sample_empresa=' . ($emp ? $emp->label() : 'none');
  $lines[] = 'sample_user=' . ($usr ? $usr->getAccountName() : 'none');
  $lines[] = 'sample_beneficios=' . $vaga->get('field_vaga_beneficios')->count();
  $lines[] = 'sample_faq=' . ($vaga->get('field_vaga_faq')->isEmpty() ? 'empty' : 'ok');
  $lines[] = 'sample_logo=' . ($emp && $emp->hasField('field_imagem') && !$emp->get('field_imagem')->isEmpty() ? 'ok' : 'missing');
  $lines[] = 'sample_sobre=' . ($vaga->get('field_text_long_formatted')->isEmpty() ? 'empty' : 'ok');
  $lines[] = 'sample_requisitos=' . ($vaga->get('field_vaga_requisitos')->isEmpty() ? 'empty' : 'ok');
}

echo implode("\n", $lines) . "\n";
