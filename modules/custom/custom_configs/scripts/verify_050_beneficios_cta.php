<?php

/**
 * @file
 * Verifica feature 050 (benefícios taxonomia + CTA). Uso:
 *   drush php:script modules/custom/custom_configs/scripts/verify_050_beneficios_cta.php
 */

$etm = \Drupal::entityTypeManager();
$term_storage = $etm->getStorage('taxonomy_term');
$node_storage = $etm->getStorage('node');
$config = \Drupal::config('block.block.default_ctav1vagas');

$vocab = \Drupal\taxonomy\Entity\Vocabulary::load('beneficios');
$expected = [
  'Plano de Carreira',
  'Vale Transporte',
  'Vale Refeição',
  'Horário Flexível',
  'Assistência Médica',
  'Auxílio Academia / Gympass',
  'Day Off no Aniversário',
];

$terms = $vocab
  ? $term_storage->loadByProperties(['vid' => 'beneficios'])
  : [];
$term_names = [];
$with_icon = 0;
foreach ($terms as $term) {
  /** @var \Drupal\taxonomy\TermInterface $term */
  $term_names[] = $term->label();
  if ($term->hasField('field_image') && !$term->get('field_image')->isEmpty()) {
    $with_icon++;
  }
}
sort($term_names);
$expected_sorted = $expected;
sort($expected_sorted);

$form = $etm->getStorage('entity_form_display')->load('node.vagas.default');
$empresa_u_hidden = $form && $form->getComponent('field_empresa_u') === NULL;
$beneficios_t = $form ? $form->getComponent('field_vaga_beneficios_t') : NULL;
$beneficios_t_ok = $beneficios_t
  && ($beneficios_t['type'] ?? '') === 'options_buttons';
$paragraphs_hidden = $form && $form->getComponent('field_vaga_beneficios') === NULL;
$text_free_hidden = $form && $form->getComponent('field_text_simple_multiple_2') === NULL;

$field = \Drupal\field\Entity\FieldConfig::loadByName('node', 'vagas', 'field_vaga_beneficios_t');
$required = $field && $field->isRequired();

$empty_nids = $node_storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'vagas')
  ->notExists('field_vaga_beneficios_t')
  ->execute();

$visibility = $config->get('visibility') ?? [];
$cta_role = isset($visibility['user_role']['roles']['candidato'])
  && ($visibility['user_role']['negate'] ?? TRUE) === FALSE;
$cta_bundle = isset($visibility['entity_bundle:node']['bundles']['vagas']);

$lines = [
  'vocab_beneficios=' . ($vocab ? 'ok' : 'MISSING'),
  'terms_count=' . count($terms),
  'terms_names_match=' . ($term_names === $expected_sorted ? 'ok' : 'MISMATCH'),
  'terms_with_icon=' . $with_icon . '/' . count($terms),
  'form_empresa_u_hidden=' . ($empresa_u_hidden ? 'ok' : 'FAIL'),
  'form_beneficios_t_checkboxes=' . ($beneficios_t_ok ? 'ok' : 'FAIL'),
  'form_paragraphs_hidden=' . ($paragraphs_hidden ? 'ok' : 'FAIL'),
  'form_text_free_hidden=' . ($text_free_hidden ? 'ok' : 'FAIL'),
  'field_vaga_beneficios_t_required=' . ($required ? 'ok' : 'FAIL'),
  'vagas_sem_beneficios_t=' . count($empty_nids),
  'cta_visibility_candidato=' . ($cta_role ? 'ok' : 'FAIL'),
  'cta_visibility_vagas=' . ($cta_bundle ? 'ok' : 'FAIL'),
];

if ($empty_nids) {
  $sample = array_slice(array_values($empty_nids), 0, 20);
  $lines[] = 'vagas_sem_beneficios_t_nids=' . implode(',', $sample);
}

echo implode("\n", $lines) . "\n";
