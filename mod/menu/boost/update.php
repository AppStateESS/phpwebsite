<?php

  /**
   * update file for menu
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function menu_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '1.2.0', '<'):
        $content[] = '<pre>Menu versions prior to 1.2.0 are not supported for update.
Please download 1.2.1.</pre>';
        break;

    case version_compare($currentVersion, '1.2.1', '<'):
        $content[] = '<pre>1.2.1 changes
-----------------
+ Fixed bug with making home link.
</pre>';

    case version_compare($currentVersion, '1.3.0', '<'):
        $files = array('conf/config.php', 'templates/admin/settings.tpl',
                       'templates/links/link.tpl', 'templates/popup_admin.tpl');
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles($files, 'menu')) {
            $content[] = '--- Successfully updated the following files:';
        } else {
            $content[] = '--- Was unable to copy the following files:';
        }
        $content[] = '     ' . implode("\n     ", $files);
        $content[] = '
1.3.0 changes
-----------------
+ Admin icon for links is now clickable. Pulls up window of options.
+ Added ability to disable floating admin links.
</pre>';

    case version_compare($currentVersion, '1.3.1', '<'):
        $files = array('templates/site_map.tpl');
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles($files, 'menu')) {
            $content[] = '--- Successfully updated the following files:';
        } else {
            $content[] = '--- Was unable to copy the following files:';
        }
        $content[] = '     ' . implode("\n     ", $files);
        $content[] = '
1.3.1 changes
-----------------
+ Bug # 1609737. Fixed site_map.tpl file. Thanks Andy.
</pre>';

    }
    return true;
}

?>