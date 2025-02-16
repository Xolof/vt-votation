<?php

if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}

function vtv_forminator_submit_errors_block($submit_errors, $form_id, $field_data_array)
{
  if (in_array(intval($form_id), VOTATION_FORM_IDS)) {
    $user_ip = Forminator_Geo::get_user_ip();
    if (in_array($user_ip, IP_BLOCK_LIST)) {
      $submit_errors[] = IP_BLOCKED_MESSAGE;
    }
  }
  return $submit_errors;
}

function vtv_forminator_invalid_form_message_block($invalid_form_message, $form_id)
{
  if (in_array(intval($form_id), VOTATION_FORM_IDS)) {
    $user_ip = Forminator_Geo::get_user_ip();
    if (in_array($user_ip, IP_BLOCK_LIST)) {
      return IP_BLOCKED_MESSAGE;
    }
  }
  return $invalid_form_message;
}

function vtv_forminator_submit_errors_email($submit_errors, $form_id, $field_data_array)
{
  if (in_array(intval($form_id), VOTATION_FORM_IDS)) {
    $email = $field_data_array[0]['value'];
    $email_already_voted_result = checkIfEmailHasAlreadyVoted($email, $form_id);
    if ($email_already_voted_result == '1') {
      $submit_errors[] = ONLY_VOTE_ONE_TIME_MESSAGE;
    }
  }
  return $submit_errors;
}

function vtv_forminator_invalid_form_message_email($invalid_form_message, $form_id)
{
  if (in_array(intval($form_id), VOTATION_FORM_IDS)) {
    $email = $_POST['email-1'];
    $email_already_voted_result = checkIfEmailHasAlreadyVoted($email, $form_id);
    if ($email_already_voted_result == '1') {
      $invalid_form_message = ONLY_VOTE_ONE_TIME_MESSAGE;
    }
    return $invalid_form_message;
  }
  return $invalid_form_message;
}

function vtv_forminator_submit_errors_sameIP($submit_errors, $form_id, $field_data_array)
{
  if (!in_array(intval($form_id), VOTATION_FORM_IDS)) {
    return $submit_errors;
  }
  $message = getSameIPErrorMessage($form_id);
  if ($message) {
    $submit_errors[]['submit'] = $message;
  }
  return $submit_errors;
}

function vtv_forminator_invalid_form_message_sameIP($invalid_form_message, $form_id)
{
  if (!in_array(intval($form_id), VOTATION_FORM_IDS)) {
    return $invalid_form_message;
  }
  $message = getSameIPErrorMessage($form_id);
  if ($message) {
    return $message;
  }
  return $invalid_form_message;
}

function checkIfEmailHasAlreadyVoted($email, $form_id)
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
  return $result[0]->email_already_voted;
}

function getSameIPErrorMessage($form_id)
{
  $message = null;
  $user_ip = Forminator_Geo::get_user_ip();
  if (!empty($user_ip)) {
    $last_entry = Forminator_Form_Entry_Model::get_last_entry_by_ip_and_form($form_id, $user_ip);
    if (!empty($last_entry)) {
      $message = ONLY_VOTE_ONE_TIME_PER_IP_MESSAGE;
    }
  }
  return $message;
}
