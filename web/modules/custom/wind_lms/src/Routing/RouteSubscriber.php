<?php

namespace Drupal\wind_lms\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Customize access to admin page
   *
   * @see web/core/modules/user/user.routing.yml
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Only administrator can access the Roles page (admin/people/roles)
    if ($route = $collection->get('entity.user_role.collection')) {
      $route->setRequirement('_role', 'administrator');
    }

    // Only administrator can access the Permissions page (admin/people/permission)
    if ($route = $collection->get('user.admin_permissions')) {
      $route->setRequirement('_role', 'administrator');
    }

    if ($route = $collection->get('user.role.settings')) {
      $route->setRequirement('_role', 'administrator');
    }
  }

}
