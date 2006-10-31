<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


function users_update(&$content, $currentVersion)
{

    switch ($currentVersion) {

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
  
    case version_compare($currentVersion, '2.0.7', '<'):
        $files = array();
        $files[] = 'templates/forms/permission_pop.tpl';
        $files[] = 'templates/usermenus/Default.tpl';
        $result = PHPWS_Boost::updateFiles($files, 'users');
        if (PEAR::isError($result)) {
            $content[] = 'Template files failed to copy locally.';
        } else {
            $content[] = 'Template files updated.';
        }

        $content[] = '- Fixed - Bug #1568383. Users was calling deprecated Time function.';
        $content[] = '- Fixed authorization problems occuring after changing user name.';
        $content[] = '- Error in the permission form should be fixed.';
        $content[] = '- Permission.php - Fixed typo causing syntax error on permission call.';
        $content[] = '- permission_pop.tpl - fixed javascript error from changes in the form class.';
        $content[] = '- Changed user login box template.';
        $content[] = '- Fixed a problem with the permissions form made from the last Form.';
        $content[] = '- Added a getPermissionGroups function.';

    case version_compare($currentVersion, '2.0.8', '<'):
        $files = array();
        $files[] = 'templates/my_page/user_setting.tpl';
        $files[] = 'conf/languages.php';
        PHPWS_Boost::updateFiles($files, 'users');
        $content[] = '- Added language selection from My Page.';


    case version_compare($currentVersion, '2.0.9', '<'):
        $content[] = '+ Added - allowUsername function Current_User class.';
        $content[] = '+ Added - page refresh after language choice.';
        $content[] = '+ Fixed - My Page was appearing regardless of user login status.';
        $content[] = '+ Added - allowUsername function to verify user name formating.';

    case version_compare($currentVersion, '2.1.0', '<'):
        $content[] = 'New - The username column in user_authorization was made into a primary key.';
        $content[] = 'The install process was rewritten to work with branch changes.';
    }

    return TRUE;
}

?>