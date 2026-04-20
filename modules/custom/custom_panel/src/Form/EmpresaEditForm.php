<?php

namespace Drupal\custom_panel\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\user\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EmpresaEditForm extends FormBase
{

  public function getFormId()
  {
    return 'custom_panel_empresa_edit_form';
  }

  private function getListOptions(string $field_name, string $entity_type = 'user'): array
  {
    $storage = FieldStorageConfig::loadByName($entity_type, $field_name);
    if (!$storage) {
      return [];
    }

    $allowed = [];
    if (function_exists('options_allowed_values')) {
      $allowed = \options_allowed_values($storage);
    }
    if (!is_array($allowed) || empty($allowed)) {
      $allowed = $storage->getSetting('allowed_values');
    }
    if (!is_array($allowed) || empty($allowed)) {
      return [];
    }

    $options = [];
    foreach ($allowed as $key => $item) {
      if (is_array($item) && isset($item['value']) && isset($item['label'])) {
        $options[(string) $item['value']] = (string) $item['label'];
      } elseif (is_array($item) && isset($item['value'])) {
        $value = (string) $item['value'];
        $options[$value] = $value;
      } elseif (!is_int($key) && (is_string($item) || is_numeric($item))) {
        $options[(string) $key] = (string) $item;
      } elseif (is_int($key) && (is_string($item) || is_numeric($item))) {
        $value = (string) $item;
        $options[$value] = $value;
      }
    }

    return $options;
  }

