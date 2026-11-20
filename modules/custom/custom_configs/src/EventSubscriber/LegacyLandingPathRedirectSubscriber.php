<?php

declare(strict_types=1);

namespace Drupal\custom_configs\EventSubscriber;

use Drupal\Core\Routing\LocalRedirectResponse;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * 301 das landings antigas para as URLs alinhadas aos títulos do menu.
 *
 * Só redireciona depois que o alias novo existe, para o deploy (código antes
 * do updb) não mandar o visitante para uma URL ainda inexistente.
 */
final class LegacyLandingPathRedirectSubscriber implements EventSubscriberInterface {

  /**
   * Alias antigo => alias novo (path completo, com barra inicial).
   */
  public const MAP = [
    '/quem-somos' => '/sobre-nos',
    '/para-empresas' => '/para-sua-empresa',
    '/para-estudantes' => '/estudantes',
  ];

  public function __construct(
    private readonly AliasManagerInterface $aliasManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Antes do RouterListener (32), com o path ainda no alias digitado.
    return [
      KernelEvents::REQUEST => ['onRequest', 40],
    ];
  }

  /**
   * Redireciona GET/HEAD do alias antigo quando o novo já está no path_alias.
   */
  public function onRequest(RequestEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }

    $request = $event->getRequest();
    if (!$request->isMethod('GET') && !$request->isMethod('HEAD')) {
      return;
    }

    $path = $request->getPathInfo();
    $path = $path === '/' ? $path : rtrim($path, '/');
    if (!isset(self::MAP[$path])) {
      return;
    }

    $target = self::MAP[$path];
    $resolved = $this->aliasManager->getPathByAlias($target);
    if ($resolved === $target) {
      return;
    }

    $query = $request->getQueryString();
    $url = $target . ($query !== NULL && $query !== '' ? '?' . $query : '');
    $response = new LocalRedirectResponse($url, 301);
    $response->getCacheableMetadata()
      ->addCacheContexts(['url.path', 'url.query_args'])
      ->addCacheTags(['path_alias_list']);
    $event->setResponse($response);
  }

}
