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

    case version_compare($currentVersion, '0.0.3', '<'):
        $result = block_update_003();
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = '+ added key column';
        break;

    }
    
    return TRUE;
}

function block_update_002(&$content)
{
    PHPWS_Core::initModClass('users', 'Users.php');
    PHPWS_User::registerPermissions('block', $content);
}

function block_update_003()
{
    $db = & new PHPWS_DB('block');
    $db->addTableColumn('key_id', 'INT DEFAULT \'0\' NOT NULL', 'id');
}


?>