<?php
/**
 * WARNING: This module has been deprecated. It will no longer be
 * maintained by phpwebsite and no further bug/security patches will
 * be released. It will be removed from the phpWebsite distribution
 * at some point in the future. We recommend migrating to one of the
 * many other freely available web forums packages.
 *
 * @deprecated since phpwebsite 1.8.0
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}
Deprecate::moduleWarning('help');
PHPWS_Core::initModClass('help', 'Help.php');

PHPWS_Help::show_help();

?>