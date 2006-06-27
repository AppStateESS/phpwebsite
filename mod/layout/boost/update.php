<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function layout_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '2.1.0', '<'):
        $db = & new PHPWS_DB;
        $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/layout/boost/update210.sql');
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = _('+ Styles per page added.');

    case version_compare($currentVersion, '2.1.2', '<'):
        $content[] = '+ Fixed header and footer form with javascript editors.';
    }
    return TRUE;
}

?>