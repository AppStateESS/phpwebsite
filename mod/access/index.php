<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

Core\Core::initModClass('access', 'Access.php');
if (Current_User::authorized('access')) {
    Access::main();
} else {
    Current_User::disallow();
    exit();
}

?>