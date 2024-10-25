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

function vtv_log($string)
{
  file_put_contents(__DIR__ . '/vtv.log', json_encode($string) . "\n", FILE_APPEND);
}

define('ALLOW_MULTIPLE_VOTES_FROM_SAME_IP', json_decode(get_option('allow_multiple_votes_from_same_ip')) ?? 'yes');
define('IP_BLOCK_LIST', json_decode(get_option('vt_votation_blocked_ips')) ?? []);
define('IP_BLOCKED_MESSAGE', 'Din IP-adress har blockerats.');
define('ONLY_VOTE_ONE_TIME_MESSAGE', __('Du kan bara rösta en gång.', 'forminator'));
define('VOTATION_FORM_IDS', json_decode(get_option('vt_votation_forminator_form_ids')) ?? []);

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

function my_admin_page()
{
  add_menu_page(
    'Årets olämpligaste barnbok',
    'Årets olämpligaste barnbok',
    'manage_options',
    'vt-votation',
    'render_votation_results',
    'dashicons-book',
    3
  );
  add_submenu_page(
    'vt-votation',
    'Resultat',
    'Resultat',
    'manage_options',
    'render_votation_results',
    'render_votation_results'
  );
  add_submenu_page(
    'vt-votation',
    'Inställningar',
    'Inställningar',
    'manage_options',
    'render_votation_settings',
    'render_votation_settings'
  );
  add_submenu_page(
    'vt-votation',
    'Manual',
    'Manual',
    'manage_options',
    'render_votation_manual',
    'render_votation_manual'
  );
  remove_submenu_page('vt-votation', 'vt-votation');
}

add_action('admin_menu', 'my_admin_page');

function render_votation_manual()
{
  require_once (__DIR__ . '/templates/votation_manual.php');
}

function render_votation_settings()
{
  $vt_votation_pages = get_pages();
  $vt_votation_forminator_forms = Forminator_API::get_forms();
  require_once (__DIR__ . '/templates/votation_settings.php');
}

add_action('admin_post_vtv_form_response', 'process_settings');

function vtv_process_option($option_name, $post_data)
{
    if (!get_option($option_name)) {
      $result = add_option($option_name, json_encode($post_data), '', 'no');
    } else if (json_decode(get_option($option_name)) != $post_data) {
      $result = update_option($option_name, json_encode($post_data), '', 'no');
    } else {
      // Nothing to update
      $result = true;
    }
    return $result;
}

