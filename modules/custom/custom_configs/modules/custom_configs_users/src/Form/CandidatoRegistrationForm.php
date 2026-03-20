<?php

namespace Drupal\custom_configs_users\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\user\Entity\User;

class CandidatoRegistrationForm extends FormBase {

    public function getFormId() {
        return 'custom_configs_users_candidato_registration_form';
    }

    /**
     * Carrega as opções de um campo list_string do usuário.
     */
    private function getListOptions(string $field_name): array {
        $storage = FieldStorageConfig::loadByName('user', $field_name);
        if (!$storage) {
            return [];
        }
        $allowed = $storage->getSetting('allowed_values');
        if (!is_array($allowed) || empty($allowed)) {
            return [];
        }
        // Formato já é chave => valor no Drupal.
        return $allowed;
    }

    public function buildForm(array $form, FormStateInterface $form_state) {

        // ── Seção 1 — Dados de Acesso ──────────────────────────────
        $form['section_acesso'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['mb-4']],
        ];
        $form['section_acesso']['heading'] = [
            '#markup' => '<h4 class="mb-3 pb-2 border-bottom"><i class="fas fa-lock me-2"></i>' . $this->t('Dados de Acesso') . '</h4>',
        ];
        $form['section_acesso']['row'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['row', 'g-3']],
        ];

        $form['section_acesso']['row']['col_name'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-6']],
        ];
        $form['section_acesso']['row']['col_name']['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nome de usuário'),
            '#required' => TRUE,
            '#maxlength' => 60,
            '#attributes' => ['class' => ['form-control']],
        ];

        $form['section_acesso']['row']['col_mail'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-6']],
        ];
        $form['section_acesso']['row']['col_mail']['mail'] = [
            '#type' => 'email',
            '#title' => $this->t('E-mail'),
            '#required' => TRUE,
            '#attributes' => ['class' => ['form-control']],
        ];

        $form['section_acesso']['row']['col_pass'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-6']],
        ];
        $form['section_acesso']['row']['col_pass']['pass'] = [
            '#type' => 'password',
            '#title' => $this->t('Senha'),
            '#required' => TRUE,
            '#description' => $this->t('Mínimo de 8 caracteres.'),
            '#attributes' => ['class' => ['form-control']],
        ];

        $form['section_acesso']['row']['col_pass_confirm'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-6']],
        ];
        $form['section_acesso']['row']['col_pass_confirm']['pass_confirm'] = [
            '#type' => 'password',
            '#title' => $this->t('Confirmar senha'),
            '#required' => TRUE,
            '#attributes' => ['class' => ['form-control']],
        ];

        // ── Seção 2 — Dados Pessoais ───────────────────────────────
        $form['section_pessoal'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['mb-4']],
        ];
        $form['section_pessoal']['heading'] = [
            '#markup' => '<h4 class="mb-3 pb-2 border-bottom"><i class="fas fa-user me-2"></i>' . $this->t('Dados Pessoais') . '</h4>',
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
            '#attributes' => [
                'class' => ['form-control'],
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
            '#attributes' => ['class' => ['form-control']],
        ];

        // ── Seção 3 — Filiação ─────────────────────────────────────
        $form['section_filiacao'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['mb-4']],
        ];
        $form['section_filiacao']['heading'] = [
            '#markup' => '<h4 class="mb-3 pb-2 border-bottom"><i class="fas fa-people-arrows me-2"></i>' . $this->t('Filiação') . '</h4>',
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
            '#attributes' => ['class' => ['form-control']],
        ];

        // ── Seção 4 — Endereço ─────────────────────────────────────
        $form['section_endereco'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['mb-4']],
        ];
        $form['section_endereco']['heading'] = [
            '#markup' => '<h4 class="mb-3 pb-2 border-bottom"><i class="fas fa-map-marker-alt me-2"></i>' . $this->t('Endereço') . '</h4>',
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
            '#attributes' => [
                'class' => ['form-control'],
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
            '#attributes' => ['class' => ['form-select']],
        ];

        // ── Seção 5 — Contato e Redes Sociais ──────────────────────
        $form['section_contato'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['mb-4']],
        ];
        $form['section_contato']['heading'] = [
            '#markup' => '<h4 class="mb-3 pb-2 border-bottom"><i class="fas fa-address-book me-2"></i>' . $this->t('Contato e Redes Sociais') . '</h4>',
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
            '#attributes' => [
                'class' => ['form-control'],
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
            '#attributes' => [
                'class' => ['form-control'],
                'placeholder' => 'https://linkedin.com/in/usuario',
            ],
        ];

        // ── Ações ──────────────────────────────────────────────────
        $form['actions'] = [
            '#type' => 'actions',
            '#attributes' => ['class' => ['mt-4', 'd-grid', 'gap-2', 'd-md-flex', 'justify-content-md-end']],
        ];
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Cadastrar'),
            '#button_type' => 'primary',
            '#attributes' => ['class' => ['btn', 'btn-primary', 'btn-lg', 'px-5']],
        ];

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        $name = trim((string) $form_state->getValue('name'));
        $mail = trim((string) $form_state->getValue('mail'));
        $pass = (string) $form_state->getValue('pass');
        $pass_confirm = (string) $form_state->getValue('pass_confirm');

        // Acesso.
        if (mb_strlen($name) < 3) {
            $form_state->setErrorByName('name', $this->t('O nome de usuário deve ter pelo menos 3 caracteres.'));
        }

        if (!\Drupal::service('email.validator')->isValid($mail)) {
            $form_state->setErrorByName('mail', $this->t('Informe um endereço de e-mail válido.'));
        }

        $existing_name = \Drupal::entityTypeManager()
            ->getStorage('user')
            ->loadByProperties(['name' => $name]);
        if (!empty($existing_name)) {
            $form_state->setErrorByName('name', $this->t('Este nome de usuário já está em uso.'));
        }

        $existing_mail = \Drupal::entityTypeManager()
            ->getStorage('user')
            ->loadByProperties(['mail' => $mail]);
        if (!empty($existing_mail)) {
            $form_state->setErrorByName('mail', $this->t('Este e-mail já está em uso.'));
        }

        if (mb_strlen($pass) < 8) {
            $form_state->setErrorByName('pass', $this->t('A senha deve ter no mínimo 8 caracteres.'));
        }

        if ($pass !== $pass_confirm) {
            $form_state->setErrorByName('pass_confirm', $this->t('A confirmação de senha não confere.'));
        }

        // CPF — apenas dígitos, 11 caracteres.
        $cpf = preg_replace('/\D/', '', (string) $form_state->getValue('field_cpf'));
        if (mb_strlen($cpf) !== 11) {
            $form_state->setErrorByName('field_cpf', $this->t('O CPF deve conter 11 dígitos.'));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        // Campos customizados a salvar na entidade.
        $custom_fields = [
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
            'field_bairro',
            'field_cidade',
            'field_estado',
            'field_telefone',
            'field_instagram',
            'field_linkedin',
        ];

        $values = [
            'name' => trim((string) $form_state->getValue('name')),
            'mail' => trim((string) $form_state->getValue('mail')),
            'pass' => (string) $form_state->getValue('pass'),
            'status' => 1,
        ];

        foreach ($custom_fields as $field) {
            $value = $form_state->getValue($field);
            if ($value !== NULL && $value !== '') {
                $values[$field] = $value;
            }
        }

        $user = User::create($values);
        $user->save();

        if ($user->id()) {
            \Drupal::currentUser()->setAccount($user);
            $session = \Drupal::service('session');
            $session->migrate();
            $session->set('uid', $user->id());
            $session->set('check_logged_in', TRUE);
            \Drupal::moduleHandler()->invokeAll('user_login', [$user]);
        }

        $this->messenger()->addStatus($this->t('Cadastro realizado com sucesso.'));
        $form_state->setRedirect('<front>');
    }

}
