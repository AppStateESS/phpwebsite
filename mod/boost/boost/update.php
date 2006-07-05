<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function boost_update(&$content, $currentVersion)
{
    switch ($currentVersion) {

    case version_compare($currentVersion, '1.8.1', '<'):
        $db = & new PHPWS_DB('phpws_key');
        $result = $db->addTableColumn('creator_id', 'int NOT NULL default \'0\'', 'creator');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to create creator_id column in phpws_key table.';
            return FALSE;
        }

        $result = $db->addTableColumn('updater_id', 'int NOT NULL default \'0\'', 'updater');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to create updater_id column in phpws_key table.';
            return FALSE;
        }
        $content[] = 'Added creator and updater id columns to phpws_key.';

        $result = $db->createTableIndex(array('restricted', 'active', 'module', 'create_date', 'update_date'));
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to create index in phpws_key table.';
            return FALSE;
        }
        $content[] = 'Added index to phpws_key table.';

        $db->setTable('phpws_key_edit');
        $result = $db->createTableIndex('key_id');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to create index in phpws_key_edit table.';
            return FALSE;
        }

        $db->setTable('phpws_key_view');
        $result = $db->createTableIndex('key_id');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to create index in phpws_key_view table.';
            return FALSE;
        }

        $content[] = 'Added index to phpws_key_edit and phpws_key_view.';
        
        $db->setTable('mod_settings');
        $result = $db->createTableIndex(array('module', 'setting_name'));
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to create index in mod_settings table.';
            return FALSE;
        }
        $content[] = 'Added index to mod_settings.';

    case version_compare($currentVersion, '1.8.5', '<'):
        $files = array();
        $files[] = 'templates/check_update.tpl';
        if (!PHPWS_Boost::updateFiles($files, 'boost')) {
            $content[] = 'Unable to update local template files.';
        }
        $content[] = '+ Added a "core" update option.';
        $content[] = '+ Fixed check not listing changes in updated modules.';
        $content[] = '+ Fixed file updates not copying if in subdirectory.';
        $content[] = '+ Updated modules now show current version and version to which they will be updated.';

    case version_compare($currentVersion, '1.8.6', '<'):
        $content[] = '+ Added core update functionality.';
        $content[] = '+ Authorization keys added to links for extra security.';
    }

    return TRUE;
}

?>