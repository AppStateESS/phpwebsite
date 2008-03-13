<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function rideboard_update(&$content, $version)
{
    switch(1) {
    case version_compare($version, '1.0.1', '<'):
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles(array('templates/settings.tpl'), 'rideboard')) {
            $content[] = '--- Local templates updated.';
        } else {
            $content[] = '--- Failed to update local templates.';
        }
        $content[] = "\n1.0.1 Version
-------------
+ Settings allows menu link creation.</pre>";
        
        
    }
    return true;
}

?>