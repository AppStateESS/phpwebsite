<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function breadcrumb_update(&$content, $currentVersion)
{
    $home_directory = PHPWS_Boost::getHomeDir();

    switch ($currentVersion) {
        
    case version_compare($currentVersion, '2.1.0', '<'):
        $content[] = '<pre>
2.1.0 changes
---------------------
+ php 5 formatted.</pre>';
        
    }

    return true;
}
?>