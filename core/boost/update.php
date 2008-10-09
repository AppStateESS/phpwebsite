<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

function core_update(&$content, $version) {
    $home_directory = PHPWS_Boost::getHomeDir();

    $boost_module = new PHPWS_Module('boost');
    if (version_compare($boost_module->version, '2.0.0', '<')) {
        $content[] = '<h1>Important!</h1>
<p>Core cannot properly update your installation yet. You will need to grab a recent copy of Boost (1.9.8 or higher).</p>
<p>Do not try to update Boost, just copy it into the mod/boost/ directory. Once you have done that, update the Core, then update Boost.</p>
';
        return false;
    }

    $content[] = '';
    // Versions previous to 1.5.0 removed 25Jul2007.
    switch (1) {
    case version_compare($version, '1.5.0', '<'):
        $content[] = '<pre>Core version prior to version 1.5.0 are not supported. Please download version 1.5.2 or earlier.</pre>';
        break;

    case version_compare($version, '1.5.1', '<'):
        $content[] = '<pre>1.5.1 changes
--------------
+ Added error check to mysql.php file to prevent over-valued CHARs
+ Fixed bug #1708507. Extra string character.
+ Database - bug #1713219 - Added auto detect parameter to insert
             function in saveObject
</pre>';

    case version_compare($version, '1.5.2', '<'):
        $content[] = '<pre>1.5.2 changes
--------------
+ Added setAnchor function to DBPager.
</pre>';

    case version_compare($version, '1.6.0', '<'):
        if (isset($GLOBALS['boost_branch_dir'])) {
            $fck_destination = $home_directory . 'javascript/editors/fckeditor';
            $fck_source = PHPWS_SOURCE_DIR . 'javascript/editors/fckeditor';

            if (!is_dir($fck_destination)) {
                if (!is_writable($home_directory . 'javascript/editors/')) {
                    $content[] = '<pre>The following must be writable for the core update to continue:';
                    $content[] = $home_directory . 'javascript/editors/</pre>';
                    return false;
            }
                if (PHPWS_File::copy_directory($fck_source, $fck_destination)) {
                    $content[] = 'Successfully copied the fckeditor directory to the branch site.';
                } else {
                    $content[] = 'Was unable to copy the fckeditor directory to the branch site.';
                }
            }

            $cal_destination = $home_directory . 'javascript/js_calendar';
            $cal_source = PHPWS_SOURCE_DIR . 'javascript/js_calendar';

            if (!is_dir($cal_destination)) {
                if (!is_writable($home_directory . 'javascript/')) {
                    $content[] = '<pre>The following must be writable for the core update to continue:';
                    $content[] = $home_directory . 'javascript/</pre>';
                    return false;
            }
                if (PHPWS_File::copy_directory($cal_source, $cal_destination)) {
                    $content[] = 'Successfully copied the js_calendar directory to the branch site.';
                } else {
                    $content[] = 'Was unable to copy the js_calendar directory to the branch site.';
                }
            }
        }

        if (PHPWS_File::rmdir($home_directory . 'javascript/editors/FCKeditor/')) {
            $content[] = 'Removed FCKeditor directory.';
        } else {
            $content[] = 'Could not remove FCKeditor directory. May be some confusion with old version.';
        }


        $files = array('conf/formConfig.php', 'conf/version.php',
                       'conf/file_types.php', 'javascript/select_confirm/README.txt',
                       'javascript/open_window/default.php', 'javascript/open_window/body2.js',
                       'javascript/ajax/requester.js', 'javascript/ajax/default.php',
                       'javascript/ajax/readme.txt', 'javascript/ajax/head.js',
                       'javascript/editors/tinymce/default.php', 'javascript/editors/tinymce/body.js');
        $content[] = '<pre>';
        coreUpdateFiles($files, $content);

        $content[] = '';
        $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/1_6_0.txt');
        $content[] = '</pre>';

    case version_compare($version, '1.6.1', '<'):
        $content[] = '<pre>';

        if (PHPWS_Boost::inBranch() || PHPWS_Core::isBranch()) {
            $yui_destination = $home_directory . 'javascript/editors/yui';
            $yui_source = PHPWS_SOURCE_DIR . 'javascript/editors/yui';

            if (!is_dir($fck_destination)) {
                if (!is_writable($home_directory . 'javascript/editors/')) {
                    $content[] = 'The following must be writable for the core update to continue:';
                    $content[] = $home_directory . 'javascript/editors/</pre>';
                    return false;
                }
            }

            if (PHPWS_File::copy_directory($yui_source, $yui_destination)) {
                $content[] = '--- Successfully copied the yui (Yahoo editor) directory to the branch site.';
            } else {
                $content[] = '--- Was unable to copy the yui (Yahoo editor) directory to the branch site.';
            }
        }

        @copy(PHPWS_SOURCE_DIR . 'core/conf/version.php', 'config/core/version.php');

        $files = array('conf/error.php', 'javascript/editors/fckeditor/default.php',
                       'javascript/editors/fckeditor/editor/custom.js');
        coreUpdateFiles($files, $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/1_6_1.txt');
        }
        $content[] = '</pre>';

    case version_compare($version, '1.6.2', '<'):
        $content[] = '<pre>';

        $files = array('javascript/open_window/head.js', 'javascript/js_calendar/default.php',
                       'javascript/js_calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.css',
                       'javascript/js_calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.js',
                       'javascript/js_calendar/head.js', 'javascript/js_calendar/phpws_addon.js');

        coreUpdateFiles($files, $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/1_6_2.txt');
        }
        $content[] = '</pre>';

    case version_compare($version, '1.6.3', '<'):
        $content[] = '<pre>';

        $db = new PHPWS_DB('registered');
        if (!$db->isTableColumn('registered_to')) {
            if (PHPWS_Error::logIfError($db->renameTableColumn('registered', 'registered_to'))) {
                $content[] = '--- Could not rename registered table\'s registered column to registered_to.</pre>';
                return false;
            } else {
                $content[] = '--- Renamed registered table\'s registered column to registered_to.';
            }
        }

        $files = array('templates/graph.tpl', 'img/ajax-loader.gif', 'conf/error.php', 'conf/version.php');
        coreUpdateFiles($files, $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/1_6_3.txt');
        }
        $content[] = '</pre>';

    case version_compare($version, '1.7.0', '<'):
        $content[] = '<pre>';
        $files = array('templates/graph.tpl', 'img/ajax-loader.gif', 'conf/file_types.php', 'conf/version.php');
        coreUpdateFiles($files, $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/1_7_0.txt');
        }
        $content[] = '</pre>';

    case version_compare($version, '1.7.1', '<'):
        $content[] = '<pre>';

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/1_7_1.txt');
        }
        $content[] = '</pre>';

    case version_compare($version, '1.7.2', '<'):
        $content[] = '<pre>';

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/1_7_2.txt');
        }
        $content[] = '</pre>';

    case version_compare($version, '1.8.0', '<'):
        $htaccess = $home_directory . '.htaccess';
        $new_htaccess = PHPWS_SOURCE_DIR . 'core/inc/htaccess';
        $backup_loc = $home_directory . 'files/.backup_htaccess';

        if (!isset($_GET['ignore_htaccess'])) {
            $ignore = sprintf('<p>You will need to replace your current .htaccess file with the new copy stored at %s<br />
<a href="index.php?module=boost&opmod=core&action=update_core&authkey=%s&ignore_htaccess=1">You can skip the .htaccess copy process by clicking here.</a></p>',
                              $new_htaccess, Current_User::getAuthKey());

            if (is_file($htaccess) && !is_writable($htaccess)) {
                $content[] = 'phpWebSite needs to update your .htaccess file. Please make it writable for this update.
When done, you may make it unwritable again.';
                $content[] = $ignore;
                return false;
            } else {
                if (is_file($htaccess) && !@copy($htaccess,  $backup_loc)) {
                    $content[] = 'Unable to backup your .htaccess file.';
                    $content[] = $ignore;
                    return false;
                } else {
                    $content[] = 'Backed up old .htaccess file to ./files/ directory.';
                }

                if (!@copy($new_htaccess, $htaccess)) {
                    $content[] = 'Unable to copy new .htaccess file to hub/branch home directory.
You will need to make your hub/branch home directory writable if the file doesn\'t exist.';
                    $content[] = $ignore;
                    return false;
                } else {
                    $content[] = 'Copied new .htaccess file to home directory.';
                }
            }
        }

        $content[] = '<pre>';
        $files = array('conf/core_modules.php', 'conf/file_types.php',
                       'conf/text_settings.php', 'conf/version.php');

        coreUpdateFiles($files, $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/1_8_0.txt');
        }

        if (PHPWS_Core::isBranch() || PHPWS_Boost::inBranch()) {
            $files = array('javascript/ajax/requester.js', 'javascript/captcha/freecap/freecap.php', 'javascript/check_all/head.js',
                           'javascript/confirm/default.php', 'javascript/jquery/head.js', 'javascript/jquery/jquery.js',
                           'javascript/jquery/jquery.selectboxes.js', 'javascript/multiple_select/body.js',
                           'javascript/multiple_select/head.js', 'javascript/multiple_select/default.php');

            coreUpdateFiles($files, $content);

            if (PHPWS_File::copy_directory(PHPWS_SOURCE_DIR . 'javascript/editors/fckeditor/', $home_directory . 'javascript/editors/fckeditor/')) {
                $content[] = 'Successfully updated branch\'s FCKeditor.';
            } else {
                $content[] = 'Unsuccessfully updated branch\'s FCKeditor.';
            }

            if (PHPWS_File::copy_directory(PHPWS_SOURCE_DIR . 'javascript/editors/tinymce/', $home_directory . 'javascript/editors/tinymce/')) {
                $content[] = 'Successfully updated branch\'s TinyMCE.';
            } else {
                $content[] = 'Unsuccessfully updated branch\'s TinyMCE.';
            }
        }
        $content[] = '</pre>';

    case version_compare($version, '1.8.1', '<'):
        $content[] = '<pre>';
        coreUpdateFiles(array('conf/version.php'), $content);
        $content[] = '1.8.1 Changes
