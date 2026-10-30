<?php

/**
 * @file
 * Lightweight Drupal 10 Production Cache Clear Script (OVH max_questions safe).
 */

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('memory_limit', '512M');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');
echo "=== DRUPAL 10 PRODUCTION LIGHTWEIGHT CACHE CLEAR ===\n";
echo "PHP Version: " . PHP_VERSION . "\n\n";

$autoloader = require_once __DIR__ . '/autoload.php';

if (function_exists('opcache_reset')) {
  @opcache_reset();
  echo "[1] OPcache reset: OK\n\n";
}

try {
  $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
  $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
  $kernel->boot();

  $db = \Drupal\Core\Database\Database::getConnection();
  if (!empty($_GET['clear'])) {
    foreach (['cache_page', 'cache_dynamic_page_cache', 'cache_render', 'cache_data'] as $bin) {
      if ($db->schema()->tableExists($bin)) {
        $db->truncate($bin)->execute();
        echo "[2] Truncated {$bin}: OK\n";
      }
    }
  }

  echo "\n=== RECENT ERRORS IN WATCHDOG ===\n";
  $rows = $db->query("SELECT wid, type, severity, message, variables, location, FROM_UNIXTIME(timestamp) as dt FROM {watchdog} WHERE type = 'php' OR severity <= 3 ORDER BY wid DESC LIMIT 15")->fetchAll();
  foreach ($rows as $r) {
    $vars = @unserialize($r->variables);
    $msg = $r->message;
    if (is_array($vars)) {
      foreach ($vars as $k => $v) {
        if (is_scalar($v)) {
          $msg = str_replace($k, (string) $v, $msg);
        }
      }
    }
    echo "[{$r->wid}] {$r->dt} | {$r->type} | {$r->location}\n  -> {$msg}\n\n";
  }

  echo "\n=== DONE ===\n";
}
catch (\Throwable $e) {
  echo "\n!!! ERROR DETECTED !!!\n";
  echo "Type: " . get_class($e) . "\n";
  echo "Message: " . $e->getMessage() . "\n";
  echo "File: " . $e->getFile() . " (Line " . $e->getLine() . ")\n";
}
