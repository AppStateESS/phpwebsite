<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function alert_uninstall(&$content)
{
    PHPWS_DB::dropTable('alert_participant');
    PHPWS_DB::dropTable('alert_prt_to_type');
    PHPWS_DB::dropTable('alert_contact');
    PHPWS_DB::dropTable('alert_item');
    PHPWS_DB::dropTable('alert_type');
    return true;
}
?>