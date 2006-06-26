<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


function users_update(&$content, $currentVersion)
{

    switch ($currentVersion) {

    case version_compare($currentVersion, '2.0.4', '<'):
        if (!PHPWS_Boost::updateFiles(array('conf/config.php'), 'users')) {
            $content[] = 'Failed to update config.php file.';
            return FALSE;
        }

        $content[] = '+ Added new definition to User\'s config.php file.';
        
        $filename = PHPWS_SOURCE_DIR . 'mod/users/boost/update_2_0_4.sql';
        $db = & new PHPWS_DB;
        $result = $db->importFile($filename);
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = '+ Created user signup authorization table.';

    case version_compare($currentVersion, '2.0.5', '<'):
        $files[] = 'templates/forms/permission_menu.tpl';
        $content[] = '+ Added ability to change deity status.';
        $content[] = '+ fixed h1 tag in template';

        if (!PHPWS_Boost::updateFiles($files, 'users')) {
            $content[] = 'Failed copying template file locally.';
        }

    case version_compare($currentVersion, '2.0.6', '<'):
        if (is_dir('images/users/confirm/') || @mkdir('images/users/confirm/')) {
            $content[] = '+ Created confirm directory.';
        } else {
            $content[] = 'Error: unable to create images/users/confirm directory.';
            return false;
        }
       
    }

    return TRUE;
}

?>