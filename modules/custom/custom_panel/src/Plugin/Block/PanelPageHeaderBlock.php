<?php

namespace Drupal\custom_panel\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides the painel page header block.
 *
 * Displays the current route's page title as a styled header.
 * To set a custom title on a specific route, define _title or _title_callback
 * in that route's routing.yml.
 *
 * @Block(
 *   id = "custom_panel_page_header",
 *   admin_label = @Translation("Painel: cabeçalho da página"),
 *   category = @Translation("Custom Panel"),
 * )
 */
class PanelPageHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected TitleResolverInterface $titleResolver;

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    TitleResolverInterface $title_resolver,
    RouteMatchInterface $route_match,
    RequestStack $request_stack,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->titleResolver = $title_resolver;
    $this->routeMatch = $route_match;
    $this->requestStack = $request_stack;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('title_resolver'),
      $container->get('current_route_match'),
      $container->get('request_stack'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $route = $this->routeMatch->getRouteObject();
    if (!$route) {
      return [];
    }

    $request = $this->requestStack->getCurrentRequest();
    $title = $this->titleResolver->getTitle($request, $route);

    return [
      '#theme' => 'custom_panel_page_header',
      '#title' => $title,
      '#cache' => [
        'contexts' => ['url.path', 'user'],
      ],
    ];
  }

}
