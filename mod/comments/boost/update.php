<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function comments_update(&$content, $currentVersion)
{
    switch ($currentVersion) {

    case version_compare($currentVersion, '0.6.2', '<'):
        $content[] = '<pre>Comments versions prior to 0.6.2 are not supported for updating.
Please download 0.6.3.</pre>';
        break;

    case version_compare($currentVersion, '0.6.3', '<'):
        $content[] = '<pre>';
        $files = array('templates/alt_view.tpl', 'templates/view.tpl');
        if (PHPWS_Boost::updateFiles($files, 'comments')) {
            $content[] = '---The following templates copied locally.';
        } else {
            $content[] = '---The following templates failed to copy locally.';
        }
        $content[] = '    ' . implode("\n    ", $files);
        $content[] = '
0.6.3 Changes
-------------
+ Added setAnchor to comments.
+ Changed anchor tag to conform with Safari.
</pre>';


    case version_compare($currentVersion, '0.6.4', '<'):
        $content[] = '<pre>';
        $files = array('templates/settings_form.tpl', 'templates/recent.tpl');
        if (PHPWS_Boost::updateFiles($files, 'comments')) {
            $content[] = '---The following templates copied locally.';
        } else {
            $content[] = '---The following templates failed to copy locally.';
        }
        $content[] = '    ' . implode("\n    ", $files);
        $content[] = '
0.6.4 Changes
-------------
+ RFE #1720589 - Added ability to show most recent comments in a popup.
+ Added permission check on single comment view.
</pre>';

    case version_compare($currentVersion, '1.0.0', '<'):
        $content[] = '<pre>';
        PHPWS_Boost::registerMyModule('comments', 'controlpanel', $content);
        $content[] = '</pre>';
        
    }
            
    return true;
}

?>