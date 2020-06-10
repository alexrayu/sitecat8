<?php

namespace Drupal\language_access\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Class MenuTranslationAccessCheck.
 *
 * @package Drupal\language_access\Access
 */
class MenuTranslationAccessCheck implements AccessInterface {

  /**
   * A custom access check.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   Route.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   *   Return either forbidden or allowed.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    $routes = ['entity.menu_link_content.content_translation_overview'];

    // Deny users access to the translation feature.
    if (in_array($route_match->getRouteName(), $routes, TRUE) && $account->id() !== "1") {
      return AccessResultForbidden::forbidden();
    }

    return AccessResult::allowed();

  }

}
