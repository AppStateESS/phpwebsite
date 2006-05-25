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
    }

    return TRUE;
}

?>