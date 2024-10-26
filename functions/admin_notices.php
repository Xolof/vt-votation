<?php

function print_plugin_admin_notices()
{
  $status = $_REQUEST['vtv_admin_notice_status'] ?? null;
  if (isset($status)) {
    ?>
        <div class="notice notice-<?= htmlentities($status) ?> is-dismissible">
        <p><b><?= htmlentities($_REQUEST['vtv_admin_notice_message']) ?></b></p>
        </div>
  <?php
  } else {
    return;
  }
}
