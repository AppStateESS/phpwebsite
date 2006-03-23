<?php 
  /** 
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

Layout::checkSettings();
if (Current_User::allow('layout')) {
    if (Layout::isMoveBox()) {
        MiniAdmin::add('layout', sprintf('<a href="index.php?module=layout&action=admin&command=turn_off_box_move">%s</a>', _('Turn off box move')));
    }
 }

if (Current_User::allow('layout')) {
    Layout::styleChangeLink();
 }

?>