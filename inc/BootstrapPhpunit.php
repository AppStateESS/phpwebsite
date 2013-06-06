<?php

$val = ini_get('include_path');

define('PHPWS_SOURCE_DIR', __DIR__ . '/../');
ini_set('display_errors', 'on');
ini_set('error_reporting', E_ALL);
require_once PHPWS_SOURCE_DIR . 'Global/Functions.php';

require_once PHPWS_SOURCE_DIR . 'Global/Implementations.php';

ini_set('include_path', $val);

?>
