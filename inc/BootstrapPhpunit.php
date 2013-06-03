<?php

$val = ini_get('include_path');

require_once('Bootstrap.php');

ini_set('include_path', $val);

?>
