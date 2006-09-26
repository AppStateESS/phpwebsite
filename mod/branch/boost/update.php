<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function branch_update(&$content, $version)
{
    switch ($version) {
    case version_compare($version, '0.1.2', '<'):
        $files[] = 'templates/config.tpl';
        $content[] = '- Patched problems preventing a Windows branch from installing.';
        $content[] = '- Updated configuration file template to match setup\'s.';
        $content[] = '- Ability to remove a branch added.';
        $content[] = '- A link now leads the creator of a new branch to the module setup page.';

        if (PHPWS_Boost::updateFiles($files, 'branch')) {
            $content[] = 'Configuration template file copied successfully.';
        } else {
            $content[] = 'Configuration template file was not copied successfully.';
        }

    case version_compare($version, '0.1.4', '<'):
        PHPWS_Boost::registerMyModule('branch', 'users', $content);
    }
    return true;
}

?>