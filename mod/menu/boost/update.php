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
    case version_compare($currentVersion, '1.0.4', '<'):
        $content[] = 'This package will not update versions under 1.0.4.';
        return false;

    case version_compare($currentVersion, '1.1.0', '<'):
        $files = array('conf/config.php',
                       'templates/links/link.tpl',
                       'templates/menu_layout/basic.tpl',
                       'img/attach.png',
                       'templates/admin/pin_list.tpl');
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles($files, 'menu')) {
            $content[] = 'The following files were updated successfully.';
        } else {
            $content[] = 'The following files were not updated successfully.';
        }
        $content[] = '    ' . implode("\n    ", $files);

        $content[] = '
1.1.0 Changes
-------------
+ Added translate functions.
+ Removed references from object constructors
+ Removed a unneeded double urlencode and a urldecode
+ Default menu is created on install.
+ Links missing &amp; were causing validation errors. Fixed.
+ Fixed missing quote in config.php links
+ Added pinLink function for developers to add a link to any menu.
+ Added mechanism for adding a pinned link.
+ Added a color : inherit to menu\'s style sheet to conform with css
  standards.
</pre>';

    case version_compare($currentVersion, '1.1.1', '<'):
$content[] = '<pre>1.1.1 Changes
-------------';        
        $files = array('templates/admin/offsite.tpl',
                       'templates/admin/pin_list.tpl',
                       'templates/menu_layout/basic.tpl',
                       'templates/links/link.tpl');
        if (PHPWS_Boost::updateFiles($files, 'menu')) {
            $content[] = 'The following files were updated successfully.';
        } else {
            $content[] = 'The following files were not updated successfully.';
        }
        $content[] = '    ' . implode("\n    ", $files);
        $content[] = '+ Changed template so IE would hide the menu.
+ Menu now allows you pin any non-admin page into the menu.
+ Change menu margins to so both FF and IE could access the popup menu.</pre>';

    }
    return true;
}

?>