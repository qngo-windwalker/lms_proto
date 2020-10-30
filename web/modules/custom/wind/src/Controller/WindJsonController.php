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

  public function getCurrentUser() {
    $user = $this->currentUser();
    $userAccount = $user->getAccount();
    return new JsonResponse([
      'uid' => $userAccount->id(),
      'name' => $userAccount->getAccountName(),
      'mail' => $userAccount->getEmail(),
      'roles' => $userAccount->getRoles(),
    ]);
  }

  public function getSiteInfo(){
    $themeName = \Drupal::service('theme.manager')->getActiveTheme()->getName();
    // If true (1), use the logo supplied by the theme
    $logoUseDefault  = theme_get_setting('logo.use_default', $themeName);
    $themeLogoPath = theme_get_setting('logo.path', $themeName);

    $externalURL = '';
    if ($wrapper = \Drupal::service('stream_wrapper_manager')->getViaUri($themeLogoPath)) {
      $externalURL = $wrapper->getExternalUrl();
    }
    $user = $this->currentUser();
    $userAccount = $user->getAccount();
    return new JsonResponse([
      'currentUser' => array(
        'uid' => $userAccount->id(),
        'name' => $userAccount->getAccountName(),
        'mail' => $userAccount->getEmail(),
        'roles' => $userAccount->getRoles(),
      ),
      'site' => array(
        'logoUseDefault' => $logoUseDefault,
        'logoPath' => $externalURL,
      )
    ]);
  }
}
