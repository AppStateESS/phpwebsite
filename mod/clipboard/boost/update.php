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
        $content[] = '<pre>+ Added translate functions</pre>';
    }

    return true;
}

?>