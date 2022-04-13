<?php
/**
 * This page is the landing page after learner logged in or completed registered.
 */

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\node\Entity\Node;

class WindLMSWelcomeController extends ControllerBase {

  /**
   * From home page redirect to my-progress
   *
   * When login user close their tab and open another tab and go to www.windwalkerxp.com,
   * this home page redirect to my-progress so the URL will correct.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function getContent() {
    if($this->currentUser()->isAuthenticated()){
      $response = new RedirectResponse('/dashboard');
      $response->send();

      $dashboardController = new \Drupal\wind_lms\Controller\WindLMSDashboardController;
      // Fall back if redirect fails.
      return $dashboardController->getContent();
    }

    $markup = '';
    $node = $this->getWelcomeNode();
    if ($node) {
      $markup = $node->body->value;
    }

    // wind_theme_preprocess_page() will add in the login form.
    return array(
      '#type' => 'markup',
      '#markup' => $markup,
      '#attached' => array(
      )
    );
  }

  public function getTitle() {
    if($this->currentUser()->isAuthenticated()){
      return '';
    }
    $node = $this->getWelcomeNode();
    if ($node) {
      return $node->getTitle();
    }
    return 'Login';
  }

  private function getWelcomeNode() {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'page');
    $query->condition('title', 'Welcome!');
    $result =  $query->execute();

    if (!empty($result)) {
      return Node::load(array_shift($result));

    }
    return FALSE;
  }

}
