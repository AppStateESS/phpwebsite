<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function signup_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '1.0.1', '<'):
        $content[] = '<pre>';

        $files = array('templates/slot_setup.tpl');
        signupUpdateFiles($files, $content);
        
        $content[] = '1.0.1 changes
----------------
+ Added ability to reset slot order should it come unraveled.
+ Fixed reroute link that was hard coded to go to sheet id 1.</pre>';

    case version_compare($currentVersion, '1.0.2', '<'):
        $content[] = '<pre>1.0.2 changes
----------------
+ Changed email to send individually.
+ Fixed: "All slots full" message was not displaying.</pre>';

    case version_compare($currentVersion, '1.1.0', '<'):
        $content[] = '<pre>';
        PHPWS_Boost::registerMyModule('signup', 'users', $content);
        $db = new PHPWS_DB('signup_sheet');
        if (PHPWS_Error::logIfError($db->addTableColumn('contact_email', 'varchar(255) default NULL'))) {
            $content[] = '--- Failed creating new column on signup_sheet.</pre>';
            return false;
        } else {
            $content[] = '--- contact_email column created successfully on signup_sheet table.';
        }
        $content[] = '1.1.2 changes
----------------
</pre>';


    }
    return true;
}

function signupUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'signup')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
}


?>