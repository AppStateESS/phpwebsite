<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function signup_uninstall(&$content) {
    \core\DB::dropTable('signup_sheet');
    \core\DB::dropTable('signup_peeps');
    \core\DB::dropTable('signup_slots');
    $content[] = dgettext('signup', 'Signup tables dropped.');
    return true;
}
?>