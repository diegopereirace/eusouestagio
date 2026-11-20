<?php

declare(strict_types=1);

namespace Drupal\custom_configs\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Remove headers that publicly identify the CMS / PHP stack.
 *
 * Feature 061: security hardening — no X-Generator / X-Powered-By.
 */
final class RemoveCmsIdentityHeadersSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // After core ResponseGeneratorSubscriber (default priority 0).
    return [
      KernelEvents::RESPONSE => ['onRespond', -10],
    ];
  }

  /**
   * Strips CMS identity headers from the main response.
   */
  public function onRespond(ResponseEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }

    $headers = $event->getResponse()->headers;
    $headers->remove('X-Generator');
    $headers->remove('X-Powered-By');
  }

}
