<?php

namespace Drupal\custom_configs_users\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Formulário para alteração de senha do usuário logado.
 */
class AlterarSenhaForm extends FormBase
{

  /**
   * Serviço de verificação e hash de senha do Drupal.
   */
  protected PasswordInterface $passwordChecker;

  /**
   * Usuário atual.
   */
  protected AccountProxyInterface $currentUser;

  public function __construct(PasswordInterface $password_checker, AccountProxyInterface $current_user)
  {
    $this->passwordChecker = $password_checker;
    $this->currentUser     = $current_user;
  }

  public static function create(ContainerInterface $container): static
  {
    return new static(
      $container->get('password'),
      $container->get('current_user'),
    );
  }

  public function getFormId(): string
  {
    return 'custom_configs_users_alterar_senha';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array
  {

    $form['#attributes']['class'][] = 'alterar-senha-form';

    $form['senha_atual'] = [
      '#type'        => 'password',
      '#title'       => $this->t('Senha atual'),
      '#required'    => TRUE,
      '#maxlength'   => 128,
      '#description' => $this->t('Digite sua senha atual para confirmar a alteração.'),
    ];

    $form['nova_senha'] = [
      '#type'        => 'password_confirm',
      '#title'       => $this->t('Nova senha'),
      '#required'    => TRUE,
      '#description' => $this->t('A senha deve ter pelo menos 8 caracteres.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type'        => 'submit',
      '#value'       => $this->t('Alterar senha'),
      '#button_type' => 'primary',
      '#attributes'  => ['class' => ['btn', 'btn-primary']],
    ];

    $form['actions']['cancelar'] = [
      '#type'       => 'link',
      '#title'      => $this->t('Cancelar'),
      '#url'        => $this->getCancelUrl(),
      '#attributes' => ['class' => ['btn', 'btn-outline-secondary', 'ms-2']],
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void
  {
    $uid  = $this->currentUser->id();
    $user = User::load($uid);

    if (!$user) {
      $form_state->setErrorByName('senha_atual', $this->t('Usuário não encontrado.'));
      return;
    }

    // Verifica a senha atual informada.
    $senha_atual = $form_state->getValue('senha_atual');
    if (!$this->passwordChecker->check($senha_atual, $user->getPassword())) {
      $form_state->setErrorByName('senha_atual', $this->t('A senha atual informada está incorreta.'));
    }

    // Valida tamanho mínimo da nova senha.
    $nova_senha = $form_state->getValue('nova_senha');
    if (!empty($nova_senha) && mb_strlen($nova_senha) < 8) {
      $form_state->setErrorByName('nova_senha', $this->t('A nova senha deve ter no mínimo 8 caracteres.'));
    }

    // Impede reutilização da mesma senha.
    if (!empty($nova_senha) && $this->passwordChecker->check($nova_senha, $user->getPassword())) {
      $form_state->setErrorByName('nova_senha', $this->t('A nova senha não pode ser igual à senha atual.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    $uid  = $this->currentUser->id();
    $user = User::load($uid);

    if (!$user) {
      $this->messenger()->addError($this->t('Não foi possível alterar a senha. Usuário não encontrado.'));
      return;
    }

    $nova_senha = $form_state->getValue('nova_senha');
    $user->setPassword($nova_senha);
    $user->save();

    $this->messenger()->addStatus($this->t('Sua senha foi alterada com sucesso.'));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * Retorna a URL da página de alterar senha conforme o papel do usuário.
   */
  protected function getCancelUrl(): \Drupal\Core\Url
  {
    if ($this->currentUser->hasRole('empresa')) {
      return \Drupal\Core\Url::fromRoute('custom_configs_users.empresa_alterar_senha');
    }

    return \Drupal\Core\Url::fromRoute('custom_configs_users.candidato_alterar_senha');
  }
}
