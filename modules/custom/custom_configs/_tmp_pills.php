<?php

declare(strict_types=1);

$plugin = \Drupal::service('plugin.manager.block')
  ->createInstance('custom_banners_vagas_hero_search');
$build = $plugin->build();
foreach ($build['#pills'] as $pill) {
  echo $pill['label'] . "\t" . $pill['url'] . "\n";
}
echo 'COUNT=' . count($build['#pills']) . "\n";
