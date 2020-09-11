<?php

/**
 * This page wind/json/status is created so Site Manager can ping this page to check if this site is up and running.
 */
namespace Drupal\wind\Controller;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_module\Entity\OpignoActivity;
use Drupal\Core\Controller\ControllerBase;

class WindJsonStatusController extends ControllerBase {

  public function getContent() {
    return new JsonResponse([
      'code' => 200,
      'status' => 'Running'
    ]);
  }
}
