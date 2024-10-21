<?php
if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}

$existing_votation_page_id = VOTATION_PAGE_ID;
$existing_votation_form_id = VOTATION_FORM_ID;
?>

<h1><?= __('Inställningar', 'my-textdomain'); ?></h1>

<?php $vt_votation_plugin_name = 'vt-votation'; ?>

<?php if (current_user_can('manage_options')): ?>
  <?php $vtv_add_meta_nonce = wp_create_nonce('vtv_add_user_meta_form_nonce'); ?>        
  <div class="vtv_add_user_meta_form">
    <form 
      action="<?= esc_url(admin_url('admin-post.php')); ?>"
      method="post"
      id="vtv_add_user_meta_form"
    >      
      <label for="vtv_votation_page">Sida för omröstning</label>
      <br>
      <select required id="vtv_votation_page" name="vtv[votation_page]">
        <option value="">Välj en sida</option>
        <?php foreach ($vt_votation_pages as $page): ?>
          <option value="<?= $page->ID ?>" <?= $existing_votation_page_id == $page->ID ? 'selected' : null ?>><?= $page->post_title ?></option>
        <?php endforeach; ?>
      </select>
      <br>
      <br>
      <label for="vtv_votation_forminator_form">Formulär för omröstning</label>
      <br>
      <select required id="vtv_votation_forminator_form" name="vtv[votation_forminator_form]">
        <option value="">Välj ett formulär</option>
        <?php foreach ($vt_votation_forminator_forms as $form): ?>
          <option value="<?= $form->id ?>" <?= $existing_votation_form_id == $form->id ? 'selected' : null ?>><?= $form->settings['formName'] ?></option>
        <?php endforeach; ?>
      </select>
      <br>
      <br>
      <input type="hidden" name="action" value="vtv_form_response">
      <input type="hidden" name="vtv_add_user_meta_nonce" value="<?= $vtv_add_meta_nonce ?>" />
      <input type="submit" name="submit" id="submit" class="button button-primary" value="Spara">
    </form>
  </div>
<?php else: ?>
  <p><?php __('You are not authorized to perform this operation.', $vt_votation_plugin_name) ?></p>
<?php endif; ?>
