<?php

namespace Drupal\language_access;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * A service which allows interaction with various language-related topics.
 *
 * @package Drupal\language_access
 */
class LanguageAccessHelper {

  const LANGCODES_NOT_DEFINED = [
    LanguageInterface::LANGCODE_NOT_APPLICABLE,
    LanguageInterface::LANGCODE_NOT_SPECIFIED,
  ];

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * LanguageAccessHelper constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(LanguageManagerInterface $language_manager) {
    $this->languageManager = $language_manager;
  }

  /**
   * Retrieves a list of all languages which have a site code attached.
   *
   * @param \Drupal\Core\Language\LanguageInterface[]|null $languages
   *   An array of languages.
   *
   * @return \Drupal\Core\Language\LanguageInterface[]
   *   An array of languages which correspond to a site.
   */
  public function getFilteredLanguages($languages = NULL) {
    if (!$languages) {
      $languages = $this->languageManager->getNativeLanguages();
    }

    // Filter out all languages which do not have a site code.
    foreach ($languages as $langcode => $language) {
      $parts = explode('-', $langcode);
      if (count($parts) < 2) {
        unset($languages[$langcode]);
      }
    }

    return $languages;
  }

  /**
   * Returns a list of enabled languages.
   *
   * @param \Drupal\Core\Language\LanguageInterface[]|null $languages
   *   If not provided, all languages will be filtered.
   *
   * @return \Drupal\Core\Language\LanguageInterface[]|null
   *   Returns a filtered list of enabled languages.
   */
  public function getEnabledLanguages($languages = NULL) {
    if (!$languages) {
      $languages = $this->getFilteredLanguages();
    }

    if (\Drupal::moduleHandler()->moduleExists('disable_language')) {
      /** @var string $langcode */
      /** @var \Drupal\language\ConfigurableLanguageInterface $language */
      foreach ($languages as $langcode => $language) {
        if ($language->getThirdPartySetting('disable_language', 'disable', FALSE)) {
          unset($languages[$langcode]);
        }
      }
    }

    return $languages;
  }

  /**
   * Checks the provided language and confirms whether it counts as 'undefined'.
   *
   * @param string|LanguageInterface $language
   *   The language to check.
   *
   * @return bool
   *   Whether or not it's an undefined language.
   */
  public function languageIsUndefined($language) {
    if ($language instanceof LanguageInterface) {
      $language = $language->getId();
    }

    return in_array($language, $this::LANGCODES_NOT_DEFINED);
  }

  /**
   * Checks whether a given user can access disabled languages or not.
   *
   * @param \Drupal\user\UserInterface|\Drupal\Core\Session\AccountProxyInterface|null $user
   *   The user to check. If none provided, will use the current user.
   *
   * @return bool
   *   Whether or not disabled languages can be accessed by the user.
   */
  public function userHasDisabledLanguagesPermission($user = NULL) {
    if (!$user) {
      $user = \Drupal::currentUser();
    }

    if (\Drupal::moduleHandler()->moduleExists('disable_language')) {
      return $user->hasPermission('view disabled languages');
    }

    return TRUE;
  }

  /**
   * Internal filtering helper for the service, do not use outside of class.
   *
   * This method returns either a list of all languages with site codes,
   * or all enabled languages with site codes. This is controlled by either
   * a boolean, directly, or by the querying of a User object and whether or
   * not that user has permissions to see disabled languages.
   *
   * This will come in handy for when we permit non-administrative users into
   * the system, such as forum users and premium subscribers, and marks a
   * departure from our usual assumptions that all registered users are
   * part of the site staff.
   *
   * @param \Drupal\user\UserInterface|\Drupal\Core\Session\AccountProxyInterface|bool $include_disabled
   *   If user is supplied, will check his permission.
   *
   * @return \Drupal\Core\Language\LanguageInterface[]
   *   An array of languages.
   */
  protected function getAppropriateLanguages($include_disabled = TRUE) {
    if ($include_disabled instanceof UserInterface || $include_disabled instanceof AccountProxyInterface) {
      $include_disabled = $this->userHasDisabledLanguagesPermission($include_disabled);
    }

    $languages = $include_disabled ? $this->getFilteredLanguages() : $this->getEnabledLanguages();

    return $languages;
  }

  /**
   * Returns all languages which correspond to a certain site code.
   *
   * @param string $site
   *   The identifier of the site.
   * @param bool $include_disabled
   *   Decides if disabled languages should be counted.
   *
   * @return array[LanguageInterface]
   *   An array of languages which belong to the site.
   */
  public function getLanguagesOfSite($site, $include_disabled = TRUE) {
    $languages = $this->getAppropriateLanguages($include_disabled);

    foreach ($languages as $langcode => $language) {
      $parts = explode('-', $langcode);
      if (count($parts) < 2 || $parts[1] !== $site) {
        unset($languages[$langcode]);
      }
    }

    return $languages;
  }

