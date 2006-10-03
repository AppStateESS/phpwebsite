<?php
  /**
   * update file for search
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function search_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.0.2', '<'):
        $content[] = _('Add permissions to search.');
        if (!search_update_002($content)) {
            return FALSE;
        }


    case version_compare($currentVersion, '0.0.3', '<'):
        $content[] = _('Register Search to Key.');
        if (!search_update_003($content)) {
            return FALSE;
        }


    case version_compare($currentVersion, '0.1.0', '<'):
        if (!search_update_010($content)) {
            return FALSE;
        }

    case version_compare($currentVersion, '0.1.1', '<'):
        $content[] = 'New - Retitled key unregistration function.';
    }

    return TRUE;
}

function search_update_002(&$content)
{
    return PHPWS_Boost::registerMyModule('search', 'users', $content);
}

function search_update_003(&$content)
{
    $result = Key::registerModule('search');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        $content[] = _('A problem occurred during the update.');
        return FALSE;
    }
    return TRUE;
}

function search_update_010(&$content)
{
    $db = & new PHPWS_DB('search');
    $result = $db->addTableColumn('module', 'char(40) NOT NULL');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        $content[] = 'Unable to create "module" column.';
        return FALSE;
    }

    $result = $db->addTableColumn('created', 'int NOT NULL default \'0\'');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        $content[] = 'Unable to create "created" column.';
        return FALSE;
    }

    $content[] = 'Added module and created column to search table.';

    $db->dropTableIndex('search_index');
    $result = $db->createTableIndex(array('key_id', 'module', 'created'), 'search_index');

    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        $content[] = 'Unable to create new index on search_index table.';
        return FALSE;
    }

    $content[] = 'Added new index to search table.';

    $db->addColumn('key_id');

    $all_search = $db->select('col');

    if (PEAR::isError($all_search)) {
        PHPWS_Error::log($all_search);
        $content[] = 'Unable to get information from search table.';
        return FALSE;
    }

    if (empty($all_search)) {
        $content[] = 'Nothing in search table. No conversion necessary.';
        return TRUE;
    }

    $db->reset();
    $db->setTable('phpws_key');
    $db->addColumn('id', NULL, 'key_id');
    $db->addColumn('module');
    $db->addColumn('create_date', NULL, 'created');
    $db->addWhere('id', $all_search);
    $keys = $db->select();

    if (PEAR::isError($keys)) {
        PHPWS_Error::log($keys);
        $content[] = 'Unable to get information from the key table.';
        return FALSE;
    }

    $db->reset();
    $db->setTable('search');

    foreach ($keys as $key_data) {
        extract($key_data);
        $db->addValue('module', $module);
        $db->addValue('created', $created);
        $db->addWhere('key_id', $key_id);
        $db->update();
        $db->reset();
    }

    $content[] = 'Converted keys to search.';

    return TRUE;
}

?>