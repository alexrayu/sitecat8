<?php

namespace Drupal\language_access\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class MenuRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {

    // Add custom access check for menu-list.
    if ($route = $collection->get('entity.menu.edit_form')) {
      $requirements = $route->getRequirements();

      $requirements['_custom_access'] = '\Drupal\language_access\Access\MenuAccessCheck::access';
      $route->setRequirements($requirements);
    }

    // Add custom access check for menu-add.
    if ($route = $collection->get('entity.menu.add_link_form')) {
      $requirements = $route->getRequirements();

      $requirements['_custom_access'] = '\Drupal\language_access\Access\MenuAccessCheck::access';
      $route->setRequirements($requirements);
    }
  }
}
