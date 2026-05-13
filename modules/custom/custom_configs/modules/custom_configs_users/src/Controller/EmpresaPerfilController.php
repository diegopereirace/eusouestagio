<?php

namespace Drupal\custom_configs_users\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;

/**
 * Exibe o perfil da empresa logada.
 */
class EmpresaPerfilController extends ControllerBase
{

    public function view(): array
    {
        $uid  = $this->currentUser()->id();
        $user = User::load($uid);

        if (!$user) {
            return ['#markup' => $this->t('Usuário não encontrado.')];
        }

        $dados = [
            // Documento.
            'cnpj'                  => $this->fieldVal($user, 'field_cnpj'),
            // Dados da empresa.
            'razao_social'          => $this->fieldVal($user, 'field_razao_social'),
            'nome_fantasia'         => $this->fieldVal($user, 'field_nome_fantasia'),
            // Endereço (campos compartilhados com candidato).
            'cep'                   => $this->fieldVal($user, 'field_cep'),
            'endereco'              => $this->fieldVal($user, 'field_endereco'),
            'complemento'           => $this->fieldVal($user, 'field_complemento'),
            'bairro'                => $this->fieldVal($user, 'field_bairro'),
            'cidade'                => $this->fieldVal($user, 'field_cidade'),
            'estado'                => $this->fieldVal($user, 'field_estado'),
            // Responsável.
            'responsavel_nome'      => $this->fieldVal($user, 'field_responsavel_nome'),
            'responsavel_telefone'  => $this->fieldVal($user, 'field_responsavel_telefone'),
            'responsavel_email'     => $this->fieldVal($user, 'field_responsavel_email'),
        ];

        return [
            '#theme' => 'empresa_perfil',
            '#dados' => $dados,
            '#user'  => $user,
        ];
    }

    private function fieldVal($entity, string $field_name): string
    {
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
