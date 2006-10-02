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

    case version_compare($currentVersion, '2.1.3', '<'):
        $content[] = '- Unsets box variable of uninstalled modules';
        $content[] = '- Added new theme variable ONLY_TITLE';
        $content[] = '- Boxes with duplicate content variable names were not getting 
added properly. Fixed by having the module name verified.';

    case version_compare($currentVersion, '2.1.4', '<'):
        $files = array();
        $files[] = 'templates/move_box_select.tpl';
        PHPWS_Boost::updateFiles($files, 'layout');
        $content[] = '- Fixed bug #1551163 - theme changes were not getting saved.';
        $content[] = '- Added ability to "lock out" some theme variables to prevent boxes from being moved into them';
        $content[] = '- Added ability to reset a box';
        $content[] = '- Layout variables now appear when move box is initialized';
        $content[] = '- Changed import check for IE. Hopefully will detect it better.';
        $content[] = '- Layout will now use blank.tpl (if exists) with a nakedDisplay call';
        $content[] = '- Layout uses the local module style sheet on a branch even if force_mod_template is set.';

    }
    return TRUE;
}

?>