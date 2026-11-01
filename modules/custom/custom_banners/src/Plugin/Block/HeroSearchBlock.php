<?php

declare(strict_types=1);

namespace Drupal\custom_banners\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
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

  /**
   * Pills: label => [vid, query param].
   *
   * @var array<string, array{vid: string, param: string}>
   */
  private const PILLS = [
    'TI' => ['vid' => 'curso', 'param' => 'cursos'],
    'Administração' => ['vid' => 'curso', 'param' => 'cursos'],
    'Design' => ['vid' => 'curso', 'param' => 'cursos'],
    'Marketing' => ['vid' => 'curso', 'param' => 'cursos'],
  ];

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
    $pills = [];
    try {
      $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    }
    catch (\Throwable) {
      return [];
    }

    foreach (self::PILLS as $label => $meta) {
      $terms = $storage->loadByProperties([
        'vid' => $meta['vid'],
        'name' => $label,
        'status' => 1,
      ]);
      $term = reset($terms) ?: NULL;
      if (!$term) {
        $candidates = $storage->loadByProperties([
          'vid' => $meta['vid'],
          'status' => 1,
        ]);
        foreach ($candidates as $candidate) {
          $name = (string) $candidate->label();
          if (strcasecmp($name, $label) === 0 || stripos($name, $label) === 0) {
            $term = $candidate;
            break;
          }
        }
      }
      if (!$term) {
        continue;
      }

      $pills[] = [
        'label' => $label,
        'url' => Url::fromRoute('view.vagas.page_1', [], [
          'query' => [
            $meta['param'] => SearchTextNormalizer::normalize((string) $term->label()),
          ],
        ])->toString(),
      ];
    }

    return $pills;
  }

}
