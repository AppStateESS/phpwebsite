<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

function analytics_update(&$content, $currentVersion)
{
    switch($currentVersion) {
        case version_compare($currentVersion, '1.0.1', '<'):
            $db = new PHPWS_DB('analytics_tracker');
            $result = $db->addTableColumn('disable_if_logged', 'int NOT NULL default 0');
            if(PHPWS_Error::logIfError($result)) {
                $content[] = 'Unable to add disable_if_logged column to analytics_tracker table.';
                return false;
            }

            $files = array('templates/edit.tpl');
            if(PHPWS_Boost::updateFiles($files, 'analytics')) {
                $content[] = '--- Updated templates/edit.tpl';
            }

        case version_compare($currentVersion, '1.1.0', '<'):
            // install.sql has been wrong for awhile, this should fix any discrepancies
            $db = new PHPWS_DB('analytics_tracker');
            if(!$db->isTableColumn('disable_if_logged')) {
                $result = $db->addTableColumn('disable_if_logged', 'int NOT NULL default 0');
                if(PHPWS_Error::logIfError($result)) {
                    $content[] = 'Unable to add disable_if_logged column to analytics_tracker table.';
                    return false;
                }
                $content[] = '--- Added disable_if_logged option to database';
            }

            // Load new schema
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/analytics/boost/update/1.1.0.sql');
            if(PHPWS_Error::logIfError($result)) {
                $content[] = 'Unable to import updated schema for version 1.1.0.';
                return false;
            }
            $content[] = '--- Updated Analytics schema to 1.1.0';

            // Move Google Analytics data to its own table
            $db = new PHPWS_DB('analytics_tracker');
            $db->addColumn('id');
            $db->addColumn('account');
            $db->addWhere('type', 'GoogleAnalyticsTracker');
            $result = $db->select();
            if(PHPWS_Error::logIfError($result)) {
                $content[] = 'Unable to select Google Analytics tracker from analytics_tracker table.';
                return false;
            }

            foreach($result as $row) {
                $db = new PHPWS_DB('analytics_tracker_google');
                $db->addValue('id', $row['id']);
                // Adding UA- into the account identifier to reduce confusion
                $db->addValue('account', 'UA-' . $row['account']);
                $db->insert(false);
                $content[] = "--- Migrated Google Analytics configuration for account UA-{$row['account']}";
            }

            $db = new PHPWS_DB('analytics_tracker');
            $result = $db->dropTableColumn('account');
            if(PHPWS_Error::logIfError($result)) {
                $content[] = 'Unable to remove account column from analytics_tracker table.';
                return false;
            }
            $content[] = '--- Completed migration to Analytics 1.1.0 schema';

        case version_compare($currentVersion, '1.1.1', '<'):
            $content[] = <<<EOF
<pre>Version 1.1.1
-------------------
+ Piwik fix.
+ Fixed uninstall script
</pre>
EOF;

    }

    return true;
}

?>
