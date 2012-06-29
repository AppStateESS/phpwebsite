<?php
$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
$_SESSION['ck_dir'] = dirname($_SERVER['PHP_SELF']) . '/images/ckeditor/';
$_SESSION['ck_http'] = dirname($_SERVER['PHP_SELF']) . '/images/ckeditor/';
$_SESSION['home_dir'] = PHPWS_HOME_DIR;
$_SESSION['source_dir'] = PHPWS_SOURCE_DIR;
$_SESSION['source_http'] = PHPWS_SOURCE_HTTP . 'javascript/editors/ckeditor/filemanager/';
$_SESSION['logged'] = Current_User::isLogged();
$_SESSION['filecab'] = Current_User::allow('filecabinet');
$_SESSION['base_url'] = PHPWS_Core::getBaseURL();
$default['session_name'] = session_name();
?>
