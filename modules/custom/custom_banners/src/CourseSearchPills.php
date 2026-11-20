<?php

declare(strict_types=1);

namespace Drupal\custom_banners;

/**
 * Catálogo único das pills de curso (Home, /vagas e /para-estudantes).
 */
final class CourseSearchPills {

  /**
   * Rótulos na ordem de exibição.
   *
   * @var list<string>
   */
  public const LABELS = [
    'Tecnologia',
    'Marketing',
    'Administração',
    'Engenharia',
    'Saúde',
    'Design',
    'Direito',
  ];

  /**
   * Sinônimos quando o rótulo canônico não existe no vocabulário curso.
   *
   * Ordem = prioridade. Match: nome exato, depois prefixo case-insensitive.
   *
   * @var array<string, list<string>>
   */
  public const ALIASES = [
    'Tecnologia' => [
      'Tecnologia',
      'TI',
      'Ciência da Computação',
      'Sistemas de Informação',
      'Engenharia de Software',
    ],
    'Saúde' => [
      'Saúde',
      'Enfermagem',
      'Nutrição',
      'Biomedicina',
      'Medicina',
    ],
  ];

  /**
   * Resolve pills presentes no vocabulário.
   *
   * @param iterable<object> $candidates
   *   Termos com label().
   *
   * @return list<array{label: string, term_label: string}>
   *   label = rótulo exibido; term_label = nome do termo usado na query.
   */
  public static function resolve(iterable $candidates): array {
    $terms = [];
    foreach ($candidates as $candidate) {
      if (is_object($candidate) && method_exists($candidate, 'label')) {
        $terms[] = $candidate;
      }
    }

    $pills = [];
    foreach (self::LABELS as $label) {
      $lookups = array_values(array_unique(array_merge(
        [$label],
        self::ALIASES[$label] ?? [],
      )));
      $term = self::match($terms, $lookups);
      if (!$term) {
        continue;
      }
      $pills[] = [
        'label' => $label,
        'term_label' => (string) $term->label(),
      ];
    }

    return $pills;
  }

  /**
   * Exato, depois prefixo, na ordem dos rótulos candidatos.
   *
   * @param list<object> $candidates
   * @param list<string> $lookups
   */
  private static function match(array $candidates, array $lookups): ?object {
    foreach ($lookups as $needle) {
      foreach ($candidates as $candidate) {
        $name = (string) $candidate->label();
        if (strcasecmp($name, $needle) === 0) {
          return $candidate;
        }
      }
      foreach ($candidates as $candidate) {
        $name = (string) $candidate->label();
        if (stripos($name, $needle) === 0) {
          return $candidate;
        }
      }
    }
    return NULL;
  }

}
