<?php

namespace Drupal\language_access\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\user\Entity\User;

/**
 * Filter entityes by user's assigned countries.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("access_user_countries")
 */
class AccessUserCountries extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    return t('Limit entities by user country access.');
  }

  /**
   * Add this filter to the query.
   */
  public function query() {
    $current_user = User::load(\Drupal::currentUser()->id());
    if ($current_user->id() == 1 || $current_user->hasRole('administrator')) {
      return;
    }

    // Apply filter by language access.
    $languages = \Drupal::service('language_access.helper')->getLanguagesOfUser();
    $langcodes = [];
    foreach ($languages as $language) {
      $langcodes[] = $language->id();
    }

    $this->query->addWhere('language_access', 'langcode', $langcodes, 'IN');
  }

}