  private function getFieldValue(User $user, string $field_name): string
  {
    if ($user->hasField($field_name) && !$user->get($field_name)->isEmpty()) {
      return (string) $user->get($field_name)->value;
    }
    return '';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $user = User::load(\Drupal::currentUser()->id());
    if (!$user || !$user->hasRole('empresa')) {
      throw new AccessDeniedHttpException();
    }

    $form['#attributes']['novalidate'] = 'novalidate';

    $form['messages'] = [
      '#type' => 'status_messages',
      '#weight' => -1000,
    ];

    $form['page_title'] = [
      '#markup' => '<div class="mb-4 mt-2">'
        . '<h2 class="mb-1"><i class="fas fa-building me-2"></i>' . $this->t('Editar Informações da Empresa') . '</h2>'
        . '<p class="text-muted mb-0">' . $this->t('Atualize os dados cadastrais da empresa abaixo.') . '</p>'
        . '</div>',
    ];

    // ── Seção 1 — E-mail ───────────────────────────────────────
    $form['section_acesso'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mb-4']],
    ];
    $form['section_acesso']['heading'] = [
      '#markup' => '<h3 class="mb-3 pb-2 border-bottom"><i class="fas fa-lock me-2"></i>' . $this->t('Dados de Acesso') . '</h3>',
    ];
    $form['section_acesso']['row'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row', 'g-3']],
    ];
    $form['section_acesso']['row']['col_mail'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-6']],
    ];
    $form['section_acesso']['row']['col_mail']['mail'] = [
      '#type' => 'email',
      '#title' => $this->t('E-mail'),
      '#required' => TRUE,
      '#default_value' => $user->getEmail(),
      '#attributes' => ['class' => ['form-control']],
    ];

    // ── Seção 2 — Dados da Empresa ─────────────────────────────
    $form['section_empresa'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mb-4']],
    ];
    $form['section_empresa']['heading'] = [
      '#markup' => '<h3 class="mb-3 pb-2 border-bottom"><i class="fas fa-building me-2"></i>' . $this->t('Dados da Empresa') . '</h3>',
    ];
    $form['section_empresa']['row'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row', 'g-3']],
    ];

    $form['section_empresa']['row']['col_inscricao_municipal'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-6', 'col-lg-3']],
    ];
    $form['section_empresa']['row']['col_inscricao_municipal']['field_inscricao_municipal'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Inscrição Municipal'),
      '#maxlength' => 255,
      '#default_value' => $this->getFieldValue($user, 'field_inscricao_municipal'),
      '#attributes' => ['class' => ['form-control', 'mask-numbers']],
    ];

    $form['section_empresa']['row']['col_cnpj'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-6', 'col-lg-3']],
    ];
    $form['section_empresa']['row']['col_cnpj']['field_cnpj'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CNPJ'),
      '#required' => TRUE,
      '#maxlength' => 18,
      '#default_value' => $this->getFieldValue($user, 'field_cnpj'),
      '#attributes' => [
        'class' => ['form-control', 'mask-cnpj'],
        'placeholder' => '00.000.000/0000-00',
      ],
    ];

    $form['section_empresa']['row']['col_razao_social'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-6', 'col-lg-3']],
    ];
    $form['section_empresa']['row']['col_razao_social']['field_razao_social'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Razão Social'),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#default_value' => $this->getFieldValue($user, 'field_razao_social'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_empresa']['row']['col_nome_fantasia'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-6', 'col-lg-3']],
    ];
    $form['section_empresa']['row']['col_nome_fantasia']['field_nome_fantasia'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nome Fantasia'),
      '#maxlength' => 255,
      '#default_value' => $this->getFieldValue($user, 'field_nome_fantasia'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_empresa']['row']['col_sobre_empresa'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12']],
    ];
    $form['section_empresa']['row']['col_sobre_empresa']['field_sobre_empresa'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Sobre a Empresa'),
      '#rows' => 5,
      '#default_value' => $this->getFieldValue($user, 'field_sobre_empresa'),
      '#attributes' => ['class' => ['form-control']],
    ];

    // ── Seção 3 — Endereço ─────────────────────────────────────
    $form['section_endereco'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mb-4']],
    ];
    $form['section_endereco']['heading'] = [
      '#markup' => '<h3 class="mb-3 pb-2 border-bottom"><i class="fas fa-map-marker-alt me-2"></i>' . $this->t('Endereço') . '</h3>',
    ];
    $form['section_endereco']['row'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row', 'g-3']],
    ];

    $form['section_endereco']['row']['col_cep'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-3']],
    ];
    $form['section_endereco']['row']['col_cep']['field_cep'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CEP'),
      '#maxlength' => 9,
      '#default_value' => $this->getFieldValue($user, 'field_cep'),
      '#attributes' => [
        'class' => ['form-control', 'mask-cep'],
        'placeholder' => '00000-000',
      ],
    ];

    $form['section_endereco']['row']['col_endereco'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-9']],
    ];
    $form['section_endereco']['row']['col_endereco']['field_endereco'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Endereço'),
      '#maxlength' => 255,
      '#default_value' => $this->getFieldValue($user, 'field_endereco'),
      '#attributes' => ['class' => ['form-control']],
    ];
    $form['section_endereco']['row']['col_endereco']['field_complemento'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Complemento'),
      '#maxlength' => 255,
      '#default_value' => $this->getFieldValue($user, 'field_complemento'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_endereco']['row']['col_bairro'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_endereco']['row']['col_bairro']['field_bairro'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bairro'),
      '#maxlength' => 100,
      '#default_value' => $this->getFieldValue($user, 'field_bairro'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_endereco']['row']['col_cidade'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_endereco']['row']['col_cidade']['field_cidade'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cidade'),
      '#maxlength' => 100,
      '#default_value' => $this->getFieldValue($user, 'field_cidade'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_endereco']['row']['col_estado'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_endereco']['row']['col_estado']['field_estado'] = [
      '#type' => 'select',
      '#title' => $this->t('Estado'),
      '#options' => ['' => $this->t('- Selecione -')] + $this->getListOptions('field_estado'),
      '#default_value' => $this->getFieldValue($user, 'field_estado'),
      '#attributes' => ['class' => ['form-select']],
    ];

    // ── Seção 4 — Responsável pelo Cadastro ───────────────────
    $form['section_responsavel'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mb-4']],
    ];
    $form['section_responsavel']['heading'] = [
      '#markup' => '<h3 class="mb-3 pb-2 border-bottom"><i class="fas fa-user-tie me-2"></i>' . $this->t('Responsável pelo Cadastro') . '</h3>',
    ];
    $form['section_responsavel']['row'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row', 'g-3']],
    ];

    $form['section_responsavel']['row']['col_responsavel_nome'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12']],
    ];
    $form['section_responsavel']['row']['col_responsavel_nome']['field_responsavel_nome'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nome'),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#default_value' => $this->getFieldValue($user, 'field_responsavel_nome'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_responsavel']['row']['col_responsavel_telefone'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-6']],
    ];
    $form['section_responsavel']['row']['col_responsavel_telefone']['field_responsavel_telefone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Telefone'),
      '#maxlength' => 15,
      '#default_value' => $this->getFieldValue($user, 'field_responsavel_telefone'),
      '#attributes' => [
        'class' => ['form-control', 'mask-phone'],
        'placeholder' => '(00) 00000-0000',
      ],
    ];

    $form['section_responsavel']['row']['col_responsavel_email'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-6']],
    ];
    $form['section_responsavel']['row']['col_responsavel_email']['field_responsavel_email'] = [
      '#type' => 'email',
      '#title' => $this->t('E-mail'),
      '#maxlength' => 254,
      '#default_value' => $this->getFieldValue($user, 'field_responsavel_email'),
      '#attributes' => ['class' => ['form-control']],
    ];

    // ── Ações ──────────────────────────────────────────────────
    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['mt-4', 'd-grid', 'gap-2', 'd-md-flex', 'justify-content-md-end']],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Salvar Alterações'),
      '#button_type' => 'primary',
      '#attributes' => ['class' => ['btn', 'btn-primary', 'btn-lg', 'px-5']],
    ];

    // Bibliotecas de máscaras.
    $form['#attached']['library'][] = 'core/jquery.ui.widget';
    $form['#attached']['library'][] = 'default/jquery_mask';
    $form['#attached']['library'][] = 'default/masks';
    $form['#attached']['drupalSettings']['defaultMasks']['cepApi'] = [
      'lookupUrl' => '/api/cep',
    ];

    return $form;
  }

  // ── Validação ──────────────────────────────────────────────────

  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $user = User::load(\Drupal::currentUser()->id());
    $mail = trim((string) $form_state->getValue('mail'));
    $cnpj = preg_replace('/\D/', '', (string) $form_state->getValue('field_cnpj'));

    // E-mail válido.
    if (!\Drupal::service('email.validator')->isValid($mail)) {
      $form_state->setErrorByName('mail', $this->t('Informe um endereço de e-mail válido.'));
    }

    // E-mail único (exceto o próprio usuário).
    $existing_mail = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties(['mail' => $mail]);
    foreach ($existing_mail as $account) {
      if ((int) $account->id() !== (int) $user->id()) {
        $form_state->setErrorByName('mail', $this->t('Este e-mail já está em uso por outra conta.'));
        break;
      }
    }

    // Documento da empresa.
    if ($cnpj === '') {
      $form_state->setErrorByName('field_cnpj', $this->t('O CNPJ é obrigatório.'));
    } elseif (mb_strlen($cnpj) !== 14 || !$this->validarCnpj($cnpj)) {
      $form_state->setErrorByName('field_cnpj', $this->t('O CNPJ informado é inválido.'));
    }

    // E-mail do responsável (se preenchido, valida formato).
    $resp_email = trim((string) $form_state->getValue('field_responsavel_email'));
    if (!empty($resp_email) && !\Drupal::service('email.validator')->isValid($resp_email)) {
      $form_state->setErrorByName('field_responsavel_email', $this->t('O e-mail do responsável é inválido.'));
    }
  }

  // ── Submit ─────────────────────────────────────────────────────

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $user = User::load(\Drupal::currentUser()->id());
    if (!$user) {
      $this->messenger()->addError($this->t('Não foi possível carregar sua conta.'));
      return;
    }

    // Atualiza e-mail.
    $mail = trim((string) $form_state->getValue('mail'));
    $user->setEmail($mail);

    $cnpj = preg_replace('/\D/', '', (string) $form_state->getValue('field_cnpj'));
    $inscricao_municipal = preg_replace('/\D/', '', (string) $form_state->getValue('field_inscricao_municipal'));

    // Campos simples.
    $custom_fields = [
      'field_cnpj',
      'field_razao_social',
      'field_nome_fantasia',
      'field_sobre_empresa',
      'field_cep',
      'field_endereco',
      'field_complemento',
      'field_bairro',
      'field_cidade',
      'field_estado',
      'field_responsavel_nome',
      'field_responsavel_telefone',
      'field_responsavel_email',
    ];

    if ($user->hasField('field_inscricao_municipal')) {
      $user->set('field_inscricao_municipal', $inscricao_municipal !== '' ? $inscricao_municipal : NULL);
    }

    foreach ($custom_fields as $field) {
      if ($user->hasField($field)) {
        $value = $form_state->getValue($field);
        $user->set($field, $value !== '' ? $value : NULL);
      }
    }

    if ($user->hasField('field_cpf_empresa')) {
      $user->set('field_cpf_empresa', NULL);
    }

    $user->save();
    $this->messenger()->addStatus($this->t('As informações da empresa foram atualizadas com sucesso.'));
    $form_state->setRedirect('custom_panel.painel');
  }

  // ── Helpers de validação ───────────────────────────────────────

  private function validarCnpj(string $cnpj): bool
  {
    if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
      return FALSE;
    }

    $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
      $sum += (int) $cnpj[$i] * $weights1[$i];
    }
    $remainder = $sum % 11;
    $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

    if ((int) $cnpj[12] !== $digit1) {
      return FALSE;
    }

    $sum = 0;
    for ($i = 0; $i < 13; $i++) {
      $sum += (int) $cnpj[$i] * $weights2[$i];
    }
    $remainder = $sum % 11;
    $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

    return (int) $cnpj[13] === $digit2;
  }
}
