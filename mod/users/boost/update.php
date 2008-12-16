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

    case version_compare($currentVersion, '2.4.5', '<'):
        $content[] = '<pre>';

        $files = array('conf/error.php', 'conf/languages.php', 'templates/forms/settings.tpl',
                       'templates/manager/groups.tpl');
        userUpdateFiles($files, $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/users/boost/changes/2_4_5.txt');
        }
        $content[] = '</pre>';

    case version_compare($currentVersion, '2.4.6', '<'):
        $content[] = '<pre>';
        $files = array('templates/forms/forgot.tpl');
        userUpdateFiles($files, $content);
        if (!PHPWS_Boost::inBranch()) {
            $content[] = '
2.4.6 changes
-------------------
+ Added error check to permission menu.
+ Error for missing user groups now reports user id.
+ Forgot password will work if CAPTCHA is disabled.
+ Using new savePermissions function instead of save.
+ Current_User was calling giveItemPermissions incorrectly.';
        }
        $content[] = '</pre>';

    case version_compare($currentVersion, '2.4.7', '<'):
        $content[] = '<pre>
2.4.7 changes
-------------------
+ Removed global authorization from change password check since it is not
  written yet.
</pre>';

    case version_compare($currentVersion, '2.4.9', '<'):
        $content[] = '<pre>';

        if (PHPWS_Core::isBranch() || PHPWS_Boost::inBranch()) {
            $user_db = new PHPWS_DB('users');
            $user_db->addWhere('deity', 1);
            $user_db->addColumn('id');
            $user_db->addColumn('username');
            $user_db->setIndexBy('id');
            $user_ids = $user_db->select('col');

            if (!empty($user_ids) && !PHPWS_Error::logIfError($user_ids)) {
                $group_db = new PHPWS_DB('users_groups');
                foreach ($user_ids as $id=>$username) {
                    $group_db->addWhere('user_id', $id);
                    $result = $group_db->select('row');
                    if (!$result) {
                        $group_db->reset();
                        $group_db->addValue('active', 1);
                        $group_db->addValue('name', $username);
                        $group_db->addValue('user_id', $id);
                        if (!PHPWS_Error::logIfError($group_db->insert())) {
                            $content[] = '--- Created missing group for user: ' . $username;
                        }
                    }
                    $group_db->reset();
                }
            }
        }

        $content[] = '2.4.9 changes
-----------------
+ Raised sql character limit in default username, display_name, and
  group name installs.
+ Fixed bug with forbidden usernames
+ Added a function to group to remove its permissions upon deletion.
+ Bookmark won\'t return a user to a authkey page if their session dies.
+ Fixed bug #1850815 : unknown function itemIsAllowed in Permission.php
+ My Pages are unregistered on module removal.
+ My Page tab stays fixed.
</pre>';

    case version_compare($currentVersion, '2.5.0', '<'):
        $content[] = '<pre>';
        $files = array('templates/forms/memberlist.tpl', 'templates/forms/userForm.tpl',
                       'javascript/generate/head.js', 'templates/manager/groups.tpl',
                       'templates/manager/users.tpl');
        userUpdateFiles($files, $content);

        $content[] = '2.5.0 changes
-------------------
+ Members\' names alphabetized
+ New user email notification added.
+ Fixed member listing  dropping names past 10.
+ Added random password generator on user edit form.
+ Removed reference from Action.php causing php notice.
+ Changed redundant static method call in Permission.
+ Added dash to allowed display name characters.
+ Added \pL to display name characters.
+ Users will now query modules should a user get deleted.
+ Added an error check to Permissions.
+ Users will now look for remove_user.php in all modules\' inc/
  directory in order to run the remove_user function.
+ Using pager\'s addSortHeaders in user and group listing
+ Added display name to pager search.
</pre>';

    case version_compare($currentVersion, '2.6.0', '<'):
        $content[] = '<pre>';
        Users_Permission::registerPermissions('users', $content);
        $db = new PHPWS_DB('users_auth_scripts');
        $db->addWhere('filename', 'local.php');
        $db->addColumn('id');
        $auth_id = $db->select('one');
        PHPWS_Settings::set('users', 'local_script', $auth_id);
        PHPWS_Settings::save('users');
        $files = array('conf/languages.php', 'templates/my_page/user_setting.tpl',
                       'templates/usermenus/css.tpl', 'img/permission.png', 'templates/forms/userForm.tpl');
        userUpdateFiles($files, $content);
        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/users/boost/changes/2_6_0.txt');
        }
        $content[] = '</pre>';

    case version_compare($currentVersion, '2.6.1', '<'):
        $content[] = '<pre>2.6.1 changes
------------------
+ requireLogin now reroutes dependant on the user authorization
+ If the user\'s group is missing when they are updated, a new one is
  properly created. Prior to the fix, a new group was created without an
  assigned user id.
+ Added error message to my page if update goes bad.
</pre>';

    } // End of switch statement

    return TRUE;

}

function userUpdateFiles($files, &$content)
{
    $result = PHPWS_Boost::updateFiles($files, 'users', true);
    
    if (!is_array($result)) {
        $content[] = '--- Successfully updated the following files:';
        $content[] = '     ' . implode("\n     ", $files);
    } else {
        $content[] = '--- Was unable to copy the following files:';
        $content[] = '     ' . implode("\n     ", $result);
    }
    $content[] = '';
}

?>
