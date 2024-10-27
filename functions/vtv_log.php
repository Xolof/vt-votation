<?php

function vtv_log($string)
{
  file_put_contents(__DIR__ . '/../vtv.log', json_encode($string) . "\n", FILE_APPEND);
}
