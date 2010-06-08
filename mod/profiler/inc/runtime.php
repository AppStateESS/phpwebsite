<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (!isset($_REQUEST['module'])) {
    \core\Core::initModClass('profiler', 'Profiler.php');

    Profiler::view();
}

?>