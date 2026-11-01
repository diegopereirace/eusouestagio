<?php

declare(strict_types=1);

namespace Drupal\custom_banners\Controller;

use Drupal\custom_banners\SearchTextNormalizer;

/**
 * Normalização (minúsculas, sem acento) para busca de autocomplete.
 */
trait AutocompleteNormalizeTrait {

  protected function normalize(string $text): string {
    return SearchTextNormalizer::normalize($text);
  }

}
