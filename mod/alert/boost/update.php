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

        case version_compare($version, '1.3.0', '<'):
            PHPWS_Boost::updateFiles(array('javascript/check_all/head.js'), 'alert');
            $content[] = '<pre>1.3.0 Changes
--------------
+ Fixed uninstall script
+ Fixed +/- check all links
+ items returned now for rss feeds if not shown on the home page.
+ Deleting an alert type removes the alerts.
+ Non-post alerts won\'t post now.
+ Fixed check all button.
+ Add and subtract buttons hidden if no types created
+ Fixed form select match for type in alerts</pre>';

    }
    return true;
}


?>