<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function demographics_register($module, &$content)
{
    Core\Core::initModClass('demographics', 'Demographics.php');
    $result = Demographics::register($module);
    return $result;
}


?>