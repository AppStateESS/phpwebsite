<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

chdir('../');

require_once 'config/core/config.php';
require_once 'inc/Functions.php';

require_once 'core/class/Init.php';
require_once 'inc/Security.php';

PHPWS_Core::initCoreClass('Batch.php');
PHPWS_Core::initCoreClass('Database.php');
PHPWS_Core::initCoreClass('Form.php');
PHPWS_Core::initCoreClass('Template.php');

require_once 'mod/users/inc/init.php';
define('SESSION_NAME', md5(SITE_HASH . $_SERVER['REMOTE_ADDR']));
session_name(SESSION_NAME);
session_start();

require_once 'convert/class/Convert.php';

$convert = new Convert;
$convert->action();


?>