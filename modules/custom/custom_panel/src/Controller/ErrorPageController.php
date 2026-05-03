<?php

namespace Drupal\custom_panel\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller para páginas amigáveis de erro 403 e 404.
 */
class ErrorPageController extends ControllerBase
{

  /**
   * Página amigável para acesso negado (403).
   */
  public function accessDenied(): array
  {
    return $this->buildErrorPage(403);
  }

  /**
   * Página amigável para recurso não encontrado (404).
   */
  public function notFound(): array
  {
    return $this->buildErrorPage(404);
  }

  /**
   * Monta o conteúdo comum da página de erro.
   */
  private function buildErrorPage(int $status_code): array
  {
    if ($status_code === 403) {
      $title = $this->t('Ops! Esta área é restrita.');
      $message = $this->t('Você não tem permissão para acessar esta página no momento.');
      $hint = $this->t('Se necessário, entre com outra conta ou utilize o menu do topo para continuar navegando no site.');
      $icon = 'fa-solid fa-lock';
    } else {
      $title = $this->t('Página não encontrada.');
      $message = $this->t('O conteúdo que você tentou acessar não está disponível neste endereço.');
      $hint = $this->t('Confira se o link está correto ou continue a navegação pelo menu principal.');
      $icon = 'fa-solid fa-compass';
    }

    return [
      '#theme' => 'custom_panel_error_page',
      '#status_code' => $status_code,
      '#title' => $title,
      '#message' => $message,
      '#hint' => $hint,
      '#icon' => $icon,
      '#cache' => [
        'contexts' => ['url.path', 'languages:language_interface'],
      ],
    ];
  }
}
