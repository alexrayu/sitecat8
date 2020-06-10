<?php

namespace Drupal\language_access_fields\Plugin\EntityReferenceSelection;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;

trait LanguageAccessSelectionTrait {

  /**
   * Get the enabled languages.
   *
   * @return array
   *   List of language codes.
   */
  public function getEnabledLanguages(): array {
    return array_keys(\Drupal::service('language_access.helper')
      ->getEnabledLanguages());
  }

  /**
   * Get the user languages.
   *
   * @return array
   *   List of language codes.
   */
  public function getUserLanguages(): array {
    return array_keys(\Drupal::service('language_access.helper')
      ->getLanguagesOfUser());
  }

  /**
   * Get the parent entity's language.
   *
   * @return array
   *   List of language codes.
   */
  public function getParentEntityLanguage(): array {
    if (!isset($this->configuration['handler_settings']['parent_language'])) {
      return [
        \Drupal::languageManager()
          ->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)
          ->getId(),
      ];
    }
    return [$this->configuration['handler_settings']['parent_language']];
  }

  /**
   * Get language codes required for filtering.
   *
   * @param integer $filter
   *
   * @return array
   *   The list of language codes to use in the filter.
   */
  public function getLanguages($filter): array {

    switch ($filter) {
      case self::FILTER_USER:
        return $this->getUserLanguages();

      case self::FILTER_USER_UNDEFINED:
        return $this->getUserLanguages() + ['und'];

      case self::FILTER_ENABLED:
        return $this->getEnabledLanguages();

      case self::FILTER_ENABLED_UNDEFINED:
        return $this->getEnabledLanguages() + ['und'];

      case self::FILTER_PARENT:
        return $this->getParentEntityLanguage();

      case self::FILTER_PARENT_UNDEFINED:
        return $this->getParentEntityLanguage() + ['und'];
        break;

      case self::FILTER_NOT_APPLICABLE:
        return ['zxx'];

      default:
        return [];
    }

  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $configuration = $this->getConfiguration();
    $form['language_filter'] = [
      '#type' => 'select',
      '#title' => $this->t('Language filtering'),
      '#description' => $this->t('Determines whether the available values for this field are filtered by language, and in what way.'),
      '#options' => [
        self::FILTER_NONE => $this->t('none'),
        self::FILTER_USER => $this->t('User languages'),
        self::FILTER_USER_UNDEFINED => $this->t('User languages or undefined'),
        self::FILTER_ENABLED => $this->t('Enabled languages'),
        self::FILTER_ENABLED_UNDEFINED => $this->t('Enabled languages or undefined'),
        self::FILTER_PARENT => $this->t('Parent entity language'),
        self::FILTER_PARENT_UNDEFINED => $this->t('Parent entity language or undefined'),
        self::FILTER_NOT_APPLICABLE => $this->t('Not applicable'),

      ],
      '#default_value' => $configuration['language_filter'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'language_filter' => self::FILTER_NONE,
      ] + parent::defaultConfiguration();
  }
}
