<?php

declare(strict_types=1);

namespace Drupal\custom_banners;

/**
 * Predicados SQL alinhados à listagem /vagas (custom_panel_views_query_alter).
 */
final class VagasListSearchSql {

  /**
   * Expressão PostgreSQL: lower + remoção de acentos comuns em pt-BR.
   */
  public static function accentFoldExpr(string $column): string {
    return "translate(lower($column), 'áàâãäéèêëíìîïóòôõöúùûüçñ', 'aaaaaeeeeiiiiooooouuuucn')";
  }

  /**
   * TIDs de curso cujo nome normalizado contém o termo de busca.
   *
   * @return list<int>
   */
  public static function courseTermIdsMatching(string $search): array {
    $norm = SearchTextNormalizer::normalize($search);
    if ($norm === '') {
      return [];
    }
    try {
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
        'vid' => 'curso',
        'status' => 1,
      ]);
    }
    catch (\Throwable) {
      return [];
    }
    $tids = [];
    foreach ($terms as $term) {
      $nameNorm = SearchTextNormalizer::normalize((string) $term->label());
      if ($nameNorm !== '' && str_contains($nameNorm, $norm)) {
        $tids[] = (int) $term->id();
      }
    }
    return $tids;
  }

}
