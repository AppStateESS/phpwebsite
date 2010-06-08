<?php

/**
 * Uninstall file for menu
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function menu_uninstall(&$content)
{
    Core\DB::dropTable('menu_links');
    Core\DB::dropTable('menus');
    Core\DB::dropTable('menu_assoc');

    $content[] = dgettext('menu', 'Menu tables removed.');

    return TRUE;
}
?>
