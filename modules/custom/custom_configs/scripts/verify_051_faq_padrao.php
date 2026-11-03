<?php

/**
 * @file
 * Verificação rápida feature 051 (FAQ padrão).
 *
 * Uso: docker compose exec -T drupal vendor/bin/drush php:script
 *   modules/custom/custom_configs/scripts/verify_051_faq_padrao.php
 */

\Drupal::moduleHandler()->loadInclude('custom_configs', 'install');

$faq = custom_configs_load_faq_padrao();
echo 'faq=' . ($faq ? $faq->id() . ' pub=' . ($faq->isPublished() ? '1' : '0') . ' title=' . $faq->getTitle() : 'NULL') . PHP_EOL;

if ($faq && $faq->hasField('field_faq_itens')) {
  $valid = 0;
  foreach ($faq->get('field_faq_itens') as $item) {
    $para = $item->entity;
    if (!$para) {
      continue;
    }
    $p = trim((string) ($para->get('field_pergunta')->value ?? ''));
    $r = trim((string) ($para->get('field_resposta')->value ?? ''));
    if ($p !== '' && $r !== '') {
      $valid++;
      echo "  item: {$p}" . PHP_EOL;
    }
  }
  echo "valid_items={$valid}" . PHP_EOL;
}

echo _custom_configs_ensure_vaga_faq_form_hidden() . PHP_EOL;
echo _custom_configs_ensure_faq_structure() . PHP_EOL;

$form = \Drupal::entityTypeManager()->getStorage('entity_form_display')->load('node.vagas.default');
echo 'vaga_faq_form_component=' . var_export($form ? $form->getComponent('field_vaga_faq') : 'no-form', TRUE) . PHP_EOL;

$faq_form = \Drupal::entityTypeManager()->getStorage('entity_form_display')->load('node.faq.default');
echo 'faq_field_resposta_component=' . var_export($faq_form ? $faq_form->getComponent('field_resposta') : 'no-form', TRUE) . PHP_EOL;

$nids = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'vagas')
  ->condition('status', 1)
  ->range(0, 3)
  ->execute();
$view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
$renderer = \Drupal::service('renderer');
foreach ($nids as $nid) {
  $node = \Drupal\node\Entity\Node::load($nid);
  $build = $view_builder->view($node, 'full');
  $html = (string) $renderer->renderRoot($build);
  echo sprintf(
    "nid=%d faq_section=%s q1=%s q2=%s\n",
    $nid,
    str_contains($html, 'Dúvidas Frequentes') ? 'yes' : 'no',
    str_contains($html, 'Qual a duração do estágio?') ? 'yes' : 'no',
    str_contains($html, 'Existe auxílio home office?') ? 'yes' : 'no'
  );
}
