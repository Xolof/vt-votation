<?php

if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}

function vtv_process_settings(): void
{
  if (isset($_POST['vtv_nonce']) && wp_verify_nonce($_POST['vtv_nonce'], 'vtv_nonce')) {
    if (vtv_process_blocked_ips() == false ||
        vtv_process_books() == false ||
        vtv_process_multiple_votes_from_same_ip() == false) {
      exit('option update failed');
    }

    vtv_custom_redirect('success', 'Inställningarna sparades');
    exit;
  }

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

function vtv_process_blocked_ips(): bool
{
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

  return vtv_process_option('vt_votation_blocked_ips', $blocked_ips);
}

function vtv_process_books(): bool
{
  $votation_forminator_form_ids = isset($_POST['books']) ? array_keys($_POST['books']) : [];
  foreach ($votation_forminator_form_ids as $form_id) {
    if (!is_numeric($form_id)) {
      exit('Invalid form data');
    }
  }

  return vtv_process_option('vt_votation_forminator_form_ids', $votation_forminator_form_ids);
}

function vtv_process_multiple_votes_from_same_ip(): bool
{
  $allow_multiple_votes_from_same_ip = $_POST['allow_multiple_votes_from_same_ip'];
  if (isset($allow_multiple_votes_from_same_ip)) {
    if (!in_array($allow_multiple_votes_from_same_ip, ['yes', 'no'])) {
      exit('option update failed');
    }

    return vtv_process_option('allow_multiple_votes_from_same_ip', $allow_multiple_votes_from_same_ip);
  }
  return true;
}

function vtv_process_option(string $option_name, array|string $post_data): bool
{
  $result = true;
  if (!get_option($option_name)) {
    $result = add_option($option_name, json_encode($post_data), '', 'no');
  }
  if (json_decode(get_option($option_name)) != $post_data) {
    $result = update_option($option_name, json_encode($post_data), '', 'no');
  }
  return $result;
}

function vtv_custom_redirect(string $status, string $message): void
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
