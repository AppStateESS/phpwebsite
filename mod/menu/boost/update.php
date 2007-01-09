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
    case version_compare($currentVersion, '0.0.2', '<'):
        $content[] = _('Register to key.');
        menu_update_002($content);

    case version_compare($currentVersion, '1.0.0', '<'):
        $files = array();
        $files[] = 'templates/menu_layout/basic.tpl';
        $result = PHPWS_Boost::updateFiles($files, 'menu');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to copy template file menu_layout/basic.tpl';
        }
        $content[] = '- Fixed : Menu associations not cleared when keys removed.';
        $content[] = '- Added menu class around menu template to assist with styling.';

    case version_compare($currentVersion, '1.0.1', '<'):
        $files = array();
        $files[] = 'templates/site_map.tpl';
        $files[] = 'templates/style.css';
        $files[] = 'conf/config.php';
        if (PHPWS_Boost::updateFiles($files, 'menu')) {
            $content[] = 'Template files updated successfully.';
        } else {
            $content[] = 'Template files were not updated successfully.';
        }
        $content[] = 'New - Added Site Map display that is accessible from clicking the menu name.';
        $content[] = 'Change -  The Create Offsite Link has been changed to Create Other Link.';
        $content[] = 'New - Create Other Link now sets the default url to the page from which it was clicked. Will not use a default url from a keyed page.';

    case version_compare($currentVersion, '1.0.2', '<'):
        $content[] = 'Fix - Introduced bug with Site Map in last version. Now shows admin menu again.';

    case version_compare($currentVersion, '1.0.3', '<'):
        $files = array('templates/links/link.tpl', 'templates/menu_layout/basic.tpl');
        if (PHPWS_Boost::updateFiles($files, 'menu')) {
            $content[] = 'Template files updated successfully.';
        } else {
            $content[] = 'Template files were not updated successfully.';
        }
        $content[] = 'Unkeyed link urls may now be edited.';

    case version_compare($currentVersion, '1.0.4', '<'):
        $files = array('templates/admin/offsite.tpl');
        if (PHPWS_Boost::updateFiles($files, 'menu')) {
            $content[] = 'Template files updated successfully.';
        } else {
            $content[] = 'Template files were not updated successfully.';
        }
        $content[] = 'New - added cancel button to other link template (offsite.tpl).';

    case version_compare($currentVersion, '1.1.0', '<'):
        $files = array('conf/config.php', 'templates/links/link.tpl', 'templates/menu_layout/basic.tpl', 'img/attach.png', 'templates/admin/pin_list.tpl');
        if (PHPWS_Boost::updateFiles($files, 'menu')) {
            $content[] = 'Files updated successfully.';
        } else {
            $content[] = 'Files were not updated successfully.';
            $content[] = '<pre>' . implode("\n", $files) . '</pre>';
        }
        $content[] = '+ Added pinLink function for developers to add a link to any menu.';
        $content[] = '+ Added mechanism for adding a pinned link.';
    }
    return true;
}

function menu_update_002(&$content)
{
    $result = Key::registerModule('menu');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        $content[] = _('A problem occurred during the update.');
    }
}


?>