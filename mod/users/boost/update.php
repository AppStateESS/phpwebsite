<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


function users_update(&$content, $currentVersion)
{
    $home_dir = PHPWS_Boost::getHomeDir();

    switch ($currentVersion) {

    case version_compare($currentVersion, '2.2.0', '<'):
        $content[] = 'This package does not update versions under 2.2.0';
        return false;

    case version_compare($currentVersion, '2.2.1', '<'):
        $content[] = '+ Fixed a bug causing conflicts between user and group permissions.';

    case version_compare($currentVersion, '2.2.2', '<'):
        $content[] = '+ Set username to the same character size in both users table and user_authorization.';
        $content[] = '+ Fixed typo causing branch installation failure on Postgresql.';

    case version_compare($currentVersion, '2.3.0', '<'):
        $content[] = '<pre>
2.3.0 changes
------------------------
+ Added translate function calls in classes and my_page.php
+ my_page hides translation option if language defines disable selection
+ Added a unrestricted only parameter to Current_User\'s allow and
  authorize functions
+ Dropped references from some constructors
+ Added error check to setPermissions function: won\'t accept empty
  group id
+ Changed id default to zero.
+ Removed unneeded function parameter on getGroups
</pre>
';

    case version_compare($currentVersion, '2.3.1', '<'):
        $content[] = '<pre>';
        $files = array('templates/my_page/user_setting.tpl');
        userUpdateFiles($files, $content);

        $content[] = '
2.3.1 changes
------------------------
+ Added ability for user to set editor preferences
</pre>
';

    case version_compare($currentVersion, '2.3.2', '<'):
        $content[] = '<pre>2.3.2 changes';
        $files = array('img/users.png', 'templates/user_main.tpl');
        userUpdateFiles($files, $content);

        $content[] = '+ Added error check to login.
+ Changed user control panel icon.
+ Fixed template typo that broke IE login.
+ Removed fake French translation (delete mod/users/locale/fr_FR/ directory
+ Permissions are now ordered alphabetically.
+ isUser will now always return false if passed a zero id.
+ Added new function requireLogin that forwards a user to the login
  screen
</pre>';

    case version_compare($currentVersion, '2.4.0', '<'):
        if (!PHPWS_DB::isTable('users_pw_reset')) {
            $new_table = 'CREATE TABLE users_pw_reset (
user_id INT NOT NULL default 0,
authhash CHAR( 32 ) NOT NULL default 0,
timeout INT NOT NULL default 0,
);';
            if (!PHPWS_DB::import($new_table)) {
                $content[] = 'Unable to create users_pw_reset table.';
                return false;
            } else {
                $content[] = 'Created new table: users_pw_reset';
            }
        }
        $files = array('templates/forms/reset_password.tpl', 'templates/forms/forgot.tpl',
                       'conf/config.php', 'templates/usermenus/top.tpl', 'templates/forms/settings.tpl',
                       'templates/my_page/user_setting.tpl');
        $content[] = '<pre>';
        userUpdatefiles($files, $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/users/boost/changes/2_4_0.txt');
        }
        $content[] = '</pre>';

    case version_compare($currentVersion, '2.4.1', '<'):
        $content[] = '<pre>';
        $files = array('conf/languages.php');
        userUpdateFiles($files, $content);

        $content[] = '
2.4.1 changes
------------------------
+ Default item id on permission check functions is now zero instead of
  null. This will make checking permissions a little easier on new items.
+ Bug #1690657 - Changed group select js property to onclick instead
  of onchange. Thanks singletrack.
+ Changed the language abbreviation for Danish
</pre>
';

    case version_compare($currentVersion, '2.4.2', '<'):
        $content[] = '<pre>';
        $files = array('templates/usermenus/Default.tpl');
        userUpdateFiles($files, $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/users/boost/changes/2_4_2.txt');
        }
        $content[] = '</pre>';

    case version_compare($currentVersion, '2.4.3', '<'):
        $content[] = '<pre>';
        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/users/boost/changes/2_4_3.txt');
        }
        $content[] = '</pre>';

    case version_compare($currentVersion, '2.4.4', '<'):
        $content[] = '<pre>';

        $source_dir = PHPWS_SOURCE_DIR . 'mod/users/javascript/';
        $dest_dir   = $home_dir . 'javascript/modules/users/';

        if (PHPWS_File::copy_directory($source_dir, $dest_dir, true)) {
            $content[] = "--- Successfully copied $source_dir to $dest_dir";
        } else {
            $content[] = "--- Could not copy $source_dir to $dest_dir";
        }
        
        $files = array('conf/error.php', 'templates/forms/permissions.tpl', 'templates/forms/permission_pop.tpl');
        userUpdateFiles($files, $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/users/boost/changes/2_4_4.txt');
        }
        $content[] = '</pre>';


    } // End of switch statement

    return TRUE;

}

function userUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'users')) {
        $content[] = '--- Successfully updated the following files:';
    } else {
        $content[] = '--- Was unable to copy the following files:';
    }
    $content[] = '     ' . implode("\n     ", $files);
    $content[] = '';
    
}

?>