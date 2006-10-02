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