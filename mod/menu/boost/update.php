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
        return true;
        break;
    }
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