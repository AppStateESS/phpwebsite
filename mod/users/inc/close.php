<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

// Destroy unused sessions
if (Core\Core::getCurrentModule() != 'users'){
    Core\Core::killSession('Member_Pager');
    Core\Core::killSession('All_Demo');
    Core\Core::killSession('User_Manager');
    Core\Core::killSession('Group_Manager');
}

Current_User::permissionMenu();
?>