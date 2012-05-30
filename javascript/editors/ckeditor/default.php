<?php
$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
$_SESSION['ck_dir'] = dirname($_SERVER['PHP_SELF']) . '/images/';
$_SESSION['ck_http'] = dirname($_SERVER['PHP_SELF']) . '/images/';
$_SESSION['home_dir'] = PHPWS_HOME_DIR;
$_SESSION['source_dir'] = PHPWS_SOURCE_DIR;
$_SESSION['source_http'] = PHPWS_SOURCE_HTTP . 'javascript/editors/ckeditor/filemanager/';
$_SESSION['logged'] = Current_User::isLogged();
$default['session_name'] = session_name();
?>
