<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function boost_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '1.6.1', '<'):
        $content[] = '+ Boost wasn\'t updating modules if there wasn\'t an update.php file.';
        break;
    }
    return TRUE;
}

?>