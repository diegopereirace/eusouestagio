<?php

namespace Drupal\custom_panel\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller para operações de vagas salvas.
 */
class VagasSalvasController extends ControllerBase
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
   * AJAX: alterna (toggle) o estado de "salvar vaga" para o usuário logado.
   */
  public function toggle(Request $request): JsonResponse
  {
    $nid = $request->request->get('nid');

    if (empty($nid) || !is_numeric($nid)) {
      return new JsonResponse(['error' => 'NID inválido.'], 400);
    }

    $nid = (int) $nid;
    $uid = (int) $this->currentUser()->id();

    // Verifica se o node existe e é do tipo vagas.
    $node = $this->entityTypeManager()->getStorage('node')->load($nid);
    if (!$node || $node->bundle() !== 'vagas') {
      return new JsonResponse(['error' => 'Vaga não encontrada.'], 404);
    }

    // Verifica se já está salva.
    $existing = $this->database->select('vagas_salvas', 'vs')
      ->fields('vs', ['id'])
      ->condition('uid', $uid)
      ->condition('nid', $nid)
      ->execute()
      ->fetchField();

    if ($existing) {
      // Remove.
      $this->database->delete('vagas_salvas')
        ->condition('uid', $uid)
        ->condition('nid', $nid)
        ->execute();

      return new JsonResponse([
        'status' => 'removed',
        'message' => $this->t('Vaga removida dos salvos.'),
      ]);
    }

    // Salva.
    $this->database->insert('vagas_salvas')
      ->fields([
        'uid' => $uid,
        'nid' => $nid,
        'created' => \Drupal::time()->getRequestTime(),
      ])
      ->execute();

    return new JsonResponse([
      'status' => 'saved',
      'message' => $this->t('Vaga salva com sucesso.'),
    ]);
  }

  /**
   * Página /painel/estudante/vagas-salvas: listagem de vagas salvas.
   */
  public function listagem(): array
  {
    $uid = (int) $this->currentUser()->id();
    $items_per_page = 20;

    $query = $this->database->select('vagas_salvas', 'vs')
      ->fields('vs', ['nid', 'created'])
      ->condition('vs.uid', $uid)
      ->orderBy('vs.created', 'DESC');

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
        'url' => Url::fromRoute('entity.node.canonical', ['node' => $record->nid])->toString(),
        'date' => \Drupal::service('date.formatter')->format($record->created, 'short'),
      ];
    }

    return [
      '#theme' => 'custom_panel_vagas_salvas',
      '#rows' => $rows,
      '#empty' => $this->t('Nenhuma vaga salva.'),
      '#pager' => ['#type' => 'pager'],
      '#cache' => [
        'tags' => ['vagas_salvas:' . $uid],
        'contexts' => ['user', 'url.query_args'],
      ],
    ];
  }
}
