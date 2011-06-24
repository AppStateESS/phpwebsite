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
                       'javascript/open_window/default.php',
                       'javascript/open_window/example.txt',
                       'javascript/required_input/',
                       'javascript/captcha/',
                       'javascript/jquery/jcarousellite.js',
                       'javascript/datepicker/',
                       'javascript/prompt/default.php',
                       'javascript/pick_color/',
                       'javascript/required_input/',
                       'javascript/confirm/body2.js',
                       'javascript/confirm/default.php',
                       'javascript/confirm/readme.txt',
                       'javascript/secure_pop/',
                       'javascript/editors/fckeditor/editor/custom.js',
                       'javascript/editors/fckeditor/default.php',
                       'javascript/editors/fckeditor/body.js',
                       'javascript/jquery/jquery.js',
                       'javascript/jquery/ui.core.js',
                       'javascript/jquery/ui.sortable.js',
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

        case version_compare($version, '1.9.1', '<'):
            $content[] = '<pre>';
            $files = array('javascript/check_all/head.js', 'conf/version.php', 'javascript/editors/fckeditor/head.js');
            coreUpdateFiles($files, $content);
            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/1_9_1.txt');
            }
            $content[] = '</pre>';

        case version_compare($version, '1.9.2', '<'):
            $files = array('javascript/jquery/jquery.js', 'javascript/datepicker/ui.datepicker.css',
                       'javascript/prompt/default.php', 'javascript/editor/fckeditor/custom.js',
                       'javascript/editor/fckeditor/phpwsstyles.xml');
            $content[] = '<pre>';
            coreUpdateFiles($files, $content);
            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/1_9_2.txt');
            }
            $content[] = '</pre>';

        case version_compare($version, '1.9.3', '<'):
            $files = array('javascript/editors/fckeditor/default.php',
                       'javascript/editors/fckeditor/editor/filemanager/browser/default/connectors/phpws/');
            $content[] = '<pre>';
            coreUpdateFiles($files, $content);
            $content[] = '1.9.3 changes
----------------------
+ File
  o Fixed bug with getAllFileTypes. Wouldn\'t grab file_types from
    hub.
  o Fixed problem with PHPWS_File::readDirectory not clearing first path
    after recursion
+ Form
  o Can send an array of names to setRequired now
  o Added REQUIRED_LEGEND to so explain the asterisks
  o Cut down reindexing function by using array_combine
+ Text - Updated isValidInput\'s email check
+ Database - addJoin contains a subselect ability.

Editors
+ Updated FCKeditor file connector code
+ Added security measure to prevent non-users from using upload
  function.</pre>';

        case version_compare($version, '1.9.4', '<'):
            $files = array('javascript/editors/fckeditor/editor/custom.js',
                       'javascript/prompt/default.php',
                       'javascript/editors/fckeditor/body.js',
                       'javascript/editors/fckeditor/default.js',
                       'javascript/editors/fckeditor/editor/custom.js',
                       'javascript/editors/fckeditor/editor/plugins/filecabinet/',
                       'img/ajax-loader-big.gif',
                       'javascript/captcha/freecap/',
                       'javascript/jquery_ui/',
                       'javascript/jquery/head.js',
                       'javascript/jquery/jquery.js',
                       'javascript/datepicker/default.php',
                       'javascript/datepicker/head.js',
                       'javascript/editors/fckeditor/editor/plugins/youtube/',
                       'conf/version.php');
            $content[] = '<pre>';
            coreUpdateFiles($files, $content);
            $content[] = '1.9.4 changes
----------------
+ Updated jquery and added jquery_ui
+ Datepicker uses new jquery_ui
+ Freecap - fixed file names in code
+ Core - requireInc now recognizes "core" as a first parameter
+ Added notes to datepicker readme.
+ Patch #2500039 by Olivier Sannier - cleaned up blog conversion.
+ Fixed phatform convert. Wasn\'t flipping convert flag when done.
+ Changed freecap file names to allow copying to branches.
+ Updated Norwegian translation from Anders. THANKS ANDERS!
+ Foundation of file cabinet integration with FCKeditor
+ Setup now checking for pass-call by reference default.
+ DBPager - put in some code to prevent multiple joins using the same
  column table matching
+ Fixed comment in Cache
+ Added dot files to file_types.php
+ Javascript:prompt - added code to try and prevent tags and new lines
  from breaking the prompt
+ Setup - some people experiencing problems with the File.php
  require. Added the ./
+ FCKeditor - found problem with full screen edit. Disabling autogroup
  plugin inclusion until fixed.
+ Added Tommy to credits</pre>';

        case version_compare($version, '1.9.5', '<'):
            $content[] = '<pre>';
            coreUpdateFiles(array('conf/version.php'), $content);
            $content[] = '1.9.5 changes
------------------
+ Patch# 2800703 - fixed checkBranch for newer php5. Thanks Andrew Patterson.
+ addValues in Link.php accepts arrays of values now
+ Added second parameter to PHPWS_Core::stripObjValues - strip_null.
  If true, then null values will not be added to array. False will
  include them.
+ Removed pass-by reference check from Setup.
+ Added some status checks to setup.
+ deity only check in test function makes sure user session exists.</pre>';

        case version_compare($version, '1.9.6', '<'):
            $content[] = '<pre>';
            coreUpdateFiles(array('javascript/editors/fckeditor/'), $content);
            $content[] = '1.9.6 changes
