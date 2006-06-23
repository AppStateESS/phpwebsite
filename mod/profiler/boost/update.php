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
    }     
    
    return TRUE;
}

?>