<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


function users_update(&$content, $currentVersion)
{

    switch ($currentVersion) {

    case version_compare($currentVersion, '2.0.2', '<'):
        $result = users_update_202($content);
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = '+ added ability to pick a default user menu.';
        $content[] = '+ added graphi confirmation option';
        $content[] = '- dropped default_group column';

    case version_compare($currentVersion, '2.0.3', '<'):
        $tpl_dir = 'templates/forms/';
        $files[] = $tpl_dir . 'groupForm.tpl';
        $files[] = $tpl_dir . 'memberForm.tpl';
        $files[] = $tpl_dir . 'permission_pop.tpl';
        $files[] = $tpl_dir . 'permissions.tpl';
        $files[] = $tpl_dir . 'userForm.tpl';
        $files[] = 'templates/main.tpl';
        if (!PHPWS_Boost::updateFiles($files, 'users')) {
            $content[] = 'Failed to update template files.';
            return FALSE;
        }

        $content[] = '+ Added extra administrative links for managing groups and users.';
    }

    return TRUE;
}

function users_update_202(&$content)
{
    $filename = PHPWS_SOURCE_DIR . 'mod/users/boost/update_2_0_2.sql';
    $db = & new PHPWS_DB;
    return $db->importFile($filename);
}

?>