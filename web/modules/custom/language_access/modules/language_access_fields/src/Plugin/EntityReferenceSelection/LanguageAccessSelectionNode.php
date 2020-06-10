<?php

namespace Drupal\language_access_fields\Plugin\EntityReferenceSelection;

use Drupal\node\Entity\Node;
use Drupal\node\Plugin\EntityReferenceSelection\NodeSelection;

/**
 * Filters referenced nodes by language.
 *
 * @EntityReferenceSelection(
 *   id = "language_access_fields:node",
 *   label = @Translation("Nodes filtered by language"),
 *   entity_types = {"node"},
 *   group = "language_access_fields",
 *   weight = 0,
 *   base_plugin_label = @Translation("Extended: Additionally filtered by language")
 * )
 */
class LanguageAccessSelectionNode extends NodeSelection implements LanguageAccessSelectionInterface {

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
      /** @var array[NodeInterface] $nodes */
      $nodes = Node::loadMultiple(array_keys($option_subarray));
      foreach ($option_subarray as $option => $label) {
        if (!in_array($nodes[$option]->language()->getId(), $languages)) {
          unset($option_subarray[$option]);
        }
      }
    }
    return $options;
  }

}
