<?php

/*
 * Plugin Name: VT Votation
 * Description: Anpassningar för omröstning om årets mest olämpliga barnbok.
 * Version: 0.1.0
 * Author: Liberdev
 * Author URI: http://liberdev.se
 * Text Domain: vt-votation
 */

if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}

function vtv_log($string)
{
  file_put_contents(__DIR__ . '/vtv.log', json_encode($string) . "\n", FILE_APPEND);
}

define('VOTATION_PAGE_ID', get_option('vt_votation_page'));
define('VOTATION_FORM_ID', get_option('vt_votation_forminator_form'));
define('DISALLOW_MULTIPLE_VOTES_FROM_SAME_IP', false);
define('ONLY_VOTE_ONE_TIME_MESSAGE', __('Du kan bara rösta en gång.', 'forminator'));
define('VOTATION_PAGE_TITLE', 'Årets olämpligaste barnbok 0.2.0');
define('VOTATION_FORM_NAME', 'Votation Form');

register_activation_hook(
  __FILE__,
  'vtv_activate'
);
register_deactivation_hook(
  __FILE__,
  'vtv_deactivate'
);

function vtv_activate()
{
  $pages = get_pages();
  $page_id = null;
  foreach ($pages as $page) {
    if ($page->post_title == VOTATION_PAGE_TITLE) {
      $page_id = $page->ID;
    }
  }
  if (empty($page_id)) {
    $page_title = VOTATION_PAGE_TITLE;
    $page_id = wp_insert_post(
      array(
        'comment_status' => 'close',
        'ping_status' => 'close',
        'post_author' => 1,
        'post_title' => $page_title,
        'post_name' => sanitize_title($page_title),
        'post_status' => 'publish',
        'post_content' => '',
        'post_type' => 'page',
        'page_template' => 'votation_page.php'
      )
    );
  }
  vtv_add_form();
}

function vtv_get_custom_posts_by_type($type)
{
  $results = [];
  $posts = $posts = get_posts([
    'post_type' => 'olamplig-bok',
    'post_status' => 'any',
    'numberposts' => 999999999
  ]);
  foreach ($posts as $post) {
    if ($post->post_name != '__trashed') {
      $results[] = $post;
    };
  }
  return $results;
}

function vtv_add_form()
{
  $book_posts = vtv_get_custom_posts_by_type('olamplig-bok');
  $options = array();
  foreach ($book_posts as $post) {
    $options[] = array(
      'label' => $post->post_title,
      'value' => $post->post_name,
      'id' => $post->ID
    );
  }

  $wrappers = array(
    array(
      'wrapper_id' => 'wrapper-1511378776546-9087',
      'fields' => array(
        array(
          'element_id' => 'books',
          'type' => 'checkbox',
          'cols' => '12',
          'required' => 'true',
          'field_label' => 'Böcker',
          'options' => $options
        ),
      ),
    ),
  );

  $settings = array(
    'formName' => VOTATION_FORM_NAME,
    'thankyou' => 'true',
    'thankyou-message' => __('Tack för din röst.', 'vt-votation'),
    'use-custom-submit' => 'true',
    'custom-submit-text' => __('Rösta', 'vt-votation'),
    'use-custom-invalid-form' => 'true',
    'custom-invalid-form-message' => __('Fel: Ogiltigt ifyllt formulär!', 'vt-votation')
  );

  Forminator_API::add_form(
    VOTATION_FORM_NAME,
    $wrappers,
    $settings
  );
}

function vtv_deactivate()
{
  $pages = get_pages();
  $page_id = null;
  foreach ($pages as $page) {
    if ($page->post_title == VOTATION_PAGE_TITLE) {
      $page_id = $page->ID;
    }
  }
  if ($page_id) {
    wp_delete_post($page_id);
  }

  $form_to_delete = vtv_get_forminator_form_by_name(VOTATION_FORM_NAME);
  Forminator_API::delete_form($form_to_delete->id);
}

function vtv_get_forminator_form_by_name($name)
{
  $result = null;
  $forms = Forminator_API::get_forms();
  foreach ($forms as $form) {
    if ($form->settings['formName'] == $name) {
      $result = $form;
    }
  }
  return $result;
}

add_action('acf/save_post', 'vtv_save_post');
add_action('untrash_post', 'vtv_save_post', 10);

