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
        if (!stristr($sql, 'drop')) {
            continue;
        }

        $table_name = PHPWS_DB::extractTableName($sql);

        if (empty($table_name)) {
            continue;
        }

        $version_table = $table_name . '_version';
        $version_table_seq = $version_table . '_seq';

        $result = PHPWS_DB::dropTable($version_table);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = _('There was an error removing a version table.');
        } else {
            $content[] = sprintf(_('Version table removed: %s'), $version_table);
        }
    }

}

?>