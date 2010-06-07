<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function categories_unregister($module, &$content){
    Core\Core::initModClass("categories", "Categories.php");

    Categories::removeModule($module);
}

?>