-----------------
+ Change to pullTables to allow create unique index to work properly.
+ Form class was ignoring the use_auth_key variable.
+ Fixed atHome function.</pre>';

    case version_compare($version, '1.8.2', '<'):
        $content[] = '<pre>';
        $db = new PHPWS_DB('phpws_key');
        if (!$db->isTableColumn('show_after')) {
            $db->addTableColumn('show_after', "int NOT NULL default 0");
            $db->addTableColumn('hide_after', "int NOT NULL default 2147400000");
            $db->addValue('hide_after', '2147400000');
            $db->update();
            $content[] = 'show_after and hide_after columns added to key table.';
        }

        $files = array('javascript/prompt/body2.js', 'javascript/prompt/default.php', 'javascript/prompt/readme.txt',
                       'conf/language.php', 'javascript/editors/fckeditor/editor/phpwsstyles.xml', 'javascript/check_all/body.js',
                       'javascript/check_all/default.php', 'javascript/check_all/head.js', 'javascript/check_all/README.txt',
                       'javascript/open_window/default.php', 'javascript/editors/tinymce/default.php',
                       'javascript/editors/tinymce/head.js', 'javascript/editors/tinymce/limited.js',
                       'javascript/editors/tinymce/custom.js', 'javascript/editors/tinymce/default.php',
                       'conf/smiles.pak', 'img/smilies/banana.gif', 'conf/version.php',
                       'javascript/js_calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.css',
                       'javascript/js_calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.js',
                       'javascript/js_calendar/body5.js', 'javascript/js_calendar/default.php', 'javascript/js_calendar/readme.txt');

        coreUpdateFiles($files, $content);

        if (@copy(PHPWS_SOURCE_DIR . 'core/inc/htaccess', $home_directory . '.htaccess')) {
            $content[] = 'Copied standard .htaccess file to root directory.';
        } else {
            $content[] = 'Failed to copy standard .htaccess file to root directory. You may find it in core/inc/htaccess.';
        }

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/1_8_2.txt');
        }

        $content[] = '</pre>';

    case version_compare($version, '1.9.0', '<'):
        if (@copy(PHPWS_SOURCE_DIR . 'core/inc/htaccess', $home_directory . '.htaccess')) {
            $content[] = 'Copied standard .htaccess file to root directory.';
        } else {
            $content[] = 'Failed to copy standard .htaccess file to root directory. YOU MUST COPY IT TO YOUR ROOT INSTALLATION DIRECTORY!
<br />cp core/inc/htaccess ./.htaccess';
        }

        $content[] = '<pre>';
        $files = array('javascript/js_calendar/default.php',
                       'javascript/js_calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.js',
                       'javascript/js_calendar/phpws_addon.js',
                       'javascript/open_window/body.js',
                       'javascript/open_window/body2.js',
                       'javascript/open_window/example.txt',
                       'javascript/required_input/',
                       'javascript/captcha/',
                       'javascript/jquery/jcarousellite.js',
                       'javascript/datepicker/',
                       'javascript/prompt/default.php',
                       'javascript/pick_color/',
                       'javascript/required_input/',
                       'conf/text_settings.php',
                       'conf/version.php',
                       'conf/file_types.php',
                       'conf/smiles.pak',
                       'img/core/smilies/rofl.gif');

        coreUpdateFiles($files, $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/1_9_0.txt');
        }
        $content[] = '</pre>';
    }
    return true;
}

function coreUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'core')) {
        $content[] = '--- Updated the following files:';
        $good = true;
    } else {
        $content[] = '--- Unable to update the following files:';
        $good = false;
    }
    $content[] = "     " . implode("\n     ", $files);
    $content[] = '';
    return $good;
}


?>