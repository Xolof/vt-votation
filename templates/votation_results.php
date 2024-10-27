<?php
if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}
?>

<h1>Resultat</h1>

<?php

$votation_results_db = $votation_results_db ?? [];
$votes_per_ip_results_db = $votes_per_ip_results_db ?? [];

function cmp($a, $b)
{
  if ($a == $b) {
    return 0;
  }
  return ($a->num_votes > $b->num_votes) ? -1 : 1;
}

uasort($votation_results_db, 'cmp');
uasort($votes_per_ip_results_db, 'cmp');

?>

<?php if (!count(VOTATION_FORM_IDS)): ?>
  <p>
    Inga formulär har valts.&nbsp
    <a href="<?= get_admin_url(); ?>admin.php?page=render_votation_settings">Välj formulär i inställningarna.</a>
  </p>
<?php endif; ?>

<?php if (!count($votation_results_db)): ?>
  <p>Det har ännu inte kommit in några röster.</p>
<?php endif; ?>

<?php if (count($votation_results_db)): ?>
<h2>Antal röster per bok</h2>
<div class="wrap">
<table class="widefat striped">
  <tr>
    <th><b>Placering</b></th>
    <th><b>Bok</b></th>
    <th><b>Antal röster</b></th>
    <th><b>Formulär</b></th>
  </tr>
  <?php $index = 1; ?>
  <?php foreach ($votation_results_db as $result): ?>
    <tr>
      <td>
        <?= $index ?>
      </td>
      <td>
        <?= htmlentities(__($result->book, 'my-textdomain')) ?>
      </td>
      <td>    
        <?= htmlentities($result->num_votes) ?>
      </td>  
      <td>
        <?= htmlentities(__($result->form_id, 'my-textdomain')) ?>
      </td>
    </tr>
        <?php $index++?>
  <?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<?php if (count($votes_per_ip_results_db)): ?>
<h2>Antal röster per IP-adress</h2>
<div class="wrap">
<table class="widefat striped">
  <tr>
    <th><b>IP</b></th>
    <th><b>Antal röster</b></th>
  </tr>
  <?php foreach ($votes_per_ip_results_db as $result): ?>
    <tr>
      <td>
        <?= htmlentities($result->IP_address) ?>
      </td>
      <td>
        <?= htmlentities($result->num_votes) ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
</div>
<?php endif; ?>
