<?php

namespace Drupal\custom_configs_users\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

class StudentRegistrationForm extends FormBase {

    public function getFormId() {
        return 'custom_configs_users_student_registration_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Nome de usuário'),
        '#required' => TRUE,
        '#maxlength' => 60,
        '#description' => $this->t('Digite um nome de usuário único.'),
        ];

        $form['mail'] = [
        '#type' => 'email',
        '#title' => $this->t('E-mail'),
        '#required' => TRUE,
        '#description' => $this->t('Digite um e-mail válido e não utilizado.'),
        ];

        $form['pass'] = [
        '#type' => 'password',
        '#title' => $this->t('Senha'),
        '#required' => TRUE,
        '#description' => $this->t('A senha deve ter no mínimo 8 caracteres.'),
        ];

        $form['pass_confirm'] = [
        '#type' => 'password',
        '#title' => $this->t('Confirmar senha'),
        '#required' => TRUE,
        ];

        $form['actions'] = [
        '#type' => 'actions',
        ];

        $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cadastrar'),
        '#button_type' => 'primary',
        ];

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        $name = trim((string) $form_state->getValue('name'));
        $mail = trim((string) $form_state->getValue('mail'));
        $pass = (string) $form_state->getValue('pass');
        $pass_confirm = (string) $form_state->getValue('pass_confirm');

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
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $user = User::create([
        'name' => trim((string) $form_state->getValue('name')),
        'mail' => trim((string) $form_state->getValue('mail')),
        'pass' => (string) $form_state->getValue('pass'),
        'status' => 1,
        ]);

        $user->save();

        $this->messenger()->addStatus($this->t('Cadastro realizado com sucesso.'));
        $form_state->setRedirect('<front>');
    }

}
