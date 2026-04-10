<?php

namespace Drupal\custom_panel\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller para candidatura a vagas.
 */
class CandidaturaController extends ControllerBase
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
   * AJAX: registra a candidatura do estudante à vaga.
   */
  public function candidatar(Request $request): JsonResponse
  {
    $nid = $request->request->get('nid');

    if (empty($nid) || !is_numeric($nid)) {
      return new JsonResponse(['error' => 'NID inválido.'], 400);
    }

    $nid = (int) $nid;
    $uid = (int) $this->currentUser()->id();

    $node = $this->entityTypeManager()->getStorage('node')->load($nid);
    if (!$node || $node->bundle() !== 'vagas') {
      return new JsonResponse(['error' => 'Vaga não encontrada.'], 404);
    }

    // Verifica se já se candidatou.
    $existing = $this->database->select('vagas_candidaturas', 'vc')
      ->fields('vc', ['id'])
      ->condition('uid', $uid)
      ->condition('nid', $nid)
      ->execute()
      ->fetchField();

    if ($existing) {
      return new JsonResponse([
        'status' => 'already',
        'message' => $this->t('Você já se candidatou a esta vaga.'),
      ]);
    }

    $this->database->insert('vagas_candidaturas')
      ->fields([
        'uid' => $uid,
        'nid' => $nid,
        'created' => \Drupal::time()->getRequestTime(),
      ])
      ->execute();

    return new JsonResponse([
      'status' => 'candidatado',
      'message' => $this->t('Candidatura registrada com sucesso!'),
    ]);
  }
}
