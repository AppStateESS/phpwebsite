<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function boost_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '1.6.1', '<'):
        $content[] = '+ Boost wasn\'t updating modules if there wasn\'t an update.php file.';
        $content[] = '+ Dependencies now checked during update.';

    case version_compare($currentVersion, '1.7.0', '<'):
        $content[] = '+ Adding dependency table for core.';
        $filename = PHPWS_SOURCE_DIR . 'mod/boost/boost/update_1_7_0.sql';
        $db = & new PHPWS_DB;
        return $db->importFile($filename);

    case version_compare($currentVersion, '1.7.1', '<'):
        $content[] = 'Add converted table.';
        $content[] = 'Add Key registration table.';
        $filename = PHPWS_SOURCE_DIR . 'mod/boost/boost/update_1_7_1.sql';
        $db = & new PHPWS_DB;
        return $db->importFile($filename);

    case version_compare($currentVersion, '1.8.0', '<'):
        $content[] = 'Added error messages.';
        $result = PHPWS_Boost::updateFiles(array('conf/error.php'), 'boost');
        if (!$result) {
            $content[] = _('Failed to update local files.');
            return FALSE;
        } elseif (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = _('Failed to update local files. Check your log.');
        } else {
            $content[] = _('Files updated successfully.');
        }

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
    }

    return TRUE;
}

?>