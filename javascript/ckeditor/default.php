<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
$_SESSION['ck_dir'] = dirname($_SERVER['PHP_SELF']) . '/images/filecabinet/';
$_SESSION['ck_http'] = dirname($_SERVER['PHP_SELF']) . '/images/filecabinet/';
$_SESSION['home_dir'] = PHPWS_HOME_DIR;
$_SESSION['source_dir'] = PHPWS_SOURCE_DIR;
$_SESSION['source_http'] = PHPWS_SOURCE_HTTP . 'javascript/ckeditor/filemanager/';
$_SESSION['logged'] = Current_User::isLogged();
$_SESSION['filecab'] = Current_User::allow('filecabinet');
$_SESSION['base_url'] = PHPWS_Core::getBaseURL();
$default['session_name'] = session_name();
$source_http = PHPWS_SOURCE_HTTP;
$session_name = session_name();
$header = <<<EOF
<script type="text/javascript" src="{$source_http}javascript/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
var source_http = '{$source_http}';
var sn = '{$session_name}';
CKEDITOR.config.customConfig = '{$source_http}javascript/ckeditor/phpws_config.js';
</script>
EOF;
Layout::addJSHeader($header, 'ckeditor-head');
?>
