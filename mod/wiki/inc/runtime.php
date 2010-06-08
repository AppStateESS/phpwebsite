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
 * @author      Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
 * @version     $Id: runtime.php,v 1.19 2006/08/14 03:53:06 blindman1344 Exp $
 */

Core\Core::configRequireOnce('wiki', 'config.php');

// Display on the home page if option is set
if (!isset($_REQUEST['module']) && Core\Settings::get('wiki', 'show_on_home'))
{
    Core\Core::initModClass('wiki', 'WikiManager.php');
    WikiManager::action();
}

?>