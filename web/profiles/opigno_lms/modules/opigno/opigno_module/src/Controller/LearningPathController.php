<?php

namespace Drupal\opigno_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\opigno_module\Entity\OpignoModule;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LearningPathController.
 */
class LearningPathController extends ControllerBase {

  public function addIndex() {
    $opigno_module = OpignoModule::create();
    $form = \Drupal::service('entity.form_builder')->getForm($opigno_module);
    return $form;
  }

  public function editIndex($opigno_module) {
    return \Drupal::service('entity.form_builder')->getForm($opigno_module);
  }

  public function modulesIndex($opigno_module, Request $request) {
    return [
      '#theme' => 'opigno_learning_path_modules',
      '#attached' => ['library' => ['opigno_group_manager/manage_app']],
      '#base_path' => $request->getBasePath(),
      '#base_href' => \Drupal::service('path.current')->getPath(),
      '#learning_path_id' => $opigno_module->id(),
      '#module_context' => 'true',
    ];
  }

  public function addAccess(AccountInterface $account) {
    return \Drupal::entityTypeManager()
      ->getAccessControlHandler('opigno_module')
      ->createAccess(NULL, $account, [], TRUE);
  }

  public function editAccess($opigno_module, AccountInterface $account) {
    $module = OpignoModule::load($opigno_module);
    return $module->access('update', $account, TRUE);
  }

}
