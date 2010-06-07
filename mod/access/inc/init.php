<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (isset($GLOBALS['Forward'])) {
    Core\Core::initModClass('access', 'Access.php');
    Access::forward();
}

?>