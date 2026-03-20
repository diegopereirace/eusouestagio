<?php

namespace Drupal\custom_configs_users\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\user\Entity\User;

class CandidatoRegistrationForm extends FormBase {

    public function getFormId() {
        return 'custom_configs_users_candidato_registration_form';
    }

    /**
     * Carrega as opções de um campo list_string.
     */
    private function getListOptions(string $field_name, string $entity_type = 'user'): array {
        $storage = FieldStorageConfig::loadByName($entity_type, $field_name);
        if (!$storage) {
            return [];
        }
        $allowed = $storage->getSetting('allowed_values');
        if (!is_array($allowed) || empty($allowed)) {
            return [];
        }
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

        // ── Seção 6 — Instituição de Ensino (Paragraphs) ──────────
        $form['section_instituicao'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['mb-4']],
        ];
        $form['section_instituicao']['heading'] = [
            '#markup' => '<h4 class="mb-3 pb-2 border-bottom"><i class="fas fa-university me-2"></i>' . $this->t('Instituição de Ensino') . '</h4>',
        ];

        $form['section_instituicao']['instituicoes_wrapper'] = [
            '#type' => 'container',
            '#attributes' => ['id' => 'instituicoes-wrapper'],
        ];

        $num_instituicoes = $form_state->get('num_instituicoes') ?? 1;
        $form_state->set('num_instituicoes', $num_instituicoes);

