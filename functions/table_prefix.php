<?php
use Exception;

if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}

function get_table_name_with_prefix(string $tablename_without_prefix): string
{
  global $wpdb;
  $prefix = $wpdb->prefix;
  $prefixed_tablename = $prefix . $tablename_without_prefix;

  $table_exists = $wpdb->get_results(
    $wpdb->prepare(
      "SHOW TABLES LIKE '%s'",
      $prefixed_tablename
    )
  );

  if (count($table_exists)) {
    return $prefixed_tablename;
  }

  throw new Exception("Table $prefixed_tablename not found.", 1);
}
