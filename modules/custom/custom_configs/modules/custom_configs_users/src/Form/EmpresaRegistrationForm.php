<?php

namespace Drupal\custom_configs_users\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EmpresaRegistrationForm extends FormBase {

    public function getFormId() {
        return 'custom_configs_users_empresa_registration_form';
    }

    /**
     * Carrega as opções de um campo list_string.
     */
    private function getListOptions(string $field_name, string $entity_type = 'user'): array {
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
                continue;
            }

            if (is_array($item) && isset($item['value']) && !isset($item['label'])) {
                $value = (string) $item['value'];
                $options[$value] = $value;
                continue;
            }

            if (!is_int($key) && (is_string($item) || is_numeric($item))) {
                $options[(string) $key] = (string) $item;
                continue;
            }

            if (is_int($key) && (is_string($item) || is_numeric($item))) {
                $value = (string) $item;
                $options[$value] = $value;
            }
        }

        return $options;
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        if (\Drupal::config('user.settings')->get('register') === UserInterface::REGISTER_ADMINISTRATORS_ONLY) {
            throw new AccessDeniedHttpException();
        }

        $verify_mail = (bool) \Drupal::config('user.settings')->get('verify_mail');

        $form['#attributes']['novalidate'] = 'novalidate';

        $form['messages'] = [
            '#type' => 'status_messages',
            '#weight' => -1000,
        ];
        $form['page_title'] = [
            '#markup' => '<div class="mb-4 mt-2">'
                . '<h2 class="mb-1"><i class="fas fa-building me-2"></i>' . $this->t('Cadastro de Empresa') . '</h2>'
                . '<p class="text-muted mb-0">' . $this->t('Preencha os dados abaixo para cadastrar sua empresa na plataforma.') . '</p>'
                . '</div>',
        ];
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

        if (!$verify_mail) {
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
        }
        else {
            $form['section_acesso']['verify_mail_message'] = [
                '#type' => 'markup',
                '#markup' => '<div class="alert alert-info mt-3 mb-0">'
                    . $this->t('Após o cadastro, você receberá um e-mail com um link para definir sua senha.')
                    . '</div>',
            ];
        }

        // ── Seção 2 — Dados da Empresa ─────────────────────────────
        $form['section_empresa'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['mb-4']],
        ];
        $form['section_empresa']['heading'] = [
            '#markup' => '<h4 class="mb-3 pb-2 border-bottom"><i class="fas fa-building me-2"></i>' . $this->t('Dados da Empresa') . '</h4>',
        ];
        $form['section_empresa']['row'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['row', 'g-3']],
        ];

        $form['section_empresa']['row']['col_tipo_entidade'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-4']],
        ];
        $form['section_empresa']['row']['col_tipo_entidade']['field_tipo_entidade'] = [
            '#type' => 'select',
            '#title' => $this->t('Tipo de entidade'),
            '#options' => [
                '' => $this->t('- Selecione -'),
                'fisica'   => $this->t('Pessoa Física'),
                'juridica' => $this->t('Pessoa Jurídica'),
            ],
            '#required' => TRUE,
            '#attributes' => ['class' => ['form-select']],
        ];

        $form['section_empresa']['row']['col_cpf_empresa'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-4']],
        ];
        $form['section_empresa']['row']['col_cpf_empresa']['field_cpf_empresa'] = [
            '#type' => 'textfield',
            '#title' => $this->t('CPF'),
            '#maxlength' => 14,
            '#attributes' => [
                'class' => ['form-control', 'mask-cpf'],
                'placeholder' => '000.000.000-00',
            ],
            '#states' => [
                'enabled' => [
                    ':input[name="field_tipo_entidade"]' => ['value' => 'fisica'],
                ],
            ],
        ];

        $form['section_empresa']['row']['col_cnpj'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-4']],
        ];
        $form['section_empresa']['row']['col_cnpj']['field_cnpj'] = [
            '#type' => 'textfield',
            '#title' => $this->t('CNPJ'),
            '#maxlength' => 18,
            '#attributes' => [
                'class' => ['form-control', 'mask-cnpj'],
                'placeholder' => '00.000.000/0000-00',
            ],
            '#states' => [
                'enabled' => [
                    ':input[name="field_tipo_entidade"]' => ['value' => 'juridica'],
                ],
            ],
        ];

        $form['section_empresa']['row']['col_razao_social'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-6']],
        ];
        $form['section_empresa']['row']['col_razao_social']['field_razao_social'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Razão Social'),
            '#required' => TRUE,
            '#maxlength' => 255,
            '#attributes' => ['class' => ['form-control']],
        ];

        $form['section_empresa']['row']['col_nome_fantasia'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-6']],
        ];
        $form['section_empresa']['row']['col_nome_fantasia']['field_nome_fantasia'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nome Fantasia'),
            '#maxlength' => 255,
            '#attributes' => ['class' => ['form-control']],
        ];

        $form['section_empresa']['row']['col_atividade_principal'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-8']],
        ];
        $form['section_empresa']['row']['col_atividade_principal']['field_atividade_principal'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Atividade Principal'),
            '#maxlength' => 255,
            '#attributes' => ['class' => ['form-control']],
        ];

        $form['section_empresa']['row']['col_unidade'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-4']],
        ];
        $form['section_empresa']['row']['col_unidade']['field_unidade'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Unidade'),
            '#maxlength' => 100,
            '#attributes' => ['class' => ['form-control']],
        ];

        // ── Seção 3 — Endereço ─────────────────────────────────────
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
            '#attributes' => ['class' => ['form-control']],
        ];
        $form['section_endereco']['row']['col_endereco']['field_complemento'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Complemento'),
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

        // ── Seção 4 — Responsável pelo Cadastro ───────────────────
        $form['section_responsavel'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['mb-4']],
        ];
        $form['section_responsavel']['heading'] = [
            '#markup' => '<h4 class="mb-3 pb-2 border-bottom"><i class="fas fa-user-tie me-2"></i>' . $this->t('Responsável pelo Cadastro') . '</h4>',
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
            '#attributes' => ['class' => ['form-control']],
        ];

        // ── Seção 5 — Política de Privacidade ─────────────────────
        $form['section_termo'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['mb-4']],
        ];
        $form['section_termo']['field_termo'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Li e concordo com a <a href="/politica-de-privacidade" target="_blank">Política de Privacidade</a>'),
            '#required' => FALSE,
            '#attributes' => ['class' => ['form-check-input']],
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Cadastrar'),
            '#attributes' => ['class' => ['btn', 'btn-primary']],
        ];

        // Bibliotecas de máscaras (CEP, CPF, CNPJ, telefone).
        $form['#attached']['library'][] = 'core/jquery.ui.widget';
        $form['#attached']['library'][] = 'default/jquery_mask';
        $form['#attached']['library'][] = 'default/masks';
        $form['#attached']['library'][] = 'custom_configs_users/registration_forms_validation';
        $form['#attached']['drupalSettings']['defaultMasks']['cepApi'] = [
            'lookupUrl' => '/api/cep',
        ];

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        $name = trim((string) $form_state->getValue('name'));
        $mail = trim((string) $form_state->getValue('mail'));
        $pass = (string) $form_state->getValue('pass');
        $pass_confirm = (string) $form_state->getValue('pass_confirm');
        $verify_mail = (bool) \Drupal::config('user.settings')->get('verify_mail');
        $tipo = (string) $form_state->getValue('field_tipo_entidade');

        // ── Dados de Acesso ────────────────────────────────────────
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

        if (!$verify_mail) {
            if (mb_strlen($pass) < 8) {
                $form_state->setErrorByName('pass', $this->t('A senha deve ter no mínimo 8 caracteres.'));
            }

            if ($pass !== $pass_confirm) {
                $form_state->setErrorByName('pass_confirm', $this->t('A confirmação de senha não confere.'));
            }
        }

        // ── Documento (CPF / CNPJ) ─────────────────────────────────
        if ($tipo === 'fisica') {
            $cpf = preg_replace('/\D/', '', (string) $form_state->getValue('field_cpf_empresa'));
            if (empty($cpf)) {
                $form_state->setErrorByName('field_cpf_empresa', $this->t('O CPF é obrigatório para Pessoa Física.'));
            } elseif (mb_strlen($cpf) !== 11 || !$this->validarCpf($cpf)) {
                $form_state->setErrorByName('field_cpf_empresa', $this->t('O CPF informado é inválido.'));
            }
        } elseif ($tipo === 'juridica') {
            $cnpj = preg_replace('/\D/', '', (string) $form_state->getValue('field_cnpj'));
            if (empty($cnpj)) {
                $form_state->setErrorByName('field_cnpj', $this->t('O CNPJ é obrigatório para Pessoa Jurídica.'));
            } elseif (mb_strlen($cnpj) !== 14 || !$this->validarCnpj($cnpj)) {
                $form_state->setErrorByName('field_cnpj', $this->t('O CNPJ informado é inválido.'));
            }
        } else {
            $form_state->setErrorByName('field_tipo_entidade', $this->t('Selecione o tipo de entidade para informar o documento correto.'));
        }

        // ── Termo ──────────────────────────────────────────────────
        if (!(bool) $form_state->getValue('field_termo')) {
            $form_state->setErrorByName('field_termo', $this->t('Você precisa aceitar a Política de Privacidade para concluir o cadastro.'));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        if (!(bool) $form_state->getValue('field_termo')) {
            $this->messenger()->addError($this->t('Você precisa aceitar a Política de Privacidade para concluir o cadastro.'));
            return;
        }

        $user_settings = \Drupal::config('user.settings');
        $verify_mail = (bool) $user_settings->get('verify_mail');
        $registration_policy = $user_settings->get('register');
        $submitted_password = (string) $form_state->getValue('pass');
        $account_password = $verify_mail
            ? \Drupal::service('password_generator')->generate()
            : $submitted_password;
        $account_is_active = $registration_policy === UserInterface::REGISTER_VISITORS;
        $tipo = (string) $form_state->getValue('field_tipo_entidade');

        $custom_fields = [
            'field_tipo_entidade',
            'field_cpf_empresa',
            'field_cnpj',
            'field_razao_social',
            'field_nome_fantasia',
            'field_atividade_principal',
            'field_unidade',
            // Endereço (campos compartilhados com candidato).
            'field_cep',
            'field_endereco',
            'field_complemento',
            'field_bairro',
            'field_cidade',
            'field_estado',
            // Responsável.
            'field_responsavel_nome',
            'field_responsavel_telefone',
            'field_responsavel_email',
        ];

        $values = [
            'name'   => trim((string) $form_state->getValue('name')),
            'mail'   => trim((string) $form_state->getValue('mail')),
            'init'   => trim((string) $form_state->getValue('mail')),
            'pass'   => $account_password,
            'status' => $account_is_active ? 1 : 0,
        ];

        foreach ($custom_fields as $field) {
            $value = $form_state->getValue($field);
            if ($value !== NULL && $value !== '') {
                $values[$field] = $value;
            }
        }

        if ($tipo === 'fisica') {
            unset($values['field_cnpj']);
        }

        if ($tipo === 'juridica') {
            unset($values['field_cpf_empresa']);
        }

        $user = User::create($values);

        if ($user->hasField('field_termo')) {
            $user->set('field_termo', (bool) $form_state->getValue('field_termo'));
        }

        if (Role::load('empresa')) {
            $user->addRole('empresa');
        }

        $user->save();
        $user->password = $account_password;

        if (!$user->id()) {
            $this->messenger()->addError($this->t('Não foi possível concluir o cadastro. Tente novamente.'));
            return;
        }

        if (!$verify_mail && $user->isActive()) {
            \_user_mail_notify('register_no_approval_required', $user);
            \user_login_finalize($user);
            $this->messenger()->addStatus($this->t('Cadastro realizado com sucesso.'));
            $form_state->setRedirect('custom_configs_users.empresa_perfil');
            return;
        }

        if ($user->isActive()) {
            \_user_mail_notify('register_no_approval_required', $user);
            $this->messenger()->addStatus($this->t('Cadastro realizado com sucesso. Enviamos um e-mail com instruções para acessar a conta.'));
            $form_state->setRedirect('<front>');
            return;
        }

        \_user_mail_notify('register_pending_approval', $user);
        $this->messenger()->addStatus($this->t('Cadastro realizado. Sua conta aguarda aprovação do administrador e você receberá um e-mail com as próximas instruções.'));
        $form_state->setRedirect('<front>');
    }

    /**
     * Valida o CPF pelo dígito verificador.
     */
    private function validarCpf(string $cpf): bool {
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

    /**
     * Valida o CNPJ pelo dígito verificador.
     */
    private function validarCnpj(string $cnpj): bool {
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
