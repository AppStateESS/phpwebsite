<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function branch_update(&$content, $version)
{
    switch ($version) {
    case version_compare($version, '1.1.2', '<'):
        $files[] = 'templates/config.tpl';
        $content[] = '- Patched problems preventing a Windows branch from installing.';
        $content[] = '- Updated configuration file template to match setup\'s.';

        if (PHPWS_Boost::updateFiles($files, 'branch')) {
            $content[] = 'Configuration template file copied successfully.';
        } else {
            $content[] = 'Configuration template file was not copied successfully.';
        }

    }
    return true;
}

?>