<?php

/**
 * Wiki for phpWebSite
 *
 * See docs/CREDITS for copyright information
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package Wiki
 * @author Greg Meiste <greg.meiste+github@gmail.com>
 */

$use_permissions = TRUE;

$permissions['edit_page']     = dgettext('wiki', 'Add/Edit Pages');
$permissions['delete_page']   = dgettext('wiki', 'Delete Pages');
$permissions['toggle_lock']   = dgettext('wiki', 'Toggle Page Locks');
$permissions['upload_images'] = dgettext('wiki', 'Upload Images');
$permissions['edit_settings'] = dgettext('wiki', 'Edit Settings');

//$item_permissions = TRUE;

?>