function process_settings()
{
  if (isset($_POST['vtv_add_user_meta_nonce']) && wp_verify_nonce($_POST['vtv_add_user_meta_nonce'], 'vtv_add_user_meta_form_nonce')) {
    $result = false;

    $blocked_ips = $_POST['blocked_ips'] ?? [];
    if (gettype($blocked_ips) != 'string') {
      exit('Invalid IP value submitted. Blocked IPs should be a string.');
    }

    $blocked_ips = explode(',', $blocked_ips);
    if ($blocked_ips[0] != '') {
      foreach ($blocked_ips as $ip) {
        if (
          !(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ||
            filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
        ) {
          exit('Invalid IP value submitted. Blocked IPs should be a comma separated list of IP addresses.');
        }
      }
    }
    
    $result = vtv_process_option("vt_votation_blocked_ips", $blocked_ips);

    $votation_forminator_form_ids = isset($_POST['books']) ? array_keys($_POST['books']) : [];
    foreach ($votation_forminator_form_ids as $form_id) {
      if (!is_numeric($form_id)) {
        exit('Invalid form data');
      }
    }

    $result = vtv_process_option("vt_votation_forminator_form_ids", $votation_forminator_form_ids);

    $allow_multiple_votes_from_same_ip = $_POST['allow_multiple_votes_from_same_ip'];
    if (isset($allow_multiple_votes_from_same_ip)) {
      if (!in_array($allow_multiple_votes_from_same_ip, ['yes', 'no'])) {
        exit('option update failed');
      }

      $result = vtv_process_option("allow_multiple_votes_from_same_ip", $allow_multiple_votes_from_same_ip);
    }

    if ($result == false) {
      exit('option update failed');
    }

    custom_redirect('success');
    exit;
  } else {
    wp_die(
      __('Invalid nonce specified',
        'vt-votation'),
      __('Error', 'vt-votation'),
      array(
        'response' => 403,
        'back_link' => 'admin.php?page=vt-votation'
      )
    );
  }
}

function custom_redirect($status)
{
  wp_redirect(
    esc_url_raw(
      add_query_arg(
        array(
          'vtv_admin_add_notice' => $status
        ),
        admin_url(
          'admin.php?page=render_votation_settings'
        )
      )
    )
  );
}

function print_plugin_admin_notices()
{
  if (isset($_REQUEST['vtv_admin_add_notice'])) {
    if ($_REQUEST['vtv_admin_add_notice'] === 'success') {
      ?>
        <div class="notice notice-success is-dismissible">
          <p><b>Inställningarna sparades.</b></p>
        </div>
  <?php
    }
  } else {
    return;
  }
}

add_action('admin_notices', 'print_plugin_admin_notices');

function get_votation_form_id_placeholders()
{
  $votation_form_id_placeholders = '';
  foreach (VOTATION_FORM_IDS as $id) {
    $votation_form_id_placeholders .= '%d,';
  }
  return rtrim($votation_form_id_placeholders, ',');
}

function render_votation_results()
{
  if (!VOTATION_FORM_IDS) {
    require_once (__DIR__ . '/templates/votation_results.php');
    return;
  }
  global $wpdb;
  $votation_form_id_placeholders = get_votation_form_id_placeholders();
  $votation_result_query = <<<EOD
      SELECT
        form_id, COUNT(*) as num_votes,
        SUBSTRING_INDEX(
          SUBSTRING_INDEX(wp_postmeta.meta_value, 'formName";s:5:"', -1),
          '";s:7:"version";',
          1
        ) as book    
      FROM wp_frmt_form_entry
        LEFT JOIN wp_frmt_form_entry_meta
          USING(entry_id)
        LEFT JOIN wp_postmeta
          ON post_id=form_id 
        WHERE
          form_id IN ($votation_form_id_placeholders)
          AND wp_frmt_form_entry_meta.meta_key="email-1"
        GROUP BY form_id
      ;
    EOD;
  $votation_results_db = $wpdb->get_results(
    $wpdb->prepare(
      $votation_result_query,
      VOTATION_FORM_IDS
    )
  );

  $votes_per_ip_query = <<<EOD
      SELECT
      wp_frmt_form_entry_meta.meta_value as IP_address,
      COUNT(*) as num_votes
        FROM wp_frmt_form_entry
          LEFT JOIN wp_frmt_form_entry_meta
            USING(entry_id)
          WHERE
            form_id IN ($votation_form_id_placeholders)
            AND wp_frmt_form_entry_meta.meta_key="_forminator_user_ip"
          GROUP BY IP_address;
    EOD;
  $votes_per_ip_results_db = $wpdb->get_results(
    $wpdb->prepare(
      $votes_per_ip_query,
      VOTATION_FORM_IDS
    )
  );

  require_once (__DIR__ . '/templates/votation_results.php');
}

function checkIfEmailHasAlreadyVoted($email, $form_id)
{
  global $wpdb;
  $email_already_voted_query = <<<EOD
    SELECT
      EXISTS(
        SELECT meta_value
          FROM wp_frmt_form_entry
          LEFT JOIN wp_frmt_form_entry_meta
            USING(entry_id)
            WHERE meta_key="email-1"
              AND form_id = %d
              AND meta_value="%s"
      ) as email_already_voted;
    EOD;
  $result = $wpdb->get_results(
    $wpdb->prepare(
      $email_already_voted_query,
      [$form_id, $email]
    )
  );
  return $result[0]->email_already_voted;
}

add_filter('forminator_custom_form_submit_errors', function ($submit_errors, $form_id, $field_data_array) {
  if (in_array(intval($form_id), VOTATION_FORM_IDS)) {
    $user_ip = Forminator_Geo::get_user_ip();
    if (in_array($user_ip, IP_BLOCK_LIST)) {
      $submit_errors[] = IP_BLOCKED_MESSAGE;
    }

    $email = $field_data_array[0]['value'];
    $email_already_voted_result = checkIfEmailHasAlreadyVoted($email, $form_id);
    if ($email_already_voted_result == '1') {
      $submit_errors[] = ONLY_VOTE_ONE_TIME_MESSAGE;
    };
  }
  return $submit_errors;
}, 15, 3);

add_filter('forminator_custom_form_invalid_form_message', function ($invalid_form_message, $form_id) {
  if (in_array(intval($form_id), VOTATION_FORM_IDS)) {
    $user_ip = Forminator_Geo::get_user_ip();
    if (in_array($user_ip, IP_BLOCK_LIST)) {
      return IP_BLOCKED_MESSAGE;
    }

    $email = $_POST['email-1'];
    $email_already_voted_result = checkIfEmailHasAlreadyVoted($email, $form_id);
    if ($email_already_voted_result == '1') {
      $invalid_form_message = ONLY_VOTE_ONE_TIME_MESSAGE;
    };
    return $invalid_form_message;
  }
  return $invalid_form_message;
}, 10, 3);

function getSameIPErrorMessage($form_id)
{
    $message = null;
    $user_ip = Forminator_Geo::get_user_ip();
    if (!empty($user_ip)) {
      $last_entry = Forminator_Form_Entry_Model::get_last_entry_by_ip_and_form($form_id, $user_ip);
      if (!empty($last_entry)) {
        $message = 'Du kan bara rösta en gång!';
      }
    }
    return $message;
}

if (ALLOW_MULTIPLE_VOTES_FROM_SAME_IP == 'no') {
  add_filter('forminator_custom_form_submit_errors', function ($submit_errors, $form_id, $field_data_array) {
    if (!in_array(intval($form_id), VOTATION_FORM_IDS)) {
      return $submit_errors;
    }
    $message = getSameIPErrorMessage($form_id);
    if ($message) {
      $submit_errors[]['submit'] = $message;
    }
    return $submit_errors;
  }, 15, 3);

  add_filter('forminator_custom_form_invalid_form_message', function ($invalid_form_message, $form_id) {
    if (!in_array(intval($form_id), VOTATION_FORM_IDS)) {
      return $invalid_form_message;
    }
    $message = getSameIPErrorMessage($form_id);
    if ($message) {
      return $message;
    }
    return $invalid_form_message;
  }, 10, 2);
}
