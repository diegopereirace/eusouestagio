<?php

namespace Drupal\custom_configs_users\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\user\Entity\User;

/**
 * Exibe o perfil completo do candidato logado.
 */
class CandidatoPerfilController extends ControllerBase {

    public function view(): array {
        $uid  = $this->currentUser()->id();
        $user = User::load($uid);

        if (!$user) {
            return ['#markup' => $this->t('Usuário não encontrado.')];
        }

        // ── Campos simples ─────────────────────────────────────────
        $dados = [
            'nome_completo'            => $this->fieldVal($user, 'field_nome_completo'),
            'cpf'                      => $this->fieldVal($user, 'field_cpf'),
            'orgao_emissor'            => $this->fieldVal($user, 'field_orgao_emissor'),
            'data_nascimento'          => $this->fieldVal($user, 'field_data_nascimento'),
            'sexo'                     => $this->fieldVal($user, 'field_sexo'),
            'identidade_genero'        => $this->fieldVal($user, 'field_identidade_genero'),
            'estado_civil'             => $this->fieldVal($user, 'field_estado_civil'),
            'quantidade_filhos'        => $this->fieldVal($user, 'field_quantidade_filhos'),
            'nacionalidade'            => $this->fieldVal($user, 'field_nacionalidade'),
            'estado_natal'             => $this->fieldVal($user, 'field_estado_natal'),
            // Filiação.
            'nome_mae'                 => $this->fieldVal($user, 'field_nome_mae'),
            'nome_pai'                 => $this->fieldVal($user, 'field_nome_pai'),
            // Endereço.
            'cep'                      => $this->fieldVal($user, 'field_cep'),
            'endereco'                 => $this->fieldVal($user, 'field_endereco'),
            'complemento'              => $this->fieldVal($user, 'field_complemento'),
            'bairro'                   => $this->fieldVal($user, 'field_bairro'),
            'cidade'                   => $this->fieldVal($user, 'field_cidade'),
            'estado'                   => $this->fieldVal($user, 'field_estado'),
            // Contato.
            'telefone'                 => $this->fieldVal($user, 'field_telefone'),
            'instagram'                => $this->fieldVal($user, 'field_instagram'),
            'linkedin'                 => $this->fieldVal($user, 'field_linkedin'),
            // Acadêmico.
            'escolaridade'             => $this->fieldVal($user, 'field_escolaridade'),
            'periodo_letivo'           => $this->fieldVal($user, 'field_periodo_letivo'),
            'nome_curso'               => $this->fieldVal($user, 'field_nome_curso'),
            'tipo_curso'               => $this->fieldVal($user, 'field_tipo_curso'),
            'periodo_matriculado'      => $this->fieldVal($user, 'field_periodo_matriculado'),
            'horario_curso'            => $this->fieldVal($user, 'field_horario_curso'),
            'duracao_curso'            => $this->fieldVal($user, 'field_duracao_curso'),
            'previsao_formatura'       => $this->fieldVal($user, 'field_previsao_formatura'),
            'disponibilidade_estagio'  => $this->fieldVal($user, 'field_disponibilidade_estagio'),
            'numero_matricula'         => $this->fieldVal($user, 'field_numero_matricula'),
            // Complementares.
            'possui_deficiencia'       => $this->fieldVal($user, 'field_possui_deficiencia'),
            'numero_cid'               => $this->fieldVal($user, 'field_numero_cid'),
        ];

        // ── Paragraphs — Instituições de Ensino ───────────────────
        $instituicoes = [];
        if ($user->hasField('field_instituicao_ensino')) {
            foreach ($user->get('field_instituicao_ensino') as $item) {
                $p = Paragraph::load($item->target_id);
                if ($p) {
                    $instituicoes[] = [
                        'nome'     => $this->fieldVal($p, 'field_nome_instituicao'),
                        'endereco' => $this->fieldVal($p, 'field_endereco'),
                        'bairro'   => $this->fieldVal($p, 'field_bairro'),
                        'cidade'   => $this->fieldVal($p, 'field_cidade'),
                    ];
                }
            }
        }

        // ── Paragraphs — Cursos Extracurriculares ──────────────────
        $cursos = [];
        if ($user->hasField('field_cursos_extracurriculares')) {
            foreach ($user->get('field_cursos_extracurriculares') as $item) {
                $p = Paragraph::load($item->target_id);
                if ($p) {
                    $cursos[] = [
                        'tipo'      => $this->fieldVal($p, 'field_tipo_habilidade'),
                        'habilidade'=> $this->fieldVal($p, 'field_habilidade'),
                        'nivel'     => $this->fieldVal($p, 'field_nivel'),
                        'carga'     => $this->fieldVal($p, 'field_carga_horaria'),
                    ];
                }
            }
        }

        // ── Paragraphs — Experiências Profissionais ────────────────
        $experiencias = [];
        if ($user->hasField('field_experiencias_profissionais')) {
            foreach ($user->get('field_experiencias_profissionais') as $item) {
                $p = Paragraph::load($item->target_id);
                if ($p) {
                    $experiencias[] = [
                        'empresa'     => $this->fieldVal($p, 'field_nome_empresa'),
                        'cargo'       => $this->fieldVal($p, 'field_cargo'),
                        'data_inicio' => $this->fieldVal($p, 'field_data_inicio'),
                        'data_termino'=> $this->fieldVal($p, 'field_data_termino'),
                        'regime'      => $this->fieldVal($p, 'field_regime_contrato'),
                        'atividades'  => $this->fieldVal($p, 'field_atividades'),
                    ];
                }
            }
        }

        return [
            '#theme'        => 'candidato_perfil',
            '#dados'        => $dados,
            '#instituicoes' => $instituicoes,
            '#cursos'       => $cursos,
            '#experiencias' => $experiencias,
            '#user'         => $user,
            '#cache'        => ['max-age' => 0],
        ];
    }

    /**
     * Retorna o valor de um campo ou string vazia.
     */
    private function fieldVal($entity, string $field_name): string {
        if (!$entity->hasField($field_name)) {
            return '';
        }
        $field = $entity->get($field_name);
        if ($field->isEmpty()) {
            return '';
        }
        return (string) $field->value;
    }

}
