<?php

namespace Drupal\wind\Controller;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_module\Entity\OpignoActivity;
use Drupal\Core\Controller\ControllerBase;

class WindJsonController extends ControllerBase {

  /**
   * @see web/core/modules/language/src/Plugin/Block/LanguageBlock.php
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getLanguage() {
    $container = \Drupal::getContainer();
    $languageManager = $container->get('language_manager');
    $pathMatcher = $container->get('path.matcher');

    $build = [];
    $route_name = $pathMatcher->isFrontPage() ? '<front>' : '<current>';
    $type = null;
    $links = $languageManager->getLanguageSwitchLinks($type, Url::fromRoute($route_name));

    if (isset($links->links)) {
      $build = [
        '#theme' => 'links__language_block',
        '#links' => $links->links,
        '#attributes' => [
          'class' => [
            "language-switcher-{$links->method_id}",
          ],
        ],
        '#set_active_class' => TRUE,
      ];
    }
    return new JsonResponse(['data' => $build]);
  }
}
