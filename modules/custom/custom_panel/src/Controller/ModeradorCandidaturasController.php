<?php

namespace Drupal\custom_panel\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Painel do moderador para gestão de candidaturas.
 */
class ModeradorCandidaturasController extends ControllerBase
{

  private const STATUS_LABELS = [
    'em_triagem'          => 'Em Triagem',
    'aprovado'            => 'Aprovado',
    'reprovado'           => 'Reprovado',
    'lista_espera'        => 'Lista de Espera',
    'entrevista_agendada' => 'Entrevista Agendada',
  ];

  /**
   * DEBUG TEMPORÁRIO: exibe roles e uid do usuário atual.
   */
  public function debugRoles(): array
  {
    $account = $this->currentUser();
    $user    = $this->entityTypeManager()->getStorage('user')->load($account->id());

    $info = [
      'UID'        => $account->id(),
      'Nome'       => $account->getDisplayName(),
      'Roles (currentUser)' => implode(', ', $account->getRoles()),
      'Roles (User entity)' => $user ? implode(', ', $user->getRoles()) : '—',
      'isAuthenticated'     => $account->isAuthenticated() ? 'sim' : 'não',
      'hasRole moderador (currentUser)' => $account->hasRole('moderador') ? 'SIM' : 'NÃO',
      'hasRole moderador (entity)'      => $user && $user->hasRole('moderador') ? 'SIM' : 'NÃO',
    ];

    $items = [];
    foreach ($info as $label => $value) {
      $items[] = "<strong>{$label}:</strong> {$value}";
    }

    return [
      '#markup' => '<pre style="padding:20px;background:#f5f5f5">' . implode("\n", $items) . '</pre>',
      '#cache'  => ['max-age' => 0],
    ];
  }

  /**
   * Listagem de todas as candidaturas para o moderador.
   */
  public function listagem(): array
  {
    $items_per_page = 25;
    $storage        = $this->entityTypeManager()->getStorage('node');

    // Total para o pager.
    $total = (int) $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'candidatura')
      ->count()
      ->execute();

    $pager = \Drupal::service('pager.manager')->createPager($total, $items_per_page);
    $page  = $pager->getCurrentPage();

    $nids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'candidatura')
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
        'candidatura_nid'  => $candidatura->id(),
        'candidato_nome'   => $candidato ? $candidato->getDisplayName() : '—',
        'candidato_email'  => $candidato ? $candidato->getEmail() : '—',
        'candidato_url'    => $candidato
          ? Url::fromRoute('entity.user.canonical', ['user' => $candidato->id()])->toString()
          : NULL,
        'vaga_titulo'      => $vaga ? $vaga->label() : '—',
        'vaga_url'         => $vaga ? $vaga->toUrl('canonical')->toString() : NULL,
        'data'             => \Drupal::service('date.formatter')->format($candidatura->getCreatedTime(), 'short'),
        'status_key'       => $status_key,
        'status_label'     => $this->t(self::STATUS_LABELS[$status_key] ?? $status_key),
      ];
    }

    $status_options = [];
    foreach (self::STATUS_LABELS as $key => $label) {
      $status_options[$key] = $this->t($label);
    }

    return [
      'listing' => [
        '#theme'          => 'custom_panel_moderador_candidaturas',
        '#rows'           => $rows,
        '#empty'          => $this->t('Nenhuma candidatura registrada.'),
        '#status_options' => $status_options,
        '#update_url'     => Url::fromRoute('custom_panel.moderador_candidaturas_status')->toString(),
        '#cache'          => [
          'tags'     => ['node_list:candidatura'],
          'contexts' => ['user', 'url.query_args'],
        ],
      ],
      'pager' => ['#type' => 'pager'],
    ];
  }

  /**
   * AJAX: atualiza o status de uma candidatura.
   */
  public function atualizarStatus(Request $request): JsonResponse
  {
    $nid    = (int) $request->request->get('nid');
    $status = (string) $request->request->get('status');

    if (!$nid || !array_key_exists($status, self::STATUS_LABELS)) {
      return new JsonResponse(['error' => 'Dados inválidos.'], 400);
    }

    $candidatura = $this->entityTypeManager()->getStorage('node')->load($nid);
    if (!$candidatura || $candidatura->bundle() !== 'candidatura') {
      return new JsonResponse(['error' => 'Candidatura não encontrada.'], 404);
    }

    $candidatura->set('field_candidatura_status', $status);
    $candidatura->save();

    return new JsonResponse([
      'status'  => 'ok',
      'message' => $this->t('Status atualizado com sucesso.'),
    ]);
  }
}
