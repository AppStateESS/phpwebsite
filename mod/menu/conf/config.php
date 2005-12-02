<?php

translate('menu');

  // Limits the number of characters a menu link can be
  // 0 means no limit
define('MENU_TITLE_LIMIT', 20);

define('MENU_CURRENT_LINK_STYLE', 'current-link');

define('MENU_LINK_ADD', sprintf('<img src="./images/mod/menu/gtk-add.png" title="%s" alt="%s" />', _('Add Link'), _('Add Link')));

define('MENU_LINK_ADD_OFFSITE', sprintf('<img src="./images/mod/menu/offsite.png" title="%s" alt="%s" />', _('Add Offsite Link'), _('Add Offsite Link')));

define('MENU_LINK_EDIT', sprintf('<img src="./images/mod/menu/gnome-stock-edit.png" title="%s" />', _('Edit Link Title')));

define('MENU_LINK_DELETE', sprintf('<img src="./images/mod/menu/gtk-delete.png" title="%s" />', _('Delete Link')));

define('MENU_LINK_UP', sprintf('<img src="./images/mod/menu/gtk-go-up.png" title="%s" />', _('Move Up')));

define('MENU_LINK_DOWN', sprintf('<img src="./images/mod/menu/gtk-go-down.png" title="%s" />', _('Move Down')));

define('MENU_LINK_ADMIN', sprintf('<img src="./images/mod/menu/foo.png" title="%s" />', _('Admin')));

define('MENU_ADMIN_ON', _('Admin mode on'));
define('MENU_ADMIN_OFF', _('Admin mode off'));

define('MENU_PIN', sprintf('<img style="float:right" src="./images/mod/menu/pin.png" title=%s" />', _('Pin to item')));
define('MENU_UNPIN', sprintf('<img style="float:right" src="./images/mod/menu/remove.png" title=%s" />', _('Unpin menu')));

translate();
?>