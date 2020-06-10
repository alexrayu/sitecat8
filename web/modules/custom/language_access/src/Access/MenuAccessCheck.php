<?php

namespace Drupal\language_access\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\language_access\LanguageAccessHelper;
use Symfony\Component\Routing\Route;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Class MenuAccessCheck.
 *
 * @package Drupal\language_access\Access
 */
class MenuAccessCheck implements AccessInterface {

  /**
   * Langcode list.
   *
   * @var array
   */
  protected $userLanguagesLangcodes;

  /**
   * The service for the helper class.
   *
   * @var \Drupal\language_access\LanguageAccessHelper
   */
  protected $languageAccessHelper;

  /**
   * Constructs a MenuAccessCheck object.
   *
   * @param \Drupal\language_access\LanguageAccessHelper $language_access_helper
   *   The language access helper service.
   * @param array|null $user_language_langcodes
   *   Langcode list.
   */
  public function __construct(LanguageAccessHelper $language_access_helper, array $user_language_langcodes = []) {
    $this->languageAccessHelper = $language_access_helper;
    $this->userLanguagesLangcodes = $user_language_langcodes;
  }

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
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Return access result.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account): AccessResultInterface {

    $this->setUserLanguagesLangcodes($account);
    $route_parameters = $route_match->getParameters()->all();

    // Menu object is missing.
    if (!isset($route_parameters['menu'])) {
      return AccessResult::allowed();
    }

    /** @var \Drupal\system\Entity\Menu $menu */
    $menu = $route_parameters['menu'];

    // Menu ID: e.g. "main-menu--de-kk".
    if (
      $this->isMenuType($menu->id(), alpha_menu_affected_menus()) &&
      $this->isMenuLanguageInUserLanguagesLangcodes($menu->language()->getId())
    ) {
      return AccessResult::allowed();
    }

    return AccessResultForbidden::forbidden();
  }

  /**
   * Set user language langcodes.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User.
   */
  protected function setUserLanguagesLangcodes(AccountInterface $account) {
    if (is_array($this->userLanguagesLangcodes) && \count($this->userLanguagesLangcodes) > 0) {
      return;
    }

    // Duck typing: You never now which primitive type it might have.
    $this->userLanguagesLangcodes = [];
    foreach ($this->languageAccessHelper->getLanguagesOfUser($account) as $language) {
      $this->userLanguagesLangcodes[] = $language->id();
    }
  }

  /**
   * Check if it's a valid menu ID according to your type.
   *
   * @param string $menu_id
   *   Main menu ID: e.g. "main-menu--en-gb" or "loginarea-menu--en-gb".
   * @param array $types
   *   Enabled menu types.
   *
   * @return bool
   *   Return true if there is a match otherwise false.
   */
  protected function isMenuType(string $menu_id, array $types = ['main-menu']): bool {
    return in_array(explode('--', $menu_id)[0], $types);
  }

  /**
   * Find menu langcode in the user langcodes list.
   *
   * @param string $menu_langcode
   *   Langcode: e.g. "en-gb".
   *
   * @return bool
   *   Return true if there is a match otherwise false.
   */
  protected function isMenuLanguageInUserLanguagesLangcodes(string $menu_langcode): bool {
    return in_array($menu_langcode, $this->userLanguagesLangcodes, TRUE);
  }

  /**
   * Check if user has the following role.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User.
   * @param string $role
   *   User role.
   *
   * @return bool
   *   Return true otherwise false.
   */
  protected function userHasRole(AccountInterface $account, string $role): bool {
    return in_array($role, $account->getRoles(), TRUE);
  }

}