function vtv_save_post($post_id)
{
  if (get_post_type($post_id) == 'olamplig-bok') {
    $custom_field_values = get_fields($post_id);
    $title = $custom_field_values['titel'];
    $form_to_update = vtv_get_forminator_form_by_name(VOTATION_FORM_NAME);
    $books_field = Forminator_API::get_form_field($form_to_update->id, 'books', true);
    $options = $books_field['options'];
    $new_options = array();
    $post_id_already_exists = false;
    foreach ($options as $old_option) {
      $new_option = array();
      if ($old_option['id'] == $post_id) {
        $post_id_already_exists = true;
        $new_option['label'] = $title;
        $new_option['value'] = sanitize_title($title);
        $new_option['id'] = $post_id;
      } else {
        $new_option['label'] = $old_option['label'];
        $new_option['value'] = $old_option['value'];
        $new_option['id'] = $old_option['id'];
      };

      $new_options[] = $new_option;
    }

    if (!$post_id_already_exists) {
      $new_options[] = array(
        'label' => $title,
        'value' => sanitize_title($title),
        'id' => $post_id
      );
    }

    $books_field['options'] = $new_options;
    $form_id = $form_to_update->id;
    Forminator_API::update_form_field($form_id, 'books', $books_field);
  }
}

add_action('wp_trash_post', 'vtv_trash_post', 10);

function vtv_trash_post($post_id)
{
  if (get_post_type($post_id) == 'olamplig-bok') {
    $custom_field_values = get_fields($post_id);
    $form_to_update = vtv_get_forminator_form_by_name(VOTATION_FORM_NAME);
    $title = $custom_field_values['titel'];
    $books_field = Forminator_API::get_form_field($form_to_update->id, 'books', true);
    $options = $books_field['options'];
    $new_options = array();

    foreach ($options as $old_option) {
      $new_option = array();

      if ($old_option['id'] != $post_id) {
        $new_option['label'] = $old_option['label'];
        $new_option['value'] = $old_option['value'];
        $new_option['id'] = $old_option['id'];
        $new_options[] = $new_option;
      };
    }

    $books_field['options'] = $new_options;
    vtv_log($books_field);

    $form_id = $form_to_update->id;
    Forminator_API::update_form_field($form_id, 'books', $books_field);
  }
}

// Add our custom template to the admin's templates dropdown
add_filter('theme_page_templates', 'vtv_template_as_option', 10, 3);

function vtv_template_as_option($page_templates, $theme, $post)
{
  $page_templates['votation_page.php'] = 'Votation Page';
  return $page_templates;
}

// When our custom template has been chosen then display it for the page
add_filter('template_include', 'vtv_load_template', 99);

function vtv_load_template($template)
{
  global $post;
  if (!$post) {
    return $template;
  }
  $custom_template_slug = 'votation_page.php';
  $page_template_slug = get_page_template_slug($post->ID);
  if ($page_template_slug == $custom_template_slug) {
    return plugin_dir_path(__FILE__) . 'templates/' . $custom_template_slug;
  }
  return $template;
}

add_action('wp_enqueue_scripts', 'vitillsammans_enqueue_scripts');

function vitillsammans_enqueue_scripts()
{
  if (get_the_ID() == VOTATION_PAGE_ID) {
    wp_enqueue_script(
      'votation',
      plugins_url('/assets/js/votation.js', __FILE__),
      array(),
      '1.0',
      true
    );
    wp_enqueue_style(
      'votation',
      plugins_url('/assets/css/votation.css', __FILE__)
    );
  };
}

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

add_action('admin_post_vtv_form_response', 'the_form_response');

