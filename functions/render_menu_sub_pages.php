<?php

if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}

function render_votation_manual(): void
{
  require_once (__DIR__ . '/../templates/votation_manual.php');
}

function render_votation_settings(): void
{
  $vt_votation_pages = get_pages();
  $vt_votation_forminator_forms = Forminator_API::get_forms();
  require_once (__DIR__ . '/../templates/votation_settings.php');
}

function render_votation_results(): void
{
  if (!VOTATION_FORM_IDS) {
    require_once (__DIR__ . '/../templates/votation_results.php');
    return;
  }
  $votation_results_db = get_votation_results();
  $votes_per_ip_results_db = get_votes_per_ip_results();
  require_once (__DIR__ . '/../templates/votation_results.php');
}
