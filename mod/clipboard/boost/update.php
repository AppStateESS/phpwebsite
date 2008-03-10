<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function clipboard_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.0.2', '<'):
        $db = & new PHPWS_DB('controlpanel_link');
        $db->addWhere('itemname', 'clipboard');
        $db->delete();
        $content[] = 'Removing Clipboard\'s Control Panel link.';


    case version_compare($currentVersion, '0.0.3', '<'):
        $content[] = '<pre>0.0.3 changes
-------------
+ Added translate functions</pre>';

    case version_compare($currentVersion, '1.0.0', '<'):
        $content[] = '<pre>1.0.0 changes
-------------
+ Updated language functions.</pre>';

    case version_compare($currentVersion, '1.0.1', '<'):
        $content[] = '<pre>1.0.1 changes
-------------
+ Added different window titles.
+ Increased popup width.</pre>';

    case version_compare($currentVersion, '1.0.2', '<'):
        $content[] = '<pre>1.0.2 changes
-------------
+ Clipboard won\'t allow clipping of items without a title and content.</pre>';


    }

    return true;
}

?>