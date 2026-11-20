<?php

declare(strict_types=1);

namespace Drupal\custom_banners;

/**
 * Ranking puro de sugestões de vaga/empresa para o autocomplete do hero /vagas.
 */
final class VagasSuggestionMatcher {

  private const MAX_Q_LENGTH = 80;

  private const LIMIT = 10;

  /**
   * Filtra, ranqueia e limita sugestões.
   *
   * @param string $q
   *   Termo digitado (será trim + corte em 80).
   * @param list<array{name: string, norm: string}> $titles
   *   Títulos distintos de vagas publicadas.
   * @param list<array{name: string, norm: string}> $empresas
   *   Nomes distintos de empresas publicadas com ≥1 vaga.
   *
   * @return list<array{value: string, label: string, type: string}>
   */
  public static function match(string $q, array $titles, array $empresas): array {
    $q = mb_substr(trim($q), 0, self::MAX_Q_LENGTH);
    if (mb_strlen($q) < 2) {
      return [];
    }

    $needle = SearchTextNormalizer::normalize($q);
    if ($needle === '') {
      return [];
    }

    $candidates = [];
    foreach ($titles as $row) {
      self::collectCandidate($candidates, $row, 'vaga', $needle);
    }
    foreach ($empresas as $row) {
      self::collectCandidate($candidates, $row, 'empresa', $needle);
    }

    usort($candidates, static function (array $a, array $b): int {
      if ($a['prefix'] !== $b['prefix']) {
        return $a['prefix'] <=> $b['prefix'];
      }
      if ($a['pos'] !== $b['pos']) {
        return $a['pos'] <=> $b['pos'];
      }
      // vaga antes de empresa.
      $typeOrder = ($a['type'] === 'vaga' ? 0 : 1) <=> ($b['type'] === 'vaga' ? 0 : 1);
      if ($typeOrder !== 0) {
        return $typeOrder;
      }
      return strnatcasecmp($a['name'], $b['name']);
    });

    $seen = [];
    $payload = [];
    foreach ($candidates as $candidate) {
      $key = $candidate['type'] . "\0" . $candidate['norm'];
      if (isset($seen[$key])) {
        continue;
      }
      $seen[$key] = TRUE;
      $payload[] = [
        'value' => $candidate['name'],
        'label' => $candidate['name'],
        'type' => $candidate['type'],
      ];
      if (count($payload) >= self::LIMIT) {
        break;
      }
    }

    return $payload;
  }

  /**
   * @param list<array{name: string, norm: string, type: string, pos: int, prefix: int}> $candidates
   * @param array{name: string, norm: string} $row
   */
  private static function collectCandidate(array &$candidates, array $row, string $type, string $needle): void {
    $name = trim((string) ($row['name'] ?? ''));
    $norm = (string) ($row['norm'] ?? '');
    if ($name === '' || $norm === '') {
      return;
    }
    $pos = mb_strpos($norm, $needle);
    if ($pos === FALSE) {
      return;
    }
    $candidates[] = [
      'name' => $name,
      'norm' => $norm,
      'type' => $type,
      'pos' => $pos,
      'prefix' => $pos === 0 ? 0 : 1,
    ];
  }

}
