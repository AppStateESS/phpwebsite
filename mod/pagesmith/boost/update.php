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

