<?php

$val = ini_get('include_path');

define('PHPWS_SOURCE_DIR', __DIR__ . '/../');

require_once('Bootstrap.php');

ini_set('include_path', $val);

?>
