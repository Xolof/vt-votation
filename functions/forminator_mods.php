<?php

if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}

function vtv_forminator_submit_errors_block(array $submit_errors, string $form_id, array $field_data_array): array
{
  if (in_array(intval($form_id), VOTATION_FORM_IDS)) {
    $user_ip = Forminator_Geo::get_user_ip();
    if (in_array($user_ip, IP_BLOCK_LIST)) {
      $submit_errors[] = IP_BLOCKED_MESSAGE;
      $_SESSION['IP_BLOCKED'] = true;
    }
  }
  return $submit_errors;
}

function vtv_forminator_invalid_form_message_block(string $invalid_form_message, string $form_id): string
{
  if (in_array(intval($form_id), VOTATION_FORM_IDS)) {
    if ($_SESSION['IP_BLOCKED']) {
      return IP_BLOCKED_MESSAGE;
    }
  }
  return $invalid_form_message;
}

function vtv_forminator_submit_errors_email(array $submit_errors, string $form_id, array $field_data_array): array
{
  if (in_array(intval($form_id), VOTATION_FORM_IDS)) {
    $email = $field_data_array[0]['value'];
    if (emailHasAlreadyVoted($email, $form_id)) {
      $submit_errors[] = ONLY_VOTE_ONE_TIME_MESSAGE;
      $_SESSION['EMAIL_ALREADY_VOTED'] = true;
    }
  }
  return $submit_errors;
}

function vtv_forminator_invalid_form_message_email(string $invalid_form_message, string $form_id): string
{
  if (in_array(intval($form_id), VOTATION_FORM_IDS)) {
    if ($_SESSION['EMAIL_ALREADY_VOTED']) {
      $invalid_form_message = ONLY_VOTE_ONE_TIME_MESSAGE;
    }
  }
  return $invalid_form_message;
}

function vtv_forminator_submit_errors_sameIP(array $submit_errors, string $form_id, array $field_data_array): array
{
  if (!in_array(intval($form_id), VOTATION_FORM_IDS)) {
    return $submit_errors;
  }
  if (IpAlreadyVoted($form_id)) {
    $submit_errors[]['submit'] = ONLY_VOTE_ONE_TIME_PER_IP_MESSAGE;
    $_SESSION['IP_ALREADY_VOTED'] = true;
  }
  return $submit_errors;
}

function vtv_forminator_invalid_form_message_sameIP(string $invalid_form_message, string $form_id): string
{
  if (!in_array(intval($form_id), VOTATION_FORM_IDS)) {
    return $invalid_form_message;
  }
  if ($_SESSION['IP_ALREADY_VOTED']) {
    return ONLY_VOTE_ONE_TIME_PER_IP_MESSAGE;
  }
  return $invalid_form_message;
}

function emailHasAlreadyVoted(string $email, string $form_id): bool
{
  global $wpdb;
  $prefix = $wpdb->prefix;
  $frmt_form_entry = get_table_name_with_prefix('frmt_form_entry');
  $frmt_form_entry_meta = get_table_name_with_prefix('frmt_form_entry_meta');

  $email_already_voted_query = <<<EOD
    SELECT
      EXISTS(
        SELECT meta_value
          FROM %i
          LEFT JOIN %i
            USING(entry_id)
            WHERE meta_key="email-1"
              AND form_id = %d
              AND meta_value="%s"
      ) as email_already_voted;
    EOD;
  $result = $wpdb->get_results(
    $wpdb->prepare(
      $email_already_voted_query,
      [$frmt_form_entry, $frmt_form_entry_meta, $form_id, $email]
    )
  );
  if ($result[0]->email_already_voted == '1') {
    return true;
  }
  return false;
}

function IpAlreadyVoted(string $form_id): bool
{
  $user_ip = Forminator_Geo::get_user_ip();
  if (!empty($user_ip)) {
    $last_entry = Forminator_Form_Entry_Model::get_last_entry_by_ip_and_form($form_id, $user_ip);
    if (!empty($last_entry)) {
      return true;
    }
  }
  return false;
}
