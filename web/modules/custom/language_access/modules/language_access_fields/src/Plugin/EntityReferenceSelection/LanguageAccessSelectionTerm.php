<?php

namespace Drupal\language_access_fields\Plugin\EntityReferenceSelection;

use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Plugin\EntityReferenceSelection\TermSelection;

/**
 * Filters referenced nodes by language.
 *
 * @EntityReferenceSelection(
 *   id = "language_access_fields:taxonomy_term",
 *   label = @Translation("Terms filtered by language"),
 *   entity_types = {"taxonomy_term"},
 *   group = "language_access_fields",
 *   weight = 0,
 *   base_plugin_label = @Translation("Extended: Additionally filtered by language")
 * )
 */
class LanguageAccessSelectionTerm extends TermSelection implements LanguageAccessSelectionInterface {

  use LanguageAccessSelectionTrait;

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $configuration = $this->getConfiguration();
    $query = parent::buildEntityQuery($match, $match_operator);
    if ($configuration['language_filter'] === self::FILTER_NONE) {
      return $query;
    }
    $languages = $this->getLanguages($configuration['language_filter']);
    $query->condition('langcode', $languages, 'IN');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $options = parent::getReferenceableEntities($match, $match_operator, $limit);
    $configuration = $this->getConfiguration();
    if ($configuration['language_filter'] === self::FILTER_NONE) {
      return $options;
    }
    $languages = $this->getLanguages($configuration['language_filter']);
    foreach ($options as &$option_subarray) {
      /** @var array[TermInterface] $terms */
      $terms = Term::loadMultiple(array_keys($option_subarray));
      foreach ($option_subarray as $option => $label) {
        if (!in_array($terms[$option]->language()->getId(), $languages)) {
          unset($option_subarray[$option]);
        }
      }
    }
    return $options;
  }

}
