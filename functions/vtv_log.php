<?php

if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}

function vtv_log(mixed $input): void
{
  file_put_contents(__DIR__ . '/../vtv.log', json_encode($input) . "\n", FILE_APPEND);
}
