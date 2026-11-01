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
 * Autocomplete JSON auxiliar para o campo de curso do hero.
 */
class CursosAutocompleteController extends ControllerBase {

  use AutocompleteNormalizeTrait;

  private const VID = 'curso';

  private const LIMIT = 10;

  private const CACHE_MAX_AGE = 300;

  private const CACHE_CID = 'custom_banners:cursos_autocomplete';

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
    $cidade = trim((string) $request->query->get('cidade', ''));
    $estado = trim((string) $request->query->get('estado', ''));
    $hasLocationConstraint = $cidade !== '';
    $payload = [];

    $allowEmptyQ = $hasLocationConstraint;
    if (mb_strlen($q) >= 2 || ($allowEmptyQ && $q === '')) {
      try {
        $normalizedQuery = $q !== '' ? $this->normalize($q) : '';
        $matches = $hasLocationConstraint
          ? $this->matchCoursesForLocation($normalizedQuery, $cidade, $estado)
          : $this->matchAllCourses($normalizedQuery);

        foreach (array_slice($matches, 0, self::LIMIT) as $match) {
          $payload[] = [
            'value' => $match['name'],
            'label' => $match['name'],
          ];
        }
      }
      catch (\Throwable) {
        $payload = [];
      }
    }

    return $this->buildResponse($payload, $hasLocationConstraint);
  }

  /**
   * @return list<array{name: string, pos: int}>
   */
  private function matchAllCourses(string $normalizedQuery): array {
    if ($normalizedQuery === '') {
      return [];
    }
    $matches = [];
    foreach ($this->loadCourses() as $course) {
      $pos = mb_strpos($course['norm'], $normalizedQuery);
      if ($pos !== FALSE) {
        $matches[] = $course + ['pos' => $pos];
      }
    }
    usort($matches, static function (array $a, array $b): int {
      $aPrefix = $a['pos'] === 0 ? 0 : 1;
      $bPrefix = $b['pos'] === 0 ? 0 : 1;
      return $aPrefix <=> $bPrefix ?: strnatcasecmp($a['name'], $b['name']);
    });
    return $matches;
  }

  /**
   * @return list<array{name: string, pos: int}>
   */
  private function matchCoursesForLocation(string $normalizedQuery, string $cidade, string $estado): array {
    $cidadeNorm = $this->normalize($cidade);
    $estadoNorm = $estado !== '' ? $this->normalize($estado) : '';
    if ($cidadeNorm === '') {
      return [];
    }

    $byName = [];
    $index = new VagaLocationCourseIndex($this->database, $this->dataCache);
    foreach ($index->getTriples() as $triple) {
      if (!str_contains($triple['cidade_norm'], $cidadeNorm)) {
        continue;
      }
      if ($estadoNorm !== '' && $this->normalize($triple['estado']) !== $estadoNorm) {
        continue;
      }
      if ($normalizedQuery !== '' && mb_strpos($triple['curso_norm'], $normalizedQuery) === FALSE) {
        continue;
      }
      $name = $triple['curso'];
      if (!isset($byName[$name])) {
        $pos = $normalizedQuery === '' ? 0 : (int) mb_strpos($triple['curso_norm'], $normalizedQuery);
        $byName[$name] = ['name' => $name, 'pos' => $pos === FALSE ? 1 : $pos];
      }
    }

    $matches = array_values($byName);
    usort($matches, static function (array $a, array $b): int {
      $aPrefix = $a['pos'] === 0 ? 0 : 1;
      $bPrefix = $b['pos'] === 0 ? 0 : 1;
      return $aPrefix <=> $bPrefix ?: strnatcasecmp($a['name'], $b['name']);
    });
    return $matches;
  }

  /**
   * Lista (cacheada) de cursos publicados com nome normalizado.
   *
   * @return list<array{name: string, norm: string}>
   */
  private function loadCourses(): array {
    if ($cached = $this->dataCache->get(self::CACHE_CID)) {
      return $cached->data;
    }

    $query = $this->database->select('taxonomy_term_field_data', 't');
    $query->fields('t', ['name']);
    $query->condition('t.vid', self::VID);
    $query->condition('t.status', 1);
    $query->condition('t.default_langcode', 1);
    $query->orderBy('t.name');

    $courses = [];
    foreach ($query->execute() as $row) {
      $name = trim((string) $row->name);
      if ($name === '') {
        continue;
      }
      $courses[] = ['name' => $name, 'norm' => $this->normalize($name)];
    }

    $this->dataCache->set(
      self::CACHE_CID,
      $courses,
      time() + 3600,
      ['taxonomy_term_list:' . self::VID, 'taxonomy_term_list'],
    );

    return $courses;
  }

  private function buildResponse(array $payload, bool $withLocationConstraint): CacheableJsonResponse {
    $tags = ['taxonomy_term_list:' . self::VID];
    $contexts = ['url.query_args:q'];
    if ($withLocationConstraint) {
      $tags[] = 'node_list:vagas';
      $contexts[] = 'url.query_args:cidade';
      $contexts[] = 'url.query_args:estado';
    }

    $response = new CacheableJsonResponse($payload);
    $response->addCacheableDependency(
      (new CacheableMetadata())
        ->setCacheContexts($contexts)
        ->setCacheTags($tags)
        ->setCacheMaxAge(self::CACHE_MAX_AGE)
    );
    $response->setPublic();
    $response->setMaxAge(self::CACHE_MAX_AGE);
    return $response;
  }

}
