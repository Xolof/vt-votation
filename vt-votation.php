<?php

/*
 * Plugin Name: VT Votation
 * Description: Anpassningar för omröstning om årets mest olämpliga barnbok.
 * Version: 0.1.0
 * Author: Liberdev
 * Author URI: https://liberdev.se
 * Text Domain: vt-votation
 */

if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}

require_once (__DIR__ . '/functions/menu_page.php');
require_once (__DIR__ . '/functions/render_menu_sub_pages.php');
require_once (__DIR__ . '/functions/process_settings.php');
require_once (__DIR__ . '/functions/admin_notices.php');
require_once (__DIR__ . '/functions/forminator_mods.php');

define('ALLOW_MULTIPLE_VOTES_FROM_SAME_IP', json_decode(get_option('allow_multiple_votes_from_same_ip')) ?? 'yes');
define('IP_BLOCK_LIST', json_decode(get_option('vt_votation_blocked_ips')) ?? []);
define('VOTATION_FORM_IDS', json_decode(get_option('vt_votation_forminator_form_ids')) ?? []);
define('IP_BLOCKED_MESSAGE', 'Din IP-adress har blockerats.');
define('ONLY_VOTE_ONE_TIME_MESSAGE', 'Du har redan röstat på den här boken med den här epostadressen.');
define('ONLY_VOTE_ONE_TIME_PER_IP_MESSAGE', 'Någon har redan röstat på den här boken med den här IP-adressen.');

register_activation_hook(
  __FILE__,
  'vtv_activate'
);
register_deactivation_hook(
  __FILE__,
  'vtv_deactivate'
);

function vtv_activate() {}
function vtv_deactivate() {}

add_action('admin_menu', 'vtv_admin_page');
add_action('admin_post_vtv_form_response', 'vtv_process_settings');
add_action('admin_notices', 'print_plugin_admin_notices');
add_filter('forminator_custom_form_submit_errors', 'vtv_forminator_submit_errors_block_and_email', 15, 3);
add_filter('forminator_custom_form_invalid_form_message', 'vtv_forminator_invalid_form_message_block_and_email', 10, 3);
if (ALLOW_MULTIPLE_VOTES_FROM_SAME_IP == 'no') {
  add_filter('forminator_custom_form_submit_errors', 'vtv_forminator_submit_errors_sameIP', 15, 3);
  add_filter('forminator_custom_form_invalid_form_message', 'vtv_forminator_invalid_form_message_sameIP', 10, 2);
}

function get_table_name_with_prefix($tablename_without_prefix)
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
  };

  throw new Exception("Table $prefixed_tablename not found.", 1);
}

function vtv_log($string)
{
  file_put_contents(__DIR__ . '/vtv.log', json_encode($string) . "\n", FILE_APPEND);
}
