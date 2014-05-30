<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

function analytics_uninstall(&$content)
{
    PHPWS_DB::dropTable('analytics_tracker_owa');
    PHPWS_DB::dropTable('analytics_tracker_piwik');
    PHPWS_DB::dropTable('analytics_tracker_google');
    PHPWS_DB::dropTable('analytics_tracker');
    $content[] = dgettext('analytics', 'Analytics tables removed.');
    return TRUE;
}
?>
