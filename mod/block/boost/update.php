<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


function block_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.0.2', '<'):
        $result = block_update_002($content);
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = '+ added the edit permission';
        break;
    }
    
    return TRUE;
}

function block_update_002(&$content)
{
    PHPWS_Core::initModClass('users', 'Users.php');
    PHPWS_User::registerPermissions('block', $content);
}

?>