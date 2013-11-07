<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
// This limit is now set in the menu settings tab. You
// you can ignore the below
// define('MENU_TITLE_LIMIT', 32);

define('MENU_CURRENT_LINK_STYLE', 'current-link active');

define('MENU_LINK_ADD',
        sprintf('<i class="fa fa-plus" title="%s"></i>',
                dgettext('menu', 'Add link')));

define('MENU_LINK_ADD_SITE',
        sprintf('<img src="%smod/menu/img/icon_link.png" title="%s" alt="%s" />',
                PHPWS_SOURCE_HTTP, dgettext('menu', 'Add offsite link'),
                dgettext('menu', 'Add offsite link')));

define('MENU_SUBLINK_ADD',
                sprintf('<i class="fa fa-plus" title="%s"></i>',
                dgettext('menu', 'Add sub-link')));

define('MENU_SUBLINK_ADD_SITE',
                sprintf('<i class="fa fa-link" title="%s"></i>',
                dgettext('menu', 'Add offsite sub-link')));

define('MENU_LINK_EDIT',
        sprintf('<i class="fa fa-edit" title="%s"></i>',
                dgettext('menu', 'Edit link title')));

define('MENU_LINK_INDENT_INCREASE',
        sprintf('<i class="fa fa-chevron-right" title="%s"></i>',
                dgettext('menu', 'Increase indent')));

define('MENU_LINK_INDENT_DECREASE',
        sprintf('<i class="fa fa-chevron-left" title="%s"></i>',
                dgettext('menu', 'Decrease indent')));

define('MENU_LINK_DELETE',
        sprintf('<i class="fa fa-trash-o" title="%s"></i>',
                dgettext('menu', 'Delete link')));

define('MENU_LINK_UP',
        sprintf('<i class="fa fa-chevron-up" title="%s"></i>',
                dgettext('menu', 'Move up')));

define('MENU_LINK_DOWN',
        sprintf('<i class="fa fa-chevron-down" title="%s"></i>',
                dgettext('menu', 'Move down')));

define('MENU_LINK_ADMIN',
        sprintf('<img src="%smod/menu/img/foo.png" title="%s" alt="%s" />',
                PHPWS_SOURCE_HTTP, dgettext('menu', 'Admin'),
                dgettext('menu', 'Admin')));

define('MENU_PIN_LINK',
        '<i class="fa fa-paste" title="' . dgettext('menu', 'Paste page link') . '"></i>');

define('MENU_ADMIN_ON',
        '<i class="fa fa-power-off" style="color : green"></i> ' . dgettext('menu', 'Edit menu'));
define('MENU_ADMIN_OFF',
        '<i class="fa fa-power-off" style="color : red"></i> ' . dgettext('menu', 'Stop editing menu'));

define('MENU_PIN',
        '<i class="fa fa-flag"></i> ' . dgettext('menu', 'Pin to page'));
define('MENU_UNPIN',
        sprintf('<img style="float:right" src="%smod/menu/img/remove.png" alt="%s" title="%s" />',
                PHPWS_SOURCE_HTTP, dgettext('menu', 'Unpin menu'),
                dgettext('menu', 'Unpin menu')));

define('NO_POST',
        sprintf('<img src="%smod/menu/img/remove.png" title="%s" alt="%s" />',
                PHPWS_SOURCE_HTTP, dgettext('menu', 'No admin options'),
                dgettext('menu', 'No admin options')));
?>
