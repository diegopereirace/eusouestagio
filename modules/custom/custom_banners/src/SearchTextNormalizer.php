<?php

declare(strict_types=1);

namespace Drupal\custom_banners;

/**
 * Normaliza textos de busca/URL: minúsculas e sem acento.
 */
final class SearchTextNormalizer {

  /**
   * Converte texto para valor seguro de query string (ex.: Administração → administracao).
   */
  public static function normalize(string $text): string {
    $text = mb_strtolower(trim($text), 'UTF-8');
    if ($text === '') {
      return '';
    }
    if (class_exists(\Normalizer::class)) {
      $normalized = \Normalizer::normalize($text, \Normalizer::FORM_D);
      if (is_string($normalized)) {
        return preg_replace('/\p{Mn}/u', '', $normalized) ?? $normalized;
      }
      return $text;
    }
    $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    return is_string($converted) && $converted !== '' ? $converted : $text;
  }

}
