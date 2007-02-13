<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function users_unregister($module, &$content){
    PHPWS_Core::initModClass('users', 'Permission.php');

    $result = Users_Permission::removePermissions($module);

    if (PEAR::isError($result)) {
        translate('users');
        $content[] = _('Permissions table not removed successfully.');        
        translate();
        return FALSE;
    } elseif ($result) {
        translate('users');
        $content[] = _('Permissions table removed successfully.');
        translate();
        return TRUE;
    }
    
    return TRUE;
}


?>