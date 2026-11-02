<?php

declare(strict_types=1);

namespace Drupal\custom_banners\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\custom_banners\VagasSuggestionMatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Autocomplete JSON do Input 1 do hero /vagas (títulos de vaga + empresas).
 */
class VagasAutocompleteController extends ControllerBase {

  use AutocompleteNormalizeTrait;

  private const CACHE_MAX_AGE = 300;

  private const CACHE_CID_TITLES = 'custom_banners:vagas_autocomplete_titles';

  private const CACHE_CID_EMPRESAS = 'custom_banners:vagas_autocomplete_empresas';

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
    $payload = [];

    try {
      $payload = VagasSuggestionMatcher::match(
        $q,
        $this->loadTitles(),
        $this->loadEmpresas(),
      );
    }
    catch (\Throwable) {
      $payload = [];
    }

    $response = new CacheableJsonResponse($payload);
    $response->addCacheableDependency(
      (new CacheableMetadata())
        ->setCacheContexts(['url.query_args:q'])
        ->setCacheTags(['node_list:vagas', 'node_list:empresa', 'node_list'])
        ->setCacheMaxAge(self::CACHE_MAX_AGE)
    );
    $response->setPublic();
    $response->setMaxAge(self::CACHE_MAX_AGE);
    return $response;
  }

  /**
   * @return list<array{name: string, norm: string}>
   */
  private function loadTitles(): array {
    if ($cached = $this->dataCache->get(self::CACHE_CID_TITLES)) {
      return $cached->data;
    }

    $query = $this->database->select('node_field_data', 'n');
    $query->fields('n', ['title']);
    $query->distinct();
    $query->condition('n.type', 'vagas');
    $query->condition('n.status', 1);
    $query->condition('n.default_langcode', 1);
    $query->condition('n.title', '', '<>');
    $query->orderBy('n.title');

    $rows = [];
    $seen = [];
    foreach ($query->execute() as $row) {
      $name = trim((string) $row->title);
      if ($name === '') {
        continue;
      }
      $norm = $this->normalize($name);
      if ($norm === '' || isset($seen[$norm])) {
        continue;
      }
      $seen[$norm] = TRUE;
      $rows[] = ['name' => $name, 'norm' => $norm];
    }

    $this->dataCache->set(
      self::CACHE_CID_TITLES,
      $rows,
      time() + self::CACHE_MAX_AGE,
      ['node_list:vagas', 'node_list'],
    );

    return $rows;
  }

  /**
   * Empresas publicadas com ≥ 1 vaga publicada via field_vaga_empresa.
   *
   * @return list<array{name: string, norm: string}>
   */
  private function loadEmpresas(): array {
    if ($cached = $this->dataCache->get(self::CACHE_CID_EMPRESAS)) {
      return $cached->data;
    }

    $query = $this->database->select('node_field_data', 'emp');
    $query->fields('emp', ['title']);
    $query->distinct();
    $query->join(
      'node__field_vaga_empresa',
      'r',
      'r.field_vaga_empresa_target_id = emp.nid AND r.deleted = 0',
    );
    $query->join(
      'node_field_data',
      'v',
      "v.nid = r.entity_id AND v.type = 'vagas' AND v.status = 1 AND v.default_langcode = 1",
    );
    $query->condition('emp.type', 'empresa');
    $query->condition('emp.status', 1);
    $query->condition('emp.default_langcode', 1);
    $query->condition('emp.title', '', '<>');
    $query->orderBy('emp.title');

    $rows = [];
    $seen = [];
    foreach ($query->execute() as $row) {
      $name = trim((string) $row->title);
      if ($name === '') {
        continue;
      }
      $norm = $this->normalize($name);
      if ($norm === '' || isset($seen[$norm])) {
        continue;
      }
      $seen[$norm] = TRUE;
      $rows[] = ['name' => $name, 'norm' => $norm];
    }

    $this->dataCache->set(
      self::CACHE_CID_EMPRESAS,
      $rows,
      time() + self::CACHE_MAX_AGE,
      ['node_list:vagas', 'node_list:empresa', 'node_list'],
    );

    return $rows;
  }

}
