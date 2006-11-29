<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function comments_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.2.5', '<'):
        $content[] = '+ Added quote next to reply tags. Template update.';
        if (!comments_update_025($content)) {
            return FALSE;
        }

    case version_compare($currentVersion, '0.2.6', '<'):
        $content[] = '+ Fixed occurrences where anonymous users could post on restricted threads.';

    case version_compare($currentVersion, '0.2.7', '<'):
        $content[] = '+ Comments without subject will use COMMENT_NO_SUBJECT define.';
        if (PHPWS_Boost::updateFiles(array('conf/config.php'), 'comments')) {
            $content[] = 'New config.php file copied locally.';
        } else {
            $content[] = 'New config.php file failed to copy locally.';
        }

    case version_compare($currentVersion, '0.2.8', '<'):
        $content[] = 'Fix - Retitled key unregistration function.';
        $content[] = 'Fix - Deleting a comment now clears its relationship to its replies.';

    case version_compare($currentVersion, '0.2.9', '<'):
        $content[] = 'Fix - Relative time listing.';

    case version_compare($currentVersion, '0.3.0', '<'):
        $content[] = 'Small changes to sync comments to current core.';

    case version_compare($currentVersion, '0.3.1', '<'):
        if (PHPWS_Boost::updateFiles(array('conf/config.php'), 'comments')) {
            $content[] = '- New config.php file copied locally.';
        } else {
            $content[] = '- New config.php file failed to copy locally.';
        }
        $content[] = '- Created default comment limit in config.';

    case version_compare($currentVersion, '0.4.0', '<'):
        $files = array();
        $files[] = 'templates/edit.tpl';
        $files[] = 'templates/settings_form.tpl';
        $files[] = 'conf/config.php';
        if (PHPWS_Boost::updateFiles($files, 'comments')) {
            $content[] = 'Templates and config file copied locally.';
        } else {
            $content[] = 'Templates and config file failed to copy locally.';
        }

        $content[] = '<pre>
0.4.0 Changes
-------------
+ Updated file conf/config.php.
+ Added default comment limit and set it to 20.
+ Updated files : templates/edit.tpl, templates/settings_form.tpl
+ Added a permission for settings control.
+ Added new Captcha class for commenting.
+ Added selector for captcha control on settings tab.
</pre>';

    case version_compare($currentVersion, '0.5.0', '<'):
        $files = array();
        $files[] = 'templates/view.tpl';
        $files[] = 'templates/alt_view.tpl';
        if (PHPWS_Boost::updateFiles($files, 'comments')) {
            $content[] = 'Templates copied locally.';
        } else {
            $content[] = 'Templates failed to copy locally.';
        }

        $content[] = '<pre>
0.5.0 Changes
-------------
+ Updated templates templates/view.tpl, templates/alt_view.tpl
+ Added anchor tag to templates and code.
+ Changed the getSourceUrl function in the Thread to use the DBPager\'s
  new saveLastView and getLastView functions.
+ Update dependent on new core.
</pre>';

    }
            
    return TRUE;
}


function comments_update_025(&$content) {
    $files[] = 'templates/alt_view.tpl';
    $files[] = 'templates/alt_view_one.tpl';
    $files[] = 'templates/view.tpl';
    $files[] = 'templates/view_one.tpl';
    $files[] = 'templates/style.css';
    $result = PHPWS_Boost::updateFiles($files, 'comments');
    if (!$result) {
        $content[] = 'Failed to copy template files locally.';
        return false;
    }
    
    return true;
}


?>