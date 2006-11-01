<?php

  // The below depends on Text.php's makeRelative function substituting the home http for "./"
$data['VALUE'] = str_replace('./images/', PHPWS_Core::getHomeHttp() . 'images/', $data['VALUE']);

if (isset($_REQUEST['module'])) {
    $data['module'] = preg_replace('/\W/', '', $_REQUEST['module']);
}

?>