<?php 
  /** 
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

Layout::checkSettings();
if (Layout::isMoveBox() && Current_User::allow('layout')) {
    MiniAdmin::add('layout', sprintf('<a href="index.php?module=layout&action=admin&command=turn_off_box_move">%s</a>', _('Turn off box move')));
 }
?>