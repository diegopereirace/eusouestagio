<?php

declare(strict_types=1);

namespace Drupal\custom_banners\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\custom_banners\SearchTextNormalizer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Hero de busca da página /vagas — textos fixos no Twig, form GET para page_1.
 *
 * Distinto do hero da home (custom_banners_hero_search).
 *
 * @Block(
 *   id = "custom_banners_vagas_hero_search",
 *   admin_label = @Translation("Hero: busca de vagas (/vagas)"),
 *   category = @Translation("Custom Banners"),
 * )
 */
class VagasHeroSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Pills canônicas: só vocabulário curso → param cursos.
   *
   * @var list<string>
   */
  private const PILL_LABELS = [
    'Tecnologia',
    'Marketing',
    'Administração',
    'Engenharia',
    'Saúde',
    'Design',
    'Direito',
  ];

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected RequestStack $requestStack,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $request = $this->requestStack->getCurrentRequest();
    $query = $request?->query ?? NULL;

    $cursosQuery = trim((string) ($query?->get('cursos') ?? ''));
    $cidadeQuery = trim((string) ($query?->get('cidade') ?? ''));
    $titleQuery = trim((string) ($query?->get('title') ?? ''));

    return [
      '#theme' => 'custom_banners_vagas_hero_search',
      '#action' => Url::fromRoute('view.vagas.page_1')->toString(),
      '#values' => [
        'title' => $titleQuery,
        'cidade' => $this->resolveCityDisplayLabel($cidadeQuery),
        'cursos' => $this->resolveCourseDisplayLabel($cursosQuery),
      ],
      '#pills' => $this->resolvePills(),
      '#attached' => [
        'library' => [
          'default/vagas_hero_search',
        ],
      ],
      '#cache' => [
        'contexts' => [
          'url.path',
          'url.query_args',
        ],
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

    foreach (self::PILL_LABELS as $label) {
      $terms = $storage->loadByProperties([
        'vid' => 'curso',
        'name' => $label,
        'status' => 1,
      ]);
      $term = reset($terms) ?: NULL;
      if (!$term) {
        $candidates = $storage->loadByProperties([
          'vid' => 'curso',
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
            'cursos' => SearchTextNormalizer::normalize((string) $term->label()),
          ],
        ])->toString(),
      ];
    }

    return $pills;
  }

  /**
   * Exibe o nome canônico do curso quando a query é o slug normalizado.
   */
  private function resolveCourseDisplayLabel(string $queryValue): string {
    if ($queryValue === '') {
      return '';
    }
    $norm = SearchTextNormalizer::normalize($queryValue);
    if ($norm === '') {
      return $queryValue;
    }
    try {
      $storage = $this->entityTypeManager->getStorage('taxonomy_term');
      $candidates = $storage->loadByProperties([
        'vid' => 'curso',
        'status' => 1,
      ]);
    }
    catch (\Throwable) {
      return $queryValue;
    }
    foreach ($candidates as $candidate) {
      $name = (string) $candidate->label();
      if (SearchTextNormalizer::normalize($name) === $norm) {
        return $name;
      }
    }
    return $queryValue;
  }

  /**
   * Exibe a cidade canônica quando a query é o slug normalizado.
   */
  private function resolveCityDisplayLabel(string $queryValue): string {
    if ($queryValue === '') {
      return '';
    }
    $norm = SearchTextNormalizer::normalize($queryValue);
    if ($norm === '') {
      return $queryValue;
    }
    try {
      $cities = \Drupal::database()->select('node__field_cidade', 'c')
        ->fields('c', ['field_cidade_value'])
        ->condition('deleted', 0)
        ->distinct()
        ->execute()
        ->fetchCol();
    }
    catch (\Throwable) {
      return $queryValue;
    }
    foreach ($cities as $city) {
      $city = trim((string) $city);
      if ($city !== '' && SearchTextNormalizer::normalize($city) === $norm) {
        return $city;
      }
    }
    return $queryValue;
  }

}
