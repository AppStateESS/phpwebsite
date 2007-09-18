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

        $files = array('javascript/open_window/head.js', 'javascript/js_calendar/default.php');
        coreUpdateFiles($files, $content);
        
        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/1_6_2.txt');
        }
        $content[] = '</pre>';
    }
    return true;
}

function coreUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'core')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "     " . implode("\n     ", $files);
    $content[] = '';
}


?>