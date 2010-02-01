<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (isset($_SERVER['WINDIR']) || preg_match('/(microsoft|win32)/i', $_SERVER['SERVER_SOFTWARE'])){
    ini_set('include_path', '.;.\\lib\\pear\\');
} else {
    ini_set('include_path', '.:./lib/pear/');
}

define('SITE_HASH', 'temporary');
define('LOG_PERMISSION', 0644);
define('LOG_TIME_FORMAT', '%X %x');
define('PHPWS_LOG_ERRORS', TRUE);
define('FORCE_MOD_CONFIG', TRUE);
define('DEFAULT_LANGUAGE', 'en_US'); //use same format (e.g. de_DE, es_ES, etc.)
define('USE_CRUTCH_FILES', FALSE);
?>