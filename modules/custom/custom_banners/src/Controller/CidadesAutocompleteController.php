<?php

declare(strict_types=1);

namespace Drupal\custom_banners\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Autocomplete JSON auxiliar para o campo de cidade do hero da home.
 */
class CidadesAutocompleteController extends ControllerBase {

  use AutocompleteNormalizeTrait;

  private const LIMIT = 10;

  private const CACHE_MAX_AGE = 300;

  private const CACHE_CID = 'custom_banners:cidades_autocomplete';

  public function __construct(
    protected Connection $database,
    protected CacheBackendInterface $dataCache,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('database'),
      $container->get('cache.data'),
    );
  }

  public function autocomplete(Request $request): CacheableJsonResponse {
    $q = trim((string) $request->query->get('q', ''));
    $curso = trim((string) $request->query->get('curso', ''));
    $hasCourseConstraint = $curso !== '';
    $payload = [];

    $allowEmptyQ = $hasCourseConstraint;
    if (mb_strlen($q) >= 2 || ($allowEmptyQ && $q === '')) {
      try {
        $normalizedQuery = $q !== '' ? $this->normalize($q) : '';
        $groups = $hasCourseConstraint
          ? $this->loadCityGroupsForCourse($curso)
          : $this->loadCityGroups();

        $matches = [];
        foreach ($groups as $group) {
          if ($group['norm'] === '') {
            continue;
          }
          if ($normalizedQuery !== '' && !str_contains($group['norm'], $normalizedQuery)) {
            continue;
          }
          $group['prefix'] = ($normalizedQuery === '' || str_starts_with($group['norm'], $normalizedQuery)) ? 0 : 1;
          $matches[] = $group;
        }

        usort($matches, static function (array $a, array $b): int {
          if ($a['prefix'] !== $b['prefix']) {
            return $a['prefix'] <=> $b['prefix'];
          }
          $byName = strnatcasecmp($a['cidade'], $b['cidade']);
          return $byName !== 0 ? $byName : strnatcasecmp($a['estado'], $b['estado']);
        });

        foreach (array_slice($matches, 0, self::LIMIT) as $match) {
          $payload[] = [
            'value' => $match['cidade'],
            'label' => $this->citySuggestionLabel(
              $match['cidade'],
              $match['estado'],
              $match['count'],
            ),
            'estado' => $match['estado'],
            'count' => $match['count'],
          ];
        }
      }
      catch (\Throwable) {
        $payload = [];
      }
    }

    $response = new CacheableJsonResponse($payload);
    $contexts = ['url.query_args:q'];
    if ($hasCourseConstraint) {
      $contexts[] = 'url.query_args:curso';
    }
    $response->addCacheableDependency(
      (new CacheableMetadata())
        ->setCacheContexts($contexts)
        ->setCacheTags(['node_list:vagas'])
        ->setCacheMaxAge(self::CACHE_MAX_AGE)
    );
    $response->setPublic();
    $response->setMaxAge(self::CACHE_MAX_AGE);
    return $response;
  }

  /**
   * Cidades com vagas publicadas (cacheado; invalida ao salvar vagas).
   *
   * @return list<array{cidade: string, estado: string, count: int, norm: string}>
   */
  private function loadCityGroups(): array {
    if ($cached = $this->dataCache->get(self::CACHE_CID)) {
      return $cached->data;
    }

    $query = $this->database->select('node_field_data', 'n');
    $query->join('node__field_cidade', 'c', 'c.entity_id = n.nid AND c.deleted = 0 AND c.delta = 0');
    $query->join('node__field_estados', 'e', 'e.entity_id = n.nid AND e.deleted = 0 AND e.delta = 0');
    $query->addField('c', 'field_cidade_value', 'cidade');
    $query->addField('e', 'field_estados_value', 'estado');
    $query->addExpression('COUNT(n.nid)', 'cnt');
    $query->condition('n.type', 'vagas');
    $query->condition('n.status', 1);
    $query->condition('c.field_cidade_value', '', '<>');
    $query->isNotNull('c.field_cidade_value');
    $query->condition('e.field_estados_value', '', '<>');
    $query->isNotNull('e.field_estados_value');
    $query->groupBy('c.field_cidade_value');
    $query->groupBy('e.field_estados_value');
    $query->having('COUNT(n.nid) >= :min', [':min' => 1]);

    $groups = [];
    foreach ($query->execute() as $row) {
      $cidade = trim((string) $row->cidade);
      $estado = trim((string) $row->estado);
      $count = (int) $row->cnt;
      if ($cidade === '' || $estado === '' || $count < 1) {
        continue;
      }
      $groups[] = [
        'cidade' => $cidade,
        'estado' => $estado,
        'count' => $count,
        'norm' => $this->normalize($cidade),
      ];
    }

    $this->dataCache->set(
      self::CACHE_CID,
      $groups,
      time() + self::CACHE_MAX_AGE,
      ['node_list:vagas', 'node_list'],
    );

    return $groups;
  }

  /**
   * Agrupa cidade+UF a partir do índice, filtrando por curso (N filtrado).
   *
   * @return list<array{cidade: string, estado: string, count: int, norm: string}>
   */
  private function loadCityGroupsForCourse(string $curso): array {
    $cursoNorm = $this->normalize($curso);
    if ($cursoNorm === '') {
      return [];
    }

    $index = new VagaLocationCourseIndex($this->database, $this->dataCache);
    $aggregated = [];
    foreach ($index->getTriples() as $triple) {
      if (!$this->matchesNormalizedPrefix($triple['curso_norm'], $cursoNorm)) {
        continue;
      }
      $key = $triple['cidade'] . "\0" . $triple['estado'];
      if (!isset($aggregated[$key])) {
        $aggregated[$key] = [
          'cidade' => $triple['cidade'],
          'estado' => $triple['estado'],
          'count' => 0,
          'norm' => $triple['cidade_norm'],
        ];
      }
      $aggregated[$key]['count'] += $triple['count'];
    }

    return array_values(array_filter(
      $aggregated,
      static fn(array $g): bool => $g['count'] >= 1,
    ));
  }

}
