<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */


function categories_update(&$content, $currentVersion)
{

    switch ($currentVersion) {
    case version_compare($currentVersion, '2.1.0', '<'):
        $result = cat_update_210();
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = '+ changed "link" column to "url"';
 
    case version_compare($currentVersion, '2.1.1', '<'):
        $content[] = _('Add permissions to search.');
        $result = cat_update_211($content);
        if (PEAR::isError($result)) {
            return $result;
        }

    case version_compare($currentVersion, '2.1.5', '<'):
        $result = cat_update_215($content);
        if (!$result) {
            return FALSE;
        }

    case version_compare($currentVersion, '2.1.6', '<'):
        $content[] = 'Fix - Key unregisteration works properly.';
    }
    return TRUE;
}

function cat_update_210()
{
    $db = & new PHPWS_DB;
    return $db->importFile(PHPWS_SOURCE_DIR . 'mod/categories/boost/update_2_1_0.sql');
}

function cat_update_211(&$content) {
    return PHPWS_Boost::registerMyModule('categories', 'users', $content);
}

function cat_update_215(&$content) {
    $db = & new PHPWS_DB('category_items');
    $result = $db->addTableColumn('module', 'char(40) NOT NULL');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        $content[] = 'Unable to create "module" column.';
        return FALSE;
    }

    $content[] = 'Created module column in category_items table.';

    $result = $db->createTableIndex(array('cat_id', 'module'));
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        $content[] = 'Unable to create index on category_items table.';
        return FALSE;
    }

    $content[] = 'Created index on category_items table.';

    $db->addColumn('key_id');

    $all_cats = $db->select('col');

    if (PEAR::isError($all_cats)) {
        PHPWS_Error::log($all_cats);
        $content[] = 'Unable to get information from category_items table.';
        return FALSE;
    }

    if (empty($all_cats)) {
        $content[] = 'Nothing in category_items table. No conversion necessary.';
        return TRUE;
    }

    $db->reset();
    $db->setTable('phpws_key');
    $db->addColumn('id', NULL, 'key_id');
    $db->addColumn('module');
    $db->addWhere('id', $all_cats);
    $keys = $db->select();

    if (PEAR::isError($keys)) {
        PHPWS_Error::log($keys);
        $content[] = 'Unable to get information from the key table.';
        return FALSE;
    }

    $db->reset();
    $db->setTable('category_items');

    if (empty($keys)) {
        return TRUE;
    }

    foreach ($keys as $key_data) {
        extract($key_data);
        $db->addValue('module', $module);
        $db->addWhere('key_id', $key_id);
        $db->update();
        $db->reset();
    }

    $content[] = 'Converted keys in category_items.';

    return TRUE;

}

?>