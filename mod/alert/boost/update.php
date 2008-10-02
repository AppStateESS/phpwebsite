<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function alert_update(&$content, $version)
{
    $home_directory = PHPWS_Boost::getHomeDir();

    switch (1) {
    case version_compare($version, '1.1.0', '<'):
        $content[] = '<pre>';
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        if (Cabinet::convertImagesToFileAssoc('alert_item', 'image_id')) {
            $content[] = '--- Converted images to new File Cabinet format.';
        } else {
            $content[] = '--- Could not convert images to new File Cabinet format.</pre>';
            return false;
        }

        $content[] = '1.1.0 Changes
--------------
+ Updated to work with File Cabinet 2.0</pre>';

    case version_compare($version, '1.2.0', '<'):
        $content[] = '<pre>1.2.0 Changes
--------------
+ Updated to PHP 5 standard.
+ </pre>';


    }
    return true;
}


?>