<?php

namespace Drupal\custom_panel\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Painel da empresa: candidaturas recebidas nas suas vagas.
 */
class EmpresaCandidaturasController extends ControllerBase
{

  private const STATUS_LABELS = [
    'em_triagem'          => 'Em Triagem',
    'aprovado'            => 'Aprovado',
    'reprovado'           => 'Reprovado',
    'lista_espera'        => 'Lista de Espera',
    'entrevista_agendada' => 'Entrevista Agendada',
  ];

  /**
   * Listagem das candidaturas recebidas nas vagas da empresa.
   */
  public function listagem(): array
  {
    $uid = (int) $this->currentUser()->id();
    if ($uid === 0) {
      throw new AccessDeniedHttpException();
    }

    $items_per_page = 20;
    $storage        = $this->entityTypeManager()->getStorage('node');
    $empty_message  = $this->t('Você ainda não possui vagas com candidaturas.');

    // Vagas publicadas pela empresa logada.
    $vaga_nids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'vagas')
      ->condition('field_empresa_u', $uid)
      ->execute();

    if (empty($vaga_nids)) {
      return $this->renderEmpty($empty_message);
    }

    // Total para o pager.
    $total = (int) $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'candidatura')
      ->condition('field_candidatura_vaga', array_values($vaga_nids), 'IN')
      ->count()
      ->execute();

    $pager = \Drupal::service('pager.manager')->createPager($total, $items_per_page);
    $page  = $pager->getCurrentPage();

    $nids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'candidatura')
      ->condition('field_candidatura_vaga', array_values($vaga_nids), 'IN')
      ->sort('created', 'DESC')
      ->range($page * $items_per_page, $items_per_page)
      ->execute();

    $rows = [];
    foreach ($storage->loadMultiple($nids) as $candidatura) {
      $vagas      = $candidatura->get('field_candidatura_vaga')->referencedEntities();
      $candidatos = $candidatura->get('field_candidatura_candidato')->referencedEntities();

      /** @var \Drupal\node\NodeInterface|null $vaga */
      $vaga = !empty($vagas) ? $vagas[0] : NULL;
      /** @var \Drupal\user\UserInterface|null $candidato */
      $candidato  = !empty($candidatos) ? $candidatos[0] : NULL;
      $status_key = $candidatura->get('field_candidatura_status')->value ?? 'em_triagem';

      $rows[] = [
        'candidato_nome'  => $candidato ? $candidato->getDisplayName() : '—',
        'candidato_email' => $candidato ? $candidato->getEmail() : '—',
        'vaga_titulo'     => $vaga ? $vaga->label() : '—',
        'vaga_url'        => $vaga ? $vaga->toUrl('canonical')->toString() : NULL,
        'data'            => \Drupal::service('date.formatter')->format($candidatura->getCreatedTime(), 'short'),
        'status_key'      => $status_key,
        'status_label'    => $this->t(self::STATUS_LABELS[$status_key] ?? $status_key),
      ];
    }

    return [
      'listing' => [
        '#theme'  => 'custom_panel_empresa_candidaturas',
        '#rows'   => $rows,
        '#empty'  => $this->t('Nenhuma candidatura recebida ainda.'),
        '#cache'  => [
          'tags'     => ['node_list:candidatura', 'node_list:vagas'],
          'contexts' => ['user', 'url.query_args'],
        ],
      ],
      'pager' => ['#type' => 'pager'],
    ];
  }

  /**
   * Helper: retorna render array com estado vazio.
   */
  private function renderEmpty(string $message): array
  {
    return [
      'listing' => [
        '#theme'  => 'custom_panel_empresa_candidaturas',
        '#rows'   => [],
        '#empty'  => $message,
        '#cache'  => [
          'tags'     => ['node_list:candidatura', 'node_list:vagas'],
          'contexts' => ['user'],
        ],
      ],
    ];
  }
}
