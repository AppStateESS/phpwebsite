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


    case version_compare($version, '1.1.0', '<'):
        $content[] = '<pre>';
        $files = array('templates/carpool_view.tpl',
                       'templates/carpools.tpl',
                       'templates/settings.tpl',
                       'templates/edit_carpool.tpl');

        if (PHPWS_Boost::updateFiles($files, 'rideboard')) {
            $content[] = '--- Local templates updated.';
        } else {
            $content[] = '--- Failed to update local templates.';
        }
        $content[] = "\n1.1.0 Version
-------------
+ Added a carpooling component.</pre>";
    }
    return true;
}

?>