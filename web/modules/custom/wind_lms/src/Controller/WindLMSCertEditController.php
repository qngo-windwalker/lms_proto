<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Drupal\group\Entity\Group;
use Drupal\opigno_group_manager\OpignoGroupContext;
use Drupal\Core\Link;
use Drupal\Core\Url;

use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_module\Entity\OpignoActivity;
use Drupal\opigno_group_manager\OpignoGroupContentTypesManager;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class WindLMSCertEditController extends ControllerBase{

  /**
   * wind_lms.cert.edit:
   *   path: cert/{node}/edit
   *
   * @param \Drupal\node\NodeInterface $node
   *
   * @return string[]
   */
  public function getContent(NodeInterface $node){
    $node->field_completion_verified->setValue(1);
    $node->revision_uid->setValue($this->currentUser()->id());
    $saveResult = $node->save();
    if(!$saveResult){
      return new JsonResponse([
        'code' => 500,
        'error' => 1,
        'message' => 'Unable to save data to node - Node Nid = .' . $node->id(),
      ]);
    }

    return new JsonResponse(['data' => array(
      'code' => 200,
      'success' => 1,
      'message' => 'Success',
    )]);
  }

}