function the_form_response()
{
  if (isset($_POST['vtv_add_user_meta_nonce']) && wp_verify_nonce($_POST['vtv_add_user_meta_nonce'], 'vtv_add_user_meta_form_nonce')) {
    $votation_page = $_POST['vtv']['votation_page'];
    $votation_forminator_form = $_POST['vtv']['votation_forminator_form'];

    if (!is_numeric($votation_page) || !is_numeric($votation_forminator_form)) {
      exit('Invalid form data');
    }

    $result = false;

    if (!get_option('vt_votation_page')) {
      $result = add_option('vt_votation_page', $votation_page, '', 'no');
    } else if (get_option('vt_votation_page') != $votation_page) {
      $result = update_option('vt_votation_page', $votation_page, '', 'no');
    } else {
      // Nothing to update
      $result = true;
    }

    if (!get_option('vt_votation_forminator_form')) {
      $result = add_option('vt_votation_forminator_form', $votation_forminator_form, '', 'no');
    } else if (get_option('vt_votation_forminator_form') != $votation_forminator_form) {
      $result = update_option('vt_votation_forminator_form', $votation_forminator_form, '', 'no');
    } else {
      // Nothing to update
      $result = true;
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

function render_votation_results()
{
  global $wpdb;
  $votation_result_query = <<<EOD
      SELECT
        meta_value as books,
        COUNT(*) as num_votes
      FROM wp_frmt_form_entry
      LEFT JOIN wp_frmt_form_entry_meta
      USING(entry_id)
      WHERE
        form_id = %d AND meta_key="books"
     GROUP BY meta_value;
    EOD;
  $votation_results_db = $wpdb->get_results(
    $wpdb->prepare(
      $votation_result_query,
      VOTATION_FORM_ID
    )
  );
  $votation_results_array = [];
  require_once (__DIR__ . '/templates/votation_results.php');
}

function checkIfEmailHasAlreadyVoted($email)
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
           AND form_id=%d AND meta_value="%s"
        ) as email_already_voted;
    EOD;
  $result = $wpdb->get_results(
    $wpdb->prepare(
      $email_already_voted_query,
      VOTATION_FORM_ID,
      $email
    )
  );
  return $result;
}

add_filter('forminator_custom_form_submit_errors', function ($submit_errors, $form_id, $field_data_array) {
  if (intval($form_id) == VOTATION_FORM_ID) {
    $user_ip = Forminator_Geo::get_user_ip();
    file_put_contents(__DIR__ . '/votation-ip.log', date('Y-m-d H:i:s') . ' ' . $user_ip . "\n", FILE_APPEND);
    $email = $field_data_array[1]['value'];
    $email_already_voted_result = checkIfEmailHasAlreadyVoted($email);
    if ($email_already_voted_result[0]->email_already_voted == '1') {
      $submit_errors[] = ONLY_VOTE_ONE_TIME_MESSAGE;
    };
  }
  return $submit_errors;
}, 15, 3);

add_filter('forminator_custom_form_invalid_form_message', function ($invalid_form_message, $form_id) {
  if ($form_id == VOTATION_FORM_ID) {
    $email = $_POST['email-1'];
    $email_already_voted_result = checkIfEmailHasAlreadyVoted($email);
    if ($email_already_voted_result[0]->email_already_voted == '1') {
      $invalid_form_message = ONLY_VOTE_ONE_TIME_MESSAGE;
    };
    return $invalid_form_message;
  }
  return $invalid_form_message;
}, 10, 3);

if (DISALLOW_MULTIPLE_VOTES_FROM_SAME_IP == true) {
  add_filter('forminator_custom_form_submit_errors', function ($submit_errors, $form_id, $field_data_array) {
    $message = __('Du kan bara rösta en gång.', 'forminator');
    if (intval($form_id) == VOTATION_FORM_ID) {
      $user_ip = Forminator_Geo::get_user_ip();
      file_put_contents(__DIR__ . '/votation-ip.log', date('Y-m-d H:i:s') . ' ' . $user_ip . "\n", FILE_APPEND);
      if (!empty($user_ip)) {
        $last_entry = Forminator_Form_Entry_Model::get_last_entry_by_ip_and_form($form_id, $user_ip);
        if (!empty($last_entry)) {
          $submit_errors[]['submit'] = $message;
        }
      }
    }
    return $submit_errors;
  }, 15, 3);

  add_filter('forminator_custom_form_invalid_form_message', function ($invalid_form_message, $form_id) {
    if ($form_id != VOTATION_FORM_ID) {
      return $invalid_form_message;
    }
    $user_ip = Forminator_Geo::get_user_ip();
    if (!empty($user_ip)) {
      $last_entry = Forminator_Form_Entry_Model::get_last_entry_by_ip_and_form($form_id, $user_ip);
      if (!empty($last_entry)) {
        $invalid_form_message = __('Du kan bara rösta en gång.', 'forminator');
      }
    }
    return $invalid_form_message;
  }, 10, 2);
}
