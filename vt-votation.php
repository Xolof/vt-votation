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

function vt_session_init()
{
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  session_write_close();
}

require_once (__DIR__ . '/functions/menu_page.php');
require_once (__DIR__ . '/functions/render_menu_sub_pages.php');
require_once (__DIR__ . '/functions/get_results.php');
require_once (__DIR__ . '/functions/process_settings.php');
require_once (__DIR__ . '/functions/admin_notices.php');
require_once (__DIR__ . '/functions/forminator_mods.php');
require_once (__DIR__ . '/functions/table_prefix.php');
require_once (__DIR__ . '/functions/vtv_log.php');

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

add_action('init', 'vt_session_init');

add_action('admin_menu', 'vtv_admin_page');
add_action('admin_post_vtv_form_response', 'vtv_process_settings');
add_action('admin_notices', 'vtv_print_plugin_admin_notices');

add_filter('forminator_custom_form_submit_errors', 'vtv_forminator_submit_errors_block', 51, 3);
add_filter('forminator_custom_form_invalid_form_message', 'vtv_forminator_invalid_form_message_block', 50, 3);

add_filter('forminator_custom_form_submit_errors', 'vtv_forminator_submit_errors_email', 31, 3);
add_filter('forminator_custom_form_invalid_form_message', 'vtv_forminator_invalid_form_message_email', 30, 3);

if (ALLOW_MULTIPLE_VOTES_FROM_SAME_IP == 'no') {
  add_filter('forminator_custom_form_submit_errors', 'vtv_forminator_submit_errors_sameIP', 41, 3);
  add_filter('forminator_custom_form_invalid_form_message', 'vtv_forminator_invalid_form_message_sameIP', 40, 2);
}
