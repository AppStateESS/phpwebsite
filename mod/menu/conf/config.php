<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

  // This limit is now set in the menu settings tab. You
  // you can ignore the below
  // define('MENU_TITLE_LIMIT', 32);

define('MENU_CURRENT_LINK_STYLE', 'current-link');

define('MENU_LINK_ADD', sprintf('<img src="./images/mod/menu/gtk-add.png" title="%s" alt="%s" />', dgettext('menu', 'Add link'), dgettext('menu', 'Add link')));

define('MENU_LINK_ADD_SITE', sprintf('<img src="./images/mod/menu/offsite.png" title="%s" alt="%s" />', dgettext('menu', 'Add other link'), dgettext('menu', 'Add other link')));

define('MENU_LINK_EDIT', sprintf('<img src="./images/mod/menu/gnome-stock-edit.png" title="%s" alt="%s" />', dgettext('menu', 'Edit link title'), dgettext('menu', 'Edit link title')));

define('MENU_LINK_DELETE', sprintf('<img src="./images/mod/menu/gtk-delete.png" title="%s" alt="%s" />', dgettext('menu', 'Delete link'), dgettext('menu', 'Delete link')));

define('MENU_LINK_UP', sprintf('<img src="./images/mod/menu/gtk-go-up.png" title="%s" alt="%s" />', dgettext('menu', 'Move up'), dgettext('menu', 'Move up')));

define('MENU_LINK_DOWN', sprintf('<img src="./images/mod/menu/gtk-go-down.png" title="%s" alt="%s" />', dgettext('menu', 'Move down'), dgettext('menu', 'Move down')));

define('MENU_LINK_ADMIN', sprintf('<img src="./images/mod/menu/foo.png" title="%s" alt="%s" />', dgettext('menu', 'Admin'), dgettext('menu', 'Admin')));

define('MENU_PIN_LINK',  sprintf('<img src="./images/mod/menu/attach.png" title="%s" alt="%s" />', dgettext('menu', 'Pin links'), dgettext('menu', 'Pin links')));

define('MENU_ADMIN_ON', dgettext('menu', 'Admin mode on'));
define('MENU_ADMIN_OFF', dgettext('menu', 'Admin mode off'));

define('MENU_PIN', sprintf('<img style="float:right" src="./images/mod/menu/pin.png" alt="%s" title="%s" />', dgettext('menu', 'Pin to item'), dgettext('menu', 'Pin to item')));
define('MENU_UNPIN', sprintf('<img style="float:right" src="./images/mod/menu/remove.png" alt="%s" title="%s" />', dgettext('menu', 'Unpin menu'), dgettext('menu', 'Unpin menu')));

define('NO_POST', sprintf('<img src="./images/mod/menu/remove.png" title="%s" alt="%s" />', dgettext('menu', 'No admin options'), dgettext('menu', 'No admin options')));


?>