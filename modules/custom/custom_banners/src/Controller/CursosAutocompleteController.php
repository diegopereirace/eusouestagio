<?php

declare(strict_types=1);

namespace Drupal\custom_banners\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Autocomplete JSON auxiliar para o campo de curso do hero.
 */
class CursosAutocompleteController extends ControllerBase {

  private const VID = 'curso';

  private const LIMIT = 10;

  public function autocomplete(Request $request): JsonResponse {
    $q = trim((string) $request->query->get('q', ''));
    if (mb_strlen($q) < 2) {
      return new JsonResponse([]);
    }

    try {
      $vocab = $this->entityTypeManager()->getStorage('taxonomy_vocabulary')->load(self::VID);
      if (!$vocab) {
        return new JsonResponse([]);
      }

      $ids = $this->entityTypeManager()->getStorage('taxonomy_term')->getQuery()
        ->accessCheck(TRUE)
        ->condition('vid', self::VID)
        ->condition('status', 1)
        ->condition('name', $q, 'CONTAINS')
        ->sort('name')
        ->range(0, self::LIMIT)
        ->execute();

      if (!$ids) {
        return new JsonResponse([]);
      }

      /** @var \Drupal\taxonomy\TermInterface[] $terms */
      $terms = $this->entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($ids);
      $matches = [];
      foreach ($terms as $term) {
        $name = (string) $term->label();
        // Filtro exposto cursos é textfield — envia o nome do termo.
        $matches[] = [
          'value' => $name,
          'label' => $name,
        ];
      }

      $response = new JsonResponse($matches);
      $response->setPublic();
      $response->setMaxAge(300);
      return $response;
    }
    catch (\Throwable) {
      return new JsonResponse([]);
    }
  }

}
