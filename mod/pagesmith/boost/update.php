<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */


function pagesmith_update(&$content, $currentVersion)
{
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
        $files = array('templates/page_list.tpl');
        pagesmithUpdateFiles($files, $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/pagesmith/boost/changes/1_0_1.txt');
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

