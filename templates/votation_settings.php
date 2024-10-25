<?php
if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}

$existing_votation_form_ids = VOTATION_FORM_IDS;

?>

<h1><?= __('Inställningar', 'my-textdomain'); ?></h1>

<?php if (current_user_can('manage_options')): ?>
  <?php $vtv_add_meta_nonce = wp_create_nonce('vtv_add_user_meta_form_nonce'); ?>        
  <div class="vtv_add_user_meta_form">
    <form 
      action="<?= esc_url(admin_url('admin-post.php')); ?>"
      method="post"
      id="vtv_add_user_meta_form"
    >      
      <fieldset>
        <legend>Formulär för omröstning</legend>
        <?php foreach ($vt_votation_forminator_forms as $form): ?>
          <div>
            <input
              type="checkbox"
              id="<?= htmlentities($form->id) ?>"
              name="books[<?= htmlentities($form->id) ?>]"
              <?= in_array($form->id, $existing_votation_form_ids) ? 'checked' : null ?>
            />
            <label for="<?= htmlentities($form->id) ?>"><?= htmlentities($form->settings['formName']) ?></label>
          </div>
        <?php endforeach; ?>
      </fieldset>
      <br>
      <fieldset>
        <legend>Tillåt flera inlämningar från samma IP-adress</legend>
        <select name="allow_multiple_votes_from_same_ip" id="allow_multiple_votes_from_same_ip">
        <option value="yes" <?= ALLOW_MULTIPLE_VOTES_FROM_SAME_IP == 'yes' ? 'selected' : null ?>>Ja</option>
        <option value="no" <?= ALLOW_MULTIPLE_VOTES_FROM_SAME_IP == 'no' ? 'selected' : null ?>>Nej</option> 
      </select> 
      </fieldset>
      <br>
      <input type="hidden" name="action" value="vtv_form_response" />
      <input type="hidden" name="vtv_add_user_meta_nonce" value="<?= htmlentities($vtv_add_meta_nonce) ?>" />
      <input type="submit" name="submit" id="submit" class="button button-primary" value="Spara" />
    </form>
  </div>
<?php else: ?>
  <p><?php 'You are not authorized to perform this operation.' ?></p>
<?php endif; ?>
