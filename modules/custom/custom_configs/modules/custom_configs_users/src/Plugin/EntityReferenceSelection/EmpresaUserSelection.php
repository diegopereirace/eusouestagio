<?php

namespace Drupal\custom_configs_users\Plugin\EntityReferenceSelection;

    use Drupal\Component\Utility\Html;
    use Drupal\Core\Entity\Attribute\EntityReferenceSelection;
    use Drupal\Core\StringTranslation\TranslatableMarkup;
    use Drupal\user\Plugin\EntityReferenceSelection\UserSelection;

    /**
     * Seleção de usuários empresa buscando por Nome Fantasia.
     */
    #[EntityReferenceSelection(
        id: "empresa_user:user",
        label: new TranslatableMarkup("Empresa (Nome Fantasia)"),
        entity_types: ["user"],
        group: "empresa_user",
        weight: 10
    )]
    class EmpresaUserSelection extends UserSelection {

    /**
     * {@inheritdoc}
     *
     * Sobrescreve a query para buscar por nome de usuário OU field_nome_fantasia.
     */
    protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
        // Chama o pai com match=NULL para evitar a condição padrão de 'name'.
        $query = parent::buildEntityQuery(NULL, $match_operator);

        if (isset($match)) {
            // Filtra por username OU Nome Fantasia.
            $or = $query->orConditionGroup()
                ->condition('name', $match, $match_operator)
                ->condition('field_nome_fantasia', $match, $match_operator);
            $query->condition($or);
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     *
     * Retorna Nome Fantasia como label. Se vazio, usa o username.
     */
    public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
        $query = $this->buildEntityQuery($match, $match_operator);
        if ($limit > 0) {
            $query->range(0, $limit);
        }

        $result = $query->execute();
        if (empty($result)) {
            return [];
        }

        $options = [];
        $entities = $this->entityTypeManager->getStorage('user')->loadMultiple($result);
        foreach ($entities as $entity_id => $entity) {
            $nome_fantasia = $entity->get('field_nome_fantasia')->value;
            $label = $nome_fantasia ?: $entity->label();
            $options[$entity->bundle()][$entity_id] = Html::escape($label);
        }

        return $options;
    }

}
