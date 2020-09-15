<?php

namespace Drupal\wind\Controller;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;

class WindHandoffController extends ControllerBase {

  /**
   * @see web/core/modules/language/src/Plugin/Block/LanguageBlock.php
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getContent() {

    $webTitle = \Drupal::request()->query->get('addSiteName');
    if($webTitle){
      // Sanitize string
      $webTitle = Html::escape($webTitle);
      $config = \Drupal::service('config.factory')->getEditable('system.site');
      $config->set('name', $webTitle); 
      $config->save();
    }

    drupal_flush_all_caches();
   
    $response = new JsonResponse([
      'code' => 200,
      'status' => 'Completed',
    ]);

    $response->headers->set('Access-Control-Allow-Origin', '*');
    return $response;
  }
}
