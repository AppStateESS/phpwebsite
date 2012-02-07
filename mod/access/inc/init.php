<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

    PHPWS_Core::initModClass('access', 'Access.php');
if (isset($GLOBALS['Forward'])) {
    Access::forward();
}

?>