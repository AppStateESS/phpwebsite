<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function calendar_update(&$content, $version)
{

    switch ($version) {
    case version_compare($version, '1.1.0', '<'):
        $files[] = 'templates/style.css';
        $files[] = 'templates/admin/settings.tpl';
        $files[] = 'templates/admin/forms/edit_schedule.tpl';
        if (PHPWS_Boost::updateFiles($files, 'calendar')) {
            $content[] = 'Template files updated.';
        } else {
            $content[] = 'Failed to copy template files.';
        }
        $content[] = 'New - event displays as Busy to the public if set as such.';
        $content[] = 'New - Settings tab returns with a few basic settings.';
    }

    return true;
}

?>