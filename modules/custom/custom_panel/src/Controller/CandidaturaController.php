<?php

namespace Drupal\custom_panel\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\custom_candidaturas\CandidaturasManager;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller para candidatura a vagas.
 *
 * Delega a lógica de registro e notificação ao CandidaturasManager.
 */
class CandidaturaController extends ControllerBase
{

  public function __construct(
    protected CandidaturasManager $candidaturasManager,
    protected MailManagerInterface $mailManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static
  {
    return new static(
      $container->get('custom_candidaturas.manager'),
      $container->get('plugin.manager.mail'),
    );
  }

  /**
   * AJAX: registra a candidatura do estudante à vaga via field_candidatos_u.
   */
  public function candidatar(Request $request): JsonResponse
  {
    $nid = $request->request->get('nid');

    if (empty($nid) || !is_numeric($nid)) {
      return new JsonResponse(['error' => 'NID inválido.'], 400);
    }

    $nid = (int) $nid;
    $uid = (int) $this->currentUser()->id();

    // Valida que a vaga existe e é do bundle correto.
    $vaga = $this->entityTypeManager()->getStorage('node')->load($nid);
    if (!$vaga instanceof NodeInterface || $vaga->bundle() !== 'vagas') {
      return new JsonResponse(['error' => 'Vaga não encontrada.'], 404);
    }

    // Delega registro ao manager (duplicidade + save).
    $resultado = $this->candidaturasManager->registrar($nid, $uid);

    if ($resultado === 'already') {
      return new JsonResponse([
        'status'  => 'already',
        'message' => $this->t('Você já se candidatou a esta vaga.'),
      ]);
    }

    if ($resultado === 'error') {
      return new JsonResponse(['error' => 'Erro ao registrar candidatura.'], 500);
    }

    // Notifica moderadores sobre a nova candidatura.
    $this->candidaturasManager->notificarModeradores($vaga, $uid);

    // E-mail de confirmação ao próprio candidato.
    $account = $this->entityTypeManager()->getStorage('user')->load($uid);
    if ($account instanceof UserInterface && !empty($account->getEmail())) {
      $params = [
        'candidato_nome' => $account->getDisplayName(),
        'vaga_titulo'    => $vaga->label(),
        'vaga_url'       => $vaga->toUrl('canonical', ['absolute' => TRUE])->toString(),
      ];
      $this->mailManager->mail(
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
