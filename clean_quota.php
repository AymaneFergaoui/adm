<?php

/**
 * @file
 * Diagnostic and cleanup script for OVH quota lock.
 */

ini_set('display_errors', '1');
ini_set('memory_limit', '512M');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

$autoloader = require_once __DIR__ . '/autoload.php';

try {
  $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
  \Drupal\Core\DrupalKernel::bootEnvironment();
  \Drupal\Core\Site\Settings::initialize(__DIR__, \Drupal\Core\DrupalKernel::findSitePath($request), $autoloader);

  $db = \Drupal\Core\Database\Database::getConnection();

  echo "=== DATABASE QUOTA & GRANTS DIAGNOSTIC ===\n\n";

  // Check grants
  $grants = $db->query("SHOW GRANTS FOR CURRENT_USER()")->fetchCol();
  echo "Grants:\n" . implode("\n", $grants) . "\n\n";

  // Check total size
  $stats = $db->query("SELECT 
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS data_index_mb,
    ROUND(SUM(data_length + index_length + data_free) / 1024 / 1024, 2) AS total_mb,
    ROUND(SUM(data_free) / 1024 / 1024, 2) AS free_mb
    FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchAssoc();

  echo "Data + Index Size: {$stats['data_index_mb']} MB\n";
  echo "Data Free (fragmented): {$stats['free_mb']} MB\n";
  echo "Total Allocated: {$stats['total_mb']} MB\n\n";

  // List top 20 tables
  echo "=== TOP 20 LARGEST TABLES ===\n";
  $tables = $db->query("SELECT table_name, 
    ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb,
    table_rows
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    ORDER BY (data_length + index_length) DESC LIMIT 20")->fetchAll();

  foreach ($tables as $t) {
    echo sprintf("%-35s %10s MB (%s rows)\n", $t->table_name, $t->size_mb, $t->table_rows);
  }

  // Cleanup action if requested
  if (isset($_GET['clean']) && $_GET['clean'] === 'yes') {
    echo "\n=== CLEANING CACHE & LOG TABLES ===\n";
    $cleanup_tables = [
      'd8_watchdog',
      'd8_cache_render',
      'd8_cache_page',
      'd8_cache_dynamic_page_cache',
      'd8_cache_data',
      'd8_cache_default',
      'd8_cache_discovery',
      'd8_cache_entity',
      'd8_cache_menu',
      'd8_cache_config',
      'd8_cache_container',
      'd8_cache_toolbar',
    ];

    foreach ($cleanup_tables as $tbl) {
      try {
        $count = $db->query("DELETE FROM `{$tbl}`")->rowCount();
        echo "Deleted from {$tbl}: OK\n";
      }
      catch (\Throwable $e) {
        echo "Error on {$tbl}: " . $e->getMessage() . "\n";
      }
    }

    echo "\nRecalculating size...\n";
    $statsAfter = $db->query("SELECT 
      ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS data_index_mb,
      ROUND(SUM(data_free) / 1024 / 1024, 2) AS free_mb
      FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchAssoc();
    echo "New Data + Index Size: {$statsAfter['data_index_mb']} MB\n";
  }
  else {
    echo "\nTo perform cleanup, visit this script with: ?clean=yes\n";
  }

}
catch (\Throwable $e) {
  echo "FATAL ERROR: " . $e->getMessage() . "\n";
  echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
