<?php

/**
 * WARNING: This module has been deprecated. It will no longer be
 * maintained by phpwebsite and no further bug/security patches will
 * be released. It will be removed from the phpWebsite distribution
 * at some point in the future. We recommend migrating to one of the
 * many other freely available web forums packages.
 *
 * @deprecated since phpwebsite 1.8.0
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    header('location: ../../index.php');
    exit();
}
Deprecate::moduleWarning('alert');
PHPWS_Core::initModClass('alert', 'Alert.php');
$alert = new Alert;

if (isset($_REQUEST['aop'])) {
    $alert->admin();
} else {
    $alert->user();
}

?>