  /**
   * Retrieves the front page of a certain language.
   *
   * @param LanguageInterface|string $langcode
   *   A langcode or language object.
   *
   * @return string
   *   Return node path, e.g. "/node/100".
   */
  public function getFrontpageUrlByLanguage($langcode) {
    // Get the target language object.
    if (is_string($langcode)) {
      $language = $this->languageManager->getLanguage($langcode);
    }

    // Remember original language before this operation.
    $original_language = $this->languageManager->getConfigOverrideLanguage();

    // Set the translation target language on the configuration factory.
    $this->languageManager->setConfigOverrideLanguage($language);
    $site_config = \Drupal::config('system.site');

    $path = $site_config->get('page');
    $path = $path['front'];

    // Set the configuration language back.
    $this->languageManager->setConfigOverrideLanguage($original_language);

    return $path;
  }

  /**
   * Retrieves the 404 page of a certain language.
   *
   * @param LanguageInterface|string $langcode
   *   A langcode or language object.
   *
   * @return string
   *   Return node path, e.g. "/node/100".
   */
  public function get404PageByLanguage($langcode) {
    // Get the target language object.
    if (is_string($langcode)) {
      $language = $this->languageManager->getLanguage($langcode);
    }

    // Remember original language before this operation.
    $original_language = $this->languageManager->getConfigOverrideLanguage();

    // Set the translation target language on the configuration factory.
    $this->languageManager->setConfigOverrideLanguage($language);
    $site_config = \Drupal::config('system.site');

    $path = $site_config->get('page');
    $path = $path['404'];

    // Set the configuration language back.
    $this->languageManager->setConfigOverrideLanguage($original_language);

    return $path;
  }

  /**
   * Retrieves the site name of a certain language.
   *
   * @param LanguageInterface|string $langcode
   *   A langcode or language object.
   *
   * @return string
   *   Return node path, e.g. "/node/100".
   */
  public function getSitenameByLanguage($langcode) {
    // Get the target language object.
    if (is_string($langcode)) {
      $language = $this->languageManager->getLanguage($langcode);
    }

    // Remember original language before this operation.
    $original_language = $this->languageManager->getConfigOverrideLanguage();

    // Set the translation target language on the configuration factory.
    $this->languageManager->setConfigOverrideLanguage($language);
    $site_config = \Drupal::config('system.site');

    $path = $site_config->get('name');

    // Set the configuration language back.
    $this->languageManager->setConfigOverrideLanguage($original_language);

    return $path;
  }

  /**
   * Prepares a list of front page URLs for all languages.
   *
   * @param bool $include_disabled
   *   Decides if disabled languages should be counted.
   *
   * @return array[string]
   *   Return an array with front page URLs.
   */
  public function getFrontpageUrls($include_disabled = TRUE) {
    $languages = $this->getAppropriateLanguages($include_disabled);

    $frontpage_urls = [];

    foreach ($languages as $langcode => $language) {
      $frontpage_urls[$langcode] = $this->getFrontpageUrlByLanguage($langcode);
    }

    return $frontpage_urls;
  }

  /**
   * Fetches the languages associated with the user.
   *
   * @param \Drupal\user\UserInterface|null $user
   *   The user to query. If not provided, will use current user.
   * @param bool $include_disabled
   *   Decides if disabled languages should be counted.
   *
   * @return array[LanguageInterface]
   *   An array of languages.
   */
  public function getLanguagesOfUser($user = NULL, $include_disabled = TRUE) {
    if (!$user) {
      $user = \Drupal::currentUser();
    }

    $user = User::load($user->id());

    $languages = [];
    if ($user->id() === '1') {
      $languages = $this->getFilteredLanguages();
    }
    else {
      foreach ($user->get('field_language') as $item) {
        /** @var \Drupal\language\ConfigurableLanguageInterface $language */
        $language = $item->entity;
        $languages[$language->getId()] = $language;
      }
    }

    // Filter the languages by whether they're enabled or not.
    $languages = $include_disabled ? $languages : $this->getEnabledLanguages($languages);

    return $languages;
  }

  /**
   * Retrieves a list of all existing sites.
   *
   * @param \Drupal\Core\Language\LanguageInterface[]|null $languages
   *   If not provided, will retrieve sites for all languages.
   * @param bool $include_disabled
   *   If TRUE, will include disabled sites.
   *
   * @return array[strings]
   *   An array of site strings.
   */
  public function getSites($languages = NULL, $include_disabled = TRUE) {
    if (!$languages) {
      $languages = $this->getAppropriateLanguages($include_disabled);
    }

    $sites = [];

    foreach ($languages as $key => $language) {
      $sites[] = $this->getSiteFromLangcode($key);
    }
    return $sites;
  }

