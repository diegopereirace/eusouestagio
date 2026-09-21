<?php

declare(strict_types=1);

namespace Drupal\custom_configs\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Renderiza Impact in Numbers a partir do Node quem_somos.
 *
 * @Block(
 *   id = "custom_configs_impact_numbers_quem_somos",
 *   admin_label = @Translation("Impact in Numbers (Quem Somos)"),
 *   category = @Translation("Custom Configs"),
 * )
 */
class ImpactNumbersQuemSomosBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $node = $this->loadQuemSomosNode();
    if (!$node || !$node->hasField('field_numeros_lista') || $node->get('field_numeros_lista')->isEmpty()) {
      return [];
    }

    $view_builder = $this->entityTypeManager->getViewBuilder('paragraph');
    $items = [];
    foreach ($node->get('field_numeros_lista') as $delta => $item) {
      $paragraph = $item->entity;
      if (!$paragraph) {
        continue;
      }
      $stat = trim((string) ($paragraph->get('field_text_simple')->value ?? ''));
      $label = trim((string) ($paragraph->get('field_text_simple_long')->value ?? ''));
      if ($stat === '' && $label === '') {
        continue;
      }
      $items[$delta] = $view_builder->view($paragraph, 'default');
    }

    if (!$items) {
      return [];
    }

    return [
      'items' => $items,
      '#attached' => [
        'library' => ['default/impact_numbers'],
      ],
      '#cache' => [
        'contexts' => ['url.path'],
        'tags' => $node->getCacheTags(),
      ],
    ];
  }

  /**
   * Carrega o primeiro node publicado do tipo quem_somos.
   */
  protected function loadQuemSomosNode(): ?NodeInterface {
    $nids = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'quem_somos')
      ->condition('status', 1)
      ->range(0, 1)
      ->execute();

    if (!$nids) {
      return NULL;
    }

    $node = $this->entityTypeManager->getStorage('node')->load(reset($nids));
    return $node instanceof NodeInterface ? $node : NULL;
  }

}
