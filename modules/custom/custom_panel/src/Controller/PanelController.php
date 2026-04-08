<?php

namespace Drupal\custom_panel\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\custom_panel\Form\CandidatoEditForm;
use Drupal\custom_panel\Form\EmpresaEditForm;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PanelController extends ControllerBase {

  /**
   * Página /painel: redireciona para a rota correta conforme a role.
   */
  public function index(): array|RedirectResponse {
    $user = $this->entityTypeManager()->getStorage('user')->load($this->currentUser()->id());

    if (!$user) {
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }

    if ($user->hasRole('empresa')) {
      return new RedirectResponse(Url::fromRoute('custom_panel.painel_empresa_perfil')->toString());
    }

    if ($user->hasRole('candidato')) {
      return new RedirectResponse(Url::fromRoute('custom_panel.painel_estudante_perfil')->toString());
    }

    // Usuários sem role candidato/empresa (ex: admin) vão para o perfil padrão.
    return new RedirectResponse(Url::fromRoute('user.page')->toString());
  }

  /**
   * Página /painel/estudante/perfil.
   */
  public function estudantePerfil(): array {
    return $this->formBuilder()->getForm(CandidatoEditForm::class);
  }

  /**
   * Página /painel/empresa/perfil.
   */
  public function empresaPerfil(): array {
    return $this->formBuilder()->getForm(EmpresaEditForm::class);
  }

}