  /**
   * Retrieves the sites associated to a user's languages.
   *
   * @param \Drupal\user\UserInterface|null $user
   *   The user to query. If not provided, will use current user.
   * @param bool $include_disabled
   *   Decides if disabled languages should be counted.
   *
   * @return array[string]
   *   An array of site codes.
   */
  public function getSitesOfUser($user = NULL, $include_disabled = TRUE) {
    if (!$user) {
      $user = \Drupal::currentUser();
    }

    $user = User::load($user->id());
    $languages = $this->getLanguagesOfUser($user, $include_disabled);

    $sites = $this->getSites($languages, $include_disabled);

    return $sites;
  }

  /**
   * Extracts a site from a langcode.
   *
   * @param string $langcode
   *   The language code.
   *
   * @return bool|string
   *   The site code, or FALSE if the language has no site code.
   */
  public function getSiteFromLangcode($langcode) {
    return substr($langcode, strpos($langcode, '-') + 1);
  }

  /**
   * Checks if a user has access to language.
   *
   * @param \Drupal\user\UserInterface|null $user
   *   The user to query. If not provided, will use current user.
   * @param \Drupal\Core\Language\LanguageInterface|null $language
   *   If not provided, will query access for the current language.
   *
   * @return bool
   *   Returns TRUE if the user has access to the language, otherwise FALSE.
   */
  public function userHasAccessToLanguage($user = NULL, $language = NULL) {
    if (!$language) {
      $language = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT);
    }
    $allowed_languages = $this->getLanguagesOfUser($user);

    $language_str = is_object($language) ? $language->getId() : $language;

    return array_key_exists($language_str, $allowed_languages);
  }

  /**
   * Checks if a user has access to language OR the language is neutral.
   *
   * @param \Drupal\user\UserInterface|null $user
   *   The user to query. If not provided, will use current user.
   * @param \Drupal\Core\Language\LanguageInterface|null $language
   *   If not provided, will query access for the current language.
   *
   * @return bool
   *   Returns TRUE if the user has access to the language, otherwise FALSE.
   */
  public function userHasAccessToLanguageOrLanguageNeutral($user = NULL, $language = NULL) {
    if (!$language) {
      $language = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT);
    }
    $allowed_languages = $this->getLanguagesOfUser($user);
    $allowed_languages['und'] = LanguageInterface::LANGCODE_NOT_SPECIFIED;

    $language_str = is_object($language) ? $language->getId() : $language;

    return array_key_exists($language_str, $allowed_languages);
  }

  /**
   * Languages to readable array.
   *
   * @param \Drupal\language\Entity\ConfigurableLanguage[] $languages
   *   An array of language objects.
   * @param bool $append_country
   *   Whether the country should be appended after the language name.
   *
   * @return array
   *   An array of readable languages; key is langcode, value is label.
   */
  public function languagesToReadableArray(array $languages, $append_country = TRUE): array {
    $list = [];

    /** @var \Drupal\language\Entity\ConfigurableLanguage $language */
    foreach ($languages as $language) {
      if ($append_country) {
        $list[$language->getId()] = $language->getName();
      }
      else {
        $list[$language->getId()] = preg_replace("/ \([^)]+\)/", '', $language->getName());
      }
    }

    return $list;
  }

  /**
   * Fetches the locale/langcode from a request.
   *
   * If no request provided, will take current request.
   *
   * @param \Symfony\Component\HttpFoundation\Request|null $request
   *   The request.
   *
   * @return string
   *   The langcode (assuming the current request has one).
   */
  public function getLangcodeFromRequest($request = NULL) {
    if (!$request) {
      $request = \Drupal::request();
    }

    return $this->langcodeByDomain($request->getHost());
  }

  /**
   * Return the domains mapping.
   *
   * @return array
   *   Domains mapping.
   */
  public function getDomains() {
    return \Drupal::config('language.negotiation')->get('url.domains');
  }

  /**
   * Return the domain name from langcode.
   *
   * @param string $langcode
   *   Language code.
   *
   * @return string|bool
   *   Domain name.
   */
  public function domainByLangcode($langcode) {
    $langcodes = $this->getDomains();
    return $langcodes[$langcode] ?? FALSE;
  }

  /**
   * Return the langcode from domain name.
   *
   * @param string $domain_name
   *   Domain name.
   *
   * @return string|bool
   *   Langcode.
   */
  public function langcodeByDomain($domain_name) {
    $langcodes = $this->getDomains();
    $domains = array_flip($langcodes);
    return $domains[$domain_name] ?? FALSE;
  }

}
