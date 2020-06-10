<?php

namespace Drupal\language_access_fields\Plugin\EntityReferenceSelection;

interface LanguageAccessSelectionInterface {

  const FILTER_NONE = 0;
  const FILTER_USER = 1;
  const FILTER_USER_UNDEFINED = 2;
  const FILTER_ENABLED = 3;
  const FILTER_ENABLED_UNDEFINED = 4;
  const FILTER_PARENT = 5;
  const FILTER_PARENT_UNDEFINED = 6;
  const FILTER_NOT_APPLICABLE = 7;

}
