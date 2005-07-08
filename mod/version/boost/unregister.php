<?php

/**
 * Removes version tables for uninstalled modules.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function version_unregister($module, &$content)
{
    $uninstall_file = PHPWS_SOURCE_DIR . 'mod/' . $module . '/boost/uninstall.sql';

    if (!is_file($uninstall_file)) {
        return;
    }

    $uninstall_sql = file($uninstall_file);

    if (empty($uninstall_file)) {
        return;
    }

    foreach ($uninstall_sql as $sql) {
        $table_name = PHPWS_DB::extractTableName($sql);

        if (empty($table_name)) {
            continue;
        }

        $version_table = $table_name . '_version';
        $version_table_seq = $version_table . '_seq';

        if (PHPWS_DB::isTable($version_table)) {
            $db = & new PHPWS_DB($version_table);
            $result = $db->dropTable();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = _('There was an error removing a version table.');
            } else {
                $content[] = sprintf(_('Version table removed: %s'), $version_table);
            }
        }

        if (PHPWS_DB::isTable($version_table_seq)) {
            $db = & new PHPWS_DB($version_table_seq);
            $result = $db->dropTable();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = _('There was an error removing a version table.');
            } else {
                $content[] = sprintf(_('Version table removed: %s'), $version_table_seq);
            }
        }

    }

}

?>