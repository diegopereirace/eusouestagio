<?php

namespace Drupal\custom_panel\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller para listagem de vagas às quais o estudante se candidatou.
 */
class VagasAplicadasController extends ControllerBase
{

  protected Connection $database;

  public function __construct(Connection $database)
  {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container): static
  {
    return new static(
      $container->get('database'),
    );
  }

  /**
   * Página /painel/estudantes/vagas-aplicadas: listagem de candidaturas.
   */
  public function listagem(): array
  {
    $uid = (int) $this->currentUser()->id();

    if ($uid === 0) {
      throw new AccessDeniedHttpException();
    }

    $items_per_page = 20;

    $query = $this->database->select('vagas_candidaturas', 'vc')
      ->fields('vc', ['nid', 'created'])
      ->condition('vc.uid', $uid)
      ->orderBy('vc.created', 'DESC');

    /** @var \Drupal\Core\Database\Query\PagerSelectExtender $pager_query */
    $pager_query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit($items_per_page);

    $results = $pager_query->execute()->fetchAll();

    $rows = [];
    $node_storage = $this->entityTypeManager()->getStorage('node');

    foreach ($results as $record) {
      $node = $node_storage->load($record->nid);
      if (!$node) {
        continue;
      }

      $rows[] = [
        'title' => $node->label(),
        'url'   => Url::fromRoute('entity.node.canonical', ['node' => $record->nid])->toString(),
        'date'  => \Drupal::service('date.formatter')->format($record->created, 'short'),
      ];
    }

    return [
      '#theme' => 'custom_panel_vagas_aplicadas',
      '#rows'  => $rows,
      '#empty' => $this->t('Você ainda não se candidatou a nenhuma vaga.'),
      '#pager' => ['#type' => 'pager'],
      '#cache' => [
        'tags'     => ['vagas_candidaturas:' . $uid],
        'contexts' => ['user', 'url.query_args'],
      ],
    ];
  }
}
