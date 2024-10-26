<?php

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
