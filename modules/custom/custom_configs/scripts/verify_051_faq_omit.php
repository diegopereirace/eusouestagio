<?php

/**
 * @file
 * Valida omitir seção FAQ quando padrão unpublished (US3) e restaura.
 */

$faq = custom_configs_load_faq_padrao();
if (!$faq) {
  echo "FAIL: FAQ padrão ausente\n";
  return;
}

$nids = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'vagas')
  ->condition('status', 1)
  ->range(0, 2)
  ->execute();
$view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
$renderer = \Drupal::service('renderer');

$faq->setUnpublished()->save();
\Drupal::service('entity.memory_cache')->deleteAll();
echo "unpublished faq\n";
foreach ($nids as $nid) {
  $node = \Drupal\node\Entity\Node::load($nid);
  $html = (string) $renderer->renderRoot($view_builder->view($node, 'full'));
  echo sprintf("nid=%d section=%s\n", $nid, str_contains($html, 'Dúvidas Frequentes') ? 'PRESENT' : 'absent');
}

$faq = custom_configs_load_faq_padrao();
$faq->setPublished()->save();
\Drupal::service('entity.memory_cache')->deleteAll();
echo "republished faq\n";
foreach ($nids as $nid) {
  $node = \Drupal\node\Entity\Node::load($nid);
  $html = (string) $renderer->renderRoot($view_builder->view($node, 'full'));
  echo sprintf("nid=%d section=%s\n", $nid, str_contains($html, 'Dúvidas Frequentes') ? 'PRESENT' : 'absent');
}
