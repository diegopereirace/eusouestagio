<?php

namespace Drupal\custom_candidaturas;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Serviço central de candidaturas via field_candidatos_u na vaga.
 */
class CandidaturasManager
{

  /**
   * Nome do campo multi-valor de candidatos na vaga.
   */
  const FIELD_CANDIDATOS = 'field_candidatos_u';

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected AccountProxyInterface $currentUser,
    protected MailManagerInterface $mailManager,
    protected LoggerChannelFactoryInterface $loggerFactory,
    protected LanguageManagerInterface $languageManager,
    protected ConfigFactoryInterface $configFactory,
  ) {}

  // ──────────────────────────────────────────────────────────────────────────
  // Registro e verificação
  // ──────────────────────────────────────────────────────────────────────────

  /**
   * Registra o candidato na vaga. Retorna 'candidatado' ou 'already'.
   *
   * @param int $nid  NID da vaga.
   * @param int $uid  UID do candidato.
   *
   * @return string  'candidatado' | 'already' | 'error'
   */
  public function registrar(int $nid, int $uid): string
  {
    $vaga = $this->carregarVaga($nid);
    if ($vaga === NULL) {
      return 'error';
    }

    if ($this->jaCandidatou($vaga, $uid)) {
      return 'already';
    }

    // Adiciona referência ao usuário candidato.
    $vaga->get(self::FIELD_CANDIDATOS)->appendItem(['target_id' => $uid]);
    $vaga->save();

    return 'candidatado';
  }

  /**
   * Verifica se o usuário já é candidato nesta vaga.
   */
  public function jaCandidatou(NodeInterface $vaga, int $uid): bool
  {
    if (!$vaga->hasField(self::FIELD_CANDIDATOS)) {
      return FALSE;
    }

    foreach ($vaga->get(self::FIELD_CANDIDATOS) as $item) {
      if ((int) $item->target_id === $uid) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Verifica por NID e UID (atalho para o preprocess).
   */
  public function jaCandidatouPorNid(int $nid, int $uid): bool
  {
    $vaga = $this->carregarVaga($nid);
    if ($vaga === NULL) {
      return FALSE;
    }

    return $this->jaCandidatou($vaga, $uid);
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Consultas
  // ──────────────────────────────────────────────────────────────────────────

  /**
   * Retorna array de UIDs candidatos de uma vaga.
   *
   * @return int[]
   */
  public function candidatosDaVaga(int $nid): array
  {
    $vaga = $this->carregarVaga($nid);
    if ($vaga === NULL || !$vaga->hasField(self::FIELD_CANDIDATOS)) {
      return [];
    }

    $uids = [];
    foreach ($vaga->get(self::FIELD_CANDIDATOS) as $item) {
      $uids[] = (int) $item->target_id;
    }

    return array_unique($uids);
  }

  /**
   * Retorna total de candidatos de uma vaga.
   */
  public function totalCandidatos(int $nid): int
  {
    return count($this->candidatosDaVaga($nid));
  }

  /**
   * Retorna NIDs das vagas às quais o candidato se inscreveu.
   *
   * @return int[]
   */
  public function vagasDoCandidat(int $uid): array
  {
    $nids = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'vagas')
      ->condition(self::FIELD_CANDIDATOS, $uid)
      ->execute();

    return array_values(array_map('intval', $nids));
  }

  /**
   * Retorna objetos NodeInterface das vagas de um candidato, ordenadas por data.
   *
   * @return NodeInterface[]
   */
  public function vagasDoCandidat0(int $uid): array
  {
    $nids = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'vagas')
      ->condition(self::FIELD_CANDIDATOS, $uid)
      ->sort('created', 'DESC')
      ->execute();

    if (empty($nids)) {
      return [];
    }

    return array_values(
      $this->entityTypeManager->getStorage('node')->loadMultiple($nids)
    );
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Notificação de moderadores
  // ──────────────────────────────────────────────────────────────────────────

  /**
   * Envia notificação aos moderadores sobre nova candidatura.
   */
  public function notificarModeradores(NodeInterface $vaga, int $uid_candidato): void
  {
    $logger = $this->loggerFactory->get('custom_candidaturas');

    $candidato = $this->entityTypeManager->getStorage('user')->load($uid_candidato);
    if (!$candidato instanceof UserInterface) {
      $logger->error('Candidato uid @uid não encontrado para notificação.', ['@uid' => $uid_candidato]);
      return;
    }

    $candidato_nome = $this->resolverNomeCandidato($candidato);
    $vaga_titulo    = (string) $vaga->label();
    $vaga_url       = $vaga->toUrl('canonical', ['absolute' => TRUE])->toString();

    // Detectar empresa vinculada à vaga.
    $empresa_nome = '';
    $empresa_mail = '';
    $tem_empresa  = FALSE;

    if ($vaga->hasField('field_empresa_u') && !$vaga->get('field_empresa_u')->isEmpty()) {
      $empresa = $vaga->get('field_empresa_u')->entity;
      if ($empresa instanceof UserInterface && $empresa->hasRole('empresa')) {
        $tem_empresa  = TRUE;
        $empresa_nome = $empresa->hasField('field_nome_fantasia') && !$empresa->get('field_nome_fantasia')->isEmpty()
          ? (string) $empresa->get('field_nome_fantasia')->value
          : $empresa->getDisplayName();
        $empresa_mail = (string) $empresa->getEmail();
      }
    }

    $moderador_ids = $this->entityTypeManager->getStorage('user')
      ->getQuery()
      ->condition('status', 1)
      ->condition('roles', 'moderador')
      ->accessCheck(FALSE)
      ->execute();

    if (empty($moderador_ids)) {
      $logger->warning('Nenhum moderador ativo encontrado para notificação de candidatura (nid @nid).', [
        '@nid' => $vaga->id(),
      ]);
      return;
    }

    $moderadores  = $this->entityTypeManager->getStorage('user')->loadMultiple($moderador_ids);
    $langcode     = $this->languageManager->getDefaultLanguage()->getId();

    $params = [
      'candidato_nome' => $candidato_nome,
      'vaga_titulo'    => $vaga_titulo,
      'vaga_url'       => $vaga_url,
      'empresa_nome'   => $empresa_nome,
      'empresa_mail'   => $empresa_mail,
      'tem_empresa'    => $tem_empresa,
      'site_name'      => (string) $this->configFactory->get('system.site')->get('name'),
    ];

    foreach ($moderadores as $moderador) {
      if (!$moderador instanceof UserInterface) {
        continue;
      }

      $email = (string) $moderador->getEmail();
      if ($email === '') {
        continue;
      }

      $result = $this->mailManager->mail(
        'custom_candidaturas',
        'nova_candidatura_moderadores',
        $email,
        $langcode,
        $params
      );

      if (!empty($result['result'])) {
        $logger->notice('Notificação de candidatura enviada para @email (vaga @nid).', [
          '@email' => $email,
          '@nid'   => $vaga->id(),
        ]);
      } else {
        $logger->error('Falha ao enviar notificação de candidatura para @email (vaga @nid).', [
          '@email' => $email,
          '@nid'   => $vaga->id(),
        ]);
      }
    }
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Helpers internos
  // ──────────────────────────────────────────────────────────────────────────

  /**
   * Carrega a vaga validando o bundle.
   */
  protected function carregarVaga(int $nid): ?NodeInterface
  {
    $vaga = $this->entityTypeManager->getStorage('node')->load($nid);
    if (!$vaga instanceof NodeInterface || $vaga->bundle() !== 'vagas') {
      return NULL;
    }

    return $vaga;
  }

  /**
   * Resolve o nome do candidato por campo customizado ou display name.
   */
  protected function resolverNomeCandidato(UserInterface $candidato): string
  {
    if ($candidato->hasField('field_nome_completo') && !$candidato->get('field_nome_completo')->isEmpty()) {
      return trim((string) $candidato->get('field_nome_completo')->value);
    }

    return $candidato->getDisplayName();
  }
}
