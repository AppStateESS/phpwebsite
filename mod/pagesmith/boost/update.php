<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */


function pagesmith_update(&$content, $currentVersion)
{
    $home_dir = PHPWS_Boost::getHomeDir();

    switch ($currentVersion) {
    case version_compare($currentVersion, '1.0.1', '<'):
        $content[] = '<pre>';

        $db = new PHPWS_DB('ps_page');
        $result = $db->addTableColumn('front_page', 'smallint NOT NULL default 0');
        if (PHPWS_Error::logIfError($result)) {
            $content[] = "--- Unable to create table column 'front_page' on ps_page table.</pre>";
            return false;
        } else {
            $content[] = "--- Created 'front_page' column on ps_page table.";
        }
        $files = array('templates/page_list.tpl', 'javascript/update/head.js', 'conf/error.php',
                       'templates/page_templates/simple/page.tpl', 'templates/page_templates/twocolumns/page.tpl');
        pagesmithUpdateFiles($files, $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/pagesmith/boost/changes/1_0_1.txt');
        }
        $content[] = '</pre>';

    case version_compare($currentVersion, '1.0.2', '<'):
        $content[] = '<pre>';
        $dest_dir   = $home_dir . 'javascript/modules/pagesmith/passinfo/';

        if (!is_dir($dest_dir)) {
            if (is_writable($home_dir . 'javascript/modules/pagesmith/') && @mkdir($dest_dir)) {
                $content[] = '--- SUCCEEDED creating "javascript/modules/passinfo/" directory.';
            } else {
                $content[] = 'PageSmith 1.0.2 requires the javascript/modules/pagesmith/ directory be writable.</pre>';
                return false;
            }
        } elseif (!is_writable($dest_dir)) {
            $content[] = 'PageSmith 1.0.2 requires the javascript/modules/pagesmith/passinfo/ directory be writable.</pre>';
            return false;
        }

        $source_dir = PHPWS_SOURCE_DIR . 'mod/pagesmith/javascript/passinfo/';
        if (!PHPWS_File::copy_directory($source_dir, $dest_dir)) {
            $content[] = "--- FAILED copying to $dest_dir.</pre>";
            return false;
        } else {
            $content[] = "--- SUCCEEDED copying to $dest_dir directory.";
        }

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/pagesmith/boost/changes/1_0_2.txt');
        }
        $content[] = '</pre>';

    case version_compare($currentVersion, '1.0.3', '<'):
        $content[] = '<pre>';

        $source_dir = PHPWS_SOURCE_DIR . 'mod/pagesmith/templates/page_templates/text_only/';
        $dest_dir   = $home_dir . 'templates/pagesmith/page_templates/text_only/';

        if (PHPWS_File::copy_directory($source_dir, $dest_dir)) {
            $content[] = "--- Successfully copied $source_dir\n    to $dest_dir\n";
        } else {
            $content[] = "--- Failed to copy $source_dir to $dest_dir</pre>";
            return false;
        }
        $files = array('conf/config.php');
        pagesmithUpdateFiles($files, $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/pagesmith/boost/changes/1_0_3.txt');
        }
        $content[] = '</pre>';

    case version_compare($currentVersion, '1.0.4', '<'):
        $content[] = '<pre>';
        $db = new PHPWS_DB('phpws_key');
        $db->addWhere('module', 'pagesmith');
        $db->addValue('edit_permission', 'edit_page');
        if (PHPWS_Error::logIfError($db->update())) {
            $content[] = 'Unable to update phpws_key table.</pre>';
            return false;
        } else {
            $content[] = 'Updated phpws_key table.';
        }
        $content[] = '1.0.4 changes
------------------
+ Fixed pagesmith edit permission.
+ PageSmith home pages were missing edit link.</pre>';

    case version_compare($currentVersion, '1.0.5', '<'):
        $content[] = '<pre>';
        pagesmithUpdateFiles(array('templates/page_templates/text_only/page.tpl'), $content);
        $content[] ='1.0.5 changes
----------------
+ Changed wording on move to front functionality
+ Added move to front to miniadmin
+ Fixed text_only template. Missing closing div tag.
</pre>';

    case version_compare($currentVersion, '1.0.6', '<'):
        $content[] = '<pre>
1.0.6 changes
-------------
+ Small fix to allow linkable images on cached pages.
</pre>';

    case version_compare($currentVersion, '1.0.7', '<'):
        $content[] = '<pre>';
        pagesmithUpdateFiles(array('templates/settings.tpl'), $content);

        $content[] = '1.0.7 changes
-------------
+ PageSmith can be set to automatically create a link when a new page
  is created.
+ Changing a page title now updates the menu link.
</pre>';

    case version_compare($currentVersion, '1.1.0', '<'):
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $content[] = '<pre>';
        Cabinet::convertImagesToFileAssoc('ps_block', 'type_id');
        $content[] = '--- Images converted for File Cabinet 2.0.0.';
        pagesmithUpdateFiles(array('javascript/passinfo/head.js'), $content);

        $content[] = '1.1.0 changes
-------------
+ PageSmith conforms to new File Cabinet update.
+ Added url parser to passinfo script to allow images to work with fck better.
</pre>';

    } // end switch

    return true;
}

function pagesmithUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'pagesmith')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n     ", $files);
    $content[] = '';
}

?>

