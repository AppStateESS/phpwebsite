<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
if (isset($_REQUEST['module']) && $_REQUEST['module'] == 'branch') {
    Core\Core::initModClass('boost', 'Boost.php');
}
?>