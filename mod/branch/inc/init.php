<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
require_once(PHPWS_SOURCE_DIR.'mod/branch/conf/defines.php');

if (isset($_REQUEST['module']) && $_REQUEST['module'] == 'branch') {
    PHPWS_Core::initModClass('boost', 'Boost.php');
}
?>