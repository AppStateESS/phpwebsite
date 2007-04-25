<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


function block_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.0.2', '<'):
        $result = block_update_002($content);
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = '+ added the edit permission';

    case version_compare($currentVersion, '0.0.3', '<'):
        $result = block_update_003();
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = '+ added key column';

    case version_compare($currentVersion, '0.0.4', '<'):
        $result = block_update_004($content);
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = '+ added edit icon to block';

    case version_compare($currentVersion, '1.0.0', '<'):
        $files[] = 'templates/sample.tpl';
        $result = PHPWS_Boost::updateFiles($files, 'block');
        if (!$result) {
            $content[] = 'Failed to add template file locally.';
            return FALSE;
        }
        $content[] = '+ Changed to only display miniadmin if javascript is enabled';
        $content[] = '+ Fixed bug #1552210 - extra breaks created per edit.';
        $content[] = '+ Added admin edit icon to block view';
        $content[] = '+ Block content now parses smart tags';

    case version_compare($currentVersion, '1.0.1', '<'):
        $content[] = 'Fix - Bug #1552210 Prevent double new lines.';

    case version_compare($currentVersion, '1.0.2', '<'):
        $content[] = 'Fix - Deleting a block doesn\'t remove list preferences in admin view.';
        $content[] = 'Fix - Added alt and title tags to admin images.';

    case version_compare($currentVersion, '1.1.0', '<'):
        $content[] = '
<pre>
+ Added ability to pin blocks to all pages.
+ Added view permissions.
</pre>';

    case version_compare($currentVersion, '1.1.1', '<'):
        $content[] = '<pre>
1.1.1 changes
-------------';
        if (PHPWS_Boost::updateFiles(array('templates/edit.tpl'), 'block')) {
            $content[] = '+ Updated edit.tpl file copied locally.';
        } else {
            $content[] = '+ Unable to copy edit.tpl locally.';
        }

        $content[] = '+ Updated translation files.
+ Added translate functions.
+ Removed table format from edit form
</pre>';

    case version_compare($currentVersion, '1.1.2', '<'):
        PHPWS_Boost::updateFiles(array('img/block.png'), 'block');
        $content[] = '<pre>1.1.2 changes
-------------
+ Added German files
+ Use new translation format
+ Changed control panel icon
</pre>';
    }
    
    return TRUE;
}

function block_update_002(&$content)
{
    PHPWS_Core::initModClass('users', 'Users.php');
    PHPWS_User::registerPermissions('block', $content);
}

function block_update_003()
{
    $db = & new PHPWS_DB('block');
    $db->addTableColumn('key_id', 'INT DEFAULT \'0\' NOT NULL', 'id');
}

function block_update_004(&$content)
{
    $files[] = 'img/edit.png';
    $files[] = 'templates/sample.tpl';
    $result = PHPWS_Boost::updateFiles($files, 'block');
    if (PEAR::isError($result)) {
        return $result;
    }
    return true;
}


?>