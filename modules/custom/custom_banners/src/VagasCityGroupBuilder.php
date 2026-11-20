<?php

declare(strict_types=1);

namespace Drupal\custom_banners;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;

/**
 * Agrupa cidades/UF com contagem de vagas, respeitando filtros da listagem.
 */
final class VagasCityGroupBuilder {

  private const CACHE_TTL = 300;

  private const CACHE_PREFIX = 'custom_banners:cidades_groups_constrained:';

  public function __construct(
    private readonly Connection $database,
    private readonly CacheBackendInterface $dataCache,
  ) {}

  /**
   * Cidades com vagas publicadas, opcionalmente filtradas como em /vagas.
   *
   * @return list<array{cidade: string, estado: string, count: int, norm: string}>
   */
  public function getGroups(string $title = '', string $curso = ''): array {
    $title = trim($title);
    $curso = trim($curso);
    $titleNorm = $title !== '' ? SearchTextNormalizer::normalize($title) : '';
    $courseTids = $curso !== '' ? VagasListSearchSql::courseTermIdsMatching($curso) : [];
    if ($curso !== '' && $courseTids === []) {
      return [];
    }

    $cacheKey = self::CACHE_PREFIX . hash('sha256', $titleNorm . "\0" . implode(',', $courseTids));
    if ($cached = $this->dataCache->get($cacheKey)) {
      return $cached->data;
    }

    $query = $this->database->select('node_field_data', 'n');
    $query->join('node__field_cidade', 'c', 'c.entity_id = n.nid AND c.deleted = 0 AND c.delta = 0');
    $query->join('node__field_estados', 'e', 'e.entity_id = n.nid AND e.deleted = 0 AND e.delta = 0');
    $query->addField('c', 'field_cidade_value', 'cidade');
    $query->addField('e', 'field_estados_value', 'estado');
    $query->addExpression('COUNT(DISTINCT n.nid)', 'cnt');
    $query->condition('n.type', 'vagas');
    $query->condition('n.status', 1);
    $query->condition('c.field_cidade_value', '', '<>');
    $query->isNotNull('c.field_cidade_value');
    $query->condition('e.field_estados_value', '', '<>');
    $query->isNotNull('e.field_estados_value');
    $query->groupBy('c.field_cidade_value');
    $query->groupBy('e.field_estados_value');
    $query->having('COUNT(DISTINCT n.nid) >= :min', [':min' => 1]);

    if ($titleNorm !== '') {
      $like = '%' . $this->database->escapeLike($titleNorm) . '%';
      $fold = VagasListSearchSql::accentFoldExpr('n.title');
      $foldEmpresa = VagasListSearchSql::accentFoldExpr('emp.title');
      $query->where(
        "($fold LIKE :vagas_city_title OR EXISTS (
          SELECT 1
          FROM {node__field_vaga_empresa} r
          INNER JOIN {node_field_data} emp
            ON emp.nid = r.field_vaga_empresa_target_id
          WHERE r.entity_id = n.nid
            AND r.deleted = 0
            AND emp.type = 'empresa'
            AND emp.status = 1
            AND $foldEmpresa LIKE :vagas_city_title
        ))",
        [':vagas_city_title' => $like],
      );
    }

    if ($courseTids !== []) {
      $placeholders = [];
      $args = [];
      foreach (array_values($courseTids) as $i => $tid) {
        $key = ':vagas_city_course_' . $i;
        $placeholders[] = $key;
        $args[$key] = $tid;
      }
      $query->where(
        'EXISTS (
          SELECT 1
          FROM {node__field_cursos_t} nfc
          WHERE nfc.entity_id = n.nid
            AND nfc.deleted = 0
            AND nfc.field_cursos_t_target_id IN (' . implode(', ', $placeholders) . ')
        )',
        $args,
      );
    }

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
        'norm' => SearchTextNormalizer::normalize($cidade),
      ];
    }

    $this->dataCache->set(
      $cacheKey,
      $groups,
      time() + self::CACHE_TTL,
      ['node_list:vagas', 'node_list', 'taxonomy_term_list:curso', 'taxonomy_term_list'],
    );

    return $groups;
  }

}
