<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

function profiler_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.1.0', '<'):
        PHPWS_DB::dropTable('profiler_item_permissions');


    case version_compare($currentVersion, '0.1.2', '<'):
        if (PHPWS_Boost::updateFiles(array('templates/forms/division_list.tpl'), 'profiler')) {
            $content[] = 'Copied new division list template locally.';
        } else {
            $content[] = 'Unable to copy template locally.';
            return false;
        }

    case version_compare($currentVersion, '0.1.4', '<'):
        $db = & new PHPWS_DB('profiler_division');
        $result = $db->renameTableColumn('show_sidebar', 'show_homepage');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to update profiler.';
            return false;
        } else {
            $content[] = 'Moved profile display to home page.';
        }
        PHPWS_Boost::updateFiles(array('conf/config.php'));
    }     
    
    return TRUE;
}

?>