<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function webpage_update(&$content, $currentVersion)
{

    switch ($currentVersion) {

    case version_compare($currentVersion, '0.5.2', '<'):
        $content[] = '<pre>Web Page versions prior to 0.5.2 are not supported for updating.
Please download version 0.5.3</pre>';
        break;

    case version_compare($currentVersion, '0.5.3', '<'):
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles(array('conf/error.php'), 'webpage')) {
            $content[] = '--- Updated conf/error.php';
        } else {
            $content[] = '--- Unable to update conf/error.php';
        }
        $content[] = '
0.5.3 Changes
--------------
+ Added error catch to page template function.
</pre>';

    case version_compare($currentVersion, '0.5.4', '<'):
        $content[] = '<pre>
0.5.4 Changes
--------------
+ Fulfilled request to change "edit" to "edit page"
+ RFE #1690681 - Added permissions link on volume list view.
+ RFE #1719299 - Page titles added to volume tabs.
</pre>';

    }

    return TRUE;
}


?>