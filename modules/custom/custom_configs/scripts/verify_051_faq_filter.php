<?php

/**
 * @file
 * Valida filtro de itens parciais / vazios no FAQ padrão (US3 T016/T018).
 */

use Drupal\paragraphs\Entity\Paragraph;

$faq = custom_configs_load_faq_padrao();
if (!$faq || !$faq->hasField('field_faq_itens')) {
  echo "FAIL: FAQ padrão ausente\n";
  return;
}

$original_refs = [];
foreach ($faq->get('field_faq_itens') as $item) {
  $original_refs[] = [
    'target_id' => $item->target_id,
    'target_revision_id' => $item->target_revision_id,
  ];
}

$invalid = Paragraph::create([
  'type' => 'faq_item_p',
  'field_pergunta' => 'Só pergunta sem resposta',
  'field_resposta' => '',
]);
$invalid->save();

$valid = Paragraph::create([
  'type' => 'faq_item_p',
  'field_pergunta' => 'Pergunta válida temporária',
  'field_resposta' => 'Resposta válida temporária',
]);
$valid->save();

$faq->set('field_faq_itens', [
  [
    'target_id' => $invalid->id(),
    'target_revision_id' => $invalid->getRevisionId(),
  ],
  [
    'target_id' => $valid->id(),
    'target_revision_id' => $valid->getRevisionId(),
  ],
]);
$faq->save();
\Drupal::service('entity.memory_cache')->deleteAll();

$nids = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'vagas')
  ->condition('status', 1)
  ->range(0, 1)
  ->execute();
$nid = $nids ? (int) reset($nids) : 0;
echo "sample_nid={$nid}\n";
if (!$nid) {
  echo "FAIL: sem vaga publicada\n";
  return;
}

$html = '';
if ($nid) {
  $node = \Drupal\node\Entity\Node::load($nid);
  $html = (string) \Drupal::service('renderer')->renderRoot(
    \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, 'full')
  );
}

echo 'mix_partial: invalid_q=' . (str_contains($html, 'Só pergunta sem resposta') ? 'LEAK' : 'filtered');
echo ' valid_q=' . (str_contains($html, 'Pergunta válida temporária') ? 'shown' : 'missing') . PHP_EOL;

// Zero válidos.
$faq->set('field_faq_itens', [
  [
    'target_id' => $invalid->id(),
    'target_revision_id' => $invalid->getRevisionId(),
  ],
]);
$faq->save();
\Drupal::service('entity.memory_cache')->deleteAll();
$html0 = '';
if ($nid) {
  $node = \Drupal\node\Entity\Node::load($nid);
  $html0 = (string) \Drupal::service('renderer')->renderRoot(
    \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, 'full')
  );
}
echo 'zero_valid: section=' . (str_contains($html0, 'Dúvidas Frequentes') ? 'PRESENT' : 'absent') . PHP_EOL;

// Restaura.
$faq->set('field_faq_itens', $original_refs);
$faq->save();
$invalid->delete();
$valid->delete();
\Drupal::service('entity.memory_cache')->deleteAll();
echo "restored original FAQ items\n";
