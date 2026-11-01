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

  /**
   * Constraint de curso/cidade: igualdade ou prefixo (não substring solta).
   */
  protected function matchesNormalizedPrefix(string $haystackNorm, string $needleNorm): bool {
    return SearchTextNormalizer::matchesNormalizedPrefix($haystackNorm, $needleNorm);
  }

  /**
   * Label `{Cidade} - {UF} - ({N})` do contrato de autocomplete.
   */
  protected function citySuggestionLabel(string $cidade, string $estado, int $count): string {
    return SearchTextNormalizer::citySuggestionLabel($cidade, $estado, $count);
  }

}
