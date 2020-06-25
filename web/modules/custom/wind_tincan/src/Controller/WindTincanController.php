<?php

namespace Drupal\wind_tincan\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Access\AccessResult;

class WindTincanController extends ControllerBase{

  public function getContent($id) {
    // Fix issue with Hi, [field_first_name] caching for regular user.
    \Drupal::service('page_cache_kill_switch')->trigger();

    $output = '<p>We launched your course in a new window but if you do not see it, a popup blocker may be preventing it from opening. Please disable popup blockers for this site.</p>';
    $output .= $this->getReturnLink();
    return [
      'content' => [
        '#markup' => $output
      ],
    ];
  }

  public function courseRender() {
    return [
      'content' => [
        '#markup' => '<p>coming soon</p>',
      ],
    ];
  }

  private function getReturnLink() {
    $url = Url::fromUserInput(
      '/dashboard',
      [
        'attributes' => [
          'class' => ''
        ]
      ]
    );
    $linkContent = '<i class="fas fa-arrow-circle-left"></i> Return to Dashboard';
    $renderedAnchorContent = render($linkContent);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }


}
