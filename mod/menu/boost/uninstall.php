<?php

/**
 * Uninstall file for menu
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function menu_uninstall(&$content)
{
    \core\DB::dropTable('menu_links');
    \core\DB::dropTable('menus');
    \core\DB::dropTable('menu_assoc');

    $content[] = dgettext('menu', 'Menu tables removed.');

    return TRUE;
}
?>
