<?php
/**
 * This controller for page [site]/course
 * This page is the landing page after learner logged in or completed registered.
 */

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class WindLMSImportUserController extends ControllerBase {

  /**
   * @return array
   */
  public function getContent() {
    return [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'react-container',
      ],
      '#attached' => [
        'library' => [
          'wind_lms/import_user',
        ]
      ]
    ];
    return $response;
  }
}
