<?php

  /**
   * Uninstall file for menu
   * 
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function menu_uninstall(&$content)
{
    PHPWS_DB::dropTable('menu_links');
    PHPWS_DB::dropTable('menus');
    PHPWS_DB::dropTable('menu_assoc');
    translate('menu');
    $content[] = _('Menu tables removed.');
    translate();
    return TRUE;
}


?>
