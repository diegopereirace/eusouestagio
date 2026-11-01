<?php

declare(strict_types=1);

namespace Drupal\custom_banners\EventSubscriber;

use Drupal\Core\Url;
use Drupal\custom_banners\SearchTextNormalizer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redireciona /vagas para query string sem maiúsculas nem acentos.
 */
final class VagasQueryCanonicalSubscriber implements EventSubscriberInterface {

  /**
   * Parâmetros de texto livre da busca de vagas.
   */
  private const TEXT_PARAMS = [
    'cursos',
    'cidade',
    'title',
    'estado',
  ];

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // Depois do path alias; antes dos controllers.
      KernelEvents::REQUEST => ['onRequest', 28],
    ];
  }

  public function onRequest(RequestEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }

    $request = $event->getRequest();
    if ($request->getPathInfo() !== '/vagas') {
      return;
    }

    $query = $request->query->all();
    if ($query === []) {
      return;
    }

    $canonical = [];
    $changed = FALSE;
    foreach ($query as $key => $value) {
      if (!is_string($key)) {
        $canonical[$key] = $value;
        continue;
      }
      if (!in_array($key, self::TEXT_PARAMS, TRUE) || !is_string($value)) {
        $canonical[$key] = $value;
        continue;
      }
      $trimmed = trim($value);
      if ($trimmed === '') {
        continue;
      }
      $normalized = SearchTextNormalizer::normalize($trimmed);
      if ($normalized !== $value) {
        $changed = TRUE;
      }
      $canonical[$key] = $normalized;
    }

    if (!$changed) {
      return;
    }

    $url = Url::fromRoute('view.vagas.page_1', [], [
      'query' => $canonical,
      'absolute' => TRUE,
    ])->toString();
    $event->setResponse(new RedirectResponse($url, 302));
  }

}
