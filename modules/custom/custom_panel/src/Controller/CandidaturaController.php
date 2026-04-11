<?php

namespace Drupal\custom_panel\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller para candidatura a vagas.
 */
class CandidaturaController extends ControllerBase
{

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

    $storage = $this->entityTypeManager()->getStorage('node');

    $vaga = $storage->load($nid);
    if (!$vaga || $vaga->bundle() !== 'vagas') {
      return new JsonResponse(['error' => 'Vaga não encontrada.'], 404);
    }

    // Verifica se já se candidatou via entity query.
    $existing = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'candidatura')
      ->condition('field_candidatura_vaga', $nid)
      ->condition('field_candidatura_candidato', $uid)
      ->count()
      ->execute();

    if ($existing > 0) {
      return new JsonResponse([
        'status' => 'already',
        'message' => $this->t('Você já se candidatou a esta vaga.'),
      ]);
    }

    // Cria o node de candidatura (não publicado — invisível no site público).
    $candidatura = Node::create([
      'type'                        => 'candidatura',
      'title'                       => 'Candidatura #' . $nid . ' — UID ' . $uid,
      'uid'                         => $uid,
      'status'                      => 0,
      'field_candidatura_vaga'      => ['target_id' => $nid],
      'field_candidatura_candidato' => ['target_id' => $uid],
      'field_candidatura_status'    => 'em_triagem',
    ]);
    $candidatura->save();

    // Envia e-mail de confirmação ao candidato.
    $account = $this->entityTypeManager()->getStorage('user')->load($uid);
    if ($account && !empty($account->getEmail())) {
      $params = [
        'candidato_nome' => $account->getDisplayName(),
        'vaga_titulo'    => $vaga->label(),
        'vaga_url'       => $vaga->toUrl('canonical', ['absolute' => TRUE])->toString(),
      ];
      \Drupal::service('plugin.manager.mail')->mail(
        'custom_panel',
        'candidatura_confirmacao',
        $account->getEmail(),
        $account->getPreferredLangcode(),
        $params
      );
    }

    return new JsonResponse([
      'status'  => 'candidatado',
      'message' => $this->t('Candidatura registrada com sucesso!'),
    ]);
  }
}