        for ($i = 0; $i < $num_instituicoes; $i++) {
            $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i] = [
                '#type' => 'container',
                '#attributes' => ['class' => ['card', 'mb-3']],
            ];
            $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i]['body'] = [
                '#type' => 'container',
                '#attributes' => ['class' => ['card-body']],
            ];
            $form['section_instituicao']['instituicoes_wrapper']['inst_' . $i]['body']['title'] = [
                '#markup' => '<h6 class="card-title text-muted">' . $this->t('Instituição @num', ['@num' => $i + 1]) . '</h6>',
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
                '#attributes' => ['class' => ['form-control']],
            ];
        }

        $form['section_instituicao']['instituicoes_wrapper']['add_instituicao'] = [
            '#type' => 'submit',
            '#value' => $this->t('Incluir Instituição'),
            '#submit' => ['::addInstituicaoCallback'],
            '#ajax' => [
                'callback' => '::ajaxRefreshInstituicoes',
                'wrapper' => 'instituicoes-wrapper',
            ],
            '#attributes' => ['class' => ['btn', 'btn-outline-secondary', 'btn-sm']],
            '#limit_validation_errors' => [],
        ];

        if ($num_instituicoes > 1) {
            $form['section_instituicao']['instituicoes_wrapper']['remove_instituicao'] = [
                '#type' => 'submit',
                '#value' => $this->t('Remover última instituição'),
                '#submit' => ['::removeInstituicaoCallback'],
                '#ajax' => [
                    'callback' => '::ajaxRefreshInstituicoes',
                    'wrapper' => 'instituicoes-wrapper',
                ],
                '#attributes' => ['class' => ['btn', 'btn-outline-danger', 'btn-sm', 'ms-2']],
                '#limit_validation_errors' => [],
            ];
        }

        // ── Seção 7 — Informações Acadêmicas ───────────────────────
        $form['section_academico'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['mb-4']],
        ];
        $form['section_academico']['heading'] = [
            '#markup' => '<h4 class="mb-3 pb-2 border-bottom"><i class="fas fa-graduation-cap me-2"></i>' . $this->t('Informações Acadêmicas') . '</h4>',
        ];
        $form['section_academico']['row'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['row', 'g-3']],
        ];

        $form['section_academico']['row']['col_nivel_atual'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-4']],
        ];
        $form['section_academico']['row']['col_nivel_atual']['field_nivel_atual'] = [
            '#type' => 'select',
            '#title' => $this->t('Nível atual'),
            '#options' => ['' => $this->t('- Selecione -')] + $this->getListOptions('field_nivel_atual'),
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
            '#attributes' => [
                'class' => ['form-control'],
                'placeholder' => 'Ex: 8',
            ],
        ];

        $form['section_academico']['row']['col_previsao_formatura'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-4']],
        ];
        $form['section_academico']['row']['col_previsao_formatura']['field_previsao_formatura'] = [
            '#type' => 'date',
            '#title' => $this->t('Previsão de formatura'),
            '#attributes' => ['class' => ['form-control']],
        ];

        $form['section_academico']['row']['col_disponibilidade'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['col-12', 'col-md-4']],
        ];
        $form['section_academico']['row']['col_disponibilidade']['field_disponibilidade_estagio'] = [
            '#type' => 'select',
            '#title' => $this->t('Disponibilidade para estágio'),
            '#options' => ['' => $this->t('- Selecione -')] + $this->getListOptions('field_disponibilidade_estagio'),
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
            '#attributes' => ['class' => ['form-control']],
        ];

        // ── Seção 8 — Cursos Extracurriculares (Paragraphs) ────────
        $form['section_extracurricular'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['mb-4']],
        ];
        $form['section_extracurricular']['heading'] = [
            '#markup' => '<h4 class="mb-3 pb-2 border-bottom"><i class="fas fa-award me-2"></i>' . $this->t('Cursos Extracurriculares') . ' <small class="text-muted">(' . $this->t('Opcional') . ')</small></h4>',
        ];

        $form['section_extracurricular']['cursos_wrapper'] = [
            '#type' => 'container',
            '#attributes' => ['id' => 'cursos-extracurriculares-wrapper'],
        ];

        $num_cursos = $form_state->get('num_cursos') ?? 0;
        $form_state->set('num_cursos', $num_cursos);

        $nivel_options = $this->getListOptions('field_nivel', 'paragraph');
        if (empty($nivel_options)) {
            $nivel_options = [
                'basico' => $this->t('Básico'),
                'intermediario' => $this->t('Intermediário'),
                'avancado' => $this->t('Avançado'),
            ];
        }

        for ($i = 0; $i < $num_cursos; $i++) {
            $form['section_extracurricular']['cursos_wrapper']['curso_' . $i] = [
                '#type' => 'container',
                '#attributes' => ['class' => ['card', 'mb-3']],
            ];
            $form['section_extracurricular']['cursos_wrapper']['curso_' . $i]['body'] = [
                '#type' => 'container',
                '#attributes' => ['class' => ['card-body']],
            ];
            $form['section_extracurricular']['cursos_wrapper']['curso_' . $i]['body']['title'] = [
                '#markup' => '<h6 class="card-title text-muted">' . $this->t('Curso @num', ['@num' => $i + 1]) . '</h6>',
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
                '#attributes' => ['class' => ['form-control']],
            ];
        }

        $form['section_extracurricular']['cursos_wrapper']['add_curso'] = [
            '#type' => 'submit',
            '#value' => $this->t('Incluir Curso Extracurricular'),
            '#submit' => ['::addCursoCallback'],
            '#ajax' => [
                'callback' => '::ajaxRefreshCursos',
                'wrapper' => 'cursos-extracurriculares-wrapper',
            ],
            '#attributes' => ['class' => ['btn', 'btn-outline-secondary', 'btn-sm']],
            '#limit_validation_errors' => [],
        ];

        if ($num_cursos > 0) {
            $form['section_extracurricular']['cursos_wrapper']['remove_curso'] = [
                '#type' => 'submit',
                '#value' => $this->t('Remover último curso'),
                '#submit' => ['::removeCursoCallback'],
                '#ajax' => [
                    'callback' => '::ajaxRefreshCursos',
                    'wrapper' => 'cursos-extracurriculares-wrapper',
                ],
                '#attributes' => ['class' => ['btn', 'btn-outline-danger', 'btn-sm', 'ms-2']],
                '#limit_validation_errors' => [],
            ];
        }

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

    /**
     * AJAX: adiciona mais uma instituição de ensino.
     */
    public function addInstituicaoCallback(array &$form, FormStateInterface $form_state) {
        $num = $form_state->get('num_instituicoes') ?? 1;
        $form_state->set('num_instituicoes', $num + 1);
        $form_state->setRebuild();
    }

    /**
     * AJAX: remove a última instituição de ensino.
     */
    public function removeInstituicaoCallback(array &$form, FormStateInterface $form_state) {
        $num = $form_state->get('num_instituicoes') ?? 1;
        if ($num > 1) {
            $form_state->set('num_instituicoes', $num - 1);
        }
        $form_state->setRebuild();
    }

    /**
     * AJAX: retorna o wrapper atualizado das instituições.
     */
    public function ajaxRefreshInstituicoes(array &$form, FormStateInterface $form_state) {
        return $form['section_instituicao']['instituicoes_wrapper'];
    }

    /**
     * AJAX: adiciona mais um curso extracurricular.
     */
    public function addCursoCallback(array &$form, FormStateInterface $form_state) {
        $num = $form_state->get('num_cursos') ?? 0;
        $form_state->set('num_cursos', $num + 1);
        $form_state->setRebuild();
    }

    /**
     * AJAX: remove o último curso extracurricular.
     */
    public function removeCursoCallback(array &$form, FormStateInterface $form_state) {
        $num = $form_state->get('num_cursos') ?? 0;
        if ($num > 0) {
            $form_state->set('num_cursos', $num - 1);
        }
        $form_state->setRebuild();
    }

    /**
     * AJAX: retorna o wrapper atualizado dos cursos.
     */
    public function ajaxRefreshCursos(array &$form, FormStateInterface $form_state) {
        return $form['section_extracurricular']['cursos_wrapper'];
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
        // Campos simples (texto/select/data) no User.
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
            // Informações Acadêmicas.
            'field_nivel_atual',
            'field_periodo_letivo',
            'field_nome_curso',
            'field_tipo_curso',
            'field_periodo_matriculado',
            'field_horario_curso',
            'field_duracao_curso',
            'field_previsao_formatura',
            'field_disponibilidade_estagio',
            'field_numero_matricula',
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

        // Paragraphs — Instituições de Ensino.
        $num_instituicoes = $form_state->get('num_instituicoes') ?? 1;
        $inst_paragraphs = [];
        for ($i = 0; $i < $num_instituicoes; $i++) {
            $nome = $form_state->getValue('inst_nome_' . $i);
            $endereco = $form_state->getValue('inst_endereco_' . $i);
            $bairro = $form_state->getValue('inst_bairro_' . $i);
            $cidade = $form_state->getValue('inst_cidade_' . $i);

            if (!empty($nome) || !empty($endereco) || !empty($bairro) || !empty($cidade)) {
                $p_values = ['type' => 'instituicao_ensino'];
                if (!empty($nome)) {
                    $p_values['field_nome_instituicao'] = $nome;
                }
                if (!empty($endereco)) {
                    $p_values['field_endereco'] = $endereco;
                }
                if (!empty($bairro)) {
                    $p_values['field_bairro'] = $bairro;
                }
                if (!empty($cidade)) {
                    $p_values['field_cidade'] = $cidade;
                }
                $paragraph = Paragraph::create($p_values);
                $paragraph->save();
                $inst_paragraphs[] = [
                    'target_id' => $paragraph->id(),
                    'target_revision_id' => $paragraph->getRevisionId(),
                ];
            }
        }

        if (!empty($inst_paragraphs)) {
            $values['field_instituicao_ensino'] = $inst_paragraphs;
        }

        // Paragraphs — Cursos Extracurriculares.
        $num_cursos = $form_state->get('num_cursos') ?? 0;
        $paragraphs = [];
        for ($i = 0; $i < $num_cursos; $i++) {
            $tipo = $form_state->getValue('curso_tipo_' . $i);
            $habilidade = $form_state->getValue('curso_habilidade_' . $i);
            $nivel = $form_state->getValue('curso_nivel_' . $i);
            $carga = $form_state->getValue('curso_carga_' . $i);

            // Só cria o Paragraph se ao menos um campo tiver valor.
            if (!empty($tipo) || !empty($habilidade) || !empty($nivel) || !empty($carga)) {
                $paragraph_values = ['type' => 'curso_extracurricular'];
                if (!empty($tipo)) {
                    $paragraph_values['field_tipo_habilidade'] = $tipo;
                }
                if (!empty($habilidade)) {
                    $paragraph_values['field_habilidade'] = $habilidade;
                }
                if (!empty($nivel)) {
                    $paragraph_values['field_nivel'] = $nivel;
                }
                if (!empty($carga)) {
                    $paragraph_values['field_carga_horaria'] = $carga;
                }
                $paragraph = Paragraph::create($paragraph_values);
                $paragraph->save();
                $paragraphs[] = [
                    'target_id' => $paragraph->id(),
                    'target_revision_id' => $paragraph->getRevisionId(),
                ];
            }
        }

        if (!empty($paragraphs)) {
            $values['field_cursos_extracurriculares'] = $paragraphs;
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