-------------------
+ recaptcha script given full path to settings file
+ Time.php - Added a intval to the relativeTime timestamp.
+ Moved fckeditor connector directory
+ Script tags should now appear for anon users if config.php allows them.
+ Changed confirm body link. href="#" confuses poor IE
+ "param" added to text_settings.php
+ fckeditor default.php changed to fix file relative links</pre>';

        case version_compare($version, '1.9.7', '<'):
            $content[] = '<pre>';
            coreUpdateFiles(array('javascript/editors/fckeditor/default.php', 'javascript/editors/fckeditor/editor/filemanager/connectors/phpws/config.php', 'javascript/editors/fckeditor/head.js'), $content);
            $content[] = '1.9.7 changes
-------------------
+ Fixed browser incompatibility issue with editors.
+ Added ability to set fckeditor\'s image, media, and files directory in the config.php
</pre>';

        case version_compare($version, '1.9.8', '<'):
            coreUpdateFiles(array('javascript/captcha/freecap/ht_freecap_words'), $content);
            $content[] = '<pre>1.9.8 changes
-----------------
+ Updated Pear library.
+ Added new class DB2.
+ PHPWS_DB:
  o changed count to accommodate group by and single col pulls
  o Added code to prevent multiple identical joins.
+ Text : Fixed bug in fixAnchors that didn\'t allow it to work with hyphenated ids
+ DBPager : fixed a problem with multiple joinResult calls to same table.</pre>';

        case version_compare($version, '2.0.0', '<'):
            if (PHPWS_Core::isBranch()) {
                $content[] = 'This update can only be performed on the hub.';
                return false;
            }
            if (!PHPWS_Boost::inBranch()) {
                $config_dir = PHPWS_SOURCE_DIR . 'config/core/';


                if (!is_writable($config_dir)) {
                    $content[] = '<p>Core update can not continue until your hub installation\'s <strong>config/core/</strong> directory is writable.</p>';
                    return false;
                }

                $source_http = sprintf("<?php\ndefine('PHPWS_SOURCE_HTTP', '%s');\n?>", PHPWS_CORE::getHomeHttp());
                if (!file_put_contents($config_dir . 'source.php', $source_http)) {
                    $content[] = '<p>Could not create config/core/source.php file.</p>';
                    return false;
                }

                $content[] = <<<EOT
                <pre>2.0.0 changes
-----------------
+ Hub/Branch overhaul. Branches pull config, templates, javascript,
  and theme files from hub instead of locally.
+ Added Icon class. Standardizes icons and prevents overlap.
+ Added Tag class: extendable class used with Image and Form2.
+ Added tag_implode function.
+ Created Form2 class.
+ Added CKeditor.
+ Added Lightbox.
+ getConfigFile does not throw error now.
+ Dutch translation updated.
+ Added autoload function for core classes.
+ Source dir derived from file path and not simply "./"
+ Added Image class.
+ Critical functions changed to throw exceptions.
+ Setup steamlined.</pre>

<p><strong>Note:</strong> this update creates a backup of your config/core/config.php file named<br />
config-prior170.php.<br />
If your installation is working, this file may be safely deleted.</p>
<p>IMPORTANT! Many settings in the old config.php have been moved to core/conf/defines.php in the hub.
You can delete all settings <strong>except</strong> the following:</p>
<ul><li>PHPWS_SOURCE_DIR</li>
<li>PHPWS_HOME_DIR</li>
<li>PHPWS_SOURCE_HTTP</li>
<li>SITE_HASH</li>
<li>PHPWS_DSN</li>
<li>PHPWS_TABLE_PREFIX</li></ul>

EOT;
            }
            if ($branch = PHPWS_Boost::inBranch(true)) {
                if (!PHPWS_File::copy_directory(PHPWS_SOURCE_DIR . 'javascript/editors/fckeditor/', $branch->directory . 'javascript/editors/fckeditor', true)) {
                    mkdir($branch->directory . 'images/ckeditor/');
                    $this->content[] = dgettext('branch', 'Failed to copy FCKeditor to branch.');
                }
                else {
                    $content[] = 'FCKeditor not copied to branch. Check directory permissions.';
                }
            } else {
                mkdir(PHPWS_SOURCE_DIR . 'images/ckeditor/');
            }

        case version_compare($version, '2.0.1', '<'):
            $content[] = <<<UPDATES
            <pre>2.0.1 changes
----------------------
+ Fixed captcha trying to pull from branch directory.
+ Fixed templates ignoring use_hub_themes setting.
+ Image class will accept tilde in source directory.
+ Database fix for insert and update on multiple tables.
+ Powerpoint types added to file check.
+ Background mode added to index (allows for selective loading on Ajax calls)
+ Freecap ereg_replace updated to preg_replace
+ Fix for js_calendar
+ Fix for javascript alert script
+ Fixes for Fckeditor and Tinymce
+ Inclusion of ngBackup
</pre>
UPDATES;
 case version_compare($version, '2.0.2', '<'):
         $content[] = <<<UPDATES
<pre>2.0.2 changes
----------------------
+ Added To top icon
+ Fixed table bug where names used in foreign key constrains (in CREATE TABLE statements) were not prefixed correctly
+ Removed HTML from some translations.
+ Fixed bug causing a table name to be repeated in a JOIN statement
+ Fixed some PHP notice errors.
+ Fixed some hub icon directory problems (Thanks Eloi).
+ Image resizing reworked to correct problems with irregular images.
+ ngBackup updates.
+ Fixed some templating issues (Thanks Tommy, Eloi).
+ URL validity checking in Text was made more robust.
+ Fixed some label id issues in form.
+ Fixed a loadDimensions error in Image (Thanks Eloi)
+ Fixed DBPager duplicating table insertions (Thanks Eloi)
+ Fixed some PEAR PHP 5 warnings.
+ Changed URL forwarding - if first value after the module is numeric, it is cast as an id.
+ Removed deny-all .htaccess file from file directory.
</pre>
UPDATES;
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