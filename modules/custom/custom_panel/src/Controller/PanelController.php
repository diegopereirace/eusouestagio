<?php

namespace Drupal\custom_panel\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\custom_panel\Form\CandidatoEditForm;
use Drupal\custom_panel\Form\EmpresaEditForm;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PanelController extends ControllerBase {

  /**
   * Página principal do painel: despacha para o formulário correto conforme a role.
   */
  public function index(): array|RedirectResponse {
    $user = $this->entityTypeManager()->getStorage('user')->load($this->currentUser()->id());

    if (!$user) {
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }

    if ($user->hasRole('empresa')) {
      return $this->formBuilder()->getForm(EmpresaEditForm::class);
    }

    if ($user->hasRole('candidato')) {
      return $this->formBuilder()->getForm(CandidatoEditForm::class);
    }

    // Usuários sem role candidato/empresa (ex: admin) vão para o perfil padrão.
    return new RedirectResponse(Url::fromRoute('user.page')->toString());
  }

}
