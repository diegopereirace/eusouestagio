<?php

namespace Drupal\custom_panel\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller para listagem de vagas às quais o estudante se candidatou.
 */
class VagasAplicadasController extends ControllerBase
{

  private const STATUS_LABELS = [
    'em_triagem'          => 'Em Triagem',
    'aprovado'            => 'Aprovado',
    'reprovado'           => 'Reprovado',
    'lista_espera'        => 'Lista de Espera',
    'entrevista_agendada' => 'Entrevista Agendada',
  ];

  /**
   * Página /painel/estudante/vagas-aplicadas: listagem de candidaturas.
   */
  public function listagem(): array
  {
    $uid = (int) $this->currentUser()->id();

    if ($uid === 0) {
      throw new AccessDeniedHttpException();
    }

    $items_per_page = 20;
    $storage        = $this->entityTypeManager()->getStorage('node');

    // Total para o pager.
    $total = (int) $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'candidatura')
      ->condition('field_candidatura_candidato', $uid)
      ->count()
      ->execute();

    $pager = \Drupal::service('pager.manager')->createPager($total, $items_per_page);
    $page  = $pager->getCurrentPage();

    $nids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'candidatura')
      ->condition('field_candidatura_candidato', $uid)
      ->sort('created', 'DESC')
      ->range($page * $items_per_page, $items_per_page)
      ->execute();

    $rows = [];
    foreach ($storage->loadMultiple($nids) as $candidatura) {
      $vagas = $candidatura->get('field_candidatura_vaga')->referencedEntities();
      /** @var \Drupal\node\NodeInterface|null $vaga */
      $vaga = !empty($vagas) ? $vagas[0] : NULL;
      if (!$vaga) {
        continue;
      }

      $status_key = $candidatura->get('field_candidatura_status')->value ?? 'em_triagem';

      $rows[] = [
        'title'      => $vaga->label(),
        'url'        => $vaga->toUrl('canonical')->toString(),
        'date'       => \Drupal::service('date.formatter')->format($candidatura->getCreatedTime(), 'short'),
        'status'     => $this->t(self::STATUS_LABELS[$status_key] ?? $status_key),
        'status_key' => $status_key,
      ];
    }

    return [
      'listing' => [
        '#theme'  => 'custom_panel_vagas_aplicadas',
        '#rows'   => $rows,
        '#empty'  => $this->t('Você ainda não se candidatou a nenhuma vaga.'),
        '#cache'  => [
          'tags'     => ['node_list:candidatura'],
          'contexts' => ['user', 'url.query_args'],
        ],
      ],
      'pager' => [
        '#type' => 'pager',
      ],
    ];
  }

  /**
   * Endpoint /vaga/apply/{node}: cria a candidatura.
   */
  public function apply(NodeInterface $node): JsonResponse
  {
    $user = $this->currentUser();
    if (!$user->isAuthenticated() || !$user->hasRole('candidato')) {
      throw new AccessDeniedHttpException('Acesso negado.');
    }

    if ($node->bundle() !== 'vagas' || !$node->isPublished()) {
      throw new NotFoundHttpException('Vaga não encontrada.');
    }

    $uid = (int) $user->id();
    $nid = (int) $node->id();

    // Verifica se já existe uma candidatura para este usuário e vaga.
    $storage = $this->entityTypeManager()->getStorage('node');
    $existing_candidacy = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'candidatura')
      ->condition('field_candidatura_candidato', $uid)
      ->condition('field_candidatura_vaga', $nid)
      ->range(0, 1)
      ->execute();

    if (!empty($existing_candidacy)) {
      return new JsonResponse([
        'success' => false,
        'message' => $this->t('Você já se candidatou para esta vaga.'),
      ], 409); // 409 Conflict
    }

    // Cria o novo nó de candidatura.
    $candidatura = $storage->create([
      'type'        => 'candidatura',
      'title'       => 'Candidatura de ' . $user->getAccountName() . ' para ' . $node->label(),
      'status'      => 1, // Publicado
      'field_candidatura_candidato' => $uid,
      'field_candidatura_vaga'      => $nid,
      'field_candidatura_status'    => 'em_triagem', // Status inicial
    ]);
    $candidatura->save();

    return new JsonResponse([
      'success' => true,
      'message' => $this->t('Candidatura enviada com sucesso!'),
      'candidacy_id' => (int) $candidatura->id(),
    ]);
  }
}
