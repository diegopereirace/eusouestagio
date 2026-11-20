<?php

declare(strict_types=1);

namespace Drupal\custom_banners\Controller;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;

/**
 * Índice cacheável vaga publicada × cidade × UF × curso (para cross-filter).
 */
final class VagaLocationCourseIndex {

  use AutocompleteNormalizeTrait;

  private const CACHE_CID = 'custom_banners:vaga_location_course_index';

  /** Alinhado ao max-age HTTP dos autocompletes (60–300s). */
  private const CACHE_TTL = 300;

  public function __construct(
    private readonly Connection $database,
    private readonly CacheBackendInterface $dataCache,
  ) {}

  /**
   * Triples agregados com contagem de vagas publicadas.
   *
   * @return list<array{
   *   cidade: string,
   *   estado: string,
   *   curso: string,
   *   count: int,
   *   cidade_norm: string,
   *   curso_norm: string
   * }>
   */
  public function getTriples(): array {
    if ($cached = $this->dataCache->get(self::CACHE_CID)) {
      return $cached->data;
    }

    $query = $this->database->select('node_field_data', 'n');
    $query->join('node__field_cidade', 'c', 'c.entity_id = n.nid AND c.deleted = 0 AND c.delta = 0');
    $query->join('node__field_estados', 'e', 'e.entity_id = n.nid AND e.deleted = 0 AND e.delta = 0');
    $query->join('node__field_cursos_t', 'ct', 'ct.entity_id = n.nid AND ct.deleted = 0');
    $query->join('taxonomy_term_field_data', 't', 't.tid = ct.field_cursos_t_target_id AND t.default_langcode = 1');
    $query->addField('c', 'field_cidade_value', 'cidade');
    $query->addField('e', 'field_estados_value', 'estado');
    $query->addField('t', 'name', 'curso');
    $query->addExpression('COUNT(DISTINCT n.nid)', 'cnt');
    $query->condition('n.type', 'vagas');
    $query->condition('n.status', 1);
    $query->condition('t.vid', 'curso');
    $query->condition('t.status', 1);
    $query->condition('c.field_cidade_value', '', '<>');
    $query->isNotNull('c.field_cidade_value');
    $query->condition('e.field_estados_value', '', '<>');
    $query->isNotNull('e.field_estados_value');
    $query->condition('t.name', '', '<>');
    $query->groupBy('c.field_cidade_value');
    $query->groupBy('e.field_estados_value');
    $query->groupBy('t.name');
    $query->having('COUNT(DISTINCT n.nid) >= :min', [':min' => 1]);

    $triples = [];
    foreach ($query->execute() as $row) {
      $cidade = trim((string) $row->cidade);
      $estado = trim((string) $row->estado);
      $curso = trim((string) $row->curso);
      $count = (int) $row->cnt;
      if ($cidade === '' || $estado === '' || $curso === '' || $count < 1) {
        continue;
      }
      $triples[] = [
        'cidade' => $cidade,
        'estado' => $estado,
        'curso' => $curso,
        'count' => $count,
        'cidade_norm' => $this->normalize($cidade),
        'curso_norm' => $this->normalize($curso),
      ];
    }

    $this->dataCache->set(
      self::CACHE_CID,
      $triples,
      time() + self::CACHE_TTL,
      ['node_list:vagas', 'node_list', 'taxonomy_term_list:curso', 'taxonomy_term_list'],
    );

    return $triples;
  }

}
