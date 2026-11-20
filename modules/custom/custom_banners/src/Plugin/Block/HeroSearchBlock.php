<?php

declare(strict_types=1);

namespace Drupal\custom_banners\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\custom_banners\CourseSearchPills;
use Drupal\custom_banners\SearchTextNormalizer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Hero de busca da home — textos fixos no template, form GET para /vagas.
 *
 * @Block(
 *   id = "custom_banners_hero_search",
 *   admin_label = @Translation("Hero: busca de vagas (home)"),
 *   category = @Translation("Custom Banners"),
 * )
 */
class HeroSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

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
    return [
      '#theme' => 'custom_banners_hero_search',
      '#action' => Url::fromRoute('view.vagas.page_1')->toString(),
      '#pills' => $this->resolvePills(),
      '#attached' => [
        'library' => [
          'default/hero_search',
        ],
      ],
      '#cache' => [
        'contexts' => ['url.path'],
        'tags' => [
          'taxonomy_term_list:curso',
        ],
      ],
    ];
  }

  /**
   * @return list<array{label: string, url: string}>
   */
  private function resolvePills(): array {
    try {
      $storage = $this->entityTypeManager->getStorage('taxonomy_term');
      $candidates = $storage->loadByProperties([
        'vid' => 'curso',
        'status' => 1,
      ]);
    }
    catch (\Throwable) {
      return [];
    }

    $pills = [];
    foreach (CourseSearchPills::resolve($candidates) as $pill) {
      $pills[] = [
        'label' => $pill['label'],
        'url' => Url::fromRoute('view.vagas.page_1', [], [
          'query' => [
            'cursos' => SearchTextNormalizer::normalize($pill['term_label']),
          ],
        ])->toString(),
      ];
    }

    return $pills;
  }

}
