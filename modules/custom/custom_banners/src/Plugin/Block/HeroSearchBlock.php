<?php

declare(strict_types=1);

namespace Drupal\custom_banners\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Hero de busca da home — textos fixos no template, form GET para /para-estudantes.
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
    'Remoto' => ['vid' => 'regime', 'param' => 'regime'],
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
      '#regime_options' => $this->loadRegimeOptions(),
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
          'taxonomy_term_list:regime',
        ],
      ],
    ];
  }

  /**
   * @return array<int|string, string>
   */
  private function loadRegimeOptions(): array {
    $options = ['' => (string) $this->t('Cidade ou Remoto')];
    try {
      $storage = $this->entityTypeManager->getStorage('taxonomy_term');
      $terms = $storage->loadByProperties(['vid' => 'regime', 'status' => 1]);
      uasort($terms, static fn ($a, $b) => strnatcasecmp((string) $a->label(), (string) $b->label()));
      foreach ($terms as $term) {
        $options[(int) $term->id()] = $term->label();
      }
    }
    catch (\Throwable) {
      // Vocabulary missing — select stays empty aside from placeholder.
    }
    return $options;
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

      $value = $meta['param'] === 'regime'
        ? (string) $term->id()
        : (string) $term->label();

      $pills[] = [
        'label' => $label,
        'url' => Url::fromRoute('view.vagas.page_1', [], [
          'query' => [$meta['param'] => $value],
        ])->toString(),
      ];
    }

    return $pills;
  }

}
