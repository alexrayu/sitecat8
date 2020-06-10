<?php

namespace Drupal\language_access_taxonomy;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\language_access\LanguageAccessHelper;

/**
 * A service which allows interaction with various language-related topics.
 *
 * @package Drupal\language_access_taxonomy
 */
class LanguageAccessTaxonomyHelper {

  /**
   * Constants for Language Access cases.
   */
  const CASE__NO_CHANGE = FALSE;

  const CASE__INAPPLICABLE = 'inapplicable';

  const CASE__USER_LANGUAGES = 'user_languages';

  const CASE__USER_LANGUAGES_PLUS_UNDEFINED = 'user_languages_plus_undefined';

  const CASE__ENABLED_LANGUAGES = 'enabled_languages';

  const CASE__ENABLED_LANGUAGES_PLUS_UNDEFINED = 'enabled_languages_plus_undefined';

  const CASE__PARENT_ENTITY = 'parent_entity';

  const CASE__PARENT_ENTITY_PLUS_UNDEFINED = 'parent_entity_plus_undefined';

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Language Access base helper service.
   *
   * @var \Drupal\language_access\LanguageAccessHelper
   */
  protected $languageAccessHelper;

  /**
   * The Entity Reference selection manager.
   *
   * @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface
   */
  protected $entityReferenceSelectionManager;

  /**
   * LanguageAccessHelper constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(LanguageManagerInterface $language_manager, LanguageAccessHelper $language_access_helper, SelectionPluginManagerInterface $entity_reference_selection_manager) {
    $this->languageManager = $language_manager;
    $this->languageAccessHelper = $language_access_helper;
    $this->entityReferenceSelectionManager = $entity_reference_selection_manager;
  }

  /**
   * Returns an array of all Language Access cases and appropriate strings.
   *
   * @return array
   *   Associative array, mapped as key:label.
   */
  public function getLanguageAccessCases() {
    return [
      $this::CASE__NO_CHANGE => t('- No change -'),
      $this::CASE__INAPPLICABLE => t('NYI: No languages (Inapplicable)'),
      $this::CASE__USER_LANGUAGES => t('NYI: User languages'),
      $this::CASE__USER_LANGUAGES_PLUS_UNDEFINED => t('NYI: User languages (and undefined)'),
      $this::CASE__ENABLED_LANGUAGES => t('NYI: Enabled system languages'),
      $this::CASE__ENABLED_LANGUAGES_PLUS_UNDEFINED => t('NYI: Enabled system languages (and undefined)'),
      $this::CASE__PARENT_ENTITY => t("Parent entity's language"),
      $this::CASE__PARENT_ENTITY_PLUS_UNDEFINED => t("Parent entity's language (and undefined)"),
    ];
  }

  /**
   * Gets a singular Language Access case string.
   *
   * @param int $case
   *   The case ID.
   *
   * @return mixed
   *   The translated string for the Language Access case.
   */
  public function getLanguageAccessCase($case) {
    $cases = $this->getLanguageAccessCases();

    return $cases[$case];
  }

  public function filterLanguages(array $languages, $filter) {
    switch ($filter) {
      case $this::CASE__USER_LANGUAGES:
        $user_languages = $this->languageAccessHelper->getLanguagesOfUser(NULL);
        $languages = array_intersect_key($languages, $user_languages);
        break;

      case $this::CASE__USER_LANGUAGES_PLUS_UNDEFINED:
        $undefined = $languages['und'] ?? FALSE;
        $user_languages = $this->languageAccessHelper->getLanguagesOfUser(NULL);
        $languages = array_intersect_key($languages, $user_languages);
        if (isset($undefined)) {
          $languages['und'] = $undefined;
        }
        break;

      case $this::CASE__ENABLED_LANGUAGES:
        $enabled_languages = $this->languageAccessHelper->getEnabledLanguages();
        $languages = array_intersect_key($languages, $enabled_languages);
        break;

      case $this::CASE__ENABLED_LANGUAGES_PLUS_UNDEFINED:
        $enabled_languages = $this->languageAccessHelper->getEnabledLanguages();
        $languages = array_intersect_key($languages, $enabled_languages);
        $undefined = $languages['und'] ?? FALSE;
        if (isset($undefined)) {
          $languages['und'] = $undefined;
        }
        break;

      case $this::CASE__INAPPLICABLE:
      default:
        if (isset($languages['zxx'])) {
          $languages = [
            'zxx' => $languages['zxx'],
          ];
        }
        break;
    }

    return $languages;
  }

  /**
   * Filters an array of options by the specified case.
   *
   * Options come from widgets such as Select, Checkboxes etc.
   *
   * @param array $options
   *   Associative array containing entity IDs and labels.
   * @param int|string $filter
   *   The Language Access filter case ID.
   * @param \Drupal\Core\Field\FieldConfigInterface $field
   *   The field definition.
   * @param \Drupal\Core\Field\WidgetInterface $widget
   *   The field widget.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity handled by the form.
   *
   * @return array
   *   An array of options filtered by the specified values.
   */
  public function filterOptions(array $options, $filter, FieldConfigInterface $field, WidgetInterface $widget, EntityInterface $entity) {
    $entities = array_combine(array_keys($options), array_keys($options));

    $entities = $this->filterEntities($entities, $filter, $field, $widget, $entity);

    $options = array_intersect_key($options, $entities);

    return $options;
  }

  /**
   * Filters an array of entity IDs, or entities, by the specified case.
   *
   * @param array $entities
   *   Associative array containing entity IDs and, optionally, entities.
   * @param int|string $filter
   *   The Language Access filter case ID.
   * @param \Drupal\Core\Field\FieldConfigInterface $field
   *   The field definition.
   * @param \Drupal\Core\Field\WidgetInterface $widget
   *   The field widget.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity handled by the form.
   *
   * @return array
   *   An array filteed by the specified values.
   */
  public function filterEntities(array $entities, $filter, FieldConfigInterface $field, WidgetInterface $widget, EntityInterface $entity) {
    switch ($filter) {
      case $this::CASE__PARENT_ENTITY:
        $entities = $this->filterEntitiesByParentEntity($entities, $field, $widget, $entity);
        break;

      default:
        break;
    }

    return $entities;
  }

  /**
   * @param array $entities
   *   Associative array containing entity IDs and, optionally, entities.
   * @param \Drupal\Core\Field\FieldConfigInterface $field
   *   The field definition.
   * @param \Drupal\Core\Field\WidgetInterface $widget
   *   The field widget.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity handled by the form.
   *
   * @return mixed
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function filterEntitiesByParentEntity($entities, FieldConfigInterface $field, WidgetInterface $widget, EntityInterface $entity) {
    if ($language = $entity->language()) {
      $reference_type = $field->getFieldStorageDefinition()
        ->getSetting('target_type');

      $option_entities = \Drupal::entityTypeManager()
        ->getStorage($reference_type)
        ->loadMultiple($entities);

      foreach ($option_entities as $id => $entity) {
        if ($entity_language = $entity->language()) {
          if ($entity_language->getId() !== $language->getId()) {
            unset($entities[$id]);
          }
        }
      }
    }

    return $entities;
  }

}
