<?php

function vtv_admin_page()
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
