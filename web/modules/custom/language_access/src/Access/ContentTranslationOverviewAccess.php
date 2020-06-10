<?php

namespace Drupal\language_access\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\language_access\LanguageAccessHelper;

/**
 * Access check for entity translation overview.
 */
class ContentTranslationOverviewAccess implements AccessInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The service for the helper class.
   *
   * @var LanguageAccessHelper
   */
  protected $languageAccessHelper;

  /**
   * Constructs a ContentTranslationOverviewAccess object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $manager
   *   The entity type manager.
   * @param \Drupal\language_access\LanguageAccessHelper $language_access_helper
   *   The language access helper service.
   */
  public function __construct(EntityManagerInterface $manager, LanguageAccessHelper $language_access_helper) {
    $this->entityManager = $manager;
    $this->languageAccessHelper = $language_access_helper;
  }

  /**
   * Checks access to the translation overview for the entity and bundle.
   * Like: /en-gb/node/244/translations
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The parametrized route.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(RouteMatchInterface $route_match, AccountInterface $account, $entity_type_id) {

    /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $route_match->getParameter($entity_type_id);

    $langcode = $entity->language()->getId();
    $current_site = $this->languageAccessHelper->getSiteFromLangcode($langcode);
    $allowed_languages = $this->languageAccessHelper->getLanguagesOfSite($current_site);

    // Don't allow access for nodes in countries having only one language.
    if (count($allowed_languages) < 2) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }
}
