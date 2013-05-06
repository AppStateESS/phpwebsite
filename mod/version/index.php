<?php

/**
 *
 * WARNING: This module has been deprecated. It will no longer be
 * maintained by phpwebsite and no further bug/security patches will
 * be released. It will be removed from the phpWebsite distribution
 * at some point in the future.
 *
 * @deprecated since phpwebsite 1.8.0
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

Deprecate::moduleWarning('vlist');

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

if (!Current_User::authorized('version')) {
    Current_User::disallow();
    return;
}

PHPWS_Core::initModClass('version', 'Admin.php');
Version_Admin::main();

?>