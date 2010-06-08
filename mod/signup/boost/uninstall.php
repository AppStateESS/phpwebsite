<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function signup_uninstall(&$content) {
    Core\DB::dropTable('signup_sheet');
    Core\DB::dropTable('signup_peeps');
    Core\DB::dropTable('signup_slots');
    $content[] = dgettext('signup', 'Signup tables dropped.');
    return true;
}
?>