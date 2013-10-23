<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

// This limit is now set in the menu settings tab. You
// you can ignore the below
// define('MENU_TITLE_LIMIT', 32);

define('MENU_CURRENT_LINK_STYLE', 'current-link active');

define('MENU_LINK_ADD', sprintf('<img src="%smod/menu/img/gtk-add.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Add link'), dgettext('menu', 'Add link')));

define('MENU_LINK_ADD_SITE', sprintf('<img src="%smod/menu/img/icon_link.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Add offsite link'), dgettext('menu', 'Add offsite link')));

define('MENU_SUBLINK_ADD', sprintf('<img src="%smod/menu/img/gtk-add.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Add sub-link'), dgettext('menu', 'Add sub-link')));

define('MENU_SUBLINK_ADD_SITE', sprintf('<img src="%smod/menu/img/icon_link.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Add offsite sub-link'), dgettext('menu', 'Add offsite sub-link')));

define('MENU_LINK_EDIT', sprintf('<img src="%smod/menu/img/gnome-stock-edit.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Edit link title'), dgettext('menu', 'Edit link title')));

define('MENU_LINK_INDENT_INCREASE', sprintf('<img src="%smod/menu/img/right.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Increase indent'), dgettext('menu', 'Increase indent')));

define('MENU_LINK_INDENT_DECREASE', sprintf('<img src="%smod/menu/img/left.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Decrease indent'), dgettext('menu', 'Decrease indent')));

define('MENU_LINK_DELETE', sprintf('<img src="%smod/menu/img/gtk-delete.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Delete link'), dgettext('menu', 'Delete link')));

define('MENU_LINK_UP', sprintf('<img src="%smod/menu/img/gtk-go-up.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Move up'), dgettext('menu', 'Move up')));

define('MENU_LINK_DOWN', sprintf('<img src="%smod/menu/img/gtk-go-down.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Move down'), dgettext('menu', 'Move down')));

define('MENU_LINK_ADMIN', sprintf('<img src="%smod/menu/img/foo.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Admin'), dgettext('menu', 'Admin')));

define('MENU_PIN_LINK',  sprintf('<img src="%smod/menu/img/attach.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Pin links'), dgettext('menu', 'Pin links')));

define('MENU_ADMIN_ON', '<i class="fasfa-off"></i>' . dgettext('menu', 'Enable Menu Editing'));
define('MENU_ADMIN_OFF', '<i class="fasfa-off"></i>' . dgettext('menu', 'Disable Menu Editing'));

define('MENU_PIN', sprintf('<img style="float:right" src="%smod/menu/img/pin.png" alt="%s" title="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Pin to item'), dgettext('menu', 'Pin to item')));
define('MENU_UNPIN', sprintf('<img style="float:right" src="%smod/menu/img/remove.png" alt="%s" title="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'Unpin menu'), dgettext('menu', 'Unpin menu')));

define('NO_POST', sprintf('<img src="%smod/menu/img/remove.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, dgettext('menu', 'No admin options'), dgettext('menu', 'No admin options')));


?>
