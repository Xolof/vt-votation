<?php

if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}

function get_votation_results(): array
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

function get_votes_per_ip_results(): array
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

  $results = $wpdb->get_results(
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

  return $results;
}

function get_votation_form_id_placeholders(): string
{
  $votation_form_id_placeholders = '';
  foreach (VOTATION_FORM_IDS as $id) {
    $votation_form_id_placeholders .= '%d,';
  }
  return rtrim($votation_form_id_placeholders, ',');
}
