<?php

// @codingStandardsIgnoreFile

$config_directories = [];
$settings['hash_salt'] = 'zr4BU6CWn3uB1_ZI9M3GkarWUCsxv8hKKvzUNp66PzbyZtmK5XpovM8wzvUfnzYym62z8NmGkg';
$settings['update_free_access'] = FALSE;
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';
$settings['trusted_host_patterns'] = [
  // '^www\.example\.com$',
];
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];
$settings['entity_update_batch_size'] = 50;
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
$config_directories['sync'] = '../config/sync';
$config['install_profile'] = 'standard';
$config['system.logging']['error_level'] = 'verbose';
$databases['default']['default'] = array (
  'database' => 'sitecat',
  'username' => 'drupal',
  'password' => 'drupal',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
