<?php

namespace Drupal\wind_jira\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Access\AccessResult;

class WindJiraConnect extends ControllerBase{
  public function getContent() {

    return [
      '#markup' => 'testing',
    ];
  }

}
