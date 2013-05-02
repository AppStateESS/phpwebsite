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

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}
Deprecate::moduleWarning('profiler');
if (isset($_REQUEST['user_cmd'])) {
    PHPWS_Core::initModClass('profiler', 'Profiler.php');
    Profiler::user();
} else {
    if (!Current_User::authorized('profiler')) {
        Current_User::disallow();
    }

    PHPWS_Core::initModClass('profiler', 'Profiler.php');
    Profiler::admin();
}

?>