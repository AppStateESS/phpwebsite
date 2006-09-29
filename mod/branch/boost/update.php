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
        $content[] = 'Fix - Fixed Branch not copying javascript directory over';
        $content[] = 'Fix - Branch now checks for writability of directory before trying to copy (Bug #1552259).';
        $content[] = 'Fix - Added permission file and changed update to register to Users (Bug #1544082).';
        $content[] = 'Fix - Hopefully patched up problems with a Windows installation';
        $content[] = 'Fix - Removed earlier Branch installation check since branch will not be installed on the branch site.';
        $content[] = 'Fix - Updated configuration file template to match setup\'s';
        $content[] = 'Fix - Branch removal now available.';
        $content[] = 'Fix - A link now appears to set the allowed modules to a newly created branch.';
        $content[] = 'Fix - Disconnect added before testing branch connections.';
        $content[] = 'Fix - Core can call branch even if not installed. Added check to disallow continuation.';
    }
    return true;
}

?>