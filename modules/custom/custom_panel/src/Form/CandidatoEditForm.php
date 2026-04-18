<?php

namespace Drupal\custom_panel\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\user\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CandidatoEditForm extends FormBase
{

  public function getFormId()
  {
    return 'custom_panel_candidato_edit_form';
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

  private function getMonthOptions(): array
  {
    return [
      '01' => $this->t('01 - Janeiro'),
      '02' => $this->t('02 - Fevereiro'),
      '03' => $this->t('03 - Março'),
      '04' => $this->t('04 - Abril'),
      '05' => $this->t('05 - Maio'),
      '06' => $this->t('06 - Junho'),
      '07' => $this->t('07 - Julho'),
      '08' => $this->t('08 - Agosto'),
      '09' => $this->t('09 - Setembro'),
      '10' => $this->t('10 - Outubro'),
      '11' => $this->t('11 - Novembro'),
      '12' => $this->t('12 - Dezembro'),
    ];
  }

  private function getYearOptions(): array
  {
    $current_year = (int) date('Y');
    $options = [];

    for ($year = $current_year - 1; $year <= $current_year + 15; $year++) {
      $options[(string) $year] = (string) $year;
    }

    return $options;
  }

  private function getPrevisaoFormaturaParts(User $user, FormStateInterface $form_state): array
  {
    $month = $form_state->getValue('field_previsao_formatura_month');
    $year = $form_state->getValue('field_previsao_formatura_year');

    if ($month !== NULL || $year !== NULL) {
      return [
        'month' => (string) ($month ?? ''),
        'year' => (string) ($year ?? ''),
      ];
    }

    $value = $this->getFieldValue($user, 'field_previsao_formatura');
    if ($value === '') {
      return ['month' => '', 'year' => ''];
    }

    try {
      $date = new \DateTime($value);
      return [
        'month' => $date->format('m'),
        'year' => $date->format('Y'),
      ];
    } catch (\Exception $exception) {
      return ['month' => '', 'year' => ''];
    }
  }

  private function normalizePrevisaoFormatura(?string $month, ?string $year): ?string
  {
    $month = trim((string) $month);
    $year = trim((string) $year);

    if ($month === '' || $year === '') {
      return NULL;
    }

    if (!preg_match('/^(0[1-9]|1[0-2])$/', $month) || !preg_match('/^\d{4}$/', $year)) {
      return NULL;
    }

    return sprintf('%04d-%02d-01', (int) $year, (int) $month);
  }

  /**
   * Retorna o valor de um campo simples do User.
   */
  private function getFieldValue(User $user, string $field_name): string
  {
    if ($user->hasField($field_name) && !$user->get($field_name)->isEmpty()) {
      return (string) $user->get($field_name)->value;
    }
    return '';
  }

  /**
   * Carrega os paragraphs de um campo entity_reference_revisions.
   */
  private function loadParagraphs(User $user, string $field_name): array
  {
    if (!$user->hasField($field_name) || $user->get($field_name)->isEmpty()) {
      return [];
    }

    $paragraphs = [];
    foreach ($user->get($field_name) as $item) {
      $paragraph = $item->entity;
      if ($paragraph instanceof Paragraph) {
        $paragraphs[] = $paragraph;
      }
    }
    return $paragraphs;
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $user = User::load(\Drupal::currentUser()->id());
    if (!$user || !$user->hasRole('candidato')) {
      throw new AccessDeniedHttpException();
    }

    $previsao_formatura = $this->getPrevisaoFormaturaParts($user, $form_state);

    $form['#attributes']['novalidate'] = 'novalidate';

    $form['messages'] = [
      '#type' => 'status_messages',
      '#weight' => -1000,
    ];

    $form['page_title'] = [
      '#markup' => '<div class="mb-4 mt-2">'
        . '<h2 class="mb-1"><i class="fas fa-user-edit me-2"></i>' . $this->t('Editar Informações Pessoais') . '</h2>'
        . '<p class="text-muted mb-0">' . $this->t('Atualize seus dados cadastrais abaixo.') . '</p>'
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

    // ── Seção 2 — Dados Pessoais ───────────────────────────────
    $form['section_pessoal'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mb-4']],
    ];
    $form['section_pessoal']['heading'] = [
      '#markup' => '<h3 class="mb-3 pb-2 border-bottom"><i class="fas fa-user me-2"></i>' . $this->t('Dados Pessoais') . '</h3>',
    ];
    $form['section_pessoal']['row'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row', 'g-3']],
    ];

    $form['section_pessoal']['row']['col_nome_completo'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12']],
    ];
    $form['section_pessoal']['row']['col_nome_completo']['field_nome_completo'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nome completo'),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#default_value' => $this->getFieldValue($user, 'field_nome_completo'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_pessoal']['row']['col_cpf'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_pessoal']['row']['col_cpf']['field_cpf'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CPF'),
      '#required' => TRUE,
      '#maxlength' => 14,
      '#default_value' => $this->getFieldValue($user, 'field_cpf'),
      '#attributes' => [
        'class' => ['form-control', 'mask-cpf'],
        'placeholder' => '000.000.000-00',
      ],
    ];

    $form['section_pessoal']['row']['col_orgao_emissor'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_pessoal']['row']['col_orgao_emissor']['field_orgao_emissor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Órgão emissor'),
      '#maxlength' => 60,
      '#default_value' => $this->getFieldValue($user, 'field_orgao_emissor'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_pessoal']['row']['col_data_nascimento'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_pessoal']['row']['col_data_nascimento']['field_data_nascimento'] = [
      '#type' => 'date',
      '#title' => $this->t('Data de nascimento'),
      '#required' => TRUE,
      '#default_value' => $this->getFieldValue($user, 'field_data_nascimento'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_pessoal']['row']['col_sexo'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_pessoal']['row']['col_sexo']['field_sexo'] = [
      '#type' => 'select',
      '#title' => $this->t('Sexo'),
      '#options' => ['' => $this->t('- Selecione -')] + $this->getListOptions('field_sexo'),
      '#required' => TRUE,
      '#default_value' => $this->getFieldValue($user, 'field_sexo'),
      '#attributes' => ['class' => ['form-select']],
    ];

    $form['section_pessoal']['row']['col_identidade_genero'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_pessoal']['row']['col_identidade_genero']['field_identidade_genero'] = [
      '#type' => 'select',
      '#title' => $this->t('Identidade de gênero'),
      '#options' => ['' => $this->t('- Selecione -')] + $this->getListOptions('field_identidade_genero'),
      '#default_value' => $this->getFieldValue($user, 'field_identidade_genero'),
      '#attributes' => ['class' => ['form-select']],
    ];

    $form['section_pessoal']['row']['col_estado_civil'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_pessoal']['row']['col_estado_civil']['field_estado_civil'] = [
      '#type' => 'select',
      '#title' => $this->t('Estado civil'),
      '#options' => ['' => $this->t('- Selecione -')] + $this->getListOptions('field_estado_civil'),
      '#default_value' => $this->getFieldValue($user, 'field_estado_civil'),
      '#attributes' => ['class' => ['form-select']],
    ];

    $form['section_pessoal']['row']['col_quantidade_filhos'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_pessoal']['row']['col_quantidade_filhos']['field_quantidade_filhos'] = [
      '#type' => 'select',
      '#title' => $this->t('Quantidade de filhos'),
      '#options' => ['' => $this->t('- Selecione -')] + $this->getListOptions('field_quantidade_filhos'),
      '#default_value' => $this->getFieldValue($user, 'field_quantidade_filhos'),
      '#attributes' => ['class' => ['form-select']],
    ];

    $form['section_pessoal']['row']['col_nacionalidade'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_pessoal']['row']['col_nacionalidade']['field_nacionalidade'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nacionalidade'),
      '#maxlength' => 100,
      '#default_value' => $this->getFieldValue($user, 'field_nacionalidade'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_pessoal']['row']['col_estado_natal'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_pessoal']['row']['col_estado_natal']['field_estado_natal'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Estado natal'),
      '#maxlength' => 100,
      '#default_value' => $this->getFieldValue($user, 'field_estado_natal'),
      '#attributes' => ['class' => ['form-control']],
    ];

    // ── Seção 3 — Filiação ─────────────────────────────────────
    $form['section_filiacao'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mb-4']],
    ];
    $form['section_filiacao']['heading'] = [
      '#markup' => '<h3 class="mb-3 pb-2 border-bottom"><i class="fas fa-people-arrows me-2"></i>' . $this->t('Filiação') . '</h3>',
    ];
    $form['section_filiacao']['row'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row', 'g-3']],
    ];

    $form['section_filiacao']['row']['col_nome_mae'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-6']],
    ];
    $form['section_filiacao']['row']['col_nome_mae']['field_nome_mae'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nome da mãe'),
      '#maxlength' => 255,
      '#default_value' => $this->getFieldValue($user, 'field_nome_mae'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_filiacao']['row']['col_nome_pai'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-6']],
    ];
    $form['section_filiacao']['row']['col_nome_pai']['field_nome_pai'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nome do pai'),
      '#maxlength' => 255,
      '#default_value' => $this->getFieldValue($user, 'field_nome_pai'),
      '#attributes' => ['class' => ['form-control']],
    ];

    // ── Seção 4 — Endereço ─────────────────────────────────────
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

    // ── Seção 5 — Contato e Redes Sociais ──────────────────────
    $form['section_contato'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mb-4']],
    ];
    $form['section_contato']['heading'] = [
      '#markup' => '<h3 class="mb-3 pb-2 border-bottom"><i class="fas fa-address-book me-2"></i>' . $this->t('Contato e Redes Sociais') . '</h3>',
    ];
    $form['section_contato']['row'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row', 'g-3']],
    ];

    $form['section_contato']['row']['col_telefone'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_contato']['row']['col_telefone']['field_telefone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Telefone'),
      '#maxlength' => 15,
      '#default_value' => $this->getFieldValue($user, 'field_telefone'),
      '#attributes' => [
        'class' => ['form-control', 'mask-phone'],
        'placeholder' => '(00) 00000-0000',
      ],
    ];

    $form['section_contato']['row']['col_instagram'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_contato']['row']['col_instagram']['field_instagram'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Instagram'),
      '#maxlength' => 100,
      '#default_value' => $this->getFieldValue($user, 'field_instagram'),
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => '@usuario',
      ],
    ];

    $form['section_contato']['row']['col_linkedin'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_contato']['row']['col_linkedin']['field_linkedin'] = [
      '#type' => 'textfield',
      '#title' => $this->t('LinkedIn'),
      '#maxlength' => 255,
      '#default_value' => $this->getFieldValue($user, 'field_linkedin'),
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => 'https://linkedin.com/in/usuario',
      ],
    ];

    // ── Seção 6 — Instituição de Ensino (Paragraphs) ──────────
    $form['section_instituicao'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mb-4']],
    ];
    $form['section_instituicao']['heading'] = [
      '#markup' => '<h3 class="mb-3 pb-2 border-bottom"><i class="fas fa-university me-2"></i>' . $this->t('Instituição de Ensino') . '</h3>',
    ];
    $form['section_instituicao']['instituicoes_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'instituicoes-wrapper'],
    ];

    $existing_inst = $this->loadParagraphs($user, 'field_instituicao_ensino');
    if ($form_state->get('num_instituicoes') === NULL) {
      $form_state->set('num_instituicoes', max(count($existing_inst), 1));
      $ids = array_map(fn(Paragraph $p) => $p->id(), $existing_inst);
      $form_state->set('existing_inst_ids', $ids);
    }
    $num_instituicoes = $form_state->get('num_instituicoes');

    for ($i = 0; $i < $num_instituicoes; $i++) {
      $p = $existing_inst[$i] ?? NULL;

      $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['card', 'mb-3']],
      ];
      $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i]['body'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['card-body']],
      ];
      $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i]['body']['title'] = [
        '#markup' => '<h4 class="card-title text-muted">' . $this->t('Instituição @num', ['@num' => $i + 1]) . '</h4>',
      ];
      $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i]['body']['row'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['row', 'g-3']],
      ];

      $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i]['body']['row']['col_nome'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12']],
      ];
      $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i]['body']['row']['col_nome']['inst_nome_' . $i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Instituição de Ensino'),
        '#maxlength' => 255,
        '#default_value' => $p ? (string) ($p->get('field_nome_instituicao')->value ?? '') : '',
        '#attributes' => ['class' => ['form-control']],
      ];

      $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i]['body']['row']['col_endereco'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12', 'col-md-6']],
      ];
      $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i]['body']['row']['col_endereco']['inst_endereco_' . $i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Endereço'),
        '#maxlength' => 255,
        '#default_value' => $p ? (string) ($p->get('field_endereco')->value ?? '') : '',
        '#attributes' => ['class' => ['form-control']],
      ];

      $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i]['body']['row']['col_bairro'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12', 'col-md-3']],
      ];
      $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i]['body']['row']['col_bairro']['inst_bairro_' . $i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Bairro'),
        '#maxlength' => 100,
        '#default_value' => $p ? (string) ($p->get('field_bairro')->value ?? '') : '',
        '#attributes' => ['class' => ['form-control']],
      ];

      $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i]['body']['row']['col_cidade'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12', 'col-md-3']],
      ];
      $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i]['body']['row']['col_cidade']['inst_cidade_' . $i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Cidade'),
        '#maxlength' => 100,
        '#default_value' => $p ? (string) ($p->get('field_cidade')->value ?? '') : '',
        '#attributes' => ['class' => ['form-control']],
      ];
    }

    $form['section_instituicao']['instituicoes_wrapper']['paragraph_actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['painel-paragraph-actions']],
    ];
    $form['section_instituicao']['instituicoes_wrapper']['paragraph_actions']['add_instituicao'] = [
      '#type' => 'submit',
      '#value' => $this->t('Incluir Instituição'),
      '#submit' => ['::addInstituicaoCallback'],
      '#ajax' => [
        'callback' => '::ajaxRefreshInstituicoes',
        'wrapper' => 'instituicoes-wrapper',
      ],
      '#attributes' => ['class' => ['btn', 'btn-outline-laranja', 'btn-sm']],
      '#limit_validation_errors' => [],
    ];

    if ($num_instituicoes > 1) {
      $form['section_instituicao']['instituicoes_wrapper']['paragraph_actions']['remove_instituicao'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remover última instituição'),
        '#submit' => ['::removeInstituicaoCallback'],
        '#ajax' => [
          'callback' => '::ajaxRefreshInstituicoes',
          'wrapper' => 'instituicoes-wrapper',
        ],
        '#attributes' => ['class' => ['btn', 'btn-outline-azul-escuro', 'btn-sm']],
        '#limit_validation_errors' => [],
      ];
    }

    // ── Seção 7 — Informações Acadêmicas ───────────────────────
    $form['section_academico'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mb-4']],
    ];
    $form['section_academico']['heading'] = [
      '#markup' => '<h3 class="mb-3 pb-2 border-bottom"><i class="fas fa-graduation-cap me-2"></i>' . $this->t('Informações Acadêmicas') . '</h3>',
    ];
    $form['section_academico']['row'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row', 'g-3']],
    ];

    $form['section_academico']['row']['col_escolaridade'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_academico']['row']['col_escolaridade']['field_escolaridade'] = [
      '#type' => 'select',
      '#title' => $this->t('Escolaridade'),
      '#options' => ['' => $this->t('- Selecione -')] + $this->getListOptions('field_escolaridade'),
      '#default_value' => $this->getFieldValue($user, 'field_escolaridade'),
      '#attributes' => ['class' => ['form-select']],
    ];

    $form['section_academico']['row']['col_periodo_letivo'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_academico']['row']['col_periodo_letivo']['field_periodo_letivo'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Período letivo'),
      '#maxlength' => 60,
      '#default_value' => $this->getFieldValue($user, 'field_periodo_letivo'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_academico']['row']['col_nome_curso'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_academico']['row']['col_nome_curso']['field_nome_curso'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nome do curso'),
      '#maxlength' => 255,
      '#default_value' => $this->getFieldValue($user, 'field_nome_curso'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_academico']['row']['col_tipo_curso'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_academico']['row']['col_tipo_curso']['field_tipo_curso'] = [
      '#type' => 'select',
      '#title' => $this->t('Tipo de curso'),
      '#options' => ['' => $this->t('- Selecione -')] + $this->getListOptions('field_tipo_curso'),
      '#default_value' => $this->getFieldValue($user, 'field_tipo_curso'),
      '#attributes' => ['class' => ['form-select']],
    ];

    $form['section_academico']['row']['col_periodo_matriculado'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_academico']['row']['col_periodo_matriculado']['field_periodo_matriculado'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Período em que está matriculado'),
      '#maxlength' => 60,
      '#default_value' => $this->getFieldValue($user, 'field_periodo_matriculado'),
      '#attributes' => ['class' => ['form-control']],
    ];

    $form['section_academico']['row']['col_horario_curso'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_academico']['row']['col_horario_curso']['field_horario_curso'] = [
      '#type' => 'select',
      '#title' => $this->t('Horário do curso'),
      '#options' => ['' => $this->t('- Selecione -')] + $this->getListOptions('field_horario_curso'),
      '#default_value' => $this->getFieldValue($user, 'field_horario_curso'),
      '#attributes' => ['class' => ['form-select']],
    ];

    $form['section_academico']['row']['col_duracao_curso'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_academico']['row']['col_duracao_curso']['field_duracao_curso'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Duração do curso (semestres)'),
      '#maxlength' => 10,
      '#default_value' => $this->getFieldValue($user, 'field_duracao_curso'),
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => 'Ex: 8',
      ],
    ];

    $form['section_academico']['row']['col_previsao_formatura'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-6']],
    ];
    $form['section_academico']['row']['col_previsao_formatura']['heading'] = [
      '#markup' => '<label class="form-label d-block mb-2">' . $this->t('Previsão de formatura') . ' <span class="text-danger">*</span></label><div class="form-text mb-2">' . $this->t('Informe somente o mês e o ano previstos para a conclusão do curso.') . '</div>',
    ];
    $form['section_academico']['row']['col_previsao_formatura']['row'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row', 'g-2']],
    ];
    $form['section_academico']['row']['col_previsao_formatura']['row']['col_mes'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-6']],
    ];
    $form['section_academico']['row']['col_previsao_formatura']['row']['col_mes']['field_previsao_formatura_month'] = [
      '#type' => 'select',
      '#title' => $this->t('Mês'),
      '#title_display' => 'invisible',
      '#required' => TRUE,
      '#empty_option' => $this->t('- Selecione o mês -'),
      '#options' => $this->getMonthOptions(),
      '#default_value' => $previsao_formatura['month'],
      '#attributes' => ['class' => ['form-select']],
    ];
    $form['section_academico']['row']['col_previsao_formatura']['row']['col_ano'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-6']],
    ];
    $form['section_academico']['row']['col_previsao_formatura']['row']['col_ano']['field_previsao_formatura_year'] = [
      '#type' => 'select',
      '#title' => $this->t('Ano'),
      '#title_display' => 'invisible',
      '#required' => TRUE,
      '#empty_option' => $this->t('- Selecione o ano -'),
      '#options' => $this->getYearOptions(),
      '#default_value' => $previsao_formatura['year'],
      '#attributes' => ['class' => ['form-select']],
    ];

    $form['section_academico']['row']['col_disponibilidade'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_academico']['row']['col_disponibilidade']['field_disponibilidade_estagio'] = [
      '#type' => 'select',
      '#title' => $this->t('Disponibilidade para estágio'),
      '#options' => ['' => $this->t('- Selecione -')] + $this->getListOptions('field_disponibilidade_estagio'),
      '#default_value' => $this->getFieldValue($user, 'field_disponibilidade_estagio'),
      '#attributes' => ['class' => ['form-select']],
    ];

    $form['section_academico']['row']['col_numero_matricula'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12', 'col-md-4']],
    ];
    $form['section_academico']['row']['col_numero_matricula']['field_numero_matricula'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Número de matrícula'),
      '#maxlength' => 60,
      '#default_value' => $this->getFieldValue($user, 'field_numero_matricula'),
      '#attributes' => ['class' => ['form-control']],
    ];

    // ── Seção 8 — Cursos Extracurriculares (Paragraphs) ────────
    $form['section_extracurricular'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mb-4']],
    ];
    $form['section_extracurricular']['heading'] = [
      '#markup' => '<h3 class="mb-3 pb-2 border-bottom"><i class="fas fa-award me-2"></i>' . $this->t('Cursos Extracurriculares') . ' <small class="text-muted">(' . $this->t('Opcional') . ')</small></h3>',
    ];
    $form['section_extracurricular']['cursos_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'cursos-extracurriculares-wrapper'],
    ];

    $existing_cursos = $this->loadParagraphs($user, 'field_cursos_extracurriculares');
    if ($form_state->get('num_cursos') === NULL) {
      $form_state->set('num_cursos', count($existing_cursos));
      $ids = array_map(fn(Paragraph $p) => $p->id(), $existing_cursos);
      $form_state->set('existing_cursos_ids', $ids);
    }
    $num_cursos = $form_state->get('num_cursos');

    $nivel_options = $this->getListOptions('field_nivel', 'paragraph');
    if (empty($nivel_options)) {
      $nivel_options = [
        'basico' => $this->t('Básico'),
        'intermediario' => $this->t('Intermediário'),
        'avancado' => $this->t('Avançado'),
      ];
    }

    for ($i = 0; $i < $num_cursos; $i++) {
      $p = $existing_cursos[$i] ?? NULL;

      $form['section_extracurricular']['cursos_wrapper']['curso_' . $i] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['card', 'mb-3']],
      ];
      $form['section_extracurricular']['cursos_wrapper']['curso_' . $i]['body'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['card-body']],
      ];
      $form['section_extracurricular']['cursos_wrapper']['curso_' . $i]['body']['title'] = [
        '#markup' => '<h4 class="card-title text-muted">' . $this->t('Curso @num', ['@num' => $i + 1]) . '</h4>',
      ];
      $form['section_extracurricular']['cursos_wrapper']['curso_' . $i]['body']['row'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['row', 'g-3']],
      ];

      $form['section_extracurricular']['cursos_wrapper']['curso_' . $i]['body']['row']['col_tipo'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12', 'col-md-3']],
      ];
      $form['section_extracurricular']['cursos_wrapper']['curso_' . $i]['body']['row']['col_tipo']['curso_tipo_' . $i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Tipo de habilidade'),
        '#maxlength' => 255,
        '#default_value' => $p ? (string) ($p->get('field_tipo_habilidade')->value ?? '') : '',
        '#attributes' => ['class' => ['form-control']],
      ];

      $form['section_extracurricular']['cursos_wrapper']['curso_' . $i]['body']['row']['col_habilidade'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12', 'col-md-3']],
      ];
      $form['section_extracurricular']['cursos_wrapper']['curso_' . $i]['body']['row']['col_habilidade']['curso_habilidade_' . $i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Habilidade'),
        '#maxlength' => 255,
        '#default_value' => $p ? (string) ($p->get('field_habilidade')->value ?? '') : '',
        '#attributes' => ['class' => ['form-control']],
      ];

      $form['section_extracurricular']['cursos_wrapper']['curso_' . $i]['body']['row']['col_nivel'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12', 'col-md-3']],
      ];
      $form['section_extracurricular']['cursos_wrapper']['curso_' . $i]['body']['row']['col_nivel']['curso_nivel_' . $i] = [
        '#type' => 'select',
        '#title' => $this->t('Nível'),
        '#options' => ['' => $this->t('- Selecione -')] + $nivel_options,
        '#default_value' => $p ? (string) ($p->get('field_nivel')->value ?? '') : '',
        '#attributes' => ['class' => ['form-select']],
      ];

      $form['section_extracurricular']['cursos_wrapper']['curso_' . $i]['body']['row']['col_carga'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12', 'col-md-3']],
      ];
      $form['section_extracurricular']['cursos_wrapper']['curso_' . $i]['body']['row']['col_carga']['curso_carga_' . $i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Carga horária'),
        '#maxlength' => 20,
        '#default_value' => $p ? (string) ($p->get('field_carga_horaria')->value ?? '') : '',
        '#attributes' => ['class' => ['form-control']],
      ];
    }

    $form['section_extracurricular']['cursos_wrapper']['paragraph_actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['painel-paragraph-actions']],
    ];
    $form['section_extracurricular']['cursos_wrapper']['paragraph_actions']['add_curso'] = [
      '#type' => 'submit',
      '#value' => $this->t('Incluir Curso Extracurricular'),
      '#submit' => ['::addCursoCallback'],
      '#ajax' => [
        'callback' => '::ajaxRefreshCursos',
        'wrapper' => 'cursos-extracurriculares-wrapper',
      ],
      '#attributes' => ['class' => ['btn', 'btn-outline-laranja', 'btn-sm']],
      '#limit_validation_errors' => [],
    ];

    if ($num_cursos > 0) {
      $form['section_extracurricular']['cursos_wrapper']['paragraph_actions']['remove_curso'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remover último curso'),
        '#submit' => ['::removeCursoCallback'],
        '#ajax' => [
          'callback' => '::ajaxRefreshCursos',
          'wrapper' => 'cursos-extracurriculares-wrapper',
        ],
        '#attributes' => ['class' => ['btn', 'btn-outline-azul-escuro', 'btn-sm']],
        '#limit_validation_errors' => [],
      ];
    }

    // ── Seção 9 — Experiência Profissional (Paragraphs) ────────
    $form['section_experiencia'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mb-4']],
    ];
    $form['section_experiencia']['heading'] = [
      '#markup' => '<h3 class="mb-3 pb-2 border-bottom"><i class="fas fa-briefcase me-2"></i>' . $this->t('Experiência Profissional') . ' <small class="text-muted">(' . $this->t('Opcional') . ')</small></h3>',
    ];
    $form['section_experiencia']['experiencias_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'experiencias-wrapper'],
    ];

    $existing_exp = $this->loadParagraphs($user, 'field_experiencias_profissionais');
    if ($form_state->get('num_experiencias') === NULL) {
      $form_state->set('num_experiencias', count($existing_exp));
      $ids = array_map(fn(Paragraph $p) => $p->id(), $existing_exp);
      $form_state->set('existing_exp_ids', $ids);
    }
    $num_experiencias = $form_state->get('num_experiencias');

    $regime_options = $this->getListOptions('field_regime_contrato', 'paragraph');
    if (empty($regime_options)) {
      $regime_options = [
        'clt'        => $this->t('CLT'),
        'estagio'    => $this->t('Estágio'),
        'pj'         => $this->t('Pessoa Jurídica (PJ)'),
        'autonomo'   => $this->t('Autônomo'),
        'temporario' => $this->t('Temporário'),
        'outros'     => $this->t('Outros'),
      ];
    }

    for ($i = 0; $i < $num_experiencias; $i++) {
      $p = $existing_exp[$i] ?? NULL;

      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['card', 'mb-3']],
      ];
      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['card-body']],
      ];
      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['title'] = [
        '#markup' => '<h4 class="card-title text-muted">' . $this->t('Experiência @num', ['@num' => $i + 1]) . '</h4>',
      ];
      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['row'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['row', 'g-3']],
      ];

      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['row']['col_empresa'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12', 'col-md-6']],
      ];
      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['row']['col_empresa']['exp_nome_empresa_' . $i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Nome da Empresa'),
        '#maxlength' => 255,
        '#default_value' => $p ? (string) ($p->get('field_nome_empresa')->value ?? '') : '',
        '#attributes' => ['class' => ['form-control']],
      ];

      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['row']['col_cargo'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12', 'col-md-6']],
      ];
      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['row']['col_cargo']['exp_cargo_' . $i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Cargo'),
        '#maxlength' => 255,
        '#default_value' => $p ? (string) ($p->get('field_cargo')->value ?? '') : '',
        '#attributes' => ['class' => ['form-control']],
      ];

      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['row']['col_data_inicio'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12', 'col-md-3']],
      ];
      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['row']['col_data_inicio']['exp_data_inicio_' . $i] = [
        '#type' => 'date',
        '#title' => $this->t('Data de Início'),
        '#default_value' => $p ? (string) ($p->get('field_data_inicio')->value ?? '') : '',
        '#attributes' => ['class' => ['form-control']],
      ];

      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['row']['col_data_termino'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12', 'col-md-3']],
      ];
      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['row']['col_data_termino']['exp_data_termino_' . $i] = [
        '#type' => 'date',
        '#title' => $this->t('Data de Término'),
        '#description' => $this->t('Deixe em branco se ainda está neste emprego.'),
        '#default_value' => $p ? (string) ($p->get('field_data_termino')->value ?? '') : '',
        '#attributes' => ['class' => ['form-control']],
      ];

      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['row']['col_regime'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12', 'col-md-6']],
      ];
      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['row']['col_regime']['exp_regime_' . $i] = [
        '#type' => 'select',
        '#title' => $this->t('Regime de Contrato'),
        '#options' => ['' => $this->t('- Selecione -')] + $regime_options,
        '#default_value' => $p ? (string) ($p->get('field_regime_contrato')->value ?? '') : '',
        '#attributes' => ['class' => ['form-select']],
      ];

      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['row']['col_atividades'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['col-12']],
      ];
      $form['section_experiencia']['experiencias_wrapper']['exp_' . $i]['body']['row']['col_atividades']['exp_atividades_' . $i] = [
        '#type' => 'textarea',
        '#title' => $this->t('Atividades'),
        '#rows' => 3,
        '#default_value' => $p ? (string) ($p->get('field_atividades')->value ?? '') : '',
        '#attributes' => ['class' => ['form-control']],
      ];
    }

    $form['section_experiencia']['experiencias_wrapper']['paragraph_actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['painel-paragraph-actions']],
    ];
    $form['section_experiencia']['experiencias_wrapper']['paragraph_actions']['add_experiencia'] = [
      '#type' => 'submit',
      '#value' => $this->t('Incluir Experiência'),
      '#submit' => ['::addExperienciaCallback'],
      '#ajax' => [
        'callback' => '::ajaxRefreshExperiencias',
        'wrapper' => 'experiencias-wrapper',
      ],
      '#attributes' => ['class' => ['btn', 'btn-outline-laranja', 'btn-sm']],
      '#limit_validation_errors' => [],
    ];

    if ($num_experiencias > 0) {
      $form['section_experiencia']['experiencias_wrapper']['paragraph_actions']['remove_experiencia'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remover última experiência'),
        '#submit' => ['::removeExperienciaCallback'],
        '#ajax' => [
          'callback' => '::ajaxRefreshExperiencias',
          'wrapper' => 'experiencias-wrapper',
        ],
        '#attributes' => ['class' => ['btn', 'btn-outline-azul-escuro', 'btn-sm']],
        '#limit_validation_errors' => [],
      ];
    }

    // ── Seção 10 — Informações Complementares ──────────────────
    $form['section_complementar'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mb-4']],
    ];
    $form['section_complementar']['heading'] = [
      '#markup' => '<h3 class="mb-3 pb-2 border-bottom"><i class="fas fa-info-circle me-2"></i>' . $this->t('Informações Complementares') . '</h3>',
    ];
    $form['section_complementar']['row'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row', 'g-3']],
    ];

    $form['section_complementar']['row']['col_deficiencia'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-12']],
    ];
    $form['section_complementar']['row']['col_deficiencia']['field_possui_deficiencia'] = [
      '#type' => 'radios',
      '#title' => $this->t('Possui alguma deficiência?'),
      '#options' => [
        '0' => $this->t('Não'),
        '1' => $this->t('Sim'),
      ],
      '#default_value' => $this->getFieldValue($user, 'field_possui_deficiencia') ?: '0',
    ];

    $form['section_complementar']['row']['col_cid'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['col-12', 'col-md-4'],
        'id' => 'cid-wrapper',
      ],
      '#states' => [
        'visible' => [
          ':input[name="field_possui_deficiencia"]' => ['value' => '1'],
        ],
      ],
    ];
    $form['section_complementar']['row']['col_cid']['field_numero_cid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Número do CID'),
      '#maxlength' => 20,
      '#default_value' => $this->getFieldValue($user, 'field_numero_cid'),
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => $this->t('Ex: F84.0'),
      ],
    ];

    // ── Ações ──────────────────────────────────────────────────
    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['mt-4', 'mb-5', 'd-flex', 'justify-content-center']],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Salvar Alterações'),
      '#attributes' => ['class' => ['btn', 'btn-primario', 'btn-lg']],
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

  // ── AJAX Callbacks ─────────────────────────────────────────────

  public function addInstituicaoCallback(array &$form, FormStateInterface $form_state)
  {
    $form_state->set('num_instituicoes', ($form_state->get('num_instituicoes') ?? 1) + 1);
    $form_state->setRebuild();
  }

  public function removeInstituicaoCallback(array &$form, FormStateInterface $form_state)
  {
    $num = $form_state->get('num_instituicoes') ?? 1;
    if ($num > 1) {
      $form_state->set('num_instituicoes', $num - 1);
    }
    $form_state->setRebuild();
  }

  public function ajaxRefreshInstituicoes(array &$form, FormStateInterface $form_state)
  {
    return $form['section_instituicao']['instituicoes_wrapper'];
  }

  public function addCursoCallback(array &$form, FormStateInterface $form_state)
  {
    $form_state->set('num_cursos', ($form_state->get('num_cursos') ?? 0) + 1);
    $form_state->setRebuild();
  }

  public function removeCursoCallback(array &$form, FormStateInterface $form_state)
  {
    $num = $form_state->get('num_cursos') ?? 0;
    if ($num > 0) {
      $form_state->set('num_cursos', $num - 1);
    }
    $form_state->setRebuild();
  }

  public function ajaxRefreshCursos(array &$form, FormStateInterface $form_state)
  {
    return $form['section_extracurricular']['cursos_wrapper'];
  }

  public function addExperienciaCallback(array &$form, FormStateInterface $form_state)
  {
    $form_state->set('num_experiencias', ($form_state->get('num_experiencias') ?? 0) + 1);
    $form_state->setRebuild();
  }

  public function removeExperienciaCallback(array &$form, FormStateInterface $form_state)
  {
    $num = $form_state->get('num_experiencias') ?? 0;
    if ($num > 0) {
      $form_state->set('num_experiencias', $num - 1);
    }
    $form_state->setRebuild();
  }

  public function ajaxRefreshExperiencias(array &$form, FormStateInterface $form_state)
  {
    return $form['section_experiencia']['experiencias_wrapper'];
  }

  // ── Validação ──────────────────────────────────────────────────

  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $user = User::load(\Drupal::currentUser()->id());
    $mail = trim((string) $form_state->getValue('mail'));

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

    // CPF — 11 dígitos.
    $cpf = preg_replace('/\D/', '', (string) $form_state->getValue('field_cpf'));
    if (mb_strlen($cpf) !== 11) {
      $form_state->setErrorByName('field_cpf', $this->t('O CPF deve conter 11 dígitos.'));
    } elseif (!$this->validarCpf($cpf)) {
      $form_state->setErrorByName('field_cpf', $this->t('O CPF informado é inválido.'));
    }

    // Nome completo.
    $nome = trim((string) $form_state->getValue('field_nome_completo'));
    if (mb_strlen($nome) < 3) {
      $form_state->setErrorByName('field_nome_completo', $this->t('O nome completo deve ter pelo menos 3 caracteres.'));
    }

    $previsao_formatura = $this->normalizePrevisaoFormatura(
      (string) $form_state->getValue('field_previsao_formatura_month'),
      (string) $form_state->getValue('field_previsao_formatura_year')
    );
    if ($previsao_formatura === NULL) {
      $form_state->setErrorByName('field_previsao_formatura_month', $this->t('Informe o mês e o ano da previsão de formatura.'));
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

    // Campos simples.
    $simple_fields = [
      'field_nome_completo',
      'field_cpf',
      'field_orgao_emissor',
      'field_data_nascimento',
      'field_sexo',
      'field_identidade_genero',
      'field_estado_civil',
      'field_quantidade_filhos',
      'field_nacionalidade',
      'field_estado_natal',
      'field_nome_mae',
      'field_nome_pai',
      'field_cep',
      'field_endereco',
      'field_complemento',
      'field_bairro',
      'field_cidade',
      'field_estado',
      'field_telefone',
      'field_instagram',
      'field_linkedin',
      'field_escolaridade',
      'field_periodo_letivo',
      'field_nome_curso',
      'field_tipo_curso',
      'field_periodo_matriculado',
      'field_horario_curso',
      'field_duracao_curso',
      'field_disponibilidade_estagio',
      'field_numero_matricula',
      'field_possui_deficiencia',
      'field_numero_cid',
    ];

    foreach ($simple_fields as $field) {
      if ($user->hasField($field)) {
        $value = $form_state->getValue($field);
        $user->set($field, $value !== '' ? $value : NULL);
      }
    }

    if ($user->hasField('field_previsao_formatura')) {
      $user->set(
        'field_previsao_formatura',
        $this->normalizePrevisaoFormatura(
          (string) $form_state->getValue('field_previsao_formatura_month'),
          (string) $form_state->getValue('field_previsao_formatura_year')
        )
      );
    }

    // ── Paragraphs: Instituições de Ensino ────────────────────
    $this->saveParagraphs(
      $user,
      'field_instituicao_ensino',
      'instituicao_ensino',
      $form_state,
      $form_state->get('num_instituicoes') ?? 1,
      $form_state->get('existing_inst_ids') ?? [],
      function (int $i, FormStateInterface $fs) {
        return [
          'field_nome_instituicao' => $fs->getValue('inst_nome_' . $i),
          'field_endereco' => $fs->getValue('inst_endereco_' . $i),
          'field_bairro' => $fs->getValue('inst_bairro_' . $i),
          'field_cidade' => $fs->getValue('inst_cidade_' . $i),
        ];
      }
    );

    // ── Paragraphs: Cursos Extracurriculares ──────────────────
    $this->saveParagraphs(
      $user,
      'field_cursos_extracurriculares',
      'curso_extracurricular',
      $form_state,
      $form_state->get('num_cursos') ?? 0,
      $form_state->get('existing_cursos_ids') ?? [],
      function (int $i, FormStateInterface $fs) {
        return [
          'field_tipo_habilidade' => $fs->getValue('curso_tipo_' . $i),
          'field_habilidade' => $fs->getValue('curso_habilidade_' . $i),
          'field_nivel' => $fs->getValue('curso_nivel_' . $i),
          'field_carga_horaria' => $fs->getValue('curso_carga_' . $i),
        ];
      }
    );

    // ── Paragraphs: Experiências Profissionais ────────────────
    $this->saveParagraphs(
      $user,
      'field_experiencias_profissionais',
      'experiencia_profissional',
      $form_state,
      $form_state->get('num_experiencias') ?? 0,
      $form_state->get('existing_exp_ids') ?? [],
      function (int $i, FormStateInterface $fs) {
        return [
          'field_nome_empresa' => $fs->getValue('exp_nome_empresa_' . $i),
          'field_cargo' => $fs->getValue('exp_cargo_' . $i),
          'field_data_inicio' => $fs->getValue('exp_data_inicio_' . $i),
          'field_data_termino' => $fs->getValue('exp_data_termino_' . $i),
          'field_regime_contrato' => $fs->getValue('exp_regime_' . $i),
          'field_atividades' => $fs->getValue('exp_atividades_' . $i),
        ];
      }
    );

    $user->save();
    $this->messenger()->addStatus($this->t('Suas informações foram atualizadas com sucesso.'));
    $form_state->setRedirect('custom_panel.painel');
  }

  /**
   * Salva paragraphs (cria novos, atualiza existentes, remove excedentes).
   */
  private function saveParagraphs(
    User $user,
    string $field_name,
    string $paragraph_type,
    FormStateInterface $form_state,
    int $num_items,
    array $existing_ids,
    callable $getValues,
  ): void {
    $references = [];

    for ($i = 0; $i < $num_items; $i++) {
      $values = $getValues($i, $form_state);
      $has_data = FALSE;
      foreach ($values as $v) {
        if (!empty($v)) {
          $has_data = TRUE;
          break;
        }
      }

      if (!$has_data) {
        continue;
      }

      // Atualiza paragraph existente ou cria novo.
      if (isset($existing_ids[$i])) {
        $paragraph = Paragraph::load($existing_ids[$i]);
        if ($paragraph) {
          foreach ($values as $fname => $fval) {
            $paragraph->set($fname, $fval !== '' ? $fval : NULL);
          }
          $paragraph->save();
          $references[] = [
            'target_id' => $paragraph->id(),
            'target_revision_id' => $paragraph->getRevisionId(),
          ];
          continue;
        }
      }

      // Cria novo paragraph.
      $p_values = ['type' => $paragraph_type];
      foreach ($values as $fname => $fval) {
        if (!empty($fval)) {
          $p_values[$fname] = $fval;
        }
      }
      $paragraph = Paragraph::create($p_values);
      $paragraph->save();
      $references[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
    }

    // Remove paragraphs que excedem o novo count.
    for ($i = $num_items; $i < count($existing_ids); $i++) {
      if (!empty($existing_ids[$i])) {
        $paragraph = Paragraph::load($existing_ids[$i]);
        if ($paragraph) {
          $paragraph->delete();
        }
      }
    }

    $user->set($field_name, $references);
  }

  /**
   * Valida CPF pelo dígito verificador.
   */
  private function validarCpf(string $cpf): bool
  {
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
      return FALSE;
    }

    for ($t = 9; $t < 11; $t++) {
      $sum = 0;
      for ($i = 0; $i < $t; $i++) {
        $sum += (int) $cpf[$i] * ($t + 1 - $i);
      }
      $remainder = (10 * $sum) % 11;
      if ($remainder >= 10) {
        $remainder = 0;
      }
      if ((int) $cpf[$t] !== $remainder) {
        return FALSE;
      }
    }

    return TRUE;
  }
}
