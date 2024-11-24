<?php

if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}

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

function vtv_process_settings()
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
          vtv_custom_redirect(
            'error',
            'Ogiltigt värde för IP-adresser. Ange blockerade IP-adresser separerade med komma.'
          );
          exit;
        }
      }
    }

    $result = vtv_process_option('vt_votation_blocked_ips', $blocked_ips);

    $votation_forminator_form_ids = isset($_POST['books']) ? array_keys($_POST['books']) : [];
    foreach ($votation_forminator_form_ids as $form_id) {
      if (!is_numeric($form_id)) {
        exit('Invalid form data');
      }
    }

    $result = vtv_process_option('vt_votation_forminator_form_ids', $votation_forminator_form_ids);

    $allow_multiple_votes_from_same_ip = $_POST['allow_multiple_votes_from_same_ip'];
    if (isset($allow_multiple_votes_from_same_ip)) {
      if (!in_array($allow_multiple_votes_from_same_ip, ['yes', 'no'])) {
        exit('option update failed');
      }

      $result = vtv_process_option('allow_multiple_votes_from_same_ip', $allow_multiple_votes_from_same_ip);
    }

    if ($result == false) {
      exit('option update failed');
    }

    vtv_custom_redirect('success', 'Inställningarna sparades');
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

function vtv_custom_redirect($status, $message)
{
  wp_redirect(
    esc_url_raw(
      add_query_arg(
        array(
          'vtv_admin_notice_status' => $status,
          'vtv_admin_notice_message' => $message
        ),
        admin_url(
          'admin.php?page=render_votation_settings'
        )
      )
    )
  );
}

function get_votation_form_id_placeholders()
{
  $votation_form_id_placeholders = '';
  foreach (VOTATION_FORM_IDS as $id) {
    $votation_form_id_placeholders .= '%d,';
  }
  return rtrim($votation_form_id_placeholders, ',');
}

function get_votation_results()
{
  global $wpdb;
  $prefix = $wpdb->prefix;
  $postmeta = get_table_name_with_prefix('postmeta');
  $frmt_form_entry = get_table_name_with_prefix('frmt_form_entry');
  $frmt_form_entry_meta = get_table_name_with_prefix('frmt_form_entry_meta');

  $votation_form_id_placeholders = get_votation_form_id_placeholders();
  $votation_result_query = <<<EOD
      SELECT
        form_id, COUNT(*) as num_votes, %i.meta_value as book
      FROM %i
        LEFT JOIN %i
          USING(entry_id)
        LEFT JOIN %i
          ON post_id=form_id 
        WHERE
          form_id IN ($votation_form_id_placeholders)
          AND %i.meta_key="email-1"
        GROUP BY form_id
      ;
    EOD;
  $results = $wpdb->get_results(
    $wpdb->prepare(
      $votation_result_query,
      array_merge(
        [$postmeta,
          $frmt_form_entry,
          $frmt_form_entry_meta,
          $postmeta],
        VOTATION_FORM_IDS,
        [$frmt_form_entry_meta]
      )
    )
  );
  return $results;
}

function get_votes_per_ip_results()
{
  global $wpdb;
  $postmeta = get_table_name_with_prefix('postmeta');
  $frmt_form_entry = get_table_name_with_prefix('frmt_form_entry');
  $frmt_form_entry_meta = get_table_name_with_prefix('frmt_form_entry_meta');

  $votation_form_id_placeholders = get_votation_form_id_placeholders();
  $votes_per_ip_query = <<<EOD
      SELECT
      %i.meta_value as IP_address,
      COUNT(*) as num_votes
        FROM %i
          LEFT JOIN %i
            USING(entry_id)
          WHERE
            form_id IN ($votation_form_id_placeholders)
            AND %i.meta_key="_forminator_user_ip"
          GROUP BY IP_address;
    EOD;
  return $wpdb->get_results(
    $wpdb->prepare(
      $votes_per_ip_query,
      array_merge(
        [$frmt_form_entry_meta,
          $frmt_form_entry,
          $frmt_form_entry_meta],
        VOTATION_FORM_IDS,
        [$frmt_form_entry_meta]
      )
    )
  );
}
