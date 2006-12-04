<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function whodis_update(&$content, $version)
{
    switch (1) {
    case version_compare($version, '0.0.2', '<'):
        if (PHPWS_Boost::updateFiles(array('templates/admin.tpl'), 'whodis')) {
            $content[] = 'Template upgraded successfully.';
        } else {
            $content[] = 'Template failed to copy to local directory.';
        }
        $content[] = 'Added purge functionality.';
        break;
    }
    return true;
}


?>