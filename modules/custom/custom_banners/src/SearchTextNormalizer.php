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

  /**
   * Label do dropdown de cidades — contrato 040/041: `{Cidade} - {UF} - ({N})`.
   */
  public static function citySuggestionLabel(string $cidade, string $estado, int $count): string {
    return sprintf('%s - %s - (%d)', $cidade, $estado, max(0, $count));
  }

  /**
   * Match de constraint (curso/cidade): igualdade ou prefixo normalizado.
   *
   * Evita false-positive de substring (ex.: "ti" em "notificacao").
   */
  public static function matchesNormalizedPrefix(string $haystackNorm, string $needleNorm): bool {
    if ($needleNorm === '' || $haystackNorm === '') {
      return FALSE;
    }
    return $haystackNorm === $needleNorm || str_starts_with($haystackNorm, $needleNorm);
  }

}
