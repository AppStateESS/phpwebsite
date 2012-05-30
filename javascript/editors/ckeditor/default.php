<?php
$_SESSION['ck_dir'] = dirname($_SERVER['PHP_SELF']) . '/images/';
$_SESSION['ck_http'] = dirname($_SERVER['PHP_SELF']) . '/images/';
$_SESSION['home_dir'] = PHPWS_HOME_DIR;
$_SESSION['source_dir'] = PHPWS_SOURCE_DIR;
$_SESSION['source_http'] = PHPWS_SOURCE_HTTP . 'javascript/editors/ckeditor/filemanager/';
$default['session_name'] = session_name();
?>
