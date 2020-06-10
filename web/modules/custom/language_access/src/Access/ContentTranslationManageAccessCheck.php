<?php

namespace Drupal\language_access\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Drupal\language_access\LanguageAccessHelper;

/**
 * Access check for entity translation CRUD operation.
 */
class ContentTranslationManageAccessCheck implements AccessInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The service for the helper class.
   *
   * @var LanguageAccessHelper
   */
  protected $languageAccessHelper;

  /**
   * Constructs a ContentTranslationManageAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\language_access\LanguageAccessHelper $language_access_helper
   *   The language access helper service.
   */
  public function __construct(EntityManagerInterface $manager, LanguageManagerInterface $language_manager, LanguageAccessHelper $language_access_helper) {
    $this->entityManager = $manager;
    $this->languageManager = $language_manager;
    $this->languageAccessHelper = $language_access_helper;
  }

  /**
   * Checks translation access for the entity and operation on the given route.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The parametrized route.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param string $source
   *   (optional) For a create operation, the language code of the source.
   * @param string $target
   *   (optional) For a create operation, the language code of the translation.
   * @param string $language
   *   (optional) For an update or delete operation, the language code of the
   *   translation being updated or deleted.
   * @param string $entity_type_id
   *   (optional) The entity type ID.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account, $source = NULL, $target = NULL, $language = NULL, $entity_type_id = NULL) {

    /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $route_match->getParameter($entity_type_id);

    $langcode = $entity->language()->getId();
    $current_site = $this->languageAccessHelper->getSiteFromLangcode($langcode);
    $allowed_languages = $this->languageAccessHelper->getLanguagesOfSite($current_site);

    $allowed_languages_list = $this->languageAccessHelper::LANGCODES_NOT_DEFINED;
    if (isset($allowed_languages) && !empty($allowed_languages)) {
      foreach ($allowed_languages as $allowed_language) {
        $allowed_languages_list[] = $allowed_language->id();
      }
    }

    // Check if current target language contained in allowed_languages.
    if (!empty($allowed_languages_list) && !in_array($target, $allowed_languages_list)) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
