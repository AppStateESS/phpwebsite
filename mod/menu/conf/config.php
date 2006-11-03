<?php

translate('menu');

  // Limits the number of characters a menu link can be
  // 0 means no limit
define('MENU_TITLE_LIMIT', 20);

define('MENU_CURRENT_LINK_STYLE', 'current-link');

define('MENU_LINK_ADD', sprintf('<img src="./images/mod/menu/gtk-add.png" title="%s" alt="%s" />', _('Add link'), _('Add link')));

define('MENU_LINK_ADD_SITE', sprintf('<img src="./images/mod/menu/offsite.png" title="%s" alt="%s" />', _('Add other link'), _('Add other link')));

define('MENU_LINK_EDIT', sprintf('<img src="./images/mod/menu/gnome-stock-edit.png" title="%s" alt="%s" />', _('Edit link title'), _('Edit link title')));

define('MENU_LINK_DELETE', sprintf('<img src="./images/mod/menu/gtk-delete.png" title="%s" alt="%s" />', _('Delete link'), _('Delete link')));

define('MENU_LINK_UP', sprintf('<img src="./images/mod/menu/gtk-go-up.png" title="%s" alt="%s" />', _('Move up'), _('Move up')));

define('MENU_LINK_DOWN', sprintf('<img src="./images/mod/menu/gtk-go-down.png" title="%s" alt="%s" />', _('Move down'), _('Move down')));

define('MENU_LINK_ADMIN', sprintf('<img src="./images/mod/menu/foo.png" title="%s" alt="%s" />', _('Admin'), _('Admin')));

define('MENU_ADMIN_ON', _('Admin mode on'));
define('MENU_ADMIN_OFF', _('Admin mode off'));

define('MENU_PIN', sprintf('<img style="float:right" src="./images/mod/menu/pin.png" title=%s" />', _('Pin to item')));
define('MENU_UNPIN', sprintf('<img style="float:right" src="./images/mod/menu/remove.png" title=%s" />', _('Unpin menu')));

translate();
?>