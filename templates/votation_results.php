<?php
if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}
?>
<h1><?= __('Resultat', 'my-textdomain'); ?></h1>
<?php
foreach ($votation_results_db as $result) {
  $num_votes = $result->num_votes;
  $books = unserialize($result->books);
  for ($i = 0; $i < $num_votes; $i++) {
    foreach ($books as $book) {
      $book = ucfirst(str_replace("-", " ", $book));
      if (!array_key_exists($book, $votation_results_array)) {
        $votation_results_array[$book] = 1;
      } else {
        $votation_results_array[$book] += 1;
      }
    }
  }
}
arsort($votation_results_array);
?>
<div class="wrap">
<table class="widefat striped">
  <tr>
    <th><b>Placering</b></th>
    <th><b><?= __('Bok', 'my-textdomain') ?></b></th>
    <th><b><?= __('Antal röster', 'my-textdomain') ?></b></th>
  </tr>
  <?php $index = 1; ?>
  <?php foreach ($votation_results_array as $book => $num_votes): ?>
    <tr>
      <td>
        <?= $index ?>
      </td>
      <td>
        <?= htmlentities(__($book, 'my-textdomain')) ?>
      </td>
      <td>    
        <?= $num_votes ?>
      </td>  
    </tr>
        <?php $index++?>
  <?php endforeach; ?>
</table>
</